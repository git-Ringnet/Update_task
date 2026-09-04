<template>
  <div>
    <router-view />

    <!-- Toast Notifications Container -->
    <div class="fixed top-5 left-1/2 sm:left-auto sm:right-5 -translate-x-1/2 sm:translate-x-0 z-[9999] space-y-3 pointer-events-none w-full max-w-[calc(100%-32px)] sm:max-w-sm">
      <transition-group 
        enter-active-class="transition duration-300 ease-out transform"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition duration-200 ease-in transform"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div 
          v-for="t in toastStore.toasts" 
          :key="t.id" 
          class="pointer-events-auto w-full bg-white/95 backdrop-blur-md rounded-2xl border p-4 shadow-xl flex items-start gap-3 transition-all duration-300"
          :class="{
            'border-emerald-100 shadow-emerald-100/30': t.type === 'success',
            'border-rose-100 shadow-rose-100/30': t.type === 'error',
            'border-amber-100 shadow-amber-100/30': t.type === 'warning'
          }"
        >
          <!-- Status icon -->
          <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs"
            :class="{
              'bg-emerald-50 text-emerald-600': t.type === 'success',
              'bg-rose-50 text-rose-500': t.type === 'error',
              'bg-amber-50 text-amber-600': t.type === 'warning'
            }"
          >
            <i class="fa-solid" :class="{
              'fa-circle-check': t.type === 'success',
              'fa-circle-xmark': t.type === 'error',
              'fa-circle-exclamation': t.type === 'warning'
            }"></i>
          </span>

          <!-- Content -->
          <div class="flex-1">
            <p class="text-sm font-bold text-gray-900 leading-snug">{{ t.message }}</p>
          </div>

          <!-- Close button -->
          <button 
            @click="toastStore.remove(t.id)" 
            type="button" 
            class="text-gray-400 hover:text-gray-600 cursor-pointer flex-shrink-0 focus:outline-none p-0.5 rounded-lg hover:bg-gray-50"
          >
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Beautiful Confirm Dialog Container -->
    <transition
      enter-active-class="transition duration-300 ease-out animate-fade-in"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="confirmStore.isOpen" class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-xs" @click="confirmStore.cancel"></div>

        <!-- Dialog Box -->
        <div class="relative bg-white rounded-3xl p-7 sm:p-8 shadow-2xl border border-gray-100 w-full max-w-md text-left animate-fade-in-up">
          <div class="flex items-start gap-4 sm:gap-5">
            <!-- Warning Icon -->
            <span class="flex-shrink-0 w-14 h-14 rounded-full bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center text-2xl shadow-3xs">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </span>

            <div class="flex-1 min-w-0 pt-0.5">
              <h3 class="text-[20px] sm:text-[22px] font-black text-gray-900 font-heading leading-snug">
                {{ confirmStore.title }}
              </h3>
              <p class="text-[15px] sm:text-[16px] font-medium text-gray-600 mt-2 leading-relaxed break-words">
                {{ confirmStore.message }}
              </p>
            </div>
          </div>

          <div class="mt-7 flex items-center justify-end gap-3">
            <button
              @click="confirmStore.cancel"
              type="button"
              class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-extrabold text-[15px] rounded-xl shadow-3xs transition-colors cursor-pointer focus:outline-none"
            >
              Hủy
            </button>
            <button
              @click="confirmStore.confirm"
              type="button"
              class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-[15px] rounded-xl shadow-3xs hover:shadow-2xs transition-all cursor-pointer focus:outline-none"
            >
              Đồng ý
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from './stores/toast'
import { useConfirmStore } from './stores/confirm'
import { useAuthStore } from './stores/auth'
import { useBrowserNotificationStore } from './stores/browserNotifications'

const toastStore = useToastStore()
const confirmStore = useConfirmStore()
const router = useRouter()
const authStore = useAuthStore()
const browserNotifications = useBrowserNotificationStore()

const requestBrowserNotificationPermission = () => {
  // Existing users may already have granted the old foreground-notification
  // permission. Re-run setup so their browser is registered for Web Push too.
  if (authStore.user?.id && browserNotifications.appEnabled && browserNotifications.permission !== 'denied') {
    browserNotifications.requestPermission()
  }
}

const handleGlobalKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'h') {
    e.preventDefault()
    router.push('/views')
  }
}

const preventGlobalDrop = (e) => {
  if (e.dataTransfer?.types?.includes('Files')) {
    e.preventDefault()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown)
  window.addEventListener('dragover', preventGlobalDrop)
  window.addEventListener('drop', preventGlobalDrop)
  browserNotifications.refreshPermission()
  requestBrowserNotificationPermission()
})

// Also request immediately after a successful login, not only after a page refresh.
watch(() => authStore.user?.id, (userId) => {
  if (userId) requestBrowserNotificationPermission()
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
  window.removeEventListener('dragover', preventGlobalDrop)
  window.removeEventListener('drop', preventGlobalDrop)
})
</script>
