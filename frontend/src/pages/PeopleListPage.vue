<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-24">
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

          <!-- Search Bar -->
          <div class="relative w-full sm:w-72">
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
                  <i class="fa-solid fa-folder text-[10px] mr-1 text-amber-500"></i> {{ user.led_projects_count || 0 }} dự án lead
                </span>
              </div>
            </div>

            <!-- Footer Action -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
              <span class="text-xs text-gray-400 font-semibold">Hoạt động trong hệ thống</span>
              <button
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

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'

const router = useRouter()
const users = ref([])
const isLoading = ref(true)
const searchQuery = ref('')

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

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  return users.value.filter(u => 
    u.name.toLowerCase().includes(q) || 
    (u.email && u.email.toLowerCase().includes(q))
  )
})

const filterProjectsByUser = (userId) => {
  router.push(`/projects?lead=${userId}`)
}

onMounted(() => {
  fetchUsers()
})
</script>
