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
      class="relative z-10 w-full max-w-[480px] bg-white rounded-3xl border border-emerald-100/80 shadow-2xl p-7 sm:p-9 transition-all duration-300">

      <!-- Brand Logo / Identity Header -->
      <div class="text-center mb-6">
        <div class="inline-block transform hover:scale-105 transition-transform duration-200">
          <CactusLogo />
        </div>
        <h2 class="text-2xl font-black text-gray-900 font-heading mt-3 leading-tight">Hệ thống Quản lý</h2>
        <p class="text-gray-500 text-xs mt-1 font-bold">Xương Rồng Project Management · Tài Khoản Nội Bộ</p>
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

      <!-- CHỌN THÀNH VIÊN ĐỂ TỰ ĐIỀN TÊN ĐĂNG NHẬP KHÔNG DẤU -->
      <div class="space-y-3 mb-6">
        <div class="flex items-center justify-between px-1">
          <span class="text-xs font-black text-gray-700 tracking-wide uppercase">Chọn tài khoản thành viên:</span>
          <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ teamMembers.length }} Thành viên</span>
        </div>

        <!-- Loading state skeleton -->
        <div v-if="isUsersLoading" class="grid grid-cols-4 gap-2.5">
          <div 
            v-for="i in 8" 
            :key="'sk-' + i" 
            class="flex flex-col items-center p-2.5 rounded-2xl border border-gray-100/50 bg-[#fbfdfc] animate-pulse"
          >
            <div class="w-11 h-11 rounded-full bg-gray-200"></div>
            <div class="h-3 bg-gray-150 rounded-md w-12 mt-2"></div>
          </div>
        </div>

        <!-- 4x2 Grid of 8 Team Members -->
        <div v-else class="grid grid-cols-4 gap-2.5">
          <button 
            v-for="member in teamMembers" 
            :key="member.username" 
            @click="selectMember(member)"
            type="button"
            class="flex flex-col items-center p-2.5 rounded-2xl border transition-all duration-200 group cursor-pointer"
            :class="isMemberSelected(member) ? 'border-emerald-500 bg-emerald-50/70 shadow-xs ring-2 ring-emerald-500/30' : 'border-gray-100 bg-[#fbfdfc] hover:bg-emerald-50/40 hover:border-emerald-300'"
          >
            <img 
              :src="member.avatar" 
              :alt="member.name" 
              class="w-11 h-11 rounded-full object-cover border-2 shadow-2xs group-hover:scale-105 transition-all" 
              :class="isMemberSelected(member) ? 'border-emerald-600 scale-105' : 'border-white group-hover:border-emerald-400'"
            />
            <span 
              class="mt-1.5 text-xs font-black tracking-tight truncate max-w-full"
              :class="isMemberSelected(member) ? 'text-emerald-800' : 'text-gray-800 group-hover:text-emerald-800'"
            >
              {{ member.name }}
            </span>
          </button>
        </div>
      </div>

      <!-- SEPARATOR -->
      <div class="relative flex items-center justify-center my-5">
        <div class="border-t border-gray-100 w-full"></div>
        <span class="bg-white px-3 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest absolute">Thông tin đăng nhập</span>
      </div>

      <!-- MANUAL CREDENTIALS FORM (UNACCENTED USERNAMES: an, thien, tin, khanh, hieu, canh, thang, thao) -->
      <form @submit.prevent="handleManualLogin" class="space-y-3.5">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Tên đăng nhập (không dấu)</label>
          <input 
            v-model="loginForm.username" 
            type="text" 
            required 
            placeholder="VD: an, thien, tin, khanh, hieu, canh, thang, thao" 
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
              placeholder="Mật khẩu nội bộ (Mặc định: Ringnet@123)" 
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
          class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 mt-1"
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
import { ref, reactive, nextTick, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import CactusLogo from '../components/CactusLogo.vue'
import axios from 'axios'

const authStore = useAuthStore()
const router = useRouter()

const error = ref('')
const showPassword = ref(false)
const passwordInputRef = ref(null)

const loginForm = reactive({
  username: '',
  password: 'Ringnet@123'
})

// Fallback Internal Team Members with Standard Unaccented Usernames
const teamMembers = ref([
  {
    name: 'Ân',
    username: 'an',
    email: 'an@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Thiên',
    username: 'thien',
    email: 'thien@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Tín',
    username: 'tin',
    email: 'tin@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Khanh',
    username: 'khanh',
    email: 'khanh@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Hiếu',
    username: 'hieu',
    email: 'hieu@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Cảnh',
    username: 'canh',
    email: 'canh@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Thắng',
    username: 'thang',
    email: 'thang@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&q=80&w=120'
  },
  {
    name: 'Thảo',
    username: 'thao',
    email: 'thao@xuongrong.vn',
    avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=120'
  }
])

const isUsersLoading = ref(true)

const fetchActiveUsers = async () => {
  try {
    isUsersLoading.value = true
    const res = await axios.get('/api/active-users')
    if (res.data && res.data.length > 0) {
      teamMembers.value = res.data
    }
  } catch (err) {
    console.error('Failed to fetch active users list:', err)
  } finally {
    isUsersLoading.value = false
  }
}

onMounted(() => {
  fetchActiveUsers()
})

const isMemberSelected = (member) => {
  if (!loginForm.username) return false
  const lowerInput = loginForm.username.trim().toLowerCase()
  return lowerInput === member.username.toLowerCase() || 
         lowerInput === member.email.toLowerCase() || 
         lowerInput === member.name.toLowerCase()
}

const selectMember = (member) => {
  loginForm.username = member.username
  loginForm.password = 'Ringnet@123'
  error.value = ''
  nextTick(() => {
    passwordInputRef.value?.focus()
  })
}

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

<style scoped>
</style>
