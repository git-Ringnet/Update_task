<template>
  <header class="bg-[#F9F4EE] border-b border-stone-200/80 sticky top-0 z-50 py-2">
    <!-- Navbar Container: Centered workspace dropdown -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-center relative" ref="dropdownRef">
      <!-- Left Slot Content (Dynamic Back / Actions) -->
      <div class="absolute left-4 sm:left-6 lg:left-8 top-1/2 -translate-y-1/2 flex items-center">
        <slot name="left"></slot>
      </div>
      
      <!-- Top Center Dropdown Trigger -->
      <button
        @click="toggleDropdown"
        type="button"
        class="bg-transparent px-5 py-2 rounded-full border border-gray-300 hover:border-emerald-350 shadow-3xs hover:shadow-2xs flex items-center gap-2 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:ring-offset-1 text-sm font-bold text-gray-900"
      >
        <img src="/cactus-logo.png" alt="Logo Xương Rồng" class="w-6 h-6 object-contain" />
        <span>Xương Rồng</span>
        <i 
          class="fa-solid fa-chevron-down text-xs text-gray-400 ml-0.5 transition-transform duration-200"
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

      <!-- Right Slot Content (Dynamic Options/Menu) -->
      <div class="absolute right-4 sm:right-6 lg:right-8 top-1/2 -translate-y-1/2 flex items-center">
        <slot name="right"></slot>
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

          <form @submit.prevent="cropAvatarAndSave" class="space-y-4">
            <div class="flex flex-col items-center gap-3">
              <!-- circular crop boundary -->
              <div 
                class="relative w-32 h-32 rounded-full overflow-hidden border-2 border-emerald-400 shadow-md group cursor-pointer select-none bg-gray-50 flex items-center justify-center"
                @click="triggerAvatarFileSelect"
                title="Nhấp để chọn ảnh từ thiết bị"
              >
                <!-- overlay -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-[10px] font-bold transition-opacity z-10">
                  <i class="fa-solid fa-camera text-base mb-1"></i>
                  <span>Chọn ảnh</span>
                </div>
                
                <!-- crop preview image -->
                <img 
                  v-if="cropImageSrc" 
                  :src="cropImageSrc"
                  :style="{ transform: `translate(${avatarOffsetX}px, ${avatarOffsetY}px) scale(${avatarZoom})` }"
                  @mousedown="onDragStart"
                  @touchstart="onDragStart"
                  class="absolute w-full h-full object-cover pointer-events-auto origin-center cursor-move"
                />
                <img 
                  v-else 
                  :src="editForm.avatar || defaultAvatar" 
                  class="absolute w-full h-full object-cover" 
                />
              </div>

              <!-- hidden file input -->
              <input 
                type="file" 
                ref="avatarFileInputRef" 
                accept="image/*" 
                class="hidden" 
                @change="onAvatarFileSelected" 
              />
              
              <button 
                type="button" 
                @click="triggerAvatarFileSelect"
                class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-gray-600 transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <i class="fa-solid fa-upload"></i>
                <span>Chọn ảnh từ máy</span>
              </button>
            </div>

            <!-- Zoom control slider when custom image selected -->
            <div v-if="cropImageSrc" class="space-y-1 select-none px-4">
              <div class="flex items-center justify-between text-xs font-semibold text-gray-500">
                <span>Căn chỉnh (Kéo thả để di chuyển)</span>
                <span>Thu phóng: {{ Math.round(avatarZoom * 100) }}%</span>
              </div>
              <input 
                type="range" 
                v-model.number="avatarZoom" 
                min="1" 
                max="3" 
                step="0.05" 
                class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500" 
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
import axios from 'axios'

const defaultAvatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'

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
  avatar: defaultAvatar
})

const editForm = reactive({
  name: '',
  email: '',
  avatar: ''
})

// Crop avatar states
const isDragging = ref(false)
const dragStart = { x: 0, y: 0 }
const avatarOffsetX = ref(0)
const avatarOffsetY = ref(0)
const avatarZoom = ref(1)
const cropImageSrc = ref(null)
const avatarFileInputRef = ref(null)

const triggerAvatarFileSelect = () => {
  avatarFileInputRef.value?.click()
}

const onAvatarFileSelected = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (event) => {
    cropImageSrc.value = event.target.result
    avatarZoom.value = 1
    avatarOffsetX.value = 0
    avatarOffsetY.value = 0
  }
  reader.readAsDataURL(file)
}

const onDragStart = (e) => {
  e.preventDefault()
  isDragging.value = true
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  const clientY = e.touches ? e.touches[0].clientY : e.clientY
  dragStart.x = clientX - avatarOffsetX.value
  dragStart.y = clientY - avatarOffsetY.value

  document.addEventListener('mousemove', onDragMove)
  document.addEventListener('mouseup', onDragEnd)
  document.addEventListener('touchmove', onDragMove, { passive: false })
  document.addEventListener('touchend', onDragEnd)
}

const onDragMove = (e) => {
  if (!isDragging.value) return
  if (e.cancelable) e.preventDefault()
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  const clientY = e.touches ? e.touches[0].clientY : e.clientY
  avatarOffsetX.value = clientX - dragStart.x
  avatarOffsetY.value = clientY - dragStart.y
}

const onDragEnd = () => {
  isDragging.value = false
  document.removeEventListener('mousemove', onDragMove)
  document.removeEventListener('mouseup', onDragEnd)
  document.removeEventListener('touchmove', onDragMove)
  document.removeEventListener('touchend', onDragEnd)
}

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const openEditProfile = () => {
  editForm.name = currentUser.value.name
  editForm.email = currentUser.value.email
  editForm.avatar = currentUser.value.avatar
  cropImageSrc.value = null
  avatarZoom.value = 1
  avatarOffsetX.value = 0
  avatarOffsetY.value = 0
  isDropdownOpen.value = false
  isProfileModalOpen.value = true
}

const handleSaveProfile = async () => {
  try {
    const res = await axios.put('/api/me', {
      name: editForm.name,
      email: editForm.email,
      avatar: editForm.avatar
    })
    
    if (authStore.user) {
      authStore.user.name = res.data.name
      authStore.user.email = res.data.email
      authStore.user.avatar = res.data.avatar
      localStorage.setItem('user', JSON.stringify(authStore.user))
    }
    isProfileModalOpen.value = false
    toastStore.success('Đã cập nhật thông tin tài khoản!')
  } catch (err) {
    toastStore.error(err.response?.data?.message || 'Không thể lưu thay đổi!')
  }
}

const cropAvatarAndSave = () => {
  if (!cropImageSrc.value) {
    handleSaveProfile()
    return
  }

  const canvas = document.createElement('canvas')
  canvas.width = 150
  canvas.height = 150
  const ctx = canvas.getContext('2d')

  const img = new Image()
  img.onload = () => {
    const previewSize = 128
    const imgRatio = img.width / img.height
    let renderW, renderH
    if (imgRatio > 1) {
      renderH = previewSize
      renderW = previewSize * imgRatio
    } else {
      renderW = previewSize
      renderH = previewSize / imgRatio
    }

    const zoomedW = renderW * avatarZoom.value
    const zoomedH = renderH * avatarZoom.value

    const initialX = (previewSize - zoomedW) / 2
    const initialY = (previewSize - zoomedH) / 2

    const finalX = initialX + avatarOffsetX.value
    const finalY = initialY + avatarOffsetY.value

    const scaleFactor = 150 / previewSize
    ctx.drawImage(img, finalX * scaleFactor, finalY * scaleFactor, zoomedW * scaleFactor, zoomedH * scaleFactor)

    editForm.avatar = canvas.toDataURL('image/jpeg', 0.9)
    handleSaveProfile()
  }
  img.src = cropImageSrc.value
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
