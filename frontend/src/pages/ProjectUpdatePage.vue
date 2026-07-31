<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-24">
    <div>
      <!-- Custom Header matching mockup -->
      <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          <!-- Left: Logo & Mascot -->
          <router-link to="/projects" class="flex items-center gap-2 group">
            <CactusLogo />
          </router-link>

          <!-- Center: Keyboard only mode badge -->
          <div class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold border border-emerald-100/60 shadow-2xs">
            <i class="fa-solid fa-bolt text-[10px] animate-pulse"></i>
            <span>Chế độ phím tắt</span>
            <span class="text-emerald-400">•</span>
            <span class="text-emerald-600 font-medium">Không cần dùng chuột</span>
          </div>

          <!-- Right: Profile Dropdown -->
          <div class="flex items-center gap-2.5">
            <img
              :src="currentUser.avatar"
              :alt="currentUser.name"
              class="w-9 h-9 rounded-full object-cover border-2 border-emerald-400/70"
            />
            <span class="text-base font-bold text-gray-900 font-heading">{{ currentUser.name }}</span>
          </div>
        </div>
      </header>

      <!-- Main Container -->
      <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Info (Progress & Keyboard hints) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-6">
          <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading">Chào buổi chiều, {{ currentUser.name }}! 👋</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Hôm nay bạn đã làm những gì?</p>
          </div>

          <!-- Progress stats and Finish all button -->
          <div class="flex flex-col items-end gap-2 bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-emerald-700 font-heading">
                Đã cập nhật {{ updatedCount }} / {{ totalCount }}
              </span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-48 h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div 
                class="h-full bg-emerald-600 rounded-full transition-all duration-300"
                :style="{ width: `${progressPercentage}%` }"
              ></div>
            </div>

            <!-- Finish All Action Button -->
            <button
              @click="handleFinishAll"
              type="button"
              class="mt-1 text-emerald-700 hover:text-emerald-950 font-bold text-xs flex items-center gap-1.5 transition-colors focus:outline-none"
            >
              <span>Hoàn thành tất cả</span>
              <kbd class="px-1.5 py-0.5 text-[9px] font-bold bg-gray-50 border border-emerald-200 text-emerald-800 rounded shadow-3xs select-none">F</kbd>
              <span class="text-[10px] text-gray-400 font-normal ml-0.5">(Ctrl + Enter)</span>
            </button>
          </div>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="space-y-4">
          <div v-for="i in 4" :key="'skel-' + i" class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4 animate-pulse">
            <div class="w-10 h-10 rounded-full bg-gray-200"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 w-1/4 rounded-md"></div>
              <div class="h-10 bg-gray-100 w-full rounded-xl"></div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="projects.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs">
          <p class="text-gray-400 font-medium">Không có dự án nào được chọn để cập nhật.</p>
          <router-link to="/projects" class="mt-4 inline-block text-sm font-semibold text-emerald-700 hover:underline">
            Quay lại danh sách dự án
          </router-link>
        </div>

        <!-- Selected Projects list to update -->
        <div v-else class="space-y-4">
          <div
            v-for="(project, index) in projects"
            :key="project.id"
            class="bg-white rounded-2xl p-5 border border-gray-100 shadow-2xs hover:shadow-xs transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-4"
          >
            <!-- Left section: project name, type icon, status -->
            <div class="flex items-center gap-3.5 min-w-0 md:w-1/3">
              <!-- Rounded Type Icon -->
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center text-base flex-shrink-0"
                :class="getIconData(project.title).bg"
              >
                <i :class="getIconData(project.title).icon"></i>
              </div>

              <!-- Title & Status -->
              <div class="min-w-0">
                <div class="font-bold text-gray-900 text-base leading-snug font-heading truncate">
                  {{ project.title }}
                </div>
                <!-- Status dot indicator -->
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="w-2 h-2 rounded-full inline-block" :class="statusDotClass(project.health)"></span>
                  <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">
                    {{ statusText(project.health) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Middle section: Textarea/Input for updates -->
            <div class="flex-1 min-w-0">
              <input
                v-model="updateTexts[project.id]"
                type="text"
                placeholder="Nhập nội dung cập nhật..."
                class="w-full px-4 py-3 bg-gray-50/70 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all shadow-3xs"
                @keydown.enter.exact.prevent="saveUpdate(project.id)"
              />
            </div>

            <!-- Right section: Update status indicator -->
            <div class="w-28 flex-shrink-0 text-right flex items-center justify-end gap-2.5 min-h-[24px]">
              <template v-if="isSaved[project.id]">
                <span class="text-xs text-gray-400 font-semibold">{{ savedTimes[project.id] }}</span>
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg transition-transform scale-110"></i>
              </template>
              <template v-else>
                <span class="text-xs text-gray-400 font-medium italic flex items-center gap-1 hover:text-gray-600 transition-colors">
                  <span>Chưa có cập nhật</span>
                  <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
              </template>
            </div>
          </div>
        </div>

      </main>
    </div>

    <!-- Bottom Navigation Bar -->
    <BottomNav />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import CactusLogo from '../components/CactusLogo.vue'
import BottomNav from '../components/BottomNav.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToastStore()

const currentUser = computed(() => authStore.user || {
  name: 'Minh',
  avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
})

// Parse selected project IDs from url query
const selectedIds = route.query.ids ? route.query.ids.split(',').map(Number) : []

const projects = ref([])
const isLoading = ref(true)

// Reactive structures to store user updates
const updateTexts = reactive({})
const savedTimes = reactive({})
const isSaved = reactive({})
const isSaving = reactive({})

// Icons mapping helper for dynamic design aesthetics
const getIconData = (title) => {
  const lower = title.toLowerCase()
  if (lower.includes('server') || lower.includes('license')) {
    return { icon: 'fa-solid fa-server text-emerald-600', bg: 'bg-emerald-100' }
  }
  if (lower.includes('migration') || lower.includes('nâng cấp') || lower.includes('upgrade') || lower.includes('wifi')) {
    return { icon: 'fa-solid fa-network-wired text-amber-600', bg: 'bg-amber-100' }
  }
  if (lower.includes('nas') || lower.includes('backup') || lower.includes('sao lưu')) {
    return { icon: 'fa-solid fa-database text-blue-600', bg: 'bg-blue-100' }
  }
  if (lower.includes('firewall') || lower.includes('fortinet') || lower.includes('bảo mật')) {
    return { icon: 'fa-solid fa-shield-halved text-purple-600', bg: 'bg-purple-100' }
  }
  if (lower.includes('office') || lower.includes('văn phòng') || lower.includes('chi nhánh') || lower.includes('camera')) {
    return { icon: 'fa-solid fa-building text-rose-600', bg: 'bg-rose-100' }
  }
  return { icon: 'fa-solid fa-folder-open text-gray-500', bg: 'bg-gray-100' }
}

const statusDotClass = (health) => {
  if (health === 'yellow') return 'bg-amber-400'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-emerald-500'
  return 'bg-gray-400'
}

const statusText = (health) => {
  if (health === 'yellow') return 'Đang theo'
  if (health === 'red') return 'Không theo'
  if (health === 'green') return 'Hoàn thành'
  return 'Không rõ'
}

// Compute progress indicators
const updatedCount = computed(() => {
  return Object.values(isSaved).filter(Boolean).length
})

const totalCount = computed(() => {
  return projects.value.length
})

const progressPercentage = computed(() => {
  if (totalCount.value === 0) return 0
  return (updatedCount.value / totalCount.value) * 100
})

const loadProjects = async () => {
  isLoading.value = true
  try {
    const res = await axios.get('/api/projects')
    const allProjects = res.data.projects
    
    if (selectedIds.length > 0) {
      projects.value = allProjects.filter(p => selectedIds.includes(p.id))
    } else {
      projects.value = allProjects
    }

    // Initialize state mapping
    projects.value.forEach(p => {
      updateTexts[p.id] = ''
      savedTimes[p.id] = null
      isSaved[p.id] = false
      isSaving[p.id] = false
    })
  } catch (err) {
    console.error('Failed to load projects for bulk update:', err)
  } finally {
    isLoading.value = false
  }
}

const saveUpdate = async (projectId) => {
  if (isSaving[projectId] || isSaved[projectId]) return
  const content = updateTexts[projectId]?.trim()
  if (!content) return

  isSaving[projectId] = true
  try {
    await axios.post('/api/comments', {
      project_id: projectId,
      user_id: authStore.user?.id || 3, // dynamic user ID
      content: content
    })
    toast.success('Đã lưu cập nhật dự án!')

    // Set saved state
    isSaved[projectId] = true
    const now = new Date()
    const pad = (n) => String(n).padStart(2, '0')
    savedTimes[projectId] = `${pad(now.getHours())}:${pad(now.getMinutes())}`
  } catch (err) {
    console.error('Failed to save project update:', err)
    toast.error('Lưu cập nhật thất bại!')
  } finally {
    isSaving[projectId] = false
  }
}

const handleFinishAll = async () => {
  // Automatically submit any unsaved updates with text
  const savePromises = []
  projects.value.forEach(p => {
    const content = updateTexts[p.id]?.trim()
    if (content && !isSaved[p.id]) {
      savePromises.push(saveUpdate(p.id))
    }
  })

  if (savePromises.length > 0) {
    await Promise.all(savePromises)
    toast.success('Đã lưu toàn bộ cập nhật!')
  }

  // Navigate back to project list
  router.push('/projects')
}

// Global keyboard shortcut listeners
const handleGlobalKeyDown = (e) => {
  // Ctrl + Enter or Cmd + Enter finishes all
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault()
    handleFinishAll()
  }

  // 'F' key outside input fields triggers finish all
  if (e.key === 'f' || e.key === 'F') {
    const activeEl = document.activeElement
    const isTyping = activeEl && (
      activeEl.tagName === 'INPUT' ||
      activeEl.tagName === 'TEXTAREA' ||
      activeEl.isContentEditable
    )
    if (!isTyping) {
      e.preventDefault()
      handleFinishAll()
    }
  }
}

onMounted(() => {
  loadProjects()
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>
