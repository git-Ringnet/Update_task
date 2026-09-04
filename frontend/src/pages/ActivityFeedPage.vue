<template>
  <div class="min-h-screen md:h-screen flex flex-col bg-[#F9F4EE] pb-2 md:overflow-hidden">
    <Navbar />

    <main class="max-w-[800px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2 flex-1 flex flex-col min-h-0">
      <!-- Header Row: Back Button & Vertically Centered Title "Hoạt động của đội" -->
      <div class="relative flex items-center justify-center mb-4 min-h-[40px] flex-shrink-0">
        <button @click="goBack" type="button"
          class="absolute left-0 top-1/2 -translate-y-1/2 inline-flex items-center gap-2 text-[15px] text-gray-700 hover:text-emerald-700 font-extrabold transition-colors cursor-pointer focus:outline-none">
          <i class="fa-solid fa-arrow-left text-sm"></i>
          <span>Quay lại</span>
        </button>

        <h1 class="text-[20px] sm:text-[22px] font-black text-gray-900 font-heading tracking-tight">
          Hoạt động của đội
        </h1>
      </div>

      <!-- Tabs for System Admin -->
      <div v-if="authStore.user?.is_system_admin"
        class="flex gap-2 mb-3 bg-stone-150 p-1.5 rounded-2xl max-w-md mx-auto w-full select-none border border-stone-200/50 flex-shrink-0">
        <button @click="activeTab = 'all'" type="button"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'all' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Tất cả
        </button>
        <button @click="activeTab = 'comments'" type="button"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'comments' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Bình luận
        </button>
        <button @click="activeTab = 'operations'" type="button"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'operations' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Lịch sử thao tác
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4 flex-1 overflow-hidden">
        <div v-for="i in 3" :key="'skel-' + i"
          class="bg-white rounded-2xl p-5 border border-gray-100 flex items-start gap-4 animate-pulse">
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
      <div v-else-if="filteredActivities.length === 0"
        class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs flex-1 flex items-center justify-center">
        <p class="text-gray-400 font-medium">Chưa có cập nhật hay hoạt động nào mới.</p>
      </div>

      <!-- Grouped Activities Feed (Scrollable inner list) -->
      <div v-else class="flex-1 overflow-y-auto scrollbar-none pr-1 mb-3 space-y-6">
        <div v-for="(group, dateStr) in groupedActivities" :key="dateStr" class="space-y-3">
          <!-- Date Header -->
          <h2 class="text-[18px] sm:text-[19px] font-black text-gray-900 font-heading mb-5 pt-1">{{ dateStr }}</h2>

          <!-- Timeline list, matching the recent-activity panel -->
          <div class="space-y-0">
            <div v-for="(act, idx) in group" :key="act.id" :id="'activity-feed-item-' + act.id"
              @click="handleActivityClick(act)"
              @touchstart="handleActivityTouchStart(act)" @touchend="handleActivityTouchEnd"
              @touchmove="handleActivityTouchMove" @contextmenu.prevent
              class="feed-activity-item relative flex gap-3 select-none pb-6 cursor-pointer group">
              <div v-if="idx < group.length - 1" class="absolute top-10 bottom-0 left-[15px] w-[1.5px] bg-gray-300 z-0">
              </div>

              <div class="flex-shrink-0 w-8 z-10">
                <img
                  :src="act.user?.avatar || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'"
                  :alt="act.user?.name" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-3xs" />
              </div>

              <div class="flex-1 min-w-0 z-10">
                <!-- Top Row: Avatar + User Name & supports & relative time / 3-dots menu -->
                <div class="flex items-center justify-between gap-2 relative">
                  <div class="min-w-0 flex-1">
                    <span class="font-extrabold text-[18px] sm:text-[19px] text-gray-900 truncate leading-snug">
                      <span>{{ act.user ? act.user.name : 'Thành viên' }}</span>
                      <template v-if="act.project?.customer">
                        <span class="font-bold text-gray-800">&nbsp;hỗ trợ&nbsp;</span>
                        <span class="text-[#1A7A56] hover:underline cursor-pointer font-extrabold"
                          @click.stop="$router.push(`/customers/${act.project.customer.id}`)">
                          {{ act.project.customer.name }}
                        </span>
                      </template>
                    </span>
                  </div>

                  <!-- Right: Timestamp normally, 3-dots icon button on hover / when menu open -->
                  <div class="relative shrink-0 flex items-center justify-end min-w-[28px]" @click.stop>
                    <!-- Relative Time (shown when not hovered and menu not active) -->
                    <span
                      class="text-[15px] sm:text-[16px] text-gray-400 font-medium whitespace-nowrap leading-snug text-right"
                      :class="activeActivityMenuId === act.id ? 'hidden' : 'group-hover:hidden'">
                      {{ formatCommentRelativeTime(act.created_at) }}
                    </span>

                    <!-- 3-dots Menu Button (shown on hover or when menu is active) -->
                    <button type="button" @click.stop="toggleActivityMenu(act.id, $event)" title="Tùy chọn"
                      class="text-gray-400 hover:text-gray-700 w-5 h-5 rounded flex items-center justify-center cursor-pointer transition-colors p-0 -mr-0.5"
                      :class="activeActivityMenuId === act.id ? 'flex text-gray-700 bg-gray-200/60' : 'hidden group-hover:flex'">
                      <i class="fa-solid fa-ellipsis-vertical text-base leading-none"></i>
                    </button>

                    <!-- Dropdown Menu for Delete -->
                    <div v-if="activeActivityMenuId === act.id"
                      class="absolute top-full right-0 mt-1 z-50 bg-white border border-gray-200 rounded-xl shadow-lg py-1 min-w-[110px] animate-fade-in-up">
                      <button v-if="canDeleteComment(act)" type="button" @click.stop="handleDeleteComment(act.id)"
                        class="w-full text-left px-3 py-1.5 text-sm font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer transition-colors">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Xóa</span>
                      </button>
                      <div v-else class="px-3 py-1.5 text-xs font-semibold text-gray-400">
                        Không có thao tác
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project title -->
                <div v-if="act.project"
                  class="text-[#1A7A56] hover:underline font-extrabold text-[18px] sm:text-[19px] cursor-pointer mt-0.5 mb-1 max-w-full truncate block leading-snug">
                  {{ act.project.title }}
                </div>

                <!-- Comment content -->
                <div class="text-[16px] sm:text-[18px] text-gray-900 leading-relaxed break-words mt-0.5 space-y-1">
                  <!-- Zalo Quote Reply Preview inside activity feed page -->
                  <div v-if="parseReplyInfo(act.content)"
                    @click.stop="scrollToComment(parseReplyInfo(act.content))"
                    class="bg-[#e1e3ea] px-2.5 py-1.5 rounded-r-md rounded-l-xs border-l-2 border-emerald-500 text-xs mb-1 select-none max-w-full cursor-pointer hover:bg-[#d5d7de] transition-colors">
                    <div class="text-[14px] font-bold text-gray-500 flex items-center gap-1">
                      <i class="fa-solid fa-reply text-xs"></i>
                      <span>{{ parseReplyInfo(act.content).user }}</span>
                    </div>
                    <div class="text-[14px] text-gray-450 truncate mt-0.5 max-w-[280px]">
                      {{ parseReplyInfo(act.content).text }}
                    </div>
                  </div>

                  <div v-if="parseCommentText(act.content)" class="whitespace-pre-line font-normal text-gray-900">
                    {{ parseCommentText(act.content) }}
                  </div>

                  <!-- Attachments (Images & Files side-by-side) -->
                  <div v-if="parseCommentImages(act.content).length > 0 || parseCommentFiles(act.content).length > 0"
                    class="flex flex-wrap items-end gap-1.5 pt-1">
                    <!-- Images -->
                    <button v-for="(img, imgIdx) in parseCommentImages(act.content)" :key="'img-' + imgIdx"
                      type="button" @click.stop="openImagePreview(img.url)"
                      class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all flex-shrink-0 shadow-3xs"
                      :title="'Xem ảnh: ' + img.name">
                      <img :src="img.url" class="w-full h-full object-cover" alt="" loading="lazy" />
                    </button>

                    <!-- Files -->
                    <a v-for="(file, fIdx) in parseCommentFiles(act.content)" :key="'file-' + fIdx" :href="file.url"
                      :download="file.name" target="_blank" @click.stop
                      class="w-8 h-10 rounded border border-[#d4a574] bg-[#f5e6d0] hover:bg-[#edd9bc] flex flex-col items-center justify-end overflow-hidden cursor-pointer transition-colors flex-shrink-0"
                      :title="'Tải xuống: ' + file.name">
                      <i class="fa-solid fa-file text-[#c87828] text-xs mb-0.5"></i>
                      <span
                        class="text-[8px] font-bold text-[#8b5a2b] bg-[#e8c99a] w-full text-center py-0.5 leading-none">FILE</span>
                    </a>
                  </div>
                </div>

                <!-- Bottom Actions: Reply icon -->
                <div class="flex items-center gap-3 mt-1.5">
                  <button @click.stop="handleReplyToActivity(act)" type="button" title="Trả lời hoạt động này"
                    class="text-gray-400 hover:text-emerald-700 cursor-pointer transition-colors inline-flex items-center gap-1 p-0.5">
                    <i class="fa-solid fa-reply text-[17px] sm:text-[19px]"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Load more container -->
        <div v-if="filteredActivities.length > displayLimit" class="pt-4 flex justify-center bg-[#F9F4EE] mb-4">
          <button @click="displayLimit += 15" type="button"
            class="px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100/80 text-emerald-800 font-extrabold text-xs rounded-xl shadow-3xs transition-all cursor-pointer flex items-center gap-1.5 focus:outline-none">
            <i class="fa-solid fa-angles-down text-[10px]"></i>
            <span>Xem thêm hoạt động (Còn {{ filteredActivities.length - displayLimit }} hoạt động)</span>
          </button>
        </div>
      </div>

      <!-- Bottom Chat Composer with Solid Background and Border -->
      <div v-if="activeTab !== 'operations'"
        class="bg-[#F9F4EE] border-2 border-[#4d4d4d] rounded-2xl shadow-3xs overflow-visible flex-shrink-0">
        <ActivityComposer ref="activityComposerRef" v-model="chatMessage" v-model:project-id="chatProjectId"
          :projects="projectStore.projects" :users="projectStore.users" :groups="mentionGroups"
          :replying-to="replyingToActivity" :reply-text="parseCommentText(replyingToActivity?.content)"
          :submitting="isSubmittingChat" @submit="submitChat" @cancel-reply="cancelReply" />
      </div>
    </main>

    <!-- Image Lightbox Modal -->
    <div v-if="activePreviewImage"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
      @click="activePreviewImage = null">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
        <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
        <button @click="activePreviewImage = null" type="button"
          class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 hover:bg-slate-900 text-white rounded-full flex items-center justify-center transition-colors shadow-lg cursor-pointer">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import ActivityComposer from '../components/ActivityComposer.vue'
import { useAuthStore } from '../stores/auth'
import { useProjectStore } from '../stores/project'
import { useToastStore } from '../stores/toast'


const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const projectStore = useProjectStore()
const toast = useToastStore()

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
const activeTab = ref(route.query.tab || 'all')
const chatMessage = ref('')
const chatProjectId = ref(null)
const replyingToActivity = ref(null)
const isSubmittingChat = ref(false)
const activityComposerRef = ref(null)
const mentionGroups = ref([])
const activeActivityIdForMobileActions = ref(null)
let activityTouchTimer = null
let activityTouchStarted = false
let ignoreActivityClickUntil = 0

watch(() => route.query.tab, (newTab) => {
  activeTab.value = newTab || 'all'
})

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

const handleReplyToActivity = (activity) => {
  activeActivityIdForMobileActions.value = null
  replyingToActivity.value = activity
  chatProjectId.value = activity.project_id || activity.project?.id || projectStore.projects[0]?.id
  chatMessage.value = activity.user?.name ? `@${activity.user.name} ` : ''
  activityComposerRef.value?.focus()
}

const handleActivityTouchStart = (activity) => {
  activityTouchStarted = true
  activityTouchTimer = window.setTimeout(() => {
    if (!activityTouchStarted) return
    activeActivityIdForMobileActions.value = activity.id
    ignoreActivityClickUntil = Date.now() + 700
    navigator.vibrate?.(50)
  }, 500)
}

const handleActivityTouchEnd = () => {
  activityTouchStarted = false
  if (activityTouchTimer) window.clearTimeout(activityTouchTimer)
}

const handleActivityTouchMove = () => {
  handleActivityTouchEnd()
}

const handleActivityClick = (activity) => {
  if (Date.now() < ignoreActivityClickUntil) return
  goToProject(activity.project_id)
}

const activeActivityMenuId = ref(null)
const isDeletingComment = ref(false)

const toggleActivityMenu = (id, event) => {
  event?.stopPropagation()
  activeActivityMenuId.value = activeActivityMenuId.value === id ? null : id
}

const canDeleteComment = (log) => {
  if (!log || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (log.user_id && Number(log.user_id) === Number(user.id)) || (log.user?.id && Number(log.user.id) === Number(user.id)))
}

const handleDeleteComment = async (commentId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa hoạt động này?')) return
  try {
    isDeletingComment.value = true
    await axios.delete(`/api/comments/${commentId}`)
    activities.value = activities.value.filter(a => a.id !== commentId)
    activeActivityMenuId.value = null
    toast.success('Đã xóa hoạt động thành công')
  } catch (err) {
    console.error('Failed to delete comment:', err)
    toast.error(err.response?.data?.message || 'Không thể xóa hoạt động này')
  } finally {
    isDeletingComment.value = false
  }
}

const handleOutsideActivityClick = (event) => {
  if (!event.target.closest?.('.feed-activity-item')) {
    activeActivityIdForMobileActions.value = null
    activeActivityMenuId.value = null
  }
}

const cancelReply = () => {
  replyingToActivity.value = null
  chatMessage.value = ''
}

const submitChat = async () => {
  if (isSubmittingChat.value) return
  const projectId = chatProjectId.value || projectStore.projects[0]?.id
  if (!projectId) {
    toast.error('Vui lòng chọn hoặc tạo dự án để gửi cập nhật.')
    return
  }

  isSubmittingChat.value = true
  try {
    const attachmentHtml = await activityComposerRef.value?.buildAttachmentHtml() || ''
    if (!chatMessage.value.trim() && !attachmentHtml) return

    let content = chatMessage.value + attachmentHtml
    if (replyingToActivity.value) {
      const replyMeta = {
        user: replyingToActivity.value.user?.name || 'Hệ thống',
        text: parseCommentText(replyingToActivity.value.content),
      }
      content = `[reply:${JSON.stringify(replyMeta)}]${content}`
    }

    await axios.post('/api/comments', { project_id: projectId, content })
    chatMessage.value = ''
    replyingToActivity.value = null
    activityComposerRef.value?.clearAttachments()
    toast.success('Gửi cập nhật hoạt động thành công!')
    fetchActivities()
  } catch (err) {
    console.error('Failed to submit activity:', err)
    toast.error(err.response?.data?.message || err.message || 'Gửi cập nhật thất bại. Vui lòng thử lại.')
  } finally {
    isSubmittingChat.value = false
  }
}

const filteredActivities = computed(() => {
  let list = activities.value

  if (authStore.user?.is_system_admin) {
    if (activeTab.value === 'comments') {
      list = list.filter(c => !c.type || c.type === 'comment')
    } else if (activeTab.value === 'operations') {
      list = list.filter(c => c.type && c.type !== 'comment')
    }
  }

  return list
})

// Group comments by date headers (Hôm nay, Hôm qua, or specific date string)
const groupedActivities = computed(() => {
  const groups = {}
  const sliced = filteredActivities.value.slice(0, displayLimit.value)

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

const formatCommentRelativeTime = (dateStr) => {
  if (!dateStr) return '1h'
  const date = new Date(dateStr)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)

  if (diffSec < 60) return 'Vừa xong'
  const diffMin = Math.floor(diffSec / 60)
  if (diffMin < 60) return `${diffMin}m`
  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours}h`
  const diffDays = Math.floor(diffHours / 24)
  return `${diffDays}d`
}

const statusDotClass = (health) => {
  if (health === 'yellow' || health === 'white') return 'bg-white border border-gray-300 shadow-3xs'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-white border border-gray-300 shadow-3xs'
  return 'bg-gray-400'
}

const getActivityStyle = (act) => {
  const health = act.project_health
  if (health === 'green') {
    return 'bg-white border-gray-300 border text-gray-800'
  } else if (health === 'red') {
    return 'bg-[#fca5a5] border-[#f87171] border-2 text-gray-900'
  }
  return 'bg-white border-gray-300 border text-gray-800'
}

const activePreviewImage = ref(null)
const openImagePreview = (url) => {
  activePreviewImage.value = url
}

const parseReplyInfo = (content) => {
  if (!content) return null
  const m = content.match(/^\[reply:(\{.*?\})\]/)
  if (m) {
    try {
      return JSON.parse(m[1])
    } catch (e) {
      console.error('Failed to parse reply info:', e)
    }
  }
  return null
}

const parseCommentText = (content) => {
  if (!content) return ''
  return content
    .replace(/^\[reply:\{.*?\}\]/, '')
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

  // 2. New server-backed attachment links.
  const htmlLinkRegex = /<a\b[^>]*\bhref=["']([^"']+)["'][^>]*>[\s\S]*?📎\s*Tệp đính kèm:\s*([^<]+)<\/a>/gi
  while ((m = htmlLinkRegex.exec(content)) !== null) {
    matches.push({ name: m[2].trim().replace(/&quot;/g, '"') || 'Tài liệu', url: m[1] })
  }

  // 3. Legacy HTML file spans <span...>📎 Tệp đính kèm: name</span>
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

onMounted(async () => {
  projectStore.activePage = 'home'
  projectStore.activeStatus = null
  await Promise.all([
    fetchActivities(),
    projectStore.fetchProjects(),
    projectStore.fetchAuxData(),
    axios.get('/api/mention-groups').then(res => { mentionGroups.value = res.data || [] }).catch(() => { }),
  ])
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('click', handleOutsideActivityClick)
})

const scrollToComment = (reply) => {
  if (!reply) return

  let targetId = reply.id

  // Fallback for legacy comments without id in reply json
  if (!targetId && reply.user && reply.text) {
    const quoteTextNorm = reply.text.trim().toLowerCase()
    const foundAct = activities.value.find(act => {
      const creatorName = (act.user ? act.user.name : 'Thành viên').trim().toLowerCase()
      const actText = parseCommentText(act.content).trim().toLowerCase()
      return creatorName === reply.user.trim().toLowerCase() && actText.includes(quoteTextNorm)
    })
    if (foundAct) {
      targetId = foundAct.id
    }
  }

  if (!targetId) {
    toast.warning('Không tìm thấy bình luận gốc.')
    return
  }

  // If the target comment is further down than the currently displayed activities, increase limit
  const isTargetLoaded = activities.value.slice(0, displayLimit.value).some(a => String(a.id) === String(targetId))
  if (!isTargetLoaded) {
    const targetIdx = activities.value.findIndex(a => String(a.id) === String(targetId))
    if (targetIdx !== -1) {
      displayLimit.value = Math.max(displayLimit.value, targetIdx + 15)
    }
  }

  setTimeout(() => {
    const el = document.getElementById(`activity-feed-item-${targetId}`)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      el.classList.add('activity-card-highlight')
      setTimeout(() => {
        el.classList.remove('activity-card-highlight')
      }, 2500)
    } else {
      toast.warning('Không tìm thấy bình luận gốc trong danh sách hiển thị hiện tại.')
    }
  }, 60)
}

onUnmounted(() => {
  if (activityTouchTimer) window.clearTimeout(activityTouchTimer)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', handleOutsideActivityClick)
})
</script>

<style scoped>
@keyframes activity-card-flash {
  0%,
  100% {
    background-color: transparent;
  }

  15%,
  70% {
    background-color: #a7f3d0;
  }
}

.activity-card-highlight {
  animation: activity-card-flash 2.5s ease-in-out;
  border-radius: 12px;
}
</style>
