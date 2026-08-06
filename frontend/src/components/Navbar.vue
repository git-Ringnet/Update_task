<template>
  <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-2xs py-2">
    <!-- Navbar Container: Centered workspace dropdown -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-center relative" ref="dropdownRef">
      
      <!-- Top Center Dropdown Trigger -->
      <button
        @click="toggleDropdown"
        type="button"
        class="bg-white px-6 py-2.5 rounded-full border border-gray-200/80 hover:border-emerald-300 shadow-3xs hover:shadow-2xs flex items-center gap-2 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:ring-offset-1 text-sm font-bold text-gray-900"
      >
        <span>🌵</span>
        <span>Xương Rồng</span>
        <i 
          class="fa-solid fa-chevron-down text-xs text-gray-400 ml-1 transition-transform duration-200"
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
          class="absolute top-14 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 p-3 z-50 ring-1 ring-black/5"
        >
          <!-- WORKSPACE SECTION -->
          <div class="space-y-1">
            
            <!-- Trang chủ Item (Formerly Views) -->
            <button
              @click="navigate('/views')"
              type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group"
            >
              <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
                <i class="fa-solid fa-house text-gray-600 group-hover:text-emerald-600"></i>
              </span>
              <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 leading-snug group-hover:text-emerald-800">
                  Trang chủ
                </div>
                <div class="text-[10px] text-gray-400 truncate mt-0.5">
                  Bộ lọc, chế độ xem tùy chỉnh
                </div>
              </div>
            </button>

            <!-- Projects Item -->
            <button
              @click="navigate('/projects')"
              type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group"
            >
              <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
                <i class="fa-regular fa-folder text-gray-600 group-hover:text-emerald-600"></i>
              </span>
              <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 leading-snug group-hover:text-emerald-800">
                  Dự án
                </div>
                <div class="text-[10px] text-gray-400 truncate mt-0.5">
                  Tất cả dự án & sáng kiến
                </div>
              </div>
            </button>

            <!-- Relationships Item -->
            <button
              @click="navigate('/customers')"
              type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group"
            >
              <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
                <i class="fa-solid fa-users text-gray-600 group-hover:text-emerald-600"></i>
              </span>
              <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 leading-snug group-hover:text-emerald-800">
                  Mối quan hệ
                </div>
                <div class="text-[10px] text-gray-400 truncate mt-0.5">
                  Khách hàng, đối tác, nội bộ
                </div>
              </div>
            </button>

            <!-- People Item -->
            <button
              @click="navigate('/people')"
              type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group"
            >
              <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
                <i class="fa-regular fa-user text-gray-600 group-hover:text-emerald-600"></i>
              </span>
              <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 leading-snug group-hover:text-emerald-800">
                  Thành viên
                </div>
                <div class="text-[10px] text-gray-400 truncate mt-0.5">
                  Thành viên hệ thống & vai trò
                </div>
              </div>
            </button>
          </div>

          <!-- Divider -->
          <div class="border-t border-gray-100 my-2"></div>

          <!-- TOOLS SECTION -->
          <div class="space-y-0.5">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider pl-2.5 mb-1 select-none">
              CÔNG CỤ
            </div>

            <!-- Settings -->
            <button
              @click="openEditProfile"
              type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-emerald-50/60 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-gray-700 hover:text-emerald-800"
            >
              <i class="fa-solid fa-gear text-sm text-gray-400"></i>
              <span>Cài đặt tài khoản</span>
            </button>

            <!-- Logout -->
            <button
              @click="handleLogout"
              type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-rose-50 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-rose-600 hover:text-rose-700"
            >
              <i class="fa-solid fa-right-from-bracket text-sm text-rose-500"></i>
              <span>Đăng xuất</span>
            </button>
          </div>
        </div>
      </transition>
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
import { useToastStore } from '../stores/toast'
const toastStore = useToastStore()

const isDropdownOpen = ref(false)
const isProfileModalOpen = ref(false)
const dropdownRef = ref(null)

const navigate = (path) => {
  isDropdownOpen.value = false
  router.push(path)
}

const triggerImport = () => {
  isDropdownOpen.value = false
  toastStore.success('Hệ thống nhập dữ liệu Excel đã sẵn sàng!')
}

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
  toastStore.success('Đã cập nhật thông tin tài khoản!')
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
