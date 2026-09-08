<template>
  <div class="flex flex-col">
    <!-- Editing Banner if editing an existing activity -->
    <div v-if="editingComment"
      class="flex items-center justify-between bg-amber-50 px-3.5 py-1.5 border-l-3 border-amber-500 text-sm shadow-3xs border-b border-amber-200/80">
      <div class="flex items-center gap-1.5 min-w-0">
        <i class="fa-solid fa-pen-to-square text-amber-600 text-xs shrink-0"></i>
        <span class="text-[13px] font-black text-amber-900 tracking-wide uppercase">Chỉnh sửa hoạt động</span>
      </div>
      <button type="button" title="Hủy chỉnh sửa" @click="$emit('cancel-edit')"
        class="text-amber-600 hover:text-amber-900 p-0.5 rounded-full hover:bg-amber-200/50 cursor-pointer shrink-0 ml-2">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Replying to banner if replying -->
    <div v-if="replyingTo"
      class="flex items-start justify-between bg-[#e1e3ea] px-3.5 py-2 border-l-3 border-[#0068FF] text-sm shadow-3xs border-b border-gray-300">
      <div class="flex-1 min-w-0">
        <div class="text-[14px] font-extrabold text-[#0068FF] uppercase tracking-wider flex items-center gap-1">
          <i class="fa-solid fa-reply text-xs"></i>
          <span>Trả lời {{ replyingTo.user?.name || (typeof replyingTo.user === 'string' ? replyingTo.user : 'Hệ thống') }}</span>
        </div>
        <div class="text-[15px] text-gray-600 truncate mt-0.5">{{ replyText || replyingTo.content || replyingTo.text }}</div>
      </div>
      <button v-if="!editingComment" type="button" title="Hủy trả lời" @click="$emit('cancel-reply')"
        class="text-gray-400 hover:text-gray-650 p-1 rounded-full hover:bg-gray-200/50 cursor-pointer shrink-0 ml-2">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Main Composer Box (Integrated seamlessly into bottom of panel) -->
    <div class="flex flex-col bg-transparent relative transition-all overflow-visible activity-composer"
      :class="{ 'ring-2 ring-emerald-500': isDragging }"
      @dragenter.prevent="handleDragEnter"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop">

      <!-- Drag Over Visual Overlay -->
      <div v-if="isDragging"
        class="absolute inset-0 z-40 bg-emerald-500/10 border-2 border-dashed border-emerald-500 rounded-xl flex flex-col items-center justify-center gap-1.5 backdrop-blur-[2px] pointer-events-none transition-all">
        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm animate-bounce">
          <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
        </div>
        <p class="text-sm font-bold text-emerald-800">Thả tệp hoặc ảnh vào đây để tải lên</p>
      </div>

      <!-- Mention / Project Autocomplete Suggestions -->
      <div v-if="showSuggestions && suggestions.length"
        class="absolute z-50 bottom-full left-0 right-0 mb-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-[220px] overflow-y-auto divide-y divide-gray-100"
        @touchstart.stop @mousedown.stop>
        <div class="px-3 py-1.5 bg-gray-50 text-[13px] font-extrabold text-gray-400 uppercase tracking-wider sticky top-0 z-10">
          {{ trigger === '#' ? 'Chọn dự án' : 'Gắn thẻ thành viên hoặc nhóm' }}
        </div>
        <button v-for="(item, index) in suggestions" :key="`${item.type}-${item.id}`" type="button"
          @pointerdown.prevent="selectSuggestion(item)"
          @touchstart.prevent="selectSuggestion(item)"
          @mousedown.prevent="selectSuggestion(item)"
          @click.prevent="selectSuggestion(item)"
          class="w-full text-left px-3.5 py-2.5 text-[16px] font-bold flex justify-between items-center cursor-pointer hover:bg-emerald-50 hover:text-emerald-800 active:bg-emerald-100 touch-manipulation"
          :class="index === suggestionIndex ? 'bg-emerald-50 text-emerald-800' : 'text-gray-700'">
          <span class="truncate flex-1 flex items-center gap-1.5">
            <i v-if="item.type === 'project'" class="fa-solid fa-folder text-emerald-600 text-xs"></i>
            <i v-else-if="item.type === 'all'" class="fa-solid fa-users text-emerald-600 text-xs"></i>
            <i v-else-if="item.type === 'member'" class="fa-solid fa-user text-blue-500 text-xs"></i>
            <i v-else class="fa-solid fa-users text-amber-500 text-xs"></i>
            <span>{{ item.title }}</span>
          </span>
          <span class="text-[14px] text-gray-400 font-semibold truncate max-w-[130px] shrink-0 ml-2">
            {{ item.subtitle }}
          </span>
        </button>
      </div>

      <!-- Top Toolbar Row: Paperclip, Image, Project Selector Pill -->
      <div class="flex items-center justify-between px-3.5 py-2 bg-[#F9F4EE] border-b border-gray-300/40 select-none"
        :class="{ 'rounded-t-[14px]': !replyingTo && !editingComment }">
        <div class="flex items-center gap-3 text-gray-700">
          <input ref="fileInputRef" type="file" multiple
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" class="hidden" @change="handleFileSelection" />
          <input ref="imageFileInputRef" type="file" multiple
            accept="image/*" class="hidden" @change="handleFileSelection" />

          <!-- Paperclip button -->
          <button type="button" title="Đính kèm tệp" @click="fileInputRef?.click()"
            class="text-gray-700 hover:text-[#1A7A56] cursor-pointer transition-colors p-0.5">
            <i class="fa-solid fa-paperclip text-lg"></i>
          </button>

          <!-- Image button -->
          <button type="button" title="Đính kèm ảnh" @click="imageFileInputRef?.click()"
            class="text-gray-700 hover:text-[#1A7A56] cursor-pointer transition-colors p-0.5">
            <i class="fa-solid fa-image text-lg"></i>
          </button>
        </div>

        <!-- Project Selector Pill (Green pill matching image) -->
        <div class="relative min-w-0 max-w-[210px]">
          <button type="button" @click="toggleProjectPicker"
            class="px-3 py-1 bg-[#e6f4ea] hover:bg-[#d8edd9] border border-emerald-300/80 rounded-full flex items-center gap-1.5 cursor-pointer text-[#1A7A56] font-extrabold text-[16px] transition-colors max-w-full truncate shadow-3xs"
            :title="selectedProject ? selectedProject.title : 'Chọn dự án'">
            <span class="truncate">{{ selectedProject ? selectedProject.title : 'Chọn dự án...' }}</span>
            <i class="fa-solid fa-chevron-down text-xs shrink-0 transition-transform"
              :class="isProjectPickerOpen ? 'rotate-180' : ''"></i>
          </button>

          <!-- Backdrop overlay -->
          <div v-if="isProjectPickerOpen"
            class="fixed inset-0 z-40 bg-black/20 md:bg-transparent"
            @click="dismissProjectPicker"></div>

          <!-- Project Picker Dropdown (Anchored directly above the project selector pill on desktop) -->
          <div v-if="isProjectPickerOpen"
            class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 md:absolute md:top-auto md:bottom-full md:left-auto md:right-0 md:translate-x-0 md:translate-y-0 md:mb-2 z-50 w-[min(340px,calc(100vw-32px))] md:w-[350px] bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[380px] animate-fade-in-up"
          >
            <div class="px-3.5 py-2.5 text-sm font-black text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
              <span>Chọn dự án</span>
              <button type="button" @click="dismissProjectPicker" class="text-gray-400 hover:text-gray-700 p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
              </button>
            </div>

            <div class="p-2 border-b border-gray-100 bg-white flex-shrink-0">
              <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input
                  ref="mobileSearchInputRef"
                  v-model="projectSearch"
                  type="text"
                  placeholder="Tìm kiếm dự án..."
                  class="w-full pl-8 pr-3 py-1.5 text-sm font-bold border border-gray-200 rounded-lg focus:outline-none focus:border-emerald-500 bg-gray-50"
                  autocomplete="off"
                  @keydown="handlePickerKeydown"
                />
              </div>
            </div>

            <div ref="projectPickerListRef" class="max-h-60 overflow-y-auto overscroll-contain divide-y divide-gray-100 flex-1">
              <button v-for="(project, pIdx) in filteredPickerProjects" :key="`picker-${project.id}`" type="button"
                @mousedown.prevent="selectProjectFromPicker(project)"
                class="w-full px-3.5 py-2.5 flex items-center justify-between gap-3 text-left cursor-pointer transition-colors"
                :class="pIdx === pickerSelectedIndex ? 'bg-emerald-50 text-emerald-900' : 'hover:bg-gray-50'">
                <span class="min-w-0">
                  <span class="block text-[16px] font-extrabold text-gray-900 truncate">{{ project.title }}</span>
                  <span v-if="project.customer?.name" class="block text-[14px] text-gray-400 font-semibold truncate mt-0.5">
                    {{ project.customer.name }}
                  </span>
                </span>
                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                  :class="String(project.id) === String(projectId) ? 'border-emerald-600' : 'border-gray-300'">
                  <span v-if="String(project.id) === String(projectId)" class="w-2 h-2 rounded-full bg-emerald-600"></span>
                </span>
              </button>
              <div v-if="filteredPickerProjects.length === 0"
                class="px-3 py-5 text-center text-sm font-semibold text-gray-400">
                Không tìm thấy dự án phù hợp.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attachment list preview -->
      <div v-if="attachments.length" class="flex flex-wrap gap-2 px-3.5 pt-2 pb-1 bg-white">
        <div v-for="(attachment, index) in attachments" :key="attachment.key"
          class="relative flex-shrink-0">
          <img v-if="attachment.isImage" :src="attachment.preview" :alt="attachment.name"
            class="w-11 h-11 rounded-lg object-cover border border-gray-200 bg-gray-50 shadow-3xs" />
          <div v-else
            class="h-11 max-w-[150px] px-2 rounded-lg border border-amber-200 bg-amber-50 flex items-center gap-1.5 text-[14px] font-bold text-amber-800">
            <i class="fa-solid fa-file text-amber-600"></i>
            <span class="truncate">{{ attachment.name }}</span>
          </div>
          <span v-if="attachment.uploadStatus === 'uploading'"
            class="absolute inset-0 rounded-lg bg-black/35 text-white flex items-center justify-center"
            title="Äang táº£i lÃªn">
            <i class="fa-solid fa-spinner fa-spin text-xs"></i>
          </span>
          <span v-else-if="attachment.uploadStatus === 'error'"
            class="absolute inset-0 rounded-lg bg-rose-600/75 text-white flex items-center justify-center"
            title="Táº£i lÃªn lá»—i, sáº½ tá»± thá»­ láº¡i khi gá»­i">
            <i class="fa-solid fa-rotate-right text-xs"></i>
          </span>
          <button type="button" title="Bỏ tệp" @click="removeAttachment(index)"
            class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-gray-700 hover:bg-rose-600 text-white flex items-center justify-center text-[9px] cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Textarea Input Area: inline with submit button for instant visibility on all devices -->
      <div class="flex items-end gap-2 px-3.5 py-2 bg-[#ebe6df] rounded-b-[14px] cursor-text"
        @click="focusTextarea">
        <textarea ref="textareaRef"
          :value="messageModel"
          @input="syncInputState"
          @beforeinput="syncInputState"
          @compositionstart="syncInputState"
          @compositionupdate="syncInputState"
          @compositionend="syncInputState"
          @keyup="syncInputState"
          @keydown="handleKeydown"
          @change="syncInputState"
          @focus="syncInputState"
          @blur="handleBlur"
          @paste="handlePaste"
          rows="1"
          name="chat_activity_message"
          id="activity-composer-textarea"
          :placeholder="editingComment ? 'Chỉnh sửa nội dung hoạt động...' : 'Báo thông tin cho đồng đội'"
          class="flex-1 min-h-[36px] max-h-[140px] overflow-y-auto bg-transparent border-0 focus:ring-0 focus:outline-none text-[16px] sm:text-[18px] font-normal text-gray-900 resize-none p-0 placeholder-gray-500 leading-relaxed"
          autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
          data-lpignore="true" data-1p-ignore="true" data-form-type="other" aria-autocomplete="none"></textarea>
        
        <div class="flex-shrink-0 flex items-center mb-0.5">
          <button @click="handleSubmit" :disabled="submitting || !canSend" type="button"
            :title="editingComment ? 'Lưu thay đổi' : 'Gửi cập nhật (Hú hú)'"
            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-white shadow-xs transition-all active:scale-95 shrink-0 cursor-pointer"
            :class="canSend && !submitting ? 'bg-[#45A246] hover:bg-[#3a903b] opacity-100 shadow-sm' : 'bg-gray-300/80 opacity-40 cursor-not-allowed'">
            <i class="fa-solid fa-dove text-sm"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const props = defineProps({
  projects: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  groups: { type: Array, default: () => [] },
  replyingTo: { type: Object, default: null },
  editingComment: { type: Object, default: null },
  submitting: { type: Boolean, default: false },
  replyText: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'update:projectId', 'submit', 'cancel-reply', 'cancel-edit'])
const messageModel = defineModel({ type: String, default: '' })
const projectModel = defineModel('projectId', { default: null })
const message = messageModel
const projectId = computed({ get: () => projectModel.value, set: value => { projectModel.value = value } })

const authStore = useAuthStore()
const textareaRef = ref(null)
const lastCursorPosition = ref(null)
const isExpanded = ref(false)
const isAutoExpanded = ref(false)
const isProjectPickerOpen = ref(false)
const projectSearch = ref('')
const showSuggestions = ref(false)
const trigger = ref('')
const query = ref('')
const suggestionIndex = ref(0)
const fileInputRef = ref(null)
const imageFileInputRef = ref(null)
const attachments = ref([])

const mobileSearchInputRef = ref(null)

const rawInputText = ref(messageModel.value || '')
const canSend = computed(() => {
  const domText = textareaRef.value?.value || ''
  const rawText = rawInputText.value || ''
  const modelText = messageModel.value || ''
  const currentText = (domText || rawText || modelText).trim()
  return Boolean(currentText.length > 0 || attachments.value.length > 0)
})
const hasText = canSend
const isButtonActive = canSend
const canSubmit = canSend

const resizeTextarea = () => {
  const textarea = textareaRef.value
  if (!textarea) return
  textarea.style.height = 'auto'
  const contentHeight = textarea.scrollHeight
  textarea.style.height = `${Math.min(140, Math.max(36, contentHeight))}px`
}

const compressImage = file => {
  if (!/^image\/(jpeg|png|webp)$/i.test(file.type) || file.size <= 350 * 1024) {
    return Promise.resolve(file)
  }
  return new Promise(resolve => {
  const reader = new FileReader()
  reader.onload = event => {
    const image = new Image()
    image.onload = () => {
      const canvas = document.createElement('canvas')
      let width = image.width
      let height = image.height
      const maxDimension = 1200
      if (width > maxDimension || height > maxDimension) {
        if (width > height) {
          height = Math.round(height * maxDimension / width)
          width = maxDimension
        } else {
          width = Math.round(width * maxDimension / height)
          height = maxDimension
        }
      }
      canvas.width = width
      canvas.height = height
      canvas.getContext('2d').drawImage(image, 0, 0, width, height)
      canvas.toBlob(blob => resolve(blob
        ? new File([blob], file.name, { type: file.type || 'image/jpeg' })
        : file), file.type.includes('png') ? 'image/png' : 'image/jpeg', 0.75)
    }
    image.onerror = () => resolve(file)
    image.src = event.target.result
  }
  reader.onerror = () => resolve(file)
  reader.readAsDataURL(file)
  })
}

const uploadAttachmentBatch = async items => {
  if (!items.length) return
  items.forEach(item => {
    item.uploadStatus = 'uploading'
    item.uploadError = null
  })
  const formData = new FormData()
  items.forEach(item => formData.append('files[]', item.file))
  const request = axios.post('/api/attachments', formData)
    .then(response => {
      const uploadedFiles = response.data || []
      if (uploadedFiles.length !== items.length) throw new Error('Incomplete attachment upload response')
      items.forEach((item, index) => {
        item.uploaded = uploadedFiles[index]
        item.uploadStatus = 'done'
      })
    })
    .catch(error => {
      items.forEach(item => {
        if (!item.uploaded) {
          item.uploadStatus = 'error'
          item.uploadError = error
        }
      })
      throw error
    })
  items.forEach(item => { item.uploadPromise = request })
  return request
}

const addAttachments = async files => {
  const validFiles = Array.from(files || []).filter(Boolean)
  if (!validFiles.length) return
  const newItems = await Promise.all(validFiles.map(async file => {
    const isImage = file.type.startsWith('image/')
    const processedFile = isImage ? await compressImage(file) : file
    return {
      key: `${Date.now()}-${Math.random()}`,
      file: processedFile,
      name: file.name,
      isImage,
      preview: isImage ? URL.createObjectURL(processedFile) : null,
      uploaded: null,
      uploadStatus: 'pending',
      uploadPromise: null,
      uploadError: null,
    }
  }))
  attachments.value.push(...newItems)
  uploadAttachmentBatch(newItems).catch(() => {})
}

const isDragging = ref(false)
let dragCounter = 0

const handleDragEnter = (e) => {
  if (e.dataTransfer?.types?.includes('Files')) {
    dragCounter++
    isDragging.value = true
  }
}

const handleDragOver = (e) => {
  if (e.dataTransfer?.types?.includes('Files')) {
    e.dataTransfer.dropEffect = 'copy'
  }
}

const handleDragLeave = (e) => {
  if (e.dataTransfer?.types?.includes('Files')) {
    dragCounter = Math.max(0, dragCounter - 1)
    if (dragCounter === 0) {
      isDragging.value = false
    }
  }
}

const handleDrop = async (e) => {
  dragCounter = 0
  isDragging.value = false
  const files = Array.from(e.dataTransfer?.files || [])
  await addAttachments(files)
}

const handleFileSelection = async event => {
  await addAttachments(event.target.files)
  event.target.value = ''
}

const handlePaste = async event => {
  const items = Array.from(event.clipboardData?.items || [])
  const files = items.map(item => item.getAsFile()).filter(Boolean)
  if (!files.length) return
  event.preventDefault()
  const namedFiles = files.map((file, index) => {
    if (!file.type.startsWith('image/')) return file
    const extension = file.type.split('/')[1] || 'png'
    return new File([file], file.name || `pasted_image_${Date.now()}_${index + 1}.${extension}`, { type: file.type })
  })
  await addAttachments(namedFiles)
}

const removeAttachment = index => {
  const attachment = attachments.value[index]
  if (attachment?.preview) URL.revokeObjectURL(attachment.preview)
  attachments.value.splice(index, 1)
}

const escapeHtml = value => String(value || '')
  .replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;')

const buildAttachmentHtml = async () => {
  const inFlight = [...new Set(attachments.value
    .filter(attachment => !attachment.uploaded && attachment.uploadStatus === 'uploading')
    .map(attachment => attachment.uploadPromise)
    .filter(Boolean))]
  if (inFlight.length) await Promise.allSettled(inFlight)

  const pendingAttachments = attachments.value.filter(attachment => !attachment.uploaded)
  if (pendingAttachments.length) {
    const formData = new FormData()
    pendingAttachments.forEach(attachment => formData.append('files[]', attachment.file))
    const response = await axios.post('/api/attachments', formData)
    const uploadedFiles = response.data || []
    if (uploadedFiles.length !== pendingAttachments.length) {
      throw new Error('Máy chủ không trả về đầy đủ thông tin tệp đã tải lên.')
    }
    pendingAttachments.forEach((attachment, index) => {
      attachment.uploaded = uploadedFiles[index]
      attachment.uploadStatus = 'done'
    })
  }

  let html = ''
  for (const attachment of attachments.value) {
    const url = escapeHtml(attachment.uploaded.url)
    const name = escapeHtml(attachment.uploaded.original_name || attachment.name)
    html += attachment.isImage
      ? `<br/><img src="${url}" class="max-h-56 rounded-xl my-2 border border-gray-200 shadow-2xs block" />`
      : `<br/><a href="${url}" download="${name}" target="_blank">📎 Tệp đính kèm: ${name}</a>`
  }
  return html
}

const clearAttachments = () => {
  attachments.value.forEach(attachment => {
    if (attachment.preview) URL.revokeObjectURL(attachment.preview)
  })
  attachments.value = []
}

const normalize = value => String(value || '')
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .replace(/đ/g, 'd')
  .replace(/Đ/g, 'd')
  .toLowerCase()
  .trim()

const matches = (value) => {
  const normalizedQuery = normalize(query.value)
  if (!normalizedQuery) return true
  const normalizedValue = normalize(value)

  if (normalizedValue.includes(normalizedQuery)) return true

  const words = normalizedValue.split(/\s+/).filter(Boolean)
  const startsWithWord = words.some(word => word.startsWith(normalizedQuery))
  const initials = words.map(word => word[0]).join('')
  return startsWithWord || initials.includes(normalizedQuery)
}

const selectedProject = computed(() => props.projects.find(project => String(project.id) === String(projectId.value)) || null)
const filteredPickerProjects = computed(() => {
  const rawQuery = projectSearch.value.trim()
  if (!rawQuery) return props.projects
  const normalizedQuery = normalize(rawQuery)
  return props.projects.filter(project => {
    const title = normalize(project.title)
    const customer = normalize(project.customer?.name)
    return title.includes(normalizedQuery) || customer.includes(normalizedQuery)
  })
})

const syncProjectSearch = () => {
  if (!isProjectPickerOpen.value) projectSearch.value = ''
}

const dismissProjectPicker = () => {
  isProjectPickerOpen.value = false
  syncProjectSearch()
}

const toggleProjectPicker = () => {
  isProjectPickerOpen.value = !isProjectPickerOpen.value
  if (isProjectPickerOpen.value) {
    projectSearch.value = ''
    nextTick(() => {
      mobileSearchInputRef.value?.focus()
    })
  }
}

const pickerSelectedIndex = ref(0)
const projectPickerListRef = ref(null)

watch([filteredPickerProjects, isProjectPickerOpen], () => {
  pickerSelectedIndex.value = 0
})

const scrollPickerItemIntoView = () => {
  nextTick(() => {
    const listEl = projectPickerListRef.value
    if (!listEl) return
    const activeItem = listEl.children[pickerSelectedIndex.value]
    if (activeItem?.scrollIntoView) {
      activeItem.scrollIntoView({ block: 'nearest' })
    }
  })
}

const handlePickerKeydown = (event) => {
  if (event.key === 'Escape') {
    event.preventDefault()
    dismissProjectPicker()
    return
  }
  const total = filteredPickerProjects.value.length
  if (!total) return

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    pickerSelectedIndex.value = (pickerSelectedIndex.value + 1) % total
    scrollPickerItemIntoView()
    return
  }

  if (event.key === 'ArrowUp') {
    event.preventDefault()
    pickerSelectedIndex.value = (pickerSelectedIndex.value - 1 + total) % total
    scrollPickerItemIntoView()
    return
  }

  if (event.key === 'Enter') {
    event.preventDefault()
    const selected = filteredPickerProjects.value[pickerSelectedIndex.value]
    if (selected) {
      selectProjectFromPicker(selected)
    }
  }
}

const selectProjectFromPicker = project => {
  projectId.value = project.id
  projectSearch.value = ''
  isProjectPickerOpen.value = false
  nextTick(() => textareaRef.value?.focus())
}

const suggestions = computed(() => {
  if (trigger.value === '#') {
    return props.projects.filter(project => matches(project.title) || matches(project.customer?.name)).slice(0, 10).map(project => ({
      type: 'project', id: project.id, title: project.title, subtitle: project.customer?.name || 'Dự án'
    }))
  }

  if (trigger.value !== '@') return []
  const items = []
  const currentUserId = String(authStore.user?.id || '')
  const otherUsers = (props.users || []).filter(user => String(user.id) !== currentUserId)

  if (matches('all') || matches('@all')) items.push({ type: 'all', id: 'all', title: '@all', token: 'all', subtitle: `Tất cả ${otherUsers.length} thành viên` })
  otherUsers.filter(user => matches(user.name) || matches(String(user.email || '').split('@')[0])).forEach(user => {
    items.push({ type: 'member', id: user.id, title: user.name, subtitle: 'Thành viên' })
  })
  ;(props.groups || []).filter(group => matches(group.name) || matches(group.description)).forEach(group => {
    items.push({ type: 'group', id: group.id, title: group.name, subtitle: group.description || 'Nhóm nhắc tên' })
  })
  return items.slice(0, 10)
})

watch(() => props.projects, projects => {
  if (projects.length) {
    if (!projectModel.value || !projects.some(p => String(p.id) === String(projectModel.value))) {
      projectModel.value = projects[0].id
    }
  } else {
    projectModel.value = null
  }
}, { immediate: true })

watch(messageModel, value => {
  rawInputText.value = value || ''
  if (!String(value || '').trim()) {
    isAutoExpanded.value = false
  }
  nextTick(resizeTextarea)
})

const syncInputState = (event) => {
  let text = ''
  if (event && event.target && typeof event.target.value === 'string') {
    text = event.target.value
  } else if (textareaRef.value && typeof textareaRef.value.value === 'string') {
    text = textareaRef.value.value
  } else {
    text = messageModel.value || ''
  }

  rawInputText.value = text
  if (messageModel.value !== text) {
    messageModel.value = text
  }

  nextTick(resizeTextarea)

  const textarea = textareaRef.value || event?.target
  const cursor = (textarea && typeof textarea.selectionStart === 'number')
    ? textarea.selectionStart
    : text.length
  lastCursorPosition.value = cursor

  const beforeCursor = text.substring(0, cursor)
  // Match @ or # preceded by start of line or whitespace
  const match = beforeCursor.match(/(?:^|\s)([@#])([^\s@#]*)$/)
  if (!match) {
    showSuggestions.value = false
    return
  }
  trigger.value = match[1]
  query.value = match[2]
  suggestionIndex.value = 0
  showSuggestions.value = true
}

const handleInput = syncInputState

const handleBlur = () => {
  // Grace period so touches on suggestion items register before dismissing
  setTimeout(() => {
    showSuggestions.value = false
  }, 250)
}

const handleSubmit = () => {
  if (textareaRef.value && typeof textareaRef.value.value === 'string') {
    const directVal = textareaRef.value.value
    rawInputText.value = directVal
    messageModel.value = directVal
  }
  if (!canSend.value) return
  emit('submit')
}

const selectSuggestion = item => {
  const textarea = textareaRef.value
  if (!textarea) return

  const text = (rawInputText.value || messageModel.value || textarea.value || '')
  const cursor = lastCursorPosition.value ?? textarea.selectionStart ?? text.length
  const before = text.substring(0, cursor)
  const after = text.substring(cursor)
  
  // Find where the trigger (@ or #) starts in before
  const tokenMatch = before.match(/(?:^|\s)([@#])([^\s@#]*)$/)
  let prefix = before
  if (tokenMatch) {
    const triggerIndex = before.lastIndexOf(tokenMatch[1])
    prefix = before.substring(0, triggerIndex)
  }

  let replacement = ''
  if (item.type === 'project') {
    projectId.value = item.id
    replacement = prefix
  } else {
    replacement = `${prefix}@${item.token || item.title} `
  }

  const finalValue = replacement + after

  textarea.value = finalValue
  rawInputText.value = finalValue
  messageModel.value = finalValue
  showSuggestions.value = false
  lastCursorPosition.value = replacement.length

  nextTick(() => {
    resizeTextarea()
    textarea.focus()
    textarea.setSelectionRange(replacement.length, replacement.length)
  })
}

const handleKeydown = event => {
  syncInputState(event)
  if (showSuggestions.value && suggestions.value.length) {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault()
      const direction = event.key === 'ArrowDown' ? 1 : -1
      suggestionIndex.value = (suggestionIndex.value + direction + suggestions.value.length) % suggestions.value.length
      return
    }
    if (event.key === 'Enter' || event.key === 'Tab') {
      event.preventDefault()
      selectSuggestion(suggestions.value[suggestionIndex.value])
      return
    }
    if (event.key === 'Escape') {
      event.preventDefault()
      showSuggestions.value = false
      return
    }
  }
  const isMobileDevice = typeof window !== 'undefined' && (window.innerWidth < 768 || ('ontouchstart' in window) || navigator.maxTouchPoints > 0)
  if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey && !event.metaKey) {
    if (isMobileDevice) {
      // On mobile / virtual keyboards, Enter should insert a newline, only pressing the submit button sends
      return
    }
    event.preventDefault()
    handleSubmit()
  }
}

onMounted(() => {
  // Initial size check
  nextTick(resizeTextarea)
})

onUnmounted(() => {
  clearAttachments()
})

const focusTextarea = (e) => {
  if (e?.target !== textareaRef.value) {
    textareaRef.value?.focus()
  }
}

const focus = () => {
  nextTick(() => {
    const el = textareaRef.value
    if (!el) return
    el.focus()
    const len = el.value.length
    el.setSelectionRange(len, len)
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  })
}
defineExpose({ focus, focusTextarea, buildAttachmentHtml, clearAttachments })
</script>
