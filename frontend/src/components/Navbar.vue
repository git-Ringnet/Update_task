<template>
  <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-2xs">
    <!-- Top Header: Only Logo on Left & Profile Dropdown on Right -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Left: Logo Mascot -->
      <router-link to="/" class="flex items-center gap-2 group">
        <CactusLogo />
      </router-link>

      <!-- Right: User Profile Dropdown -->
      <div class="relative" ref="dropdownRef">
        <button
          @click="toggleDropdown"
          type="button"
          class="flex items-center gap-2.5 p-1 rounded-full hover:bg-gray-50 transition-colors focus:outline-none group"
        >
          <img
            :src="currentUser.avatar"
            :alt="currentUser.name"
            class="w-9 h-9 rounded-full object-cover border-2 border-emerald-400/70 shadow-2xs group-hover:scale-105 transition-transform"
          />
          <span class="text-base font-bold text-gray-900 font-heading">{{ currentUser.name }}</span>
          <i 
            class="fa-solid fa-chevron-down text-xs text-gray-400 group-hover:text-gray-600 transition-transform duration-200"
            :class="{ 'rotate-180': isDropdownOpen }"
          ></i>
        </button>

        <!-- Dropdown Menu -->
        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform scale-95 opacity-0 -translate-y-1"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 -translate-y-1"
        >
          <div
            v-if="isDropdownOpen"
            class="absolute right-0 top-12 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 ring-1 ring-black/5"
          >
            <!-- User Info Header -->
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
              <img
                :src="currentUser.avatar"
                class="w-10 h-10 rounded-full object-cover border border-emerald-300"
              />
              <div class="min-w-0">
                <div class="font-bold text-gray-900 text-sm truncate font-heading">{{ currentUser.name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ currentUser.email }}</div>
              </div>
            </div>

            <!-- Menu Options -->
            <div class="py-1">
              <button
                @click="openEditProfile"
                type="button"
                class="w-full px-4 py-2.5 text-left text-sm font-medium text-gray-700 hover:bg-emerald-50/60 hover:text-emerald-800 transition-colors flex items-center gap-2.5"
              >
                <i class="fa-solid fa-user-pen text-emerald-600 text-sm"></i>
                <span>Chỉnh sửa thông tin</span>
              </button>
            </div>

            <!-- Divider & Logout -->
            <div class="border-t border-gray-100 pt-1 mt-1">
              <button
                @click="handleLogout"
                type="button"
                class="w-full px-4 py-2.5 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors flex items-center gap-2.5"
              >
                <i class="fa-solid fa-right-from-bracket text-rose-500 text-sm"></i>
                <span>Đăng xuất</span>
              </button>
            </div>

          </div>
        </transition>

      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="isProfileModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">Chỉnh Sửa Thông Tin Tài Khoản</h3>
            <button @click="isProfileModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <form @submit.prevent="handleSaveProfile" class="space-y-4">
            <div class="flex justify-center mb-2">
              <img
                :src="editForm.avatar"
                class="w-20 h-20 rounded-full object-cover border-2 border-emerald-400 shadow-md"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên tài khoản</label>
              <input
                v-model="editForm.name"
                required
                type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
              <input
                v-model="editForm.email"
                required
                type="email"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">URL Ảnh đại diện (Avatar)</label>
              <input
                v-model="editForm.avatar"
                type="url"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button
                type="button"
                @click="isProfileModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl"
              >
                Hủy
              </button>
              <button
                type="submit"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs"
              >
                Lưu thay đổi
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

  </header>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import CactusLogo from './CactusLogo.vue'

const authStore = useAuthStore()
const router = useRouter()

const isDropdownOpen = ref(false)
const isProfileModalOpen = ref(false)
const dropdownRef = ref(null)

const currentUser = computed(() => authStore.user || {
  name: 'Minh',
  email: 'minh@xuongrong.vn',
  avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
})

const editForm = reactive({
  name: '',
  email: '',
  avatar: ''
})

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const openEditProfile = () => {
  editForm.name = currentUser.value.name
  editForm.email = currentUser.value.email
  editForm.avatar = currentUser.value.avatar
  isDropdownOpen.value = false
  isProfileModalOpen.value = true
}

const handleSaveProfile = () => {
  if (authStore.user) {
    authStore.user.name = editForm.name
    authStore.user.email = editForm.email
    authStore.user.avatar = editForm.avatar
    localStorage.setItem('user', JSON.stringify(authStore.user))
  }
  isProfileModalOpen.value = false
}

const handleLogout = async () => {
  isDropdownOpen.value = false
  await authStore.logout()
  router.push('/login')
}

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
