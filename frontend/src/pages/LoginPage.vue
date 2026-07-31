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
      class="relative z-10 w-full max-w-md bg-white rounded-3xl border border-emerald-100 shadow-2xl p-8 sm:p-10 transition-all duration-300 hover:shadow-emerald-100/40">

      <!-- Brand Logo / Identity Header -->
      <div class="text-center mb-8">
        <div class="inline-block transform hover:scale-105 transition-transform duration-200">
          <CactusLogo />
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 font-heading mt-4">Hệ thống Quản lý dự án</h2>
        <p class="text-gray-400 text-sm mt-1 font-medium">Đăng nhập để quản lý công việc và dự án</p>
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

      <!-- Login Credentials Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Username input -->
        <div>
          <label for="username" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tên đăng
            nhập / Email</label>
          <div class="relative">
            <input id="username" v-model="username" required type="text" placeholder="an@xuongrong.vn hoặc Minh"
              class="w-full pl-10 pr-4 py-3 bg-gray-50/70 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all shadow-3xs" />
            <span class="absolute left-3.5 top-3.5 text-gray-400 text-sm">
              <i class="fa-regular fa-user"></i>
            </span>
          </div>
        </div>

        <!-- Password input -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-bold text-gray-600 uppercase tracking-wider">Mật
              khẩu</label>
            <a href="#" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors">Quên mật
              khẩu?</a>
          </div>
          <div class="relative">
            <input id="password" v-model="password" required type="password" placeholder="••••••••"
              class="w-full pl-10 pr-4 py-3 bg-gray-50/70 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all shadow-3xs" />
            <span class="absolute left-3.5 top-3.5 text-gray-400 text-sm">
              <i class="fa-solid fa-lock"></i>
            </span>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
          <input id="remember-me" type="checkbox"
            class="h-4 w-4 rounded text-emerald-600 border-gray-300 accent-emerald-600 cursor-pointer focus:ring-emerald-500" />
          <label for="remember-me" class="ml-2 block text-xs font-semibold text-gray-500 cursor-pointer select-none">
            Ghi nhớ đăng nhập
          </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" :disabled="authStore.isLoading"
          class="w-full py-3 bg-[#2d8a39] hover:bg-[#236e2d] disabled:bg-gray-300 text-white font-bold text-sm rounded-xl shadow-xs transition-colors duration-150 flex items-center justify-center gap-2 cursor-pointer focus:outline-none">
          <template v-if="authStore.isLoading">
            <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
            <span>Đang xử lý...</span>
          </template>
          <template v-else>
            <span>Đăng nhập</span>
          </template>
        </button>
      </form>

      <!-- Social Logins Section -->
      <div class="mt-6">
        <div class="relative flex py-2 items-center">
          <div class="flex-grow border-t border-gray-100"></div>
          <span class="flex-shrink mx-4 text-xs font-bold text-gray-400 uppercase tracking-wider select-none">Hoặc đăng
            nhập
            bằng</span>
          <div class="flex-grow border-t border-gray-100"></div>
        </div>

        <!-- Real Google sign-in button container -->
        <div class="mt-4 flex justify-center">
          <div id="google-signin-btn" class="w-full"></div>
        </div>

        <!-- Fallback test logins link -->
        <div class="mt-4 text-center">
          <button @click="showGooglePicker = true" type="button"
            class="text-xs font-bold text-emerald-700 hover:text-emerald-950 transition-colors">
            Đăng nhập nhanh bằng tài khoản test (Demo)
          </button>
        </div>
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

const username = ref('')
const password = ref('')
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
    router.push('/projects')
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
          width: '100%',
          text: 'signin_with',
          shape: 'pill'
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

const handleLogin = async () => {
  error.value = ''
  try {
    await authStore.login(username.value, password.value)
    router.push('/projects')
  } catch (err) {
    error.value = err
  }
}

const handleGoogleSelect = async (account) => {
  error.value = ''
  showGooglePicker.value = false
  try {
    await authStore.googleLogin(account.email, account.name, account.avatar)
    router.push('/projects')
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
    router.push('/projects')
  } catch (err) {
    error.value = err
  }
}
</script>
