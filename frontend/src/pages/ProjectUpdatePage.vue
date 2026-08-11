<template>
  <div class="min-h-screen bg-[#f4f5f0] flex flex-col justify-between pb-24">
    <div>
      <!-- Custom Header matching mockup -->
      <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-2xs">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
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
      <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <button
          @click="goBack"
          type="button"
          class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium mb-4 transition-colors cursor-pointer focus:outline-none"
        >
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại</span>
        </button>

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

        <!-- Error state -->
        <div v-else-if="loadError" class="bg-white rounded-2xl p-12 text-center border border-rose-100 shadow-2xs">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-50 flex items-center justify-center">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
          </div>
          <p class="text-gray-700 font-bold text-base mb-1">Không thể tải danh sách dự án</p>
          <p class="text-gray-400 text-sm font-medium mb-5">{{ loadError }}</p>
          <button
            @click="loadProjects"
            type="button"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition-colors cursor-pointer focus:outline-none"
          >
            <i class="fa-solid fa-rotate-right text-xs"></i>
            <span>Thử lại</span>
          </button>
        </div>

        <!-- Empty state -->
        <div v-else-if="projects.length === 0" class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs">
          <p class="text-gray-400 font-medium">Không có dự án nào được chọn để cập nhật.</p>
          <router-link to="/projects" class="mt-4 inline-block text-sm font-semibold text-emerald-700 hover:underline">
            Quay lại danh sách dự án
          </router-link>
        </div>

        <!-- Selected Projects list to update -->
        <div v-else class="space-y-3">
          <div
            v-for="(project, index) in projects"
            :key="project.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-2xs hover:shadow-xs transition-shadow"
          >
            <!-- Main row: Title + Textarea + Status -->
            <div class="flex items-start gap-4 lg:gap-5 p-4 sm:p-5">

              <!-- Left: Project title only (no icon, no status) -->
              <div class="flex-shrink-0 w-36 sm:w-44 min-w-0 pt-1.5">
                <div class="font-extrabold text-gray-900 text-sm leading-snug font-heading break-words min-w-0">
                  {{ project.title }}
                </div>
              </div>

              <!-- Middle: Textarea input (inline, borderless style like detail page) -->
              <div class="flex-1 min-w-0">
                <textarea
                  v-model="updateTexts[project.id]"
                  @input="isSaved[project.id] = false"
                  @paste="handlePaste(project.id, $event)"
                  rows="1"
                  placeholder="Nhập nội dung cập nhật hoạt động hôm nay... (Ctrl+V để dán ảnh)"
                  class="w-full bg-transparent text-sm font-bold text-gray-900 leading-relaxed py-1 focus:outline-none placeholder-gray-400 resize-none border-0"
                  @keydown.enter.exact.prevent="saveUpdate(project.id)"
                  @keydown.ctrl.enter.prevent="handleFinishAll"
                ></textarea>
              </div>

              <!-- Right: Update status indicator -->
              <div class="flex-shrink-0 text-right flex items-center justify-end gap-2 min-h-[24px] pt-1.5">
                <template v-if="isSaved[project.id]">
                  <span class="text-xs text-gray-400 font-semibold whitespace-nowrap">{{ savedTimes[project.id] }}</span>
                  <i class="fa-solid fa-circle-check text-emerald-600 text-lg transition-transform scale-110"></i>
                </template>
                <template v-else>
                  <span class="text-xs text-gray-400 font-medium italic flex items-center gap-1 hover:text-gray-600 transition-colors whitespace-nowrap">
                    <span>Chưa có cập nhật</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                  </span>
                </template>
              </div>
            </div>

            <!-- Bottom bar: Attachment buttons + hints -->
            <div class="flex items-center justify-between gap-3 flex-wrap px-4 sm:px-5 pb-3 pt-0">
              <div class="flex items-center gap-2">
                <!-- Hidden File Input for Images -->
                <input 
                  :id="'img-input-' + project.id"
                  type="file" 
                  accept="image/*" 
                  multiple 
                  class="hidden" 
                  @change="handleFileSelect(project.id, $event, true)"
                />
                <label 
                  :for="'img-input-' + project.id"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold cursor-pointer transition-colors border border-emerald-200/60 select-none shadow-3xs"
                >
                  <i class="fa-solid fa-image text-emerald-600"></i>
                  <span>Thêm hình ảnh</span>
                </label>

                <!-- Hidden File Input for Documents -->
                <input 
                  :id="'file-input-' + project.id"
                  type="file" 
                  accept="*" 
                  multiple 
                  class="hidden" 
                  @change="handleFileSelect(project.id, $event, false)"
                />
                <label 
                  :for="'file-input-' + project.id"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold cursor-pointer transition-colors border border-slate-200 select-none shadow-3xs"
                >
                  <i class="fa-solid fa-paperclip text-slate-500"></i>
                  <span>Đính kèm file</span>
                </label>
              </div>

              <div class="text-[11px] text-gray-400 font-bold select-none flex items-center gap-1.5">
                <i class="fa-regular fa-lightbulb text-emerald-600 text-[13px]"></i>
                <span>Enter để lưu riêng, Ctrl + Enter để lưu tất cả</span>
              </div>
            </div>

            <!-- Attached Files Preview Grid -->
            <div v-if="attachedFiles[project.id]?.length > 0" class="flex items-center gap-2.5 flex-wrap px-4 sm:px-5 pb-4 pt-1 border-t border-gray-100 mx-4 sm:mx-5">
              <div 
                v-for="(file, fIdx) in attachedFiles[project.id]" 
                :key="fIdx"
                class="relative group"
              >
                <!-- Image Preview Thumbnail -->
                <div v-if="file.isImage" class="relative w-16 h-16 rounded-xl overflow-hidden border border-gray-200 shadow-3xs group">
                  <img :src="file.url" class="w-full h-full object-cover" />
                  <button 
                    @click="removeAttachment(project.id, fIdx)"
                    type="button" 
                    class="absolute top-1 right-1 w-5 h-5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-md hover:bg-rose-600 transition-colors cursor-pointer"
                    title="Xóa hình ảnh"
                  >
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>

                <!-- Document File Pill -->
                <div v-else class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 shadow-3xs">
                  <i class="fa-solid fa-file-lines text-emerald-600"></i>
                  <span class="max-w-[120px] truncate">{{ file.name }}</span>
                  <span class="text-[10px] text-gray-400">({{ formatFileSize(file.size) }})</span>
                  <button 
                    @click="removeAttachment(project.id, fIdx)"
                    type="button" 
                    class="text-gray-400 hover:text-rose-500 ml-1 cursor-pointer"
                    title="Xóa file"
                  >
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>


  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import CactusLogo from '../components/CactusLogo.vue'

import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToastStore()

const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}

const currentUser = computed(() => authStore.user || {
  name: 'Minh',
  avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
})

// Parse selected project IDs from url query
const selectedIds = route.query.ids ? route.query.ids.split(',').map(Number) : []

const projects = ref([])
const isLoading = ref(true)
const loadError = ref(null)

// Reactive structures to store user updates
const updateTexts = reactive({})
const attachedFiles = reactive({})
const savedTimes = reactive({})
const isSaved = reactive({})
const isSaving = reactive({})

const compressImage = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        let width = img.width
        let height = img.height
        const maxDim = 1200
        if (width > maxDim || height > maxDim) {
          if (width > height) {
            height = Math.round((height * maxDim) / width)
            width = maxDim
          } else {
            width = Math.round((width * maxDim) / height)
            height = maxDim
          }
        }
        canvas.width = width
        canvas.height = height
        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)
        const dataUrl = canvas.toDataURL(file.type.includes('png') ? 'image/png' : 'image/jpeg', 0.75)
        resolve(dataUrl)
      }
      img.onerror = () => resolve(e.target.result)
      img.src = e.target.result
    }
    reader.readAsDataURL(file)
  })
}

const handleFileSelect = async (projectId, event, isImageOnly = false) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  if (!attachedFiles[projectId]) {
    attachedFiles[projectId] = []
  }

  for (const file of Array.from(files)) {
    const isImg = file.type.startsWith('image/')
    let fileUrl = ''
    if (isImg) {
      fileUrl = await compressImage(file)
    } else {
      fileUrl = await new Promise((resolve) => {
        const reader = new FileReader()
        reader.onload = (e) => resolve(e.target.result)
        reader.readAsDataURL(file)
      })
    }

    attachedFiles[projectId].push({
      name: file.name,
      size: file.size,
      type: file.type,
      url: fileUrl,
      isImage: isImg
    })
    isSaved[projectId] = false
  }

  event.target.value = ''
}

const handlePaste = async (projectId, event) => {
  const clipboardData = event.clipboardData
  if (!clipboardData || !clipboardData.items) return

  const imageItems = Array.from(clipboardData.items).filter(item => item.type.startsWith('image/'))
  if (imageItems.length === 0) return

  // Prevent default paste behavior only when there are images
  event.preventDefault()

  if (!attachedFiles[projectId]) {
    attachedFiles[projectId] = []
  }

  for (const item of imageItems) {
    const file = item.getAsFile()
    if (!file) continue

    const fileUrl = await compressImage(file)
    const timestamp = new Date().toISOString().slice(11, 19).replace(/:/g, '')
    const ext = file.type.includes('png') ? 'png' : 'jpg'

    attachedFiles[projectId].push({
      name: `pasted_${timestamp}.${ext}`,
      size: file.size,
      type: file.type,
      url: fileUrl,
      isImage: true
    })
    isSaved[projectId] = false
  }
}

const removeAttachment = (projectId, fileIndex) => {
  if (attachedFiles[projectId]) {
    attachedFiles[projectId].splice(fileIndex, 1)
    isSaved[projectId] = false
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
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
  loadError.value = null
  try {
    const res = await axios.get('/api/projects')
    const allProjects = res.data?.projects || res.data || []
    
    if (!Array.isArray(allProjects)) {
      throw new Error('Dữ liệu trả về không hợp lệ')
    }

    if (selectedIds.length > 0) {
      projects.value = allProjects.filter(p => selectedIds.includes(p.id))
    } else {
      projects.value = allProjects
    }

    // Initialize state mapping
    projects.value.forEach(p => {
      updateTexts[p.id] = ''
      attachedFiles[p.id] = []
      savedTimes[p.id] = null
      isSaved[p.id] = false
      isSaving[p.id] = false
    })
  } catch (err) {
    console.error('Failed to load projects for bulk update:', err)
    if (err.response?.status === 401) {
      loadError.value = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
    } else if (err.response?.status >= 500) {
      loadError.value = 'Lỗi máy chủ. Vui lòng thử lại sau.'
    } else if (!err.response) {
      loadError.value = 'Không thể kết nối đến máy chủ. Kiểm tra kết nối mạng.'
    } else {
      loadError.value = err.message || 'Đã xảy ra lỗi không xác định.'
    }
  } finally {
    isLoading.value = false
  }
}

const saveUpdate = async (projectId) => {
  if (isSaving[projectId]) return
  let text = updateTexts[projectId]?.trim() || ''
  const files = attachedFiles[projectId] || []

  if (!text && files.length === 0) return

  let finalContent = text
  if (files.length > 0) {
    const fileMarkdown = files.map(f => {
      if (f.isImage) {
        return `![${f.name}](${f.url})`
      } else {
        return `📎 [${f.name}](${f.url})`
      }
    }).join('\n')
    
    finalContent = finalContent ? `${finalContent}\n\n${fileMarkdown}` : fileMarkdown
  }

  isSaving[projectId] = true
  try {
    await axios.post('/api/comments', {
      project_id: projectId,
      user_id: authStore.user?.id || 3,
      content: finalContent
    })
    toast.success('Đã lưu cập nhật dự án!')

    // Set saved state
    isSaved[projectId] = true
    const now = new Date()
    const pad = (n) => String(n).padStart(2, '0')
    savedTimes[projectId] = `${pad(now.getHours())}:${pad(now.getMinutes())}`

    // Automatically navigate back to home screen when all projects are updated!
    const allSaved = projects.value.every(p => isSaved[p.id])
    if (allSaved) {
      setTimeout(() => {
        router.push('/views')
      }, 500)
    }
  } catch (err) {
    console.error('Failed to save project update:', err)
    toast.error('Lưu cập nhật thất bại!')
  } finally {
    isSaving[projectId] = false
  }
}

const handleFinishAll = async () => {
  // Automatically submit any unsaved updates with text or files
  const savePromises = []
  projects.value.forEach(p => {
    const text = updateTexts[p.id]?.trim() || ''
    const files = attachedFiles[p.id] || []
    if ((text || files.length > 0) && !isSaved[p.id]) {
      savePromises.push(saveUpdate(p.id))
    }
  })

  if (savePromises.length > 0) {
    await Promise.all(savePromises)
    toast.success('Đã lưu toàn bộ cập nhật!')
  }

  // Navigate back to home screen
  setTimeout(() => {
    router.push('/views')
  }, 400)
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
