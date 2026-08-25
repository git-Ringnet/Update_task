<template>
  <div class="min-h-screen bg-[#F9F4EE] flex flex-col justify-between pb-24">
    <div>
      <!-- Navbar Component -->
      <Navbar />

      <!-- Main Container -->
      <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Title & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
          <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading">Mối quan hệ</h1>
          </div>
          
          <div class="flex items-center gap-2">
            <!-- Add Button -->
            <button @click="startCreateCustomer()" type="button"
              class="px-5 py-2.5 bg-[#45A246] hover:bg-[#3a903b] text-white font-bold text-sm rounded-xl shadow-2xs transition-colors flex items-center gap-2 flex-shrink-0 cursor-pointer">
              <i class="fa-solid fa-plus text-xs"></i>
              <span>Thêm mối quan hệ</span>
            </button>

            <!-- Search Toggle Button -->
            <button
              @click="isSearchOpen = !isSearchOpen"
              type="button"
              class="w-10 h-10 border border-gray-200 hover:bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 transition-colors cursor-pointer bg-transparent"
            >
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>

        <!-- Search Bar Drawer -->
        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform opacity-0 -translate-y-2"
          enter-to-class="transform opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform opacity-100 translate-y-0"
          leave-to-class="transform opacity-0 -translate-y-2"
        >
          <div v-if="isSearchOpen" class="mb-6 bg-gray-50/50 p-4 border border-gray-200/60 rounded-2xl">
            <div class="relative max-w-md">
              <input
                ref="searchInputRef"
                v-model="searchQueryLocal"
                @input="handleSearchLocal"
                type="text"
                placeholder="Tìm kiếm mối quan hệ theo tên hoặc mã..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#45A246] focus:ring-1 focus:ring-[#45A246] bg-white"
              />
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
              </span>
            </div>
          </div>
        </transition>

        <!-- Status Filter Tabs -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">

          <!-- Filter Tabs -->
          <div class="flex flex-wrap items-center gap-3">
            <!-- Tất cả -->
            <button @click="setTab('all')" type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="activeType === 'all'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'">
              <span>Tất cả</span>
              <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                :class="activeType === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'">
                {{ counts.all || 0 }}
              </span>
            </button>

            <!-- Khách hàng -->
            <button @click="setTab('customer')" type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="activeType === 'customer'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'">
              <span>Khách hàng</span>
              <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                :class="activeType === 'customer' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'">
                {{ counts.customer || 0 }}
              </span>
            </button>

            <!-- Vendor / Partner -->
            <button @click="setTab('vendor')" type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="activeType === 'vendor'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'">
              <span>Vendor / Partner</span>
              <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                :class="activeType === 'vendor' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'">
                {{ counts.vendor || 0 }}
              </span>
            </button>

            <!-- Nội bộ -->
            <button @click="setTab('internal')" type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="activeType === 'internal'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'">
              <span>Nội bộ</span>
              <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                :class="activeType === 'internal' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'">
                {{ counts.internal || 0 }}
              </span>
            </button>

            <!-- Khác -->
            <button @click="setTab('other')" type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="activeType === 'other'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'">
              <span>Khác</span>
              <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                :class="activeType === 'other' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'">
                {{ counts.other || 0 }}
              </span>
            </button>
          </div>
        </div>

        <!-- Table Card Container -->
        <div class="bg-transparent rounded-2xl border border-gray-300 shadow-xs overflow-hidden">
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="border-b border-gray-300 text-xs font-bold text-gray-400 uppercase tracking-wider bg-transparent">
                  <th scope="col" class="py-3.5 px-6">MỐI QUAN HỆ</th>
                  <th scope="col" class="py-3.5 px-6">SỐ DỰ ÁN</th>
                  <th scope="col" class="py-3.5 px-6">CẬP NHẬT GẦN NHẤT</th>
                  <th scope="col" class="py-3.5 px-4 text-right"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-300 text-sm">
                <!-- Skeleton Loading Rows -->
                <template v-if="isLoading">
                  <tr v-for="i in 5" :key="'skeleton-' + i" class="animate-pulse">
                    <td class="py-4 px-6">
                      <div class="h-4 bg-gray-200/80 rounded-md w-3/4 mb-2"></div>
                      <div class="h-3 bg-gray-100/90 rounded-md w-1/2"></div>
                    </td>
                    <td class="py-4 px-6 align-middle">
                      <div class="h-4 bg-gray-200/80 rounded-md w-12"></div>
                    </td>
                    <td class="py-4 px-6 align-middle">
                      <div class="h-4 bg-gray-200/80 rounded-md w-28 mb-1"></div>
                      <div class="h-3 bg-gray-100/90 rounded-md w-20"></div>
                    </td>
                    <td class="py-4 px-4 text-right align-middle">
                      <div class="w-4 h-4 bg-gray-100/80 rounded-md ml-auto"></div>
                    </td>
                  </tr>
                </template>

                <!-- Empty State -->
                <tr v-else-if="customers.length === 0">
                  <td colspan="4" class="py-12 text-center text-gray-400">
                    Không tìm thấy mối quan hệ nào trong mục này.
                  </td>
                </tr>

                <!-- Relationship Rows -->
                <tr v-for="(c, index) in displayedCustomers" :key="c.id" @click="$router.push(`/customers/${c.id}`)"
                  class="hover:bg-emerald-50/20 transition-colors group animate-fade-in-up cursor-pointer"
                  :style="{ animationDelay: `${index * 45}ms` }">
                  <!-- Name & Category Subtitle -->
                  <td class="py-4 px-6 max-w-[250px] md:max-w-md">
                    <div
                      class="font-bold text-gray-900 text-base leading-snug font-heading group-hover:text-emerald-700 transition-colors break-words">
                      {{ c.name }}
                    </div>
                    <div class="text-xs text-gray-500 font-medium mt-0.5">
                      {{ formatType(c.type) }}
                    </div>
                  </td>

                  <!-- Projects count -->
                  <td class="py-4 px-6 align-middle">
                    <div class="font-bold text-gray-900 text-base leading-none">
                      {{ c.projects_count || 0 }}
                    </div>
                    <div class="text-xs text-gray-400 font-normal mt-1">
                      dự án
                    </div>
                  </td>

                  <!-- Last update -->
                  <td class="py-4 px-6 align-middle">
                    <div class="text-gray-700 font-medium text-sm">
                      {{ formatRelativeTime(c.last_activity_at || c.updated_at) }}
                    </div>
                    <div class="text-xs text-gray-400 font-normal mt-0.5">
                      {{ c.updater ? c.updater.name : 'Minh' }} cập nhật
                    </div>
                  </td>

                  <!-- Action Buttons -->
                  <td class="py-4 px-4 text-right align-middle">
                    <div class="flex items-center justify-end gap-2">
                      <button @click.stop="startEditCustomer(c)" type="button"
                        class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                        title="Chỉnh sửa">
                        <i class="fa-solid fa-pen text-sm"></i>
                      </button>
                      <button @click.stop="handleDeleteCustomer(c.id)" type="button"
                        class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                        title="Xóa">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                      </button>
                      <i
                        class="fa-solid fa-chevron-right text-gray-300 group-hover:text-gray-600 text-sm transition-colors ml-1"></i>
                    </div>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

          <!-- Giao diện Điện thoại: Danh sách Card Khách hàng (Ẩn trên Desktop) -->
          <div class="block md:hidden divide-y divide-gray-300 bg-transparent rounded-t-2xl overflow-hidden">
            <!-- Skeleton loading state -->
            <template v-if="isLoading && customers.length === 0">
              <div v-for="i in 3" :key="'skeleton-card-' + i" class="p-4 animate-pulse space-y-3">
                <div class="flex items-center justify-between">
                  <div class="h-4 bg-gray-200 rounded-md w-2/3"></div>
                  <div class="w-10 h-4 bg-gray-200 rounded-md"></div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="h-3 bg-gray-100 rounded-md w-20"></div>
                </div>
              </div>
            </template>

            <!-- Empty state -->
            <div v-else-if="customers.length === 0" class="py-12 text-center text-gray-400 text-sm">
              Không tìm thấy mối quan hệ nào trong mục này.
            </div>

            <!-- Customer Card List -->
            <div v-for="(c, index) in displayedCustomers" :key="'card-' + c.id"
              @click="$router.push(`/customers/${c.id}`)"
              class="p-4 hover:bg-emerald-50/20 active:bg-emerald-50/30 transition-colors flex flex-col gap-2.5 relative cursor-pointer">
              <!-- Row 1: Name + Category & Edit/Delete Buttons -->
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <h3
                    class="font-bold text-gray-900 text-base leading-snug break-words font-heading group-hover:text-emerald-700">
                    {{ c.name }}
                  </h3>
                  <p class="text-xs text-gray-500 font-semibold mt-0.5">
                    {{ formatType(c.type) }}
                  </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1.5 flex-shrink-0" @click.stop>
                  <button @click="startEditCustomer(c)" type="button"
                    class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                    title="Chỉnh sửa">
                    <i class="fa-solid fa-pen text-sm"></i>
                  </button>
                  <button @click="handleDeleteCustomer(c.id)" type="button"
                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                    title="Xóa">
                    <i class="fa-solid fa-trash-can text-sm"></i>
                  </button>
                </div>
              </div>

              <!-- Row 2: Projects count + Time -->
              <div class="flex items-center justify-between gap-3 pt-1 text-xs text-gray-500">
                <div class="flex flex-wrap items-center gap-3 min-w-0">
                  <!-- Projects count -->
                  <span class="font-bold text-gray-800">
                    {{ c.projects_count || 0 }} dự án
                  </span>

                  <!-- Divider -->
                  <span class="text-gray-300">•</span>

                  <!-- Last update time -->
                  <span class="font-medium truncate text-[11px]">
                    Cập nhật {{ formatRelativeTime(c.last_activity_at || c.updated_at) }} (by {{ c.updater ?
                      c.updater.name :
                    'Minh' }})
                  </span>
                </div>

                <!-- Right chevron -->
                <div class="flex-shrink-0 text-gray-300">
                  <i class="fa-solid fa-chevron-right text-xs"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="p-4 border-t border-gray-300 flex items-center justify-between bg-transparent select-none">
            <div class="text-xs text-gray-500 font-semibold">
              Trang {{ currentPage }} / {{ totalPages }} ({{ customers.length }} đối tác)
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                type="button"
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-bold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
                :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700'"
              >
                <i class="fa-solid fa-chevron-left"></i>
              </button>
              
              <div class="flex items-center gap-1">
                <template v-for="page in totalPages" :key="page">
                  <button
                    v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                    @click="goToPage(page)"
                    type="button"
                    class="w-8 h-8 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                    :class="page === currentPage ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'"
                  >
                    {{ page }}
                  </button>
                  <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-1 text-gray-400">...</span>
                </template>
              </div>
              
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                type="button"
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-bold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
                :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700'"
              >
                <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
          </div>

        </div>

      </main>

    </div>

    <!-- Create / Edit Relationship Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="isModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">
              {{ editingCustomerId ? 'Chỉnh Sửa Mối Quan Hệ' : 'Thêm Mối Quan Hệ Mới' }}
            </h3>
            <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>
          <form @submit.prevent="handleCreateCustomer" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên đối tác / Khách hàng *</label>
              <input ref="nameInputRef" v-model="form.name" required type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-[#45A246] focus:ring-2 focus:ring-[#45A246]/20 transition-all shadow-3xs"
                placeholder="VD: Dell Technologies" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Phân loại *</label>
                <select v-model="form.type" required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                  <option value="customer">Khách hàng</option>
                  <option value="vendor">Vendor / Partner</option>
                  <option value="internal">Nội bộ</option>
                  <option value="other">Khác</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tình trạng *</label>
                <select v-model="form.status" required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                  <option value="green">🟢 Xanh (Đang tốt)</option>
                  <option value="yellow">🟡 Vàng (Thiếu quan tâm)</option>
                  <option value="red">🔴 Đỏ (Bỏ mặc)</option>
                </select>
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" @click="isModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 font-semibold cursor-pointer">Hủy</button>
              <button type="submit"
                class="px-5 py-2 bg-[#45A246] hover:bg-[#3a903b] text-white rounded-xl text-sm font-semibold shadow-3xs cursor-pointer">
                {{ editingCustomerId ? 'Lưu thay đổi' : 'Tạo ngay' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted } from 'vue'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'

import HealthStatusSelector from '../components/HealthStatusSelector.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'

const authStore = useAuthStore()
const toast = useToastStore()
const confirmStore = useConfirmStore()

const customers = ref([])
const counts = ref({ all: 0, customer: 0, vendor: 0, internal: 0, other: 0 })
const activeType = ref('all')
const isLoading = ref(false)
const isModalOpen = ref(false)
const nameInputRef = ref(null)

const searchQueryLocal = ref('')
const isSearchOpen = ref(false)
const searchInputRef = ref(null)

const handleSearchLocal = () => {
  currentPage.value = 1
  sessionStorage.setItem('customers-page', 1)
  fetchCustomers()
}

watch(isSearchOpen, (newVal) => {
  currentPage.value = 1
  sessionStorage.setItem('customers-page', 1)
  if (newVal) {
    nextTick(() => {
      searchInputRef.value?.focus()
    })
  } else {
    searchQueryLocal.value = ''
    fetchCustomers()
  }
})

watch(isModalOpen, async (newVal) => {
  if (newVal) {
    await nextTick()
    nameInputRef.value?.focus()
  }
})
const editingCustomerId = ref(null)

const currentPage = ref(Number(sessionStorage.getItem('customers-page') || 1))
const itemsPerPage = 15

const displayedCustomers = computed(() => {
  // Adjust current page if it is out of bounds
  const total = Math.ceil(customers.value.length / itemsPerPage)
  if (currentPage.value > total && total > 0) {
    currentPage.value = total
    sessionStorage.setItem('customers-page', total)
  }
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return customers.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(customers.value.length / itemsPerPage)
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    sessionStorage.setItem('customers-page', page)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const startCreateCustomer = () => {
  editingCustomerId.value = null
  form.name = ''
  form.code = ''
  form.type = 'customer'
  form.status = 'green'
  isModalOpen.value = true
}

const startEditCustomer = (c) => {
  editingCustomerId.value = c.id
  form.name = c.name
  form.code = c.code || ''
  form.type = c.type
  form.status = c.status
  isModalOpen.value = true
}

const handleDeleteCustomer = async (customerId) => {
  const confirmed = await confirmStore.show({
    title: 'Xóa mối quan hệ',
    message: 'Bạn có chắc chắn muốn xóa mối quan hệ này?'
  })
  if (!confirmed) return
  try {
    await axios.delete(`/api/customers/${customerId}`)
    toast.success('Đã xóa mối quan hệ thành công!')
    fetchCustomers(true)
  } catch (err) {
    console.error('Failed to delete relationship:', err)
    toast.error('Xóa mối quan hệ thất bại!')
  }
}

const handleStatusChange = async (customerId, newStatus) => {
  const customer = customers.value.find(c => c.id === customerId)
  if (customer) {
    customer.status = newStatus
  }
  try {
    await axios.put(`/api/customers/${customerId}`, { status: newStatus })
    toast.success('Cập nhật tình trạng thành công!')
    fetchCustomers(true)
  } catch (err) {
    console.error('Failed to update relationship status:', err)
    toast.error('Cập nhật tình trạng thất bại!')
  }
}

const form = reactive({
  name: '',
  code: '',
  type: 'customer',
  status: 'green',
  contact_name: '',
  phone: '',
})

const fetchCustomers = async (isSilent = false) => {
  if (!isSilent && customers.value.length === 0) {
    isLoading.value = true
  }
  try {
    const res = await axios.get('/api/customers', {
      params: { 
        type: activeType.value,
        search: searchQueryLocal.value
      }
    })
    customers.value = res.data.customers
    counts.value = res.data.counts
  } catch (err) {
    console.error('Failed to fetch relationships:', err)
  } finally {
    isLoading.value = false
  }
}

const setTab = (type) => {
  currentPage.value = 1
  sessionStorage.setItem('customers-page', 1)
  activeType.value = type
  fetchCustomers()
}

const handleCreateCustomer = async () => {
  try {
    if (editingCustomerId.value) {
      await axios.put(`/api/customers/${editingCustomerId.value}`, {
        ...form,
        user_id: authStore.user?.id
      })
      toast.success('Cập nhật mối quan hệ thành công!')
    } else {
      await axios.post('/api/customers', {
        ...form,
        user_id: authStore.user?.id
      })
      toast.success('Thêm mối quan hệ thành công!')
    }
    form.name = ''
    form.code = ''
    isModalOpen.value = false
    editingCustomerId.value = null
    fetchCustomers(true)
  } catch (err) {
    console.error('Failed to save relationship:', err)
    toast.error('Lưu mối quan hệ thất bại!')
  }
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatType = (type) => {
  if (type === 'customer') return 'Khách hàng'
  if (type === 'vendor') return 'Vendor / Partner'
  if (type === 'internal') return 'Nội bộ'
  return 'Khác'
}

const formatStatusTitle = (status) => {
  if (status === 'green') return 'Xanh'
  if (status === 'yellow') return 'Vàng'
  if (status === 'red') return 'Đỏ'
  return status
}

const formatStatusSubtext = (status) => {
  if (status === 'green') return 'Đang tốt'
  if (status === 'yellow') return 'Thiếu quan tâm'
  if (status === 'red') return 'Bỏ mặc'
  return ''
}

const statusColorClass = (status) => {
  if (status === 'green') return 'text-emerald-600'
  if (status === 'yellow') return 'text-amber-500'
  if (status === 'red') return 'text-rose-500'
  return 'text-gray-700'
}

const statusDotClass = (status) => {
  if (status === 'green') return 'bg-[#45A246] health-dot-green'
  if (status === 'yellow' || status === 'white') return 'bg-white border border-gray-300 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  return 'bg-gray-400'
}

const formatRelativeTime = (dateStr) => {
  if (!dateStr) return 'Vừa xong'
  const date = new Date(dateStr)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)

  if (diffSec < 60) return 'Vừa xong'
  const diffMin = Math.floor(diffSec / 60)
  if (diffMin < 60) return `${diffMin} phút trước`
  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours} giờ trước`
  const diffDays = Math.floor(diffHours / 24)
  return `${diffDays} ngày trước`
}

onMounted(() => {
  fetchCustomers()
})
</script>
