<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-24">
    <div>
      <!-- Custom Header matching bulk update page -->
      <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          <!-- Left: Logo -->
          <router-link to="/tasks" class="flex items-center gap-2 group">
            <CactusLogo />
          </router-link>

          <!-- Center: Mode Badge -->
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
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading font-bold">Chào buổi chiều, {{ currentUser.name }}! 👋</h1>
            <p class="text-gray-500 text-sm mt-1 font-bold">Hôm nay bạn đã hoàn thành những công việc gì?</p>
          </div>

          <!-- Progress stats and Finish all button -->
          <div class="flex flex-col items-end gap-2 bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-emerald-700 font-heading">
                Đã hoàn thành {{ completedCount }} / {{ totalCount }}
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
              class="mt-1 text-emerald-700 hover:text-emerald-950 font-bold text-xs flex items-center gap-1.5 transition-colors focus:outline-none cursor-pointer"
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
        <div v-else-if="tasks.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs">
          <p class="text-gray-400 font-medium">Không có công việc nào được chọn để hoàn thành.</p>
          <router-link to="/tasks" class="mt-4 inline-block text-sm font-semibold text-emerald-700 hover:underline font-bold">
            Quay lại danh sách công việc
          </router-link>
        </div>

        <!-- Selected Tasks list to complete -->
        <div v-else class="space-y-4">
          <div
            v-for="(task, index) in tasks"
            :key="task.id"
            class="bg-white rounded-2xl p-5 border border-gray-100 shadow-2xs hover:shadow-xs transition-shadow flex flex-col md:flex-row md:items-start justify-between gap-4"
          >
            <!-- Left section: task name, project tag -->
            <div class="flex items-start gap-3.5 min-w-0 md:w-1/3">
              <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 text-sm shadow-3xs font-extrabold">
                <i class="fa-solid fa-list-check"></i>
              </div>
              <div class="min-w-0">
                <h3 class="font-extrabold text-gray-900 text-sm leading-snug break-words group-hover:text-emerald-700">
                  {{ task.title }}
                </h3>
                <div class="flex items-center gap-1.5 mt-1.5">
                  <span class="px-2 py-0.5 bg-gray-100 border border-gray-200 text-gray-500 font-extrabold rounded text-[10px] tracking-wide uppercase">
                    {{ task.project ? task.project.title : 'Dự án' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Middle section: Textarea for completion note -->
            <div class="flex-1 min-w-0 space-y-2.5">
              <textarea
                v-model="noteTexts[task.id]"
                :disabled="isSaved[task.id]"
                rows="2"
                placeholder="Nhập ghi chú / kết quả hoàn thành công việc..."
                class="w-full px-4 py-2.5 bg-gray-50/70 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-colors shadow-3xs resize disabled:bg-gray-100 disabled:text-gray-400"
                @keydown.enter.exact.prevent="saveComplete(task.id)"
              ></textarea>
              <div class="text-[11px] text-gray-400 font-bold select-none flex items-center gap-1">
                <i class="fa-regular fa-lightbulb text-emerald-600 text-xs"></i>
                <span>Ấn Enter để hoàn thành task này, Ctrl + Enter để hoàn thành tất cả</span>
              </div>
            </div>

            <!-- Right section: Completion status indicator -->
            <div class="w-28 flex-shrink-0 text-right flex items-center justify-end gap-2.5 min-h-[24px] select-none">
              <template v-if="isSaved[task.id]">
                <span class="text-xs text-gray-400 font-semibold">{{ savedTimes[task.id] }}</span>
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg transition-transform scale-110"></i>
              </template>
              <template v-else>
                <span class="text-xs text-gray-400 font-medium italic flex items-center gap-1 hover:text-gray-600 transition-colors">
                  <span>Chưa hoàn thành</span>
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

// Parse selected task IDs from url query
const selectedIds = route.query.ids ? route.query.ids.split(',').map(Number) : []

const tasks = ref([])
const isLoading = ref(true)

// Reactive structures to store user notes & saved states
const noteTexts = reactive({})
const savedTimes = reactive({})
const isSaved = reactive({})
const isSaving = reactive({})

// Compute progress indicators
const completedCount = computed(() => {
  return Object.values(isSaved).filter(Boolean).length
})

const totalCount = computed(() => {
  return tasks.value.length
})

const progressPercentage = computed(() => {
  if (totalCount.value === 0) return 0
  return (completedCount.value / totalCount.value) * 100
})

const loadTasks = async () => {
  isLoading.value = true
  try {
    const res = await axios.get('/api/tasks')
    const allTasks = res.data
    
    if (selectedIds.length > 0) {
      tasks.value = allTasks.filter(t => selectedIds.includes(t.id))
    } else {
      tasks.value = allTasks
    }

    // Initialize state mapping
    tasks.value.forEach(t => {
      noteTexts[t.id] = ''
      savedTimes[t.id] = null
      isSaved[t.id] = t.status === 'done'
      isSaving[t.id] = false
    })
  } catch (err) {
    console.error('Failed to load tasks for bulk completion:', err)
    toast.error('Tải danh sách công việc thất bại!')
  } finally {
    isLoading.value = false
  }
}

const saveComplete = async (taskId) => {
  if (isSaving[taskId] || isSaved[taskId]) return
  const content = noteTexts[taskId]?.trim()

  isSaving[taskId] = true
  try {
    // 1. Update status to done
    await axios.patch(`/api/tasks/${taskId}/status`, {
      status: 'done',
      user_id: authStore.user?.id || 3
    })

    // 2. Post comment note if content is entered
    if (content) {
      const taskObj = tasks.value.find(t => t.id === taskId)
      await axios.post('/api/comments', {
        project_id: taskObj?.project_id || 1,
        task_id: taskId,
        user_id: authStore.user?.id || 3,
        content: content
      })
    }

    // Set saved state
    isSaved[taskId] = true
    const now = new Date()
    const pad = (n) => String(n).padStart(2, '0')
    savedTimes[taskId] = `${pad(now.getHours())}:${pad(now.getMinutes())}`
    toast.success('Đã hoàn thành công việc!')
  } catch (err) {
    console.error('Failed to complete task:', err)
    toast.error('Hoàn thành công việc thất bại!')
  } finally {
    isSaving[taskId] = false
  }
}

const handleFinishAll = async () => {
  // Automatically submit any unsaved tasks
  const savePromises = []
  tasks.value.forEach(t => {
    if (!isSaved[t.id]) {
      savePromises.push(saveComplete(t.id))
    }
  })

  if (savePromises.length > 0) {
    await Promise.all(savePromises)
    toast.success('Đã hoàn thành toàn bộ công việc được chọn!')
  }

  // Navigate back to tasks page
  router.push('/tasks')
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
  loadTasks()
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>
