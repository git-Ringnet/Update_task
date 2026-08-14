<template>
  <div class="min-h-screen bg-[#F9F4EE] pb-24">
    <Navbar />

    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Back Button -->
      <button
        @click="goBack"
        type="button"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium mb-4 transition-colors cursor-pointer focus:outline-none"
      >
        <i class="fa-solid fa-arrow-left text-xs"></i>
        <span>Quay lại</span>
      </button>

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
              class="rounded-2xl p-5 shadow-2xs hover:shadow-xs hover:border-emerald-100/70 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex items-start gap-4 select-none group"
              :class="getActivityStyle(act)"
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
                <div class="flex flex-wrap items-start gap-2 mt-1 min-w-0">
                  <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0 mt-1.5" :class="statusDotClass(act.project?.health)"></span>
                  <span class="font-bold text-gray-900 text-sm group-hover:text-emerald-700 transition-colors break-words flex-1 min-w-0">
                    {{ act.project?.title || 'Dự án' }}
                  </span>
                </div>

                <!-- Customer Details -->
                <div v-if="act.project?.customer" class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1 break-words">
                  Khách hàng: <span class="text-gray-500 font-semibold">{{ act.project.customer.name }}</span>
                </div>

                <!-- Description / Log Content -->
                <div class="mt-2.5 text-xs text-gray-700 leading-relaxed font-semibold break-words space-y-2">
                  <p v-if="parseCommentText(act.content)" class="whitespace-pre-line font-medium text-gray-700">
                    {{ parseCommentText(act.content) }}
                  </p>

                  <!-- Render Compact Image Pills -->
                  <div v-if="parseCommentImages(act.content).length > 0" class="flex flex-wrap gap-1.5 pt-0.5">
                    <button 
                      v-for="(img, imgIdx) in parseCommentImages(act.content)" 
                      :key="imgIdx" 
                      type="button"
                      @click.stop="openImagePreview(img.url)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100/90 border border-emerald-200/80 text-emerald-800 rounded-lg text-xs font-bold transition-colors cursor-pointer max-w-full select-none shadow-3xs"
                      :title="img.name"
                    >
                      <i class="fa-solid fa-image text-emerald-600 text-[11px]"></i>
                      <span class="truncate max-w-[150px]">{{ img.name }}</span>
                      <i class="fa-solid fa-expand text-[9px] text-emerald-500 ml-0.5"></i>
                    </button>
                  </div>

                  <!-- Render Compact File Pills -->
                  <div v-if="parseCommentFiles(act.content).length > 0" class="flex flex-wrap gap-1.5 pt-0.5">
                    <a 
                      v-for="(file, fIdx) in parseCommentFiles(act.content)" 
                      :key="fIdx" 
                      :href="file.url" 
                      :download="file.name" 
                      target="_blank"
                      @click.stop
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200/90 border border-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors cursor-pointer max-w-full select-none shadow-3xs"
                      :title="file.name"
                    >
                      <i class="fa-solid fa-paperclip text-slate-500 text-[11px]"></i>
                      <span class="truncate max-w-[150px]">{{ file.name }}</span>
                      <i class="fa-solid fa-download text-[9px] text-slate-400 ml-0.5"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Load more container -->
          <div v-if="activities.length > displayLimit" class="pt-4 flex justify-center bg-[#F9F4EE] mb-4">
            <button
              @click="displayLimit += 15"
              type="button"
              class="px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100/80 text-emerald-800 font-extrabold text-xs rounded-xl shadow-3xs transition-all cursor-pointer flex items-center gap-1.5 focus:outline-none"
            >
              <i class="fa-solid fa-angles-down text-[10px]"></i>
              <span>Xem thêm hoạt động (Còn {{ activities.length - displayLimit }} hoạt động)</span>
            </button>
          </div>

        </div>
      </div>
    </main>

    <!-- Image Lightbox Modal -->
    <div v-if="activePreviewImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" @click="activePreviewImage = null">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
        <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
        <button 
          @click="activePreviewImage = null" 
          type="button" 
          class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 hover:bg-slate-900 text-white rounded-full flex items-center justify-center transition-colors shadow-lg cursor-pointer"
        >
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'


const router = useRouter()
const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}
const activities = ref([])
const isLoading = ref(true)
const displayLimit = ref(15)

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
  const sliced = activities.value.slice(0, displayLimit.value)
  
  sliced.forEach(item => {
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
  if (health === 'yellow' || health === 'white') return 'bg-white border border-gray-300 shadow-3xs'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-[#45A246]'
  return 'bg-gray-400'
}

const getActivityStyle = (act) => {
  const health = act.project_health
  if (health === 'green') {
    return 'bg-[#86efac] border-[#4ade80] border-2 text-gray-900'
  } else if (health === 'red') {
    return 'bg-[#fca5a5] border-[#f87171] border-2 text-gray-900'
  }
  return 'bg-white border-gray-300 border text-gray-800'
}

const activePreviewImage = ref(null)
const openImagePreview = (url) => {
  activePreviewImage.value = url
}

const parseCommentText = (content) => {
  if (!content) return ''
  return content
    .replace(/!\[.*?\]\((.*?)\)/g, '')
    .replace(/📎\s*\[(.*?)\]\((.*?)\)/g, '')
    .replace(/<img[^>]*>/gi, '')
    .replace(/<a[^>]*>📎\s*Tệp đính kèm:[^<]*<\/a>/gi, '')
    .replace(/<span[^>]*>📎\s*Tệp đính kèm:[^<]*<\/span>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/<br\s*\/?>/gi, ' ')
    .trim()
}

const parseCommentImages = (content) => {
  if (!content) return []
  const matches = []
  
  // 1. Markdown images ![name](url)
  const mdRegex = /!\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = mdRegex.exec(content)) !== null) {
    matches.push({ name: m[1] || 'Hình ảnh', url: m[2] })
  }

  // 2. HTML <img> tags <img src="url" ...>
  const htmlRegex = /<img[^>]+src="([^"]*)"[^>]*>/gi
  while ((m = htmlRegex.exec(content)) !== null) {
    let fileName = 'Hình ảnh đính kèm'
    const src = m[1]
    if (src && !src.startsWith('data:')) {
      const urlParts = src.split('/')
      const lastPart = urlParts[urlParts.length - 1]?.split('?')[0]
      if (lastPart) fileName = decodeURIComponent(lastPart)
    }
    matches.push({ name: fileName, url: src })
  }

  return matches
}

const parseCommentFiles = (content) => {
  if (!content) return []
  const matches = []

  // 1. Markdown files 📎 [name](url)
  const mdRegex = /📎\s*\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = mdRegex.exec(content)) !== null) {
    matches.push({ name: m[1] || 'Tài liệu', url: m[2] })
  }

  // 2. HTML file spans <span...>📎 Tệp đính kèm: name</span>
  const htmlRegex = /<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi
  while ((m = htmlRegex.exec(content)) !== null) {
    const rawName = m[1].trim()
    matches.push({ name: rawName, url: '#' })
  }

  return matches
}

const goToProject = (projectId) => {
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' || e.code === 'Escape') {
    if (activePreviewImage.value) {
      activePreviewImage.value = null
    }
  }
}

onMounted(() => {
  fetchActivities()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>
