<template>
  <div class="min-h-screen bg-[#F9F4EE] flex flex-col justify-between pb-24">
    <div>
      <!-- Navbar Component -->
      <Navbar />

      <!-- Main Container -->
      <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading flex items-center gap-3">
              <span>Thành viên hệ thống</span>
              <span class="text-xs px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full">
                {{ users.length }} thành viên
              </span>
            </h1>
            <p class="text-sm text-gray-500 font-semibold mt-1">
              Quản lý danh sách người dùng, thành viên và vai trò phụ trách dự án
            </p>
          </div>

          <!-- Search & Add Actions -->
          <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Tìm kiếm thành viên..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white shadow-3xs font-semibold"
              />
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
              </span>
            </div>
            <button
              v-if="authStore.user?.is_admin"
              @click="openAddModal"
              type="button"
              class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
            >
              <i class="fa-solid fa-user-plus"></i>
              <span>Thêm thành viên</span>
            </button>
          </div>
        </div>

        <!-- Skeleton Loading State -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="'sk-user-' + i" class="bg-white border border-gray-200/80 rounded-2xl p-5 animate-pulse space-y-4 shadow-3xs">
            <div class="flex items-center gap-3.5">
              <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
              <div class="space-y-2 flex-1">
                <div class="h-4 bg-gray-200 rounded-md w-2/3"></div>
                <div class="h-3 bg-gray-150 rounded-md w-1/2"></div>
              </div>
            </div>
            <div class="h-3 bg-gray-150 rounded-md w-3/4"></div>
          </div>
        </div>

        <!-- Empty Search State -->
        <div v-else-if="filteredUsers.length === 0" class="bg-white border border-gray-200 rounded-2xl p-12 text-center text-gray-450 font-semibold shadow-3xs">
          <i class="fa-solid fa-user-slash text-3xl mb-3 text-gray-300"></i>
          <p>Không tìm thấy thành viên nào khớp với từ khóa "{{ searchQuery }}"</p>
        </div>

        <!-- Users Grid Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="user in filteredUsers"
            :key="user.id"
            class="bg-white border border-gray-200/80 hover:border-emerald-300 rounded-2xl p-5 shadow-3xs hover:shadow-2xs transition-all flex flex-col justify-between space-y-4 group"
          >
            <!-- User Header & Info -->
            <div class="space-y-4">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="relative flex-shrink-0">
                    <img
                      :src="user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                      :alt="user.name"
                      class="w-12 h-12 rounded-full object-cover border-2 border-emerald-100 shadow-3xs"
                    />
                    <span class="w-3 h-3 rounded-full bg-emerald-500 border-2 border-white absolute bottom-0 right-0"></span>
                  </div>

                  <div class="min-w-0 flex-1">
                    <h3 class="font-extrabold text-base text-gray-900 leading-snug group-hover:text-emerald-700 transition-colors truncate">
                      {{ user.name }}
                    </h3>
                    <p class="text-xs text-gray-400 font-medium truncate mt-0.5">
                      {{ user.email }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Badges & Stats -->
              <div class="flex items-center gap-2 flex-wrap pt-1 border-t border-gray-100">
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 font-extrabold text-xs rounded-lg border border-emerald-100/60">
                  <i class="fa-solid fa-user-shield text-[10px] mr-1 text-emerald-600"></i> Thành viên
                </span>

                <span class="px-2.5 py-1 bg-gray-50 text-gray-600 font-bold text-xs rounded-lg border border-gray-200/60">
                  <i class="fa-solid fa-folder text-[10px] mr-1 text-amber-500"></i> {{ user.participating_projects_count || 0 }} dự án tham gia
                </span>
              </div>
            </div>

            <!-- Footer Action -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
              <span class="text-xs text-gray-400 font-semibold">Hoạt động trong hệ thống</span>
              <div class="flex items-center gap-1.5">
                <button
                  v-if="authStore.user?.is_admin && user.id !== currentUserId"
                  @click="openPasswordModal(user)"
                  type="button"
                  class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 hover:text-amber-800 rounded-xl transition-colors cursor-pointer flex items-center justify-center"
                  title="Đặt lại mật khẩu"
                >
                  <i class="fa-solid fa-key text-sm"></i>
                </button>
                <button
                  v-if="authStore.user?.is_admin && user.id !== currentUserId"
                  @click="confirmDeleteUser(user)"
                  type="button"
                  class="p-1.5 bg-red-50 hover:bg-red-100 text-red-650 hover:text-red-700 rounded-xl transition-colors cursor-pointer flex items-center justify-center"
                  title="Xóa thành viên"
                >
                  <i class="fa-solid fa-trash-can text-sm"></i>
                </button>
                <button
                  v-if="authStore.user?.is_admin"
                  @click="filterProjectsByUser(user.id)"
                  type="button"
                  class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-xl transition-colors cursor-pointer flex items-center gap-1.5"
                >
                  <span>Xem dự án</span>
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>

    <!-- Modal Thêm thành viên -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-xs" @click="isAddModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">
              Thêm thành viên mới
            </h3>
            <button @click="isAddModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>
          <form @submit.prevent="handleAddUser" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Họ và tên *</label>
              <input v-model="addForm.name" required type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-3xs text-gray-805"
                placeholder="VD: Nguyễn Văn Tín" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email *</label>
              <input v-model="addForm.email" required type="email"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-3xs text-gray-805"
                placeholder="VD: tin.nguyen@xuongrong.vn" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Mật khẩu *</label>
              <input v-model="addForm.password" required type="password"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-3xs text-gray-805"
                placeholder="Tối thiểu 6 ký tự (Mặc định: Ringnet@123)" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Đường dẫn ảnh đại diện (URL)</label>
              <input v-model="addForm.avatar" type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-3xs text-gray-805"
                placeholder="Bỏ trống để dùng ảnh mặc định" />
            </div>

            <!-- Error message if any -->
            <div v-if="addError" class="text-xs text-red-650 font-semibold bg-red-50 p-2.5 rounded-xl border border-red-100">
              {{ addError }}
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" @click="isAddModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 font-semibold cursor-pointer">Hủy</button>
              <button type="submit" :disabled="isSubmitting"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-3xs cursor-pointer disabled:opacity-50 flex items-center gap-1.5">
                <i v-if="isSubmitting" class="fa-solid fa-circle-notch animate-spin mr-1"></i>
                <span>Tạo thành viên</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal đặt lại mật khẩu thành viên -->
    <div v-if="passwordUser" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-xs" @click="closePasswordModal"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div>
              <h3 class="text-lg font-bold text-gray-900">Đặt lại mật khẩu</h3>
              <p class="text-xs text-gray-400 font-semibold mt-0.5">{{ passwordUser.name }}</p>
            </div>
            <button @click="closePasswordModal" type="button" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>
          <form @submit.prevent="updateMemberPassword" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Mật khẩu mới *</label>
              <input v-model="newPassword" required minlength="6" type="password" autocomplete="new-password"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-3xs text-gray-805"
                placeholder="Tối thiểu 6 ký tự" />
            </div>
            <p v-if="passwordError" class="text-xs text-red-650 font-semibold bg-red-50 p-2.5 rounded-xl border border-red-100">{{ passwordError }}</p>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" @click="closePasswordModal" class="px-4 py-2 text-sm text-gray-600 font-semibold cursor-pointer">Hủy</button>
              <button type="submit" :disabled="isUpdatingPassword"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-3xs cursor-pointer disabled:opacity-50 flex items-center gap-1.5">
                <i v-if="isUpdatingPassword" class="fa-solid fa-circle-notch animate-spin mr-1"></i>
                <span>Cập nhật mật khẩu</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import { useAuthStore } from '../stores/auth'
import { useConfirmStore } from '../stores/confirm'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const authStore = useAuthStore()
const confirmStore = useConfirmStore()
const toast = useToastStore()

const users = ref([])
const isLoading = ref(true)
const searchQuery = ref('')

const currentUserId = computed(() => authStore.user?.id)

// Add Member Modal State
const isAddModalOpen = ref(false)
const isSubmitting = ref(false)
const addError = ref('')
const passwordUser = ref(null)
const newPassword = ref('')
const passwordError = ref('')
const isUpdatingPassword = ref(false)

const addForm = reactive({
  name: '',
  email: '',
  password: 'Ringnet@123',
  avatar: ''
})

const openAddModal = () => {
  addForm.name = ''
  addForm.email = ''
  addForm.password = 'Ringnet@123'
  addForm.avatar = ''
  addError.value = ''
  isAddModalOpen.value = true
}

const handleAddUser = async () => {
  try {
    isSubmitting.value = true
    addError.value = ''
    await axios.post('/api/users', addForm)
    isAddModalOpen.value = false
    await fetchUsers()
  } catch (err) {
    console.error('Failed to create user:', err)
    addError.value = err.response?.data?.message || 'Không thể tạo thành viên.'
  } finally {
    isSubmitting.value = false
  }
}

const openPasswordModal = (user) => {
  passwordUser.value = user
  newPassword.value = ''
  passwordError.value = ''
}

const closePasswordModal = () => {
  passwordUser.value = null
  newPassword.value = ''
  passwordError.value = ''
}

const updateMemberPassword = async () => {
  if (!passwordUser.value) return

  try {
    isUpdatingPassword.value = true
    passwordError.value = ''
    await axios.put(`/api/users/${passwordUser.value.id}/password`, { password: newPassword.value })
    toast.success(`Đã cập nhật mật khẩu cho ${passwordUser.value.name}.`)
    closePasswordModal()
  } catch (err) {
    console.error('Failed to update member password:', err)
    passwordError.value = err.response?.data?.message || 'Không thể cập nhật mật khẩu.'
  } finally {
    isUpdatingPassword.value = false
  }
}

const fetchUsers = async () => {
  try {
    isLoading.value = true
    const res = await axios.get('/api/users')
    users.value = res.data || []
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    isLoading.value = false
  }
}

const confirmDeleteUser = async (user) => {
  const confirmed = await confirmStore.show({
    title: 'Xóa thành viên',
    message: `Bạn có chắc chắn muốn xóa thành viên "${user.name}" khỏi hệ thống? Tất cả các bình luận của họ sẽ bị xóa, và các dự án, công việc họ đang quản lý/phụ trách sẽ trống người đảm nhiệm.`
  })

  if (!confirmed) return

  try {
    await axios.delete(`/api/users/${user.id}`)
    await fetchUsers()
  } catch (err) {
    console.error('Failed to delete user:', err)
    alert(err.response?.data?.message || 'Xóa thành viên thất bại')
  }
}

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  return users.value.filter(u => 
    u.name.toLowerCase().includes(q) || 
    (u.email && u.email.toLowerCase().includes(q))
  )
})

const filterProjectsByUser = (userId) => {
  router.push(`/projects?participant=${userId}`)
}

onMounted(() => {
  fetchUsers()
})
</script>
