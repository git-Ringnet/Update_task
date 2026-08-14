<template>
  <div class="min-h-screen bg-[#F9F4EE] pb-24">
    <!-- Navbar Component -->
    <Navbar />

    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      
      <!-- Back Link & Top Header -->
      <div>
        <button
          @click="goBack"
          type="button"
          class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#45A246] font-medium mb-3 transition-colors cursor-pointer focus:outline-none"
        >
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại</span>
        </button>

        <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-5 min-w-0">
          <!-- Title & Subtitle -->
          <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight font-heading flex items-start gap-3">
              <span class="w-5 h-5 rounded-full inline-block flex-shrink-0 mt-1.5" :class="statusDotClass(customer.status)"></span>
              <span class="break-words min-w-0 flex-1">{{ customer.name }}</span>
            </h1>
            <p class="text-gray-500 text-sm mt-1 font-semibold">
              {{ formatType(customer.type) }}
            </p>
          </div>

          <!-- Edit Relationship Button -->
          <button @click="startEditCustomer" type="button"
            class="px-4 py-2 bg-transparent hover:bg-gray-100/50 border border-gray-300 text-gray-700 font-bold text-xs rounded-xl shadow-3xs transition-colors cursor-pointer flex items-center gap-1.5 focus:outline-none">
            <i class="fa-regular fa-pen-to-square text-xs text-[#45A246]"></i>
            <span class="text-gray-800">Chỉnh sửa</span>
          </button>
        </div>
      </div>

      <!-- Projects Section -->
      <div class="bg-transparent rounded-2xl p-6 border border-gray-300 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
            DỰ ÁN ({{ relatedProjects.length }})
          </h3>
        </div>

        <div class="divide-y divide-gray-300">
          <router-link
            v-for="p in displayedProjects"
            :key="p.id"
            :to="`/projects/${p.id}`"
            class="py-3.5 flex items-center justify-between gap-4 hover:bg-[#45A246]/10 px-2 rounded-xl transition-colors group"
          >
            <div>
              <div class="font-bold text-gray-900 text-sm group-hover:text-[#45A246] flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0" :class="statusDotClass(p.health)"></span>
                <span>{{ p.title }}</span>
              </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
              <span class="text-xs text-gray-400 font-medium">Cập nhật {{ formatRelativeTime(p.last_activity_at) }}</span>
              <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-gray-600 text-xs"></i>
            </div>
          </router-link>

          <!-- Load more projects button -->
          <div v-if="relatedProjects.length > displayLimit" class="pt-4 flex justify-center">
            <button
              @click="displayLimit += 10"
              type="button"
              class="px-4 py-2 bg-[#45A246]/10 hover:bg-[#45A246]/20 text-[#45A246] font-bold text-xs rounded-xl transition-colors cursor-pointer flex items-center gap-1.5 focus:outline-none"
            >
              <i class="fa-solid fa-angles-down text-[10px]"></i>
              <span>Xem thêm dự án (Còn {{ relatedProjects.length - displayLimit }})</span>
            </button>
          </div>

          <!-- Empty Projects State -->
          <div v-if="relatedProjects.length === 0" class="py-8 text-center text-gray-400 text-sm">
            Chưa có dự án nào cho khách hàng này.
          </div>
        </div>
      </div>

    </main>

    <!-- Edit Relationship Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="isModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">
              Chỉnh Sửa Mối Quan Hệ
            </h3>
            <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>
          <form @submit.prevent="handleUpdateCustomer" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên đối tác / Khách hàng *</label>
              <input ref="nameInputRef" v-model="form.name" required type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-[#45A246] focus:ring-2 focus:ring-[#45A246]/20 transition-all shadow-3xs"
                placeholder="VD: Trung Nguyên Coffee" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Phân loại *</label>
                <select v-model="form.type" required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white font-semibold focus:outline-none focus:border-[#45A246] focus:ring-2 focus:ring-[#45A246]/20 transition-all">
                  <option value="customer">Khách hàng</option>
                  <option value="vendor">Vendor / Partner</option>
                  <option value="internal">Nội bộ</option>
                  <option value="other">Khác</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tình trạng *</label>
                <select v-model="form.status" required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white font-semibold focus:outline-none focus:border-[#45A246] focus:ring-2 focus:ring-[#45A246]/20 transition-all">
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
                Lưu thay đổi
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
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'

const route = useRoute()
const router = useRouter()
const customerId = route.params.id || 1

const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}

const customer = ref({
  name: 'Trung Nguyên Coffee',
  type: 'customer',
  status: 'green',
  updater: { name: 'Minh' }
})

const relatedProjects = ref([])
const displayLimit = ref(10)

const isModalOpen = ref(false)
const nameInputRef = ref(null)

watch(isModalOpen, async (newVal) => {
  if (newVal) {
    await nextTick()
    nameInputRef.value?.focus()
  }
})
const form = reactive({
  name: '',
  type: 'customer',
  status: 'green'
})

const startEditCustomer = () => {
  form.name = customer.value.name
  form.type = customer.value.type
  form.status = customer.value.status
  isModalOpen.value = true
}

const handleUpdateCustomer = async () => {
  try {
    await axios.put(`/api/customers/${customerId}`, {
      name: form.name,
      type: form.type,
      status: form.status
    })
    isModalOpen.value = false
    fetchCustomerDetail()
  } catch (err) {
    console.error('Failed to update customer:', err)
  }
}

const displayedProjects = computed(() => {
  return relatedProjects.value.slice(0, displayLimit.value)
})

const fetchCustomerDetail = async () => {
  displayLimit.value = 10
  try {
    const res = await axios.get(`/api/customers/${customerId}`)
    if (res.data) {
      customer.value = res.data
      relatedProjects.value = res.data.projects || []
    }
  } catch (err) {
    console.error('Failed to fetch customer detail:', err)
  }
}

const formatType = (type) => {
  if (type === 'customer') return 'Khách hàng'
  if (type === 'vendor') return 'Vendor / Partner'
  if (type === 'internal') return 'Nội bộ'
  return 'Khác'
}

const statusDotClass = (status) => {
  if (status === 'yellow' || status === 'white') return 'bg-white border border-gray-300 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  if (status === 'green') return 'bg-[#45A246] health-dot-green'
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
  fetchCustomerDetail()
})
</script>
