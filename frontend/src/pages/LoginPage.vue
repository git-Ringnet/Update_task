<template>
  <div class="min-h-screen bg-[#f3faf6] flex items-center justify-center p-4 relative overflow-hidden select-none">

    <!-- Background organic shapes/grid to make it look premium -->
    <div
      class="absolute inset-0 z-0 bg-[radial-gradient(#dcfce7_1.2px,transparent_1.2px)] [background-size:24px_24px] opacity-60">
    </div>
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-100 rounded-full blur-3xl opacity-60"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-100 rounded-full blur-3xl opacity-60"></div>

    <!-- Login card container -->
    <div
      class="relative z-10 w-full max-w-[400px] bg-white rounded-3xl border border-emerald-100/80 shadow-2xl p-8 sm:p-9 transition-all duration-300 hover:shadow-emerald-100/40">

      <!-- Brand Logo / Identity Header -->
      <div class="text-center mb-6">
        <div class="inline-block transform hover:scale-105 transition-transform duration-200">
          <CactusLogo />
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 font-heading mt-4 leading-tight">Hệ thống Quản lý</h2>
        <p class="text-gray-400 text-xs mt-1 font-bold">Xương Rồng Project Management</p>
      </div>

      <!-- General Error Message Alert Banner -->
      <transition enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0">
        <div v-if="error"
          class="mb-5 p-3.5 bg-rose-50 border border-rose-100 rounded-2xl flex items-start gap-2.5 text-xs text-rose-600 font-semibold">
          <i class="fa-solid fa-circle-exclamation text-sm mt-0.5"></i>
          <span>{{ error }}</span>
        </div>
      </transition>

      <!-- App Features Checklist Intro -->
      <div class="my-6 space-y-3 bg-[#fbfdfc] border border-emerald-50/60 p-4.5 rounded-2xl text-left shadow-3xs">
        <div class="flex items-center gap-3 text-xs text-gray-600 font-bold">
          <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fa-solid fa-check"></i>
          </span>
          <span>Quản lý dự án & công việc tập trung</span>
        </div>
        <div class="flex items-center gap-3 text-xs text-gray-600 font-bold">
          <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fa-solid fa-check"></i>
          </span>
          <span>Cập nhật hoạt động tiến độ realtime</span>
        </div>
        <div class="flex items-center gap-3 text-xs text-gray-600 font-bold">
          <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fa-solid fa-check"></i>
          </span>
          <span>Trao đổi thảo luận nhóm tức thời</span>
        </div>
      </div>

      <!-- Social Logins Section (Google Only) -->
      <div class="mt-6">
        <!-- Real Google sign-in button container -->
        <div class="mt-4 flex justify-center">
          <div id="google-signin-btn" class="flex justify-center w-full"></div>
        </div>

        <!-- Fallback test logins link -->
        <div class="mt-6 text-center">
          <button @click="showGooglePicker = true" type="button"
            class="text-xs font-bold text-emerald-700 hover:text-emerald-950 transition-colors focus:outline-none cursor-pointer">
            Đăng nhập nhanh bằng tài khoản test (Demo)
          </button>
        </div>
      </div>

      <!-- Footer Info Note -->
      <div class="mt-6 pt-5 border-t border-gray-100 text-[10px] text-gray-400 font-bold text-center select-none">
        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>
        Đăng nhập bảo mật qua Google OAuth 2.0
      </div>

    </div>

    <!-- Google OAuth Simulation Account Chooser Modal -->
    <div v-if="showGooglePicker" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs" @click="showGooglePicker = false"></div>

      <!-- Modal card -->
      <div
        class="relative bg-white rounded-2xl w-full max-w-sm shadow-2xl border border-gray-100 overflow-hidden z-10 animate-fade-in-up">
        <!-- Modal Header -->
        <div class="p-5 border-b border-gray-100 text-center bg-gray-50/50">
          <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google"
            class="w-7 h-7 mx-auto mb-2" />
          <h3 class="text-base font-bold text-gray-900">Chọn tài khoản Google</h3>
          <p class="text-xs text-gray-400 mt-0.5">để tiếp tục đăng nhập vào Xương Rồng</p>
        </div>

        <!-- Accounts list -->
        <div class="max-h-[300px] overflow-y-auto divide-y divide-gray-100">
          <button v-for="acc in googleAccounts" :key="acc.email" @click="handleGoogleSelect(acc)" type="button"
            class="w-full px-5 py-3.5 text-left hover:bg-emerald-50/20 flex items-center gap-3.5 transition-colors group cursor-pointer focus:outline-none">
            <img :src="acc.avatar" :alt="acc.name"
              class="w-9 h-9 rounded-full object-cover border border-emerald-100 flex-shrink-0" />
            <div class="min-w-0 flex-1">
              <div class="text-sm font-bold text-gray-900 group-hover:text-emerald-800 transition-colors">{{ acc.name }}
              </div>
              <div class="text-xs text-gray-400 truncate">{{ acc.email }}</div>
            </div>
            <i
              class="fa-solid fa-chevron-right text-[10px] text-gray-300 group-hover:text-emerald-600 transition-transform"></i>
          </button>
        </div>

        <!-- Custom Mock Login Input -->
        <div class="p-4 bg-gray-50 border-t border-gray-100">
          <div class="text-xs font-bold text-gray-400 mb-2 select-none uppercase">Đăng nhập tài khoản khác</div>
          <div class="flex gap-2">
            <input v-model="customGoogleEmail" type="email" placeholder="VD: guest@gmail.com"
              class="flex-1 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold focus:outline-none focus:border-emerald-500 shadow-3xs" />
            <button @click="handleCustomGoogle" type="button"
              class="px-3 py-1.5 bg-[#2d8a39] hover:bg-[#236e2d] text-white font-bold text-xs rounded-lg transition-colors cursor-pointer">
              Chọn
            </button>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-end">
          <button @click="showGooglePicker = false" type="button"
            class="text-xs font-bold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg transition-colors cursor-pointer">
            Đóng
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import CactusLogo from '../components/CactusLogo.vue'

const authStore = useAuthStore()
const router = useRouter()

const error = ref('')

const showGooglePicker = ref(false)
const customGoogleEmail = ref('')

/* global google */

const handleGoogleCallback = async (response) => {
  error.value = ''
  try {
    const res = await axios.post('/api/google-login-real', {
      id_token: response.credential
    })
    authStore.setAuth(res.data.user, res.data.token)
    router.push('/views')
  } catch (err) {
    error.value = err.response?.data?.message || 'Đăng nhập Google thất bại'
  }
}

onMounted(() => {
  const initGoogleBtn = () => {
    if (typeof google !== 'undefined') {
      google.accounts.id.initialize({
        client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID || '490106347668-6odqmcnvrkq8g6opuhu0idaf6joesdn6.apps.googleusercontent.com',
        callback: handleGoogleCallback
      })
      google.accounts.id.renderButton(
        document.getElementById('google-signin-btn'),
        {
          theme: 'outline',
          size: 'large',
          width: '320',
          text: 'signin_with',
          shape: 'pill',
          alignment: 'center'
        }
      )
    } else {
      setTimeout(initGoogleBtn, 100)
    }
  }
  initGoogleBtn()
})

// Pre-seeded Google accounts details
const googleAccounts = [
  {
    name: 'Minh',
    email: 'minh@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'An',
    email: 'an@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Khoa',
    email: 'khoa@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=120'
  }
]

const handleGoogleSelect = async (account) => {
  error.value = ''
  showGooglePicker.value = false
  try {
    await authStore.googleLogin(account.email, account.name, account.avatar)
    router.push('/views')
  } catch (err) {
    error.value = err
  }
}

const handleCustomGoogle = async () => {
  if (!customGoogleEmail.value.trim()) return

  const email = customGoogleEmail.value.trim()
  const name = email.split('@')[0]
  const avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=120' // default avatar

  error.value = ''
  showGooglePicker.value = false
  try {
    await authStore.googleLogin(email, name, avatar)
    router.push('/views')
  } catch (err) {
    error.value = err
  }
}
</script>
