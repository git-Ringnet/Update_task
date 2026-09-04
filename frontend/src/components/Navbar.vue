<template>
  <header class="bg-[#F9F4EE] sticky top-0 z-50 py-2">
    <!-- Navbar Container: Centered workspace dropdown -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-center relative"
      ref="dropdownRef">
      <!-- Left Slot Content (Dynamic Back / Actions) -->
      <div class="absolute left-4 sm:left-6 lg:left-8 top-1/2 -translate-y-1/2 flex items-center">
        <slot name="left"></slot>
      </div>

      <!-- Top Center Dropdown Trigger -->
      <button @click="toggleDropdown" type="button"
        class="bg-transparent px-5 py-2 rounded-full border border-gray-300 hover:border-emerald-350 shadow-3xs hover:shadow-2xs flex items-center gap-2 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:ring-offset-1 text-sm font-bold text-gray-900">
        <img src="/cactus-logo-square.png" alt="Logo Xương Rồng" class="w-6 h-6 object-contain" />
        <span>Xương Rồng</span>
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 ml-0.5 transition-transform duration-200"
          :class="{ 'rotate-180': isDropdownOpen }"></i>
      </button>

      <!-- Dropdown Menu -->
      <transition enter-active-class="transition duration-150 ease-out"
        enter-from-class="transform scale-95 opacity-0 -translate-y-1"
        enter-to-class="transform scale-100 opacity-100 translate-y-0"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100 translate-y-0"
        leave-to-class="transform scale-95 opacity-0 -translate-y-1">
        <div v-if="isDropdownOpen"
          class="absolute top-14 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 p-3 z-50 ring-1 ring-black/5">
          <!-- WORKSPACE SECTION -->
          <div class="space-y-1">

            <!-- Trang chủ Item (Formerly Views) -->
            <button @click="navigate('/views')" type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group">
              <span
                class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
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
            <button @click="navigate('/projects')" type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group">
              <span
                class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
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
            <button @click="navigate('/customers')" type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group">
              <span
                class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
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
            <button @click="navigate('/people')" type="button"
              class="w-full text-left p-2 hover:bg-emerald-50/60 rounded-xl transition-colors flex items-start gap-2.5 cursor-pointer group">
              <span
                class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-sm group-hover:bg-white transition-colors border border-transparent group-hover:border-emerald-100 flex-shrink-0">
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

            <!-- Broadcast -->
            <button @click="openBroadcastModal" type="button"
              class="broadcast-menu-item hidden md:flex w-full text-left px-2.5 py-2 hover:bg-emerald-50/60 rounded-lg transition-colors items-center gap-2.5 cursor-pointer text-xs font-bold text-gray-700 hover:text-emerald-800">
              <i class="fa-solid fa-tv text-sm text-emerald-600"></i>
              <span>Phát sóng</span>
            </button>

            <!-- Settings -->
            <button @click="openEditProfile" type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-emerald-50/60 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-gray-700 hover:text-emerald-800">
              <i class="fa-solid fa-gear text-sm text-gray-400"></i>
              <span>Cài đặt tài khoản</span>
            </button>

            <!-- Export SQL (System Admin only) -->
            <button v-if="authStore.user?.is_system_admin" @click="exportSql" type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-emerald-50/60 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-gray-700 hover:text-emerald-800">
              <i class="fa-solid fa-database text-sm text-emerald-600"></i>
              <span>Xuất File SQL</span>
            </button>

            <!-- Operation History (System Admin only) -->
            <button v-if="authStore.user?.is_system_admin" @click="navigate('/feed?tab=operations')" type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-emerald-50/60 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-gray-700 hover:text-emerald-800">
              <i class="fa-solid fa-clock-rotate-left text-sm text-emerald-600"></i>
              <span>Lịch sử thao tác</span>
            </button>

            <!-- Logout -->
            <button @click="handleLogout" type="button"
              class="w-full text-left px-2.5 py-2 hover:bg-rose-50 rounded-lg transition-colors flex items-center gap-2.5 cursor-pointer text-xs font-bold text-rose-600 hover:text-rose-700">
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

    <!-- Broadcast modal: teleport out of the sticky navbar stacking context. -->
    <Teleport to="body">
      <div v-if="isBroadcastModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <button type="button" class="absolute inset-0 bg-slate-950/35 backdrop-blur-xs cursor-default"
          aria-label="Đóng phát sóng" @click="closeBroadcastModal"></button>

        <form @submit.prevent="submitBroadcast"
          class="relative z-10 w-full max-w-2xl rounded-3xl border border-stone-200 bg-[#fffdf9] p-5 sm:p-7 shadow-2xl space-y-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <span
                class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-tv"></i>
              </span>
              <div>
                <h2 class="text-lg font-black text-gray-900 font-heading">PHÁT SÓNG</h2>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Chia sẻ một điều đáng tán dương hoặc cần cả team lưu
                  ý.</p>
              </div>
            </div>
            <button type="button" @click="closeBroadcastModal"
              class="w-9 h-9 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors cursor-pointer"
              aria-label="Đóng">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <div>
            <p class="text-xs font-black text-gray-700 mb-2">1. LOẠI</p>
            <div class="grid grid-cols-2 gap-2 rounded-xl bg-stone-100 p-1">
              <button type="button" @click="setBroadcastType('good')"
                class="rounded-lg py-2.5 text-xs font-black transition-colors cursor-pointer"
                :class="broadcastForm.type === 'good' ? 'bg-white text-emerald-700 border border-emerald-600 shadow-2xs' : 'text-gray-500 hover:text-emerald-700'">
                TỐT
              </button>
              <button type="button" @click="setBroadcastType('bad')"
                class="rounded-lg py-2.5 text-xs font-black transition-colors cursor-pointer"
                :class="broadcastForm.type === 'bad' ? 'bg-white text-rose-600 border border-rose-400 shadow-2xs' : 'text-gray-500 hover:text-rose-600'">
                KHÔNG TỐT
              </button>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <label for="broadcast-content" class="text-xs font-black text-gray-700">2. NỘI DUNG</label>
              <span class="text-[11px] font-bold text-gray-400">{{ broadcastForm.content.length }}/200</span>
            </div>
            <textarea id="broadcast-content" v-model="broadcastForm.content" maxlength="200" required rows="4"
              :placeholder="broadcastForm.type === 'good' ? 'Nhập nội dung tán dương hoặc lưu ý...' : 'Nhập nội dung cần cả team lưu ý...'"
              class="w-full resize-none rounded-xl border border-stone-200 bg-white px-3.5 py-3 text-sm font-medium text-gray-800 placeholder:text-gray-400 focus:border-emerald-600 focus:outline-none"></textarea>
          </div>

          <div v-if="broadcastForm.type === 'good'">
            <label for="broadcast-recipient" class="block text-xs font-black text-gray-700 mb-2">3. NGƯỜI ĐƯỢC TÁN DƯƠNG
              <span class="font-semibold text-gray-400">(tùy chọn)</span></label>
            <select id="broadcast-recipient" v-model="broadcastForm.recipientId"
              class="w-full rounded-xl border border-stone-200 bg-white px-3.5 py-3 text-sm font-semibold text-gray-700 focus:border-emerald-600 focus:outline-none cursor-pointer">
              <option value="">Chọn thành viên</option>
              <option v-for="user in broadcastUsers" :key="user.id" :value="String(user.id)">{{ user.name }}</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-3 pt-1">
            <button type="button" @click="closeBroadcastModal"
              class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-black text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">HỦY</button>
            <button type="submit" :disabled="isBroadcastSubmitting || !broadcastForm.content.trim()"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 text-xs font-black text-white shadow-sm hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50 transition-colors cursor-pointer">
              <i class="fa-solid fa-tv"></i><span>LÊN SÓNG</span>
            </button>
          </div>
        </form>
      </div>
    </Teleport>

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
                @click="triggerAvatarFileSelect" title="Nhấp để chọn ảnh từ thiết bị">
                <!-- overlay -->
                <div
                  class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-[10px] font-bold transition-opacity z-10">
                  <i class="fa-solid fa-camera text-base mb-1"></i>
                  <span>Chọn ảnh</span>
                </div>

                <!-- crop preview image -->
                <img v-if="cropImageSrc" :src="cropImageSrc"
                  :style="{ transform: `translate(${avatarOffsetX}px, ${avatarOffsetY}px) scale(${avatarZoom})` }"
                  @mousedown="onDragStart" @touchstart="onDragStart"
                  class="absolute w-full h-full object-cover pointer-events-auto origin-center cursor-move" />
                <img v-else :src="editForm.avatar || defaultAvatar" class="absolute w-full h-full object-cover" />
              </div>

              <!-- hidden file input -->
              <input type="file" ref="avatarFileInputRef" accept="image/*" class="hidden"
                @change="onAvatarFileSelected" />

              <button type="button" @click="triggerAvatarFileSelect"
                class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-gray-600 transition-colors flex items-center gap-1.5 cursor-pointer">
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
              <input type="range" v-model.number="avatarZoom" min="1" max="3" step="0.05"
                class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên tài khoản</label>
              <input v-model="editForm.name" required type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
              <input v-model="editForm.email" required type="email"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500" />
            </div>

            <!-- Browser Notifications Toggle -->
            <div v-if="browserNotifications.isSupported"
              class="pt-3 border-t border-gray-100 flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-xs font-bold text-gray-700">Thông báo trình duyệt</span>
                <span class="text-[10px] text-gray-400">Nhận thông báo khi có cập nhật mới</span>
              </div>
              <button type="button" @click="toggleBrowserNotifications"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                :style="{ backgroundColor: browserNotifications.isEnabled ? '#059669' : '#d1d5db' }">
                <span
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                  :class="browserNotifications.isEnabled ? 'translate-x-4' : 'translate-x-0'"></span>
              </button>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" @click="isProfileModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl">
                Hủy
              </button>
              <button type="submit"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs">
                Lưu thay đổi
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- Install Instructions Modal -->
    <Teleport to="body">
      <div v-if="isInstructionsModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <button type="button" class="absolute inset-0 bg-slate-950/35 backdrop-blur-xs cursor-default"
          aria-label="Đóng hướng dẫn" @click="isInstructionsModalOpen = false"></button>

        <div
          class="relative z-10 w-full max-w-sm rounded-3xl border border-stone-200 bg-[#fffdf9] p-6 shadow-2xl space-y-4">
          <div class="flex items-start gap-3">
            <span
              class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
              <i class="fa-solid fa-mobile-screen-button"></i>
            </span>
            <div>
              <h3 class="text-sm font-black text-gray-900 uppercase">
                {{ isIOS ? 'Cài đặt trên iOS' : 'Hướng dẫn cài đặt app' }}
              </h3>
              <p class="text-[10px] text-gray-500 font-medium">Để thêm ứng dụng Xương Rồng vào màn hình chính:</p>
            </div>
          </div>

          <!-- iOS Steps -->
          <div v-if="isIOS"
            class="space-y-2.5 text-xs font-semibold text-gray-700 bg-stone-50 p-3.5 rounded-2xl border border-stone-200/50">
            <div class="flex items-start gap-2">
              <span
                class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] flex-shrink-0 font-bold">1</span>
              <span>Mở trang web bằng trình duyệt <strong>Safari</strong>.</span>
            </div>
            <div class="flex items-start gap-2">
              <span
                class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] flex-shrink-0 font-bold">2</span>
              <span>Bấm vào biểu tượng <strong>Chia sẻ (Share)</strong> <i
                  class="fa-solid fa-share-from-square text-emerald-600"></i> ở thanh công cụ Safari.</span>
            </div>
            <div class="flex items-start gap-2">
              <span
                class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] flex-shrink-0 font-bold">3</span>
              <span>Cuộn xuống và chọn <strong>Thêm vào MH chính (Add to Home Screen)</strong> <i
                  class="fa-solid fa-square-plus text-emerald-600"></i>.</span>
            </div>
          </div>

          <!-- Chrome/Android/Desktop Steps -->
          <div v-else
            class="space-y-2.5 text-xs font-semibold text-gray-700 bg-stone-50 p-3.5 rounded-2xl border border-stone-200/50">
            <div class="flex items-start gap-2">
              <span
                class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] flex-shrink-0 font-bold">1</span>
              <span>Bấm vào nút <strong>Menu của trình duyệt</strong> (icon <i
                  class="fa-solid fa-ellipsis-vertical text-emerald-600"></i> 3 chấm) hoặc biểu tượng <strong>Cài đặt
                  ứng dụng</strong> <i class="fa-solid fa-download text-emerald-600"></i> trên thanh địa chỉ.</span>
            </div>
            <div class="flex items-start gap-2">
              <span
                class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] flex-shrink-0 font-bold">2</span>
              <span>Chọn <strong>Cài đặt ứng dụng (Install App)</strong> hoặc <strong>Thêm vào Màn hình chính (Add to
                  Home Screen)</strong>.</span>
            </div>
          </div>

          <div class="flex justify-end pt-1">
            <button @click="isInstructionsModalOpen = false"
              class="px-5 py-2 rounded-xl bg-emerald-700 text-xs font-black text-white hover:bg-emerald-800 shadow-sm transition-colors cursor-pointer">ĐÃ
              HIỂU</button>
          </div>
        </div>
      </div>
    </Teleport>

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
import { useBrowserNotificationStore } from '../stores/browserNotifications'
const toastStore = useToastStore()
const browserNotifications = useBrowserNotificationStore()

const isDropdownOpen = ref(false)
const isProfileModalOpen = ref(false)
const isBroadcastModalOpen = ref(false)
const isBroadcastSubmitting = ref(false)
const broadcastUsers = ref([])
const dropdownRef = ref(null)

// PWA Install state
const deferredPrompt = ref(null)
const showInstallBtn = ref(false)
const isInstructionsModalOpen = ref(false)

const isIOS = computed(() => {
  if (typeof window === 'undefined' || !navigator) return false
  return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream
})

const isStandalone = computed(() => {
  if (typeof window === 'undefined') return false
  return window.matchMedia('(display-mode: standalone)').matches
})

const isInstallable = computed(() => {
  return !isStandalone.value
})

const handleInstallClick = async () => {
  isDropdownOpen.value = false
  if (deferredPrompt.value) {
    deferredPrompt.value.prompt()
    const { outcome } = await deferredPrompt.value.userChoice
    if (outcome === 'accepted') {
      deferredPrompt.value = null
      showInstallBtn.value = false
    }
  } else {
    isInstructionsModalOpen.value = true
  }
}

const handleBeforeInstallPrompt = (e) => {
  e.preventDefault()
  deferredPrompt.value = e
  showInstallBtn.value = true
}

const handleAppInstalled = () => {
  deferredPrompt.value = null
  showInstallBtn.value = false
}

const broadcastForm = reactive({
  type: 'good',
  content: '',
  recipientId: ''
})

const setBroadcastType = (type) => {
  broadcastForm.type = type
  if (type === 'bad') broadcastForm.recipientId = ''
}

const openBroadcastModal = async () => {
  isDropdownOpen.value = false
  broadcastForm.type = 'good'
  broadcastForm.content = ''
  broadcastForm.recipientId = ''
  isBroadcastModalOpen.value = true

  try {
    const response = await axios.get('/api/users')
    broadcastUsers.value = response.data || []
  } catch (error) {
    broadcastUsers.value = []
    toastStore.error('Không thể tải danh sách thành viên.')
  }
}

const closeBroadcastModal = () => {
  if (isBroadcastSubmitting.value) return
  isBroadcastModalOpen.value = false
}

const submitBroadcast = async () => {
  if (!broadcastForm.content.trim() || isBroadcastSubmitting.value) return

  isBroadcastSubmitting.value = true
  try {
    await axios.post('/api/broadcasts', {
      type: broadcastForm.type,
      content: broadcastForm.content.trim(),
      recipient_id: broadcastForm.type === 'good' ? broadcastForm.recipientId || null : null
    })
    isBroadcastModalOpen.value = false
    toastStore.success('Đã lên sóng bảng tin!')
  } catch (error) {
    toastStore.error(error.response?.data?.message || 'Không thể lên sóng bản tin.')
  } finally {
    isBroadcastSubmitting.value = false
  }
}

const navigate = (path) => {
  isDropdownOpen.value = false
  router.push(path)
}

const triggerImport = () => {
  isDropdownOpen.value = false
  toastStore.success('Hệ thống nhập dữ liệu Excel đã sẵn sàng!')
}

const toggleBrowserNotifications = async () => {
  if (browserNotifications.isEnabled) {
    await browserNotifications.setEnabled(false)
    toastStore.success('Đã tắt thông báo cập nhật dự án.')
    return
  }

  const result = await browserNotifications.setEnabled(true)
  if (result === 'granted') {
    toastStore.success('Đã bật thông báo cập nhật dự án trên trình duyệt.')
  } else if (result === 'denied') {
    toastStore.warning('Trình duyệt đang chặn thông báo. Hãy cấp quyền trong phần cài đặt trình duyệt.')
  } else if (result === 'error') {
    toastStore.error('Không thể kết nối thông báo trình duyệt với máy chủ.')
  } else {
    toastStore.error('Trình duyệt này không hỗ trợ thông báo.')
  }
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

const exportSql = async () => {
  isDropdownOpen.value = false
  try {
    const response = await axios.get('/api/database/export', {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url

    const contentDisposition = response.headers['content-disposition']
    let fileName = 'backup_database.sql'
    if (contentDisposition) {
      const fileNameMatch = contentDisposition.match(/filename="(.+)"/)
      if (fileNameMatch && fileNameMatch[1]) {
        fileName = fileNameMatch[1]
      }
    }

    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()

    link.remove()
    window.URL.revokeObjectURL(url)

    toastStore.success('Đã xuất file SQL thành công!')
  } catch (error) {
    toastStore.error('Không thể xuất file SQL. Vui lòng thử lại sau.')
  }
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
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.addEventListener('appinstalled', handleAppInstalled)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  window.removeEventListener('appinstalled', handleAppInstalled)
})
</script>
