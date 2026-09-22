<template>
  <div class="min-h-screen bg-[#f3faf6] flex items-center justify-center p-4 relative overflow-hidden select-none">

    <!-- Background organic shapes/grid -->
    <div
      class="absolute inset-0 z-0 bg-[radial-gradient(#dcfce7_1.2px,transparent_1.2px)] [background-size:24px_24px] opacity-60">
    </div>
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-100 rounded-full blur-3xl opacity-60"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-100 rounded-full blur-3xl opacity-60"></div>

    <!-- Login card container -->
    <div
      class="relative z-10 w-full max-w-[420px] bg-white rounded-3xl border border-emerald-100/80 shadow-2xl p-7 sm:p-9 transition-all duration-300">

      <!-- Brand Logo / Identity Header -->
      <div class="text-center mb-6">
        <div class="inline-block transform hover:scale-105 transition-transform duration-200">
          <CactusLogo />
        </div>
        <h2 class="text-2xl font-black text-gray-900 font-heading mt-3 leading-tight">Hệ thống Quản lý</h2>
        <p class="text-gray-500 text-xs mt-1 font-bold">Xương Rồng Project Management · Đăng Nhập</p>
      </div>

      <!-- General Error Message Alert Banner -->
      <transition enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0">
        <div v-if="error"
          class="mb-4 p-3 bg-rose-50 border border-rose-100 rounded-2xl flex items-start gap-2.5 text-xs text-rose-600 font-semibold">
          <i class="fa-solid fa-circle-exclamation text-sm mt-0.5"></i>
          <span>{{ error }}</span>
        </div>
      </transition>

      <!-- CREDENTIALS FORM -->
      <form @submit.prevent="handleManualLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Tên đăng nhập hoặc Email</label>
          <input 
            v-model="loginForm.username" 
            type="text" 
            required 
            placeholder="Nhập tên đăng nhập hoặc email..." 
            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Mật khẩu</label>
          <div class="relative">
            <input 
              ref="passwordInputRef"
              v-model="loginForm.password" 
              :type="showPassword ? 'text' : 'password'" 
              required 
              placeholder="Nhập mật khẩu..." 
              class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
            />
            
            <!-- EYE TOGGLE BUTTON -->
            <button 
              @click="showPassword = !showPassword" 
              type="button" 
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-600 transition-colors p-1 cursor-pointer"
              :title="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
            >
              <i :class="showPassword ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm'"></i>
            </button>
          </div>
        </div>

        <button 
          type="submit" 
          :disabled="authStore.isLoading"
          class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 mt-2"
        >
          <i v-if="authStore.isLoading" class="fa-solid fa-circle-notch animate-spin text-xs"></i>
          <span>Đăng nhập hệ thống</span>
        </button>
      </form>

      <!-- Footer Info Note -->
      <div class="mt-6 pt-4 border-t border-gray-100 text-[10px] text-gray-400 font-bold text-center select-none">
        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>
        Hệ thống đăng nhập nội bộ Xương Rồng
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import CactusLogo from '../components/CactusLogo.vue'

const authStore = useAuthStore()
const router = useRouter()

const error = ref('')
const showPassword = ref(false)
const passwordInputRef = ref(null)

const loginForm = reactive({
  username: '',
  password: ''
})

const handleManualLogin = async () => {
  if (!loginForm.username.trim() || !loginForm.password.trim()) return
  error.value = ''
  try {
    await authStore.login(loginForm.username.trim(), loginForm.password.trim())
    router.push('/views')
  } catch (err) {
    error.value = err || 'Đăng nhập thất bại'
  }
}
</script>
