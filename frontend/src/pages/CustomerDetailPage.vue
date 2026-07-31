<template>
  <div class="min-h-screen bg-[#f8faf9] pb-24">
    <!-- Navbar Component -->
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      
      <!-- Back Link & Top Header -->
      <div>
        <router-link to="/customers" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium mb-3 transition-colors">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại danh sách</span>
        </router-link>

        <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-5">
          <!-- Title & Subtitle -->
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight font-heading flex items-center gap-3">
              <span class="w-5 h-5 rounded-full inline-block flex-shrink-0" :class="statusDotClass(customer.status)"></span>
              <span>{{ customer.name || 'Trung Nguyên Coffee' }}</span>
            </h1>
            <p class="text-gray-500 text-sm mt-1 font-semibold">
              {{ formatType(customer.type) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Projects Section -->
      <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
            DỰ ÁN ({{ relatedProjects.length }})
          </h3>
        </div>

        <div class="divide-y divide-gray-100">
          <router-link
            v-for="p in relatedProjects"
            :key="p.id"
            :to="`/projects/${p.id}`"
            class="py-3.5 flex items-center justify-between gap-4 hover:bg-emerald-50/30 px-2 rounded-xl transition-colors group"
          >
            <div>
              <div class="font-bold text-gray-900 text-sm group-hover:text-emerald-700 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0" :class="statusDotClass(p.health)"></span>
                <span>{{ p.title }}</span>
              </div>
              <div class="text-xs text-gray-400 mt-0.5 pl-4">
                Lead: {{ p.lead ? p.lead.name : 'An' }}
              </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
              <span class="text-xs text-gray-400 font-medium">Cập nhật {{ formatRelativeTime(p.last_activity_at) }}</span>
              <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-gray-600 text-xs"></i>
            </div>
          </router-link>

          <!-- Empty Projects State -->
          <div v-if="relatedProjects.length === 0" class="py-8 text-center text-gray-400 text-sm">
            Chưa có dự án nào cho khách hàng này.
          </div>
        </div>
      </div>

    </main>

    <!-- Bottom Navigation Bar -->
    <BottomNav />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'

const route = useRoute()
const customerId = route.params.id || 1

const customer = ref({
  name: 'Trung Nguyên Coffee',
  type: 'customer',
  status: 'green',
  updater: { name: 'Minh' }
})

const relatedProjects = ref([])

const fetchCustomerDetail = async () => {
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
  if (status === 'yellow') return 'bg-amber-400 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  if (status === 'green') return 'bg-emerald-500 health-dot-green'
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
