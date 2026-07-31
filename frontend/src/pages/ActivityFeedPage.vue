<template>
  <div class="min-h-screen bg-[#f8faf9] pb-24">
    <Navbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Title & Subtitle -->
      <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading">Có gì mới</h1>
        <p class="text-gray-500 text-sm mt-1 font-medium">Cập nhật mới từ team và dự án</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 3" :key="'skel-' + i" class="bg-white rounded-2xl p-5 border border-gray-100 flex items-start gap-4 animate-pulse">
          <div class="w-12 h-4 bg-gray-200 rounded-md"></div>
          <div class="w-10 h-10 rounded-full bg-gray-200"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 w-1/4 rounded-md"></div>
            <div class="h-3 bg-gray-150 w-1/3 rounded-md"></div>
            <div class="h-12 bg-gray-100 w-full rounded-md"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="activities.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs">
        <p class="text-gray-400 font-medium">Chưa có cập nhật hay hoạt động nào mới.</p>
      </div>

      <!-- Grouped Activities Feed -->
      <div v-else class="space-y-6">
        <div v-for="(group, dateStr) in groupedActivities" :key="dateStr" class="space-y-3">
          <!-- Date Header -->
          <h2 class="text-lg font-bold text-gray-900 font-heading mb-1.5">{{ dateStr }}</h2>
          
          <!-- Cards List -->
          <div class="space-y-3">
            <div
              v-for="act in group"
              :key="act.id"
              @click="goToProject(act.project_id)"
              class="bg-white rounded-2xl p-5 border border-gray-100 shadow-2xs hover:shadow-xs hover:border-emerald-100/70 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex items-start gap-4 select-none group"
            >
              <!-- Left: Timestamp -->
              <span class="text-xs font-semibold text-gray-400 w-12 pt-1 flex-shrink-0">
                {{ formatTime(act.created_at) }}
              </span>

              <!-- User avatar -->
              <img
                :src="act.user?.avatar || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'"
                :alt="act.user?.name"
                class="w-10 h-10 rounded-full object-cover border border-emerald-200 flex-shrink-0"
              />

              <!-- Right: Main Details -->
              <div class="flex-grow min-w-0">
                <!-- User name -->
                <div class="font-bold text-gray-900 text-base leading-snug">
                  {{ act.user?.name || 'Thành viên' }}
                </div>
                
                <!-- Project Name + Status Dot -->
                <div class="flex flex-wrap items-center gap-2 mt-1">
                  <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0" :class="statusDotClass(act.project?.health)"></span>
                  <span class="font-bold text-gray-900 text-sm group-hover:text-emerald-700 transition-colors">
                    {{ act.project?.title || 'Dự án' }}
                  </span>
                </div>

                <!-- Customer Details -->
                <div v-if="act.project?.customer" class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1">
                  Khách hàng: <span class="text-gray-500 font-semibold">{{ act.project.customer.name }}</span>
                </div>

                <!-- Description / Log Content -->
                <p class="text-sm text-gray-600 mt-2.5 leading-relaxed whitespace-pre-line font-medium">
                  {{ act.content }}
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- Bottom Fixed Navigation Bar -->
    <BottomNav />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'

const router = useRouter()
const activities = ref([])
const isLoading = ref(true)

const fetchActivities = async () => {
  isLoading.value = true
  try {
    const res = await axios.get('/api/comments')
    // Filter only comments/activities associated with a project
    activities.value = res.data.filter(c => c.project_id)
  } catch (err) {
    console.error('Failed to load activity feed:', err)
  } finally {
    isLoading.value = false
  }
}

// Group comments by date headers (Hôm nay, Hôm qua, or specific date string)
const groupedActivities = computed(() => {
  const groups = {}
  
  activities.value.forEach(item => {
    if (!item.created_at) return
    const date = new Date(item.created_at)
    const today = new Date()
    const yesterday = new Date()
    yesterday.setDate(today.getDate() - 1)

    let dateStr = ''
    if (date.toDateString() === today.toDateString()) {
      dateStr = 'Hôm nay'
    } else if (date.toDateString() === yesterday.toDateString()) {
      dateStr = 'Hôm qua'
    } else {
      const pad = (n) => String(n).padStart(2, '0')
      dateStr = `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`
    }

    if (!groups[dateStr]) {
      groups[dateStr] = []
    }
    groups[dateStr].push(item)
  })
  
  return groups
})

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const pad = (n) => String(n).padStart(2, '0')
  return `${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const statusDotClass = (health) => {
  if (health === 'yellow') return 'bg-amber-400'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-emerald-500'
  return 'bg-gray-400'
}

const goToProject = (projectId) => {
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

onMounted(() => {
  fetchActivities()
})
</script>
