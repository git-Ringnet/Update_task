<template>
  <div>
    <router-view />

    <!-- Toast Notifications Container -->
    <div class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none max-w-sm w-full">
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
  </div>
</template>

<script setup>
import { useToastStore } from './stores/toast'
const toastStore = useToastStore()
</script>
