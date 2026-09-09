<template>
  <div class="activity-feed-page flex flex-col bg-[#F9F4EE] overflow-hidden"
    :style="{ height: 'var(--vvh, 100dvh)', maxHeight: 'var(--vvh, 100dvh)' }">
    <Navbar />

    <main
      class="max-w-[800px] w-full mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6 pb-2 sm:pb-3 flex-1 flex flex-col min-h-0 overflow-hidden">
      <!-- Header Row: Back Button & Vertically Centered Title "Hoạt động của đội" -->
      <div class="relative flex items-center justify-center mb-3 sm:mb-4 min-h-[40px] flex-shrink-0">
        <button @click="goBack" type="button" title="Quay lại"
          class="absolute left-0 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-auto sm:h-auto rounded-full sm:rounded-none flex items-center justify-center sm:gap-2 text-[15px] text-gray-700 hover:text-emerald-700 font-extrabold transition-colors cursor-pointer focus:outline-none hover:bg-stone-200/60 sm:hover:bg-transparent">
          <i class="fa-solid fa-arrow-left text-base sm:text-sm"></i>
          <span class="hidden sm:inline">Quay lại</span>
        </button>

        <h1 class="text-[20px] sm:text-[22px] font-black text-[#32312F] font-heading tracking-tight">
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
        <p class="text-gray-400 font-medium">Trong 7 ngày qua, chưa có hoạt động mới</p>
      </div>

      <!-- Grouped Activities Feed (Scrollable inner list, chat style) -->
      <div v-else ref="activityFeedScrollRef" @scroll="handleFeedScroll"
        class="activity-feed-scroll flex-1 min-h-0 overflow-y-auto scrollbar-none pr-1 mb-2 sm:mb-3 space-y-6"
        style="-webkit-overflow-scrolling: touch; touch-action: pan-y; overscroll-behavior-y: contain;">
        <!-- Loading older comments indicator when scrolling up -->
        <div v-if="isLoadingOlderActivities" class="flex items-center justify-center py-2 text-xs text-gray-500 gap-2">
          <i class="fa-solid fa-circle-notch fa-spin text-emerald-600"></i>
          <span>Đang tải hoạt động cũ hơn...</span>
        </div>
        <div v-else-if="!hasMoreOlderActivities && filteredActivities.length >= 30"
          class="text-center py-1.5 mb-2 text-[12px] text-gray-400 font-semibold border-b border-gray-200/50">
          Đã hiển thị tất cả hoạt động
        </div>

        <div v-for="(group, dateStr) in groupedActivities" :key="dateStr" class="space-y-3">
          <!-- Date Header -->
          <h2 class="text-[18px] sm:text-[19px] font-black text-[#32312F] font-heading mb-5 pt-1">{{ dateStr }}</h2>

          <!-- Timeline list, matching the recent-activity panel -->
          <div class="space-y-0">
            <div v-for="(act, idx) in group" :key="act.id" :id="'activity-feed-item-' + act.id"
              @click="handleActivityClick(act)"
              class="feed-activity-item relative flex gap-3 pb-5 cursor-pointer group">
              <div v-if="idx < group.length - 1" class="absolute top-10 bottom-0 left-[15px] w-[1.5px] bg-gray-300 z-0">
              </div>

              <div class="flex-shrink-0 w-8 z-10">
                <img
                  :src="act.user?.avatar || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'"
                  :alt="act.user?.name" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-3xs" />
              </div>

              <div class="flex-1 min-w-0 z-10">
                <!-- Top Row: Avatar + User Name & supports & relative time / 3-dots menu -->
                <div class="flex items-center justify-between gap-2 relative min-h-[26px]">
                  <div class="min-w-0 flex-1">
                    <span class="font-extrabold text-[18px] sm:text-[19px] text-[#32312F] truncate leading-tight block">
                      <span>{{ act.user ? act.user.name : 'Thành viên' }}</span>
                      <template v-if="act.project?.customer">
                        <span class="font-bold text-[#32312F]">&nbsp;hỗ trợ&nbsp;</span>
                        <span class="text-[#1A7A56] hover:underline cursor-pointer font-extrabold"
                          @click.stop="$router.push(`/customers/${act.project.customer.id}`)">
                          {{ act.project.customer.name }}
                        </span>
                      </template>
                    </span>
                  </div>

                  <!-- Right: Timestamp normally, 3-dots icon button on hover / when menu open -->
                  <div class="relative shrink-0 flex items-center justify-end min-h-[30px] min-w-[32px]" @click.stop>
                    <!-- Relative Time (shown when not hovered and menu not active) -->
                    <span
                      class="text-[14px] sm:text-[15px] text-gray-400 font-medium whitespace-nowrap leading-none text-right"
                      :class="(activeActivityMenuId === act.id || activeActivityIdForMobileActions === act.id) ? 'hidden' : 'group-hover:hidden'">
                      {{ formatCommentRelativeTime(act.created_at) }}
                    </span>

                    <!-- 3-dots Menu Button (shown on hover or when menu is active) -->
                    <button type="button" @click.stop="toggleActivityMenu(act.id, $event)" title="Tùy chọn"
                      class="text-gray-400 hover:text-gray-800 hover:bg-gray-200/80 active:bg-gray-300/80 w-8 h-8 -my-1 -mr-1 rounded-lg flex items-center justify-center cursor-pointer transition-all active:scale-95 p-0"
                      :class="(activeActivityMenuId === act.id || activeActivityIdForMobileActions === act.id) ? 'flex text-gray-800 bg-gray-200/80' : 'hidden group-hover:flex'">
                      <i class="fa-solid fa-ellipsis-vertical text-[15px] leading-none"></i>
                    </button>

                    <!-- Dropdown Menu for Edit & Delete -->
                    <div v-if="activeActivityMenuId === act.id"
                      class="absolute top-full right-0 mt-1 z-50 bg-white border border-gray-200 rounded-xl shadow-lg py-1 min-w-[120px] animate-fade-in-up">
                      <button v-if="canEditComment(act)" type="button" @click.stop="handleStartEditComment(act)"
                        class="w-full text-left px-3 py-1.5 text-sm font-bold text-gray-700 hover:bg-gray-100 flex items-center gap-2 cursor-pointer transition-colors">
                        <i class="fa-solid fa-pen-to-square text-xs text-emerald-600"></i>
                        <span>Chỉnh sửa</span>
                      </button>
                      <button v-if="canDeleteComment(act)" type="button" @click.stop="handleDeleteComment(act.id)"
                        class="w-full text-left px-3 py-1.5 text-sm font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer transition-colors">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Xóa</span>
                      </button>
                      <div v-if="!canEditComment(act) && !canDeleteComment(act)"
                        class="px-3 py-1.5 text-xs font-semibold text-gray-400">
                        Không có thao tác
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project title -->
                <div v-if="act.project" @click="handleActivityProjectClick(act.project.id, $event)"
                  class="text-[#1A7A56] hover:underline font-extrabold text-[18px] sm:text-[19px] cursor-pointer mt-0.5 mb-1 max-w-full truncate block leading-snug"
                  :title="act.project.title">
                  {{ act.project.title }}
                </div>

                <!-- Comment content (Normal Display) -->
                <div class="text-[16px] sm:text-[18px] text-gray-900 leading-relaxed break-words mt-0.5 space-y-1">
                  <!-- Zalo Quote Reply Preview inside activity feed page -->
                  <div v-if="parseReplyInfo(act.content)" @click.stop="scrollToComment(parseReplyInfo(act.content))"
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
                    class="flex flex-wrap items-end gap-1.5 pt-1 pb-1.5">
                    <!-- Images -->
                    <button v-for="(img, imgIdx) in parseCommentImages(act.content)" :key="'img-' + imgIdx"
                      type="button" @click.stop="openImagePreview(img.url, parseCommentImages(act.content), imgIdx)"
                      class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all flex-shrink-0 shadow-3xs"
                      :title="'Xem ảnh: ' + img.name">
                      <img :src="img.url" class="w-full h-full object-cover" alt=""
                        :loading="idx < 3 ? 'eager' : 'lazy'" decoding="async"
                        :fetchpriority="idx < 3 ? 'high' : 'auto'" />
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
                    class="w-8.5 h-8.5 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-emerald-700 hover:bg-emerald-50/80 active:bg-emerald-100 cursor-pointer transition-all active:scale-95 -ml-1.5">
                    <i class="fa-solid fa-reply text-[15px] sm:text-[16px]"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Chat Composer with Solid Background and Border -->
      <div v-if="activeTab !== 'operations'"
        class="bg-[#F9F4EE] border-2 border-[#4d4d4d] rounded-2xl shadow-3xs overflow-visible flex-shrink-0">
        <ActivityComposer ref="activityComposerRef" v-model="chatMessage" v-model:project-id="chatProjectId"
          :projects="projectStore.projects" :users="projectStore.users" :groups="mentionGroups"
          :replying-to="replyingToActivity"
          :reply-text="replyingToActivity?.text || parseCommentText(replyingToActivity?.content)"
          :editing-comment="editingCommentLog" :submitting="isSubmittingChat" @submit="submitChat"
          @cancel-reply="cancelReply" @cancel-edit="cancelEdit" />
      </div>
    </main>

    <!-- Image Lightbox Modal -->
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
      <div v-if="activePreviewImage"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8 bg-slate-950/85 backdrop-blur-md select-none"
        @click="closeImagePreview">
        <div class="relative w-[min(92vw,1100px)] h-[min(72vh,720px)] flex flex-col items-center justify-center"
          @click.stop @touchstart="handleModalTouchStart" @touchend="handleModalTouchEnd">

          <!-- Top Bar: Image count badge + Close button -->
          <div class="absolute top-3 left-3 right-3 z-10 flex items-center justify-between pointer-events-auto">
            <div v-if="previewModalImages.length > 1"
              class="px-3 py-1 bg-black/60 backdrop-blur-md text-white/90 text-xs sm:text-sm font-bold rounded-full border border-white/10 shadow-lg">
              {{ previewModalIndex + 1 }} / {{ previewModalImages.length }}
            </div>
            <div v-else></div>

            <button type="button" @click="closeImagePreview"
              class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/20 hover:bg-rose-600 text-white flex items-center justify-center font-bold text-sm sm:text-base backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20"
              title="Đóng (Esc)">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Main Image and Prev/Next Navigation -->
          <div class="relative w-full h-full flex items-center justify-center rounded-2xl overflow-hidden bg-slate-900">
            <img :src="activePreviewImage" class="w-full h-full object-contain transition-opacity duration-150" />

            <!-- PREV BUTTON (shown when > 1 image) -->
            <button v-if="previewModalImages.length > 1" type="button" @click="prevPreviewImage"
              class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/85 text-white flex items-center justify-center font-bold text-base sm:text-lg backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20 hover:scale-110 active:scale-95"
              title="Ảnh trước (Phím ←)">
              <i class="fa-solid fa-chevron-left"></i>
            </button>

            <!-- NEXT BUTTON (shown when > 1 image) -->
            <button v-if="previewModalImages.length > 1" type="button" @click="nextPreviewImage"
              class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/85 text-white flex items-center justify-center font-bold text-base sm:text-lg backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20 hover:scale-110 active:scale-95"
              title="Ảnh tiếp theo (Phím →)">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>

          <!-- Thumbnails / Dots strip at bottom -->
          <div v-if="previewModalImages.length > 1"
            class="flex items-center justify-center gap-2 mt-4 max-w-full overflow-x-auto py-1 px-2">
            <button v-for="(pImg, pIdx) in previewModalImages" :key="'thumb-' + pIdx" type="button"
              @click="previewModalIndex = pIdx"
              class="w-10 h-10 rounded-lg overflow-hidden border-2 transition-all cursor-pointer flex-shrink-0"
              :class="pIdx === previewModalIndex ? 'border-emerald-400 scale-110 shadow-lg ring-2 ring-emerald-400/50' : 'border-white/30 opacity-60 hover:opacity-100'">
              <img :src="pImg.url || pImg.src || pImg" class="w-full h-full object-cover" />
            </button>
          </div>

        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
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
const activeTab = ref(route.query.tab || 'all')
const chatMessage = ref('')
const chatProjectId = ref(null)
const replyingToActivity = ref(null)
const isSubmittingChat = ref(false)
const activityComposerRef = ref(null)
const mentionGroups = ref([])
const activeActivityIdForMobileActions = ref(null)
const activityFeedScrollRef = ref(null)
const hasMoreOlderActivities = ref(true)
const isLoadingOlderActivities = ref(false)
let activityTouchTimer = null
let activityTouchStarted = false
let ignoreActivityClickUntil = 0

watch(() => route.query.tab, (newTab) => {
  activeTab.value = newTab || 'all'
})

let pollTimer = null

const scrollToBottom = (smooth = false) => {
  nextTick(() => {
    requestAnimationFrame(() => {
      if (activityFeedScrollRef.value) {
        if (smooth) {
          activityFeedScrollRef.value.scrollTo({
            top: activityFeedScrollRef.value.scrollHeight,
            behavior: 'smooth'
          })
        } else {
          activityFeedScrollRef.value.scrollTop = activityFeedScrollRef.value.scrollHeight
        }
      }
    })
  })
}

const handleFeedScroll = async (event) => {
  const el = event?.target || activityFeedScrollRef.value
  if (!el) return

  if (el.scrollTop <= 40 && !isLoadingOlderActivities.value && hasMoreOlderActivities.value && activities.value.length > 0) {
    await loadOlderActivities()
  }
}

const loadOlderActivities = async () => {
  if (isLoadingOlderActivities.value || !hasMoreOlderActivities.value || activities.value.length === 0) return

  isLoadingOlderActivities.value = true
  const minId = Math.min(...activities.value.map(a => Number(a.id) || Infinity))
  if (!minId || minId === Infinity) {
    isLoadingOlderActivities.value = false
    return
  }

  const container = activityFeedScrollRef.value
  const previousScrollHeight = container ? container.scrollHeight : 0
  const previousScrollTop = container ? container.scrollTop : 0

  try {
    const res = await axios.get('/api/comments', {
      params: { before_id: minId, limit: 30 }
    })
    const olderComments = (res.data || []).filter(c => Boolean(c.project_id))

    if (olderComments.length === 0) {
      hasMoreOlderActivities.value = false
    } else {
      const existingIds = new Set(activities.value.map(a => a.id))
      const newItems = olderComments.filter(a => !existingIds.has(a.id))
      if (newItems.length === 0) {
        hasMoreOlderActivities.value = false
      } else {
        activities.value = [...activities.value, ...newItems]
        if (olderComments.length < 30) {
          hasMoreOlderActivities.value = false
        }

        await nextTick()
        requestAnimationFrame(() => {
          if (container) {
            const newScrollHeight = container.scrollHeight
            container.scrollTop = (newScrollHeight - previousScrollHeight) + previousScrollTop
          }
        })
      }
    }
  } catch (err) {
    console.error('Failed to load older activities:', err)
  } finally {
    isLoadingOlderActivities.value = false
  }
}

const fetchActivities = async (silent = false) => {
  if (!silent) isLoading.value = true
  try {
    const res = await axios.get('/api/comments', { params: { limit: 50 } })
    // Filter only comments/activities associated with a project
    const filtered = (res.data || []).filter(c => c.project_id)
    activities.value = filtered
    hasMoreOlderActivities.value = filtered.length >= 50
    if (!silent) {
      scrollToBottom(false)
    }
  } catch (err) {
    console.error('Failed to load activity feed:', err)
  } finally {
    if (!silent) isLoading.value = false
  }
}

let latestActivityRequest = null
const fetchLatestActivities = () => {
  if (latestActivityRequest || activities.value.length === 0) return latestActivityRequest
  const afterId = Math.max(...activities.value.map(activity => Number(activity.id) || 0))
  latestActivityRequest = axios.get('/api/comments', {
    params: { after_id: afterId, limit: 30 }
  }).then(res => {
    const incoming = (res.data || []).filter(comment => comment.project_id)
    if (!incoming.length) return
    const incomingIds = new Set(incoming.map(comment => comment.id))

    const container = activityFeedScrollRef.value
    const isNearBottom = container
      ? (container.scrollHeight - container.scrollTop - container.clientHeight <= 150)
      : true

    activities.value = [...incoming, ...activities.value.filter(comment => !incomingIds.has(comment.id))]

    if (isNearBottom) {
      scrollToBottom(true)
    }
  }).catch(err => {
    console.error('Failed to poll new activities:', err)
  }).finally(() => {
    latestActivityRequest = null
  })
  return latestActivityRequest
}

const handleReplyToActivity = (activity) => {
  activeActivityIdForMobileActions.value = null
  editingCommentLog.value = null
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

const canEditComment = (log) => {
  if (!log || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (log.user_id && Number(log.user_id) === Number(user.id)) || (log.user?.id && Number(log.user.id) === Number(user.id)))
}

const editingCommentLog = ref(null)

const handleStartEditComment = (log) => {
  activeActivityMenuId.value = null
  activeActivityIdForMobileActions.value = null
  editingCommentLog.value = log

  // 1. Select project of this activity
  const pId = Number(log.project_id || log.project?.id)
  if (pId) {
    chatProjectId.value = pId
  }

  // 2. Check if this activity is a reply to another comment
  const replyInfo = parseReplyInfo(log.content)
  if (replyInfo) {
    replyingToActivity.value = {
      id: replyInfo.id,
      user: { name: replyInfo.user },
      content: replyInfo.text,
      text: replyInfo.text
    }
  } else {
    replyingToActivity.value = null
  }

  // 3. Populate chat text
  chatMessage.value = parseCommentText(log.content)

  // 4. Focus composer
  nextTick(() => {
    activityComposerRef.value?.focus()
  })
}

const cancelEdit = () => {
  editingCommentLog.value = null
  replyingToActivity.value = null
  chatMessage.value = ''
  activityComposerRef.value?.clearAttachments()
}

const handleDeleteComment = async (commentId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa hoạt động này?')) return
  try {
    isDeletingComment.value = true
    await axios.delete(`/api/comments/${commentId}`)
    activities.value = activities.value.filter(a => a.id !== commentId)
    if (editingCommentLog.value?.id === commentId) {
      cancelEdit()
    }
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
  if (editingCommentLog.value) {
    cancelEdit()
    return
  }
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
        id: replyingToActivity.value.id || null,
        user: replyingToActivity.value.user?.name || (typeof replyingToActivity.value.user === 'string' ? replyingToActivity.value.user : 'Hệ thống'),
        text: replyingToActivity.value.text || parseCommentText(replyingToActivity.value.content),
      }
      content = `[reply:${JSON.stringify(replyMeta)}]${content}`
    }

    if (editingCommentLog.value) {
      const editingId = editingCommentLog.value.id
      const rawContent = editingCommentLog.value.content || ''
      const htmlAttachments = rawContent.replace(/^\[reply:\{.*?\}\]/, '').match(/(<br\s*\/?>\s*(?:<img[^>]*>|<a\b[^>]*>[\s\S]*?<\/a>)|!\[.*?\]\(.*?\)|📎\s*\[.*?\]\(.*?\))/gi)
      if (htmlAttachments && htmlAttachments.length > 0 && !attachmentHtml) {
        content += htmlAttachments.join('')
      }

      const res = await axios.put(`/api/comments/${editingId}`, {
        content,
        project_id: projectId
      })

      const targetAct = activities.value.find(a => a.id === editingId)
      if (targetAct) {
        targetAct.content = res.data?.content || content
        if (res.data?.project) targetAct.project = res.data.project
        if (res.data?.project_id) targetAct.project_id = res.data.project_id
      }

      toast.success('Đã cập nhật hoạt động!')
      cancelEdit()
      fetchActivities(true)
      broadcastLocalUpdate({ projectId, commentId: editingId })
    } else {
      const res = await axios.post('/api/comments', { project_id: projectId, content })
      const createdActivity = res.data
      if (createdActivity?.id) {
        activities.value = [...activities.value.filter(item => item.id !== createdActivity.id), createdActivity]
      }
      toast.success('Gửi cập nhật hoạt động thành công!')
      chatMessage.value = ''
      replyingToActivity.value = null
      activityComposerRef.value?.clearAttachments()
      scrollToBottom(true)
      // The POST response is rendered immediately; polling reconciles later.
      broadcastLocalUpdate({ projectId })
    }
  } catch (err) {
    console.error('Failed to submit activity:', err)
    toast.error(err.response?.data?.message || err.message || 'Thao tác thất bại. Vui lòng thử lại.')
  } finally {
    isSubmittingChat.value = false
  }
}

let realtimeBroadcastChannel = null
const realtimeSourceId = `activity-feed-${Date.now()}-${Math.random()}`

const handleRealtimeChannelMessage = (event) => {
  const data = event.data
  if (!data) return
  if (data.sourceId === realtimeSourceId) return
  if (data.type === 'PUSH_RECEIVED' || data.type === 'NOTIFICATION_CLICKED' || data.type === 'PROJECT_UPDATED') {
    fetchActivities(true)
    projectStore.fetchProjects(true)
  }
}

const handleServiceWorkerMessage = (event) => {
  const data = event.data
  if (!data) return
  if (data.type === 'PUSH_RECEIVED' || data.type === 'NOTIFICATION_CLICKED' || data.type === 'PROJECT_UPDATED') {
    fetchActivities(true)
    projectStore.fetchProjects(true)
  }
}

const broadcastLocalUpdate = (payload = {}) => {
  try {
    const ch = new BroadcastChannel('project_realtime_channel')
    ch.postMessage({
      type: 'PROJECT_UPDATED',
      sourceId: realtimeSourceId,
      ...payload
    })
    ch.close()
  } catch (e) { }
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

  // Display oldest at top, newest at bottom (chat app style)
  return [...list].sort((a, b) => {
    const timeDiff = new Date(a.created_at || 0) - new Date(b.created_at || 0)
    if (timeDiff !== 0) return timeDiff
    return (Number(a.id) || 0) - (Number(b.id) || 0)
  })
})

// Group comments chronologically by date headers (Oldest dates first, Hôm nay last)
const groupedActivities = computed(() => {
  const groups = {}
  const list = filteredActivities.value

  list.forEach(item => {
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

const previewModalImages = ref([])
const previewModalIndex = ref(0)
const activePreviewImage = computed(() => {
  if (!previewModalImages.value || previewModalImages.value.length === 0) return null
  const item = previewModalImages.value[previewModalIndex.value]
  return typeof item === 'string' ? item : (item?.url || item?.src || null)
})

const openImagePreview = (url, imagesList = [], initialIndex = 0) => {
  if (imagesList && imagesList.length > 0) {
    previewModalImages.value = imagesList
    const foundIdx = initialIndex >= 0 ? initialIndex : imagesList.findIndex(img => (img.url || img.src || img) === url)
    previewModalIndex.value = Math.max(0, foundIdx)
  } else if (url) {
    previewModalImages.value = [{ url, name: 'Ảnh đính kèm' }]
    previewModalIndex.value = 0
  }
}

const closeImagePreview = () => {
  previewModalImages.value = []
  previewModalIndex.value = 0
}

const prevPreviewImage = (e) => {
  if (e) e.stopPropagation()
  if (previewModalImages.value.length > 1) {
    previewModalIndex.value = (previewModalIndex.value - 1 + previewModalImages.value.length) % previewModalImages.value.length
  }
}

const nextPreviewImage = (e) => {
  if (e) e.stopPropagation()
  if (previewModalImages.value.length > 1) {
    previewModalIndex.value = (previewModalIndex.value + 1) % previewModalImages.value.length
  }
}

let modalTouchStartX = 0
let modalTouchEndX = 0

const handleModalTouchStart = (e) => {
  if (e.touches && e.touches[0]) {
    modalTouchStartX = e.touches[0].clientX
  }
}

const handleModalTouchEnd = (e) => {
  if (e.changedTouches && e.changedTouches[0]) {
    modalTouchEndX = e.changedTouches[0].clientX
    const diff = modalTouchEndX - modalTouchStartX
    if (Math.abs(diff) > 40) {
      if (diff < 0) {
        nextPreviewImage()
      } else {
        prevPreviewImage()
      }
    }
  }
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

const selectProjectForChat = (projectId, event = null) => {
  const pId = Number(projectId)
  const proj = projectStore.projects.find(p => Number(p.id) === pId)
  chatProjectId.value = pId

  nextTick(() => {
    activityComposerRef.value?.focus()
  })

  if (proj) {
    toast.success(`Đã chọn "${proj.title}" vào khung chat`)
  }
}

const handleActivityProjectClick = (projectId, event) => {
  if (event?.altKey) {
    event.preventDefault?.()
    event.stopPropagation?.()
    selectProjectForChat(projectId, event)
    return
  }
  goToProject(projectId, event)
}

const goToProject = (projectId, event = null) => {
  if (event?.altKey) {
    event.preventDefault?.()
    event.stopPropagation?.()
    selectProjectForChat(projectId, event)
    return
  }
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

const handleKeydown = (e) => {
  if (activePreviewImage.value) {
    if (e.key === 'ArrowLeft' || e.code === 'ArrowLeft') {
      prevPreviewImage()
      e.preventDefault?.()
      return
    }
    if (e.key === 'ArrowRight' || e.code === 'ArrowRight') {
      nextPreviewImage()
      e.preventDefault?.()
      return
    }
    if (e.key === 'Escape' || e.code === 'Escape') {
      closeImagePreview()
      e.preventDefault?.()
      return
    }
  }

  if (e.key === 'Escape' || e.code === 'Escape') {
    closeImagePreview()
  }
}

const handleVisibilityOrFocus = () => {
  if (document.visibilityState === 'visible' && !isLoading.value) {
    fetchActivities(true)
  }
}

const isVirtualKeyboardOpen = ref(false)
let keyboardBlurTimeout = null

const resetWindowScroll = () => {
  if (typeof window === 'undefined') return
  if (window.scrollY !== 0 || document.documentElement.scrollTop !== 0 || document.body.scrollTop !== 0) {
    window.scrollTo(0, 0)
    document.documentElement.scrollTop = 0
    document.body.scrollTop = 0
  }
}

const updateKeyboardState = (fromFocus = false) => {
  if (typeof window === 'undefined') return
  if (window.innerWidth >= 768) {
    isVirtualKeyboardOpen.value = false
    document.documentElement.style.removeProperty('--vvh')
    return
  }
  const activeEl = document.activeElement
  const isInputFocused = Boolean(activeEl && (
    activeEl.tagName === 'INPUT' ||
    activeEl.tagName === 'TEXTAREA' ||
    activeEl.isContentEditable
  ))
  if (window.visualViewport) {
    const currentVVH = window.visualViewport.height
    document.documentElement.style.setProperty('--vvh', `${currentVVH}px`)
    const screenHeight = Math.max(window.innerHeight, window.screen?.height || 0, 600)
    const isHeightReduced = currentVVH < screenHeight * 0.82
    if (fromFocus || isInputFocused) {
      isVirtualKeyboardOpen.value = true
    } else {
      isVirtualKeyboardOpen.value = Boolean(isHeightReduced)
    }
  } else {
    isVirtualKeyboardOpen.value = isInputFocused
  }
  resetWindowScroll()
}

const handleVirtualKeyboardFocusIn = (e) => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  const target = e.target
  if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) {
    if (keyboardBlurTimeout) {
      clearTimeout(keyboardBlurTimeout)
      keyboardBlurTimeout = null
    }
    isVirtualKeyboardOpen.value = true
    updateKeyboardState(true)
    
    // Smooth multi-pass scroll reset as iOS virtual keyboard animates
    resetWindowScroll()
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 50)
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 150)
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 300)
  }
}

const handleVirtualKeyboardFocusOut = () => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  if (keyboardBlurTimeout) clearTimeout(keyboardBlurTimeout)
  keyboardBlurTimeout = setTimeout(() => {
    updateKeyboardState()
    resetWindowScroll()
  }, 120)
}

const handleVisualViewportChange = () => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  const activeEl = document.activeElement
  const isInputFocused = Boolean(activeEl && (
    activeEl.tagName === 'INPUT' ||
    activeEl.tagName === 'TEXTAREA' ||
    activeEl.isContentEditable
  ))
  if (window.visualViewport) {
    const currentVVH = window.visualViewport.height
    document.documentElement.style.setProperty('--vvh', `${currentVVH}px`)
    const screenHeight = Math.max(window.innerHeight, window.screen?.height || 0, 600)
    const isHeightReduced = currentVVH < screenHeight * 0.82
    if (!isHeightReduced) {
      isVirtualKeyboardOpen.value = false
    } else if (isInputFocused) {
      isVirtualKeyboardOpen.value = true
    }
  }
  resetWindowScroll()
}

onMounted(async () => {
  updateKeyboardState()
  resetWindowScroll()
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
  document.addEventListener('focusin', handleVirtualKeyboardFocusIn)
  document.addEventListener('focusout', handleVirtualKeyboardFocusOut)
  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', handleVisualViewportChange)
    window.visualViewport.addEventListener('scroll', handleVisualViewportChange)
  }

  // Lightweight incremental polling keeps the delay low without downloading
  // the complete activity history over and over.
  pollTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible' && !isSubmittingChat.value && !isLoading.value) {
      fetchLatestActivities()
    }
  }, 5000)

  document.addEventListener('visibilitychange', handleVisibilityOrFocus)
  window.addEventListener('focus', handleVisibilityOrFocus)

  // 1. Listen on BroadcastChannel for instant cross-tab and SW push sync
  try {
    realtimeBroadcastChannel = new BroadcastChannel('project_realtime_channel')
    realtimeBroadcastChannel.addEventListener('message', handleRealtimeChannelMessage)
  } catch (e) { }

  // 2. Listen on Service Worker postMessages
  if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', handleServiceWorkerMessage)
  }
})

onUnmounted(() => {
  if (activityTouchTimer) window.clearTimeout(activityTouchTimer)
  if (keyboardBlurTimeout) clearTimeout(keyboardBlurTimeout)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', handleOutsideActivityClick)
  document.removeEventListener('focusin', handleVirtualKeyboardFocusIn)
  document.removeEventListener('focusout', handleVirtualKeyboardFocusOut)
  if (window.visualViewport) {
    window.visualViewport.removeEventListener('resize', handleVisualViewportChange)
    window.visualViewport.removeEventListener('scroll', handleVisualViewportChange)
  }
  if (pollTimer) window.clearInterval(pollTimer)
  document.removeEventListener('visibilitychange', handleVisibilityOrFocus)
  window.removeEventListener('focus', handleVisibilityOrFocus)

  if (realtimeBroadcastChannel) {
    realtimeBroadcastChannel.removeEventListener('message', handleRealtimeChannelMessage)
    realtimeBroadcastChannel.close()
    realtimeBroadcastChannel = null
  }

  if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator) {
    navigator.serviceWorker.removeEventListener('message', handleServiceWorkerMessage)
  }
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
</script>

<style scoped>
.activity-feed-page {
  height: 100vh;
  height: 100dvh;
  height: var(--vvh, 100dvh);
  max-height: var(--vvh, 100dvh);
  width: 100%;
  position: relative;
  overflow: hidden;
}

@media (max-width: 767px) {
  .activity-feed-page {
    height: var(--vvh, 100dvh) !important;
    max-height: var(--vvh, 100dvh) !important;
    min-height: 0 !important;
  }
}

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
