<template>
  <div class="flex flex-col">
    <!-- Replying to banner if replying -->
    <div v-if="replyingTo"
      class="flex items-start justify-between bg-[#e1e3ea] px-3.5 py-2 border-l-3 border-[#0068FF] text-sm shadow-3xs border-b border-gray-300">
      <div class="flex-1 min-w-0">
        <div class="text-[14px] font-extrabold text-[#0068FF] uppercase tracking-wider flex items-center gap-1">
          <i class="fa-solid fa-reply text-xs"></i>
          <span>Trả lời {{ replyingTo.user?.name || 'Hệ thống' }}</span>
        </div>
        <div class="text-[15px] text-gray-600 truncate mt-0.5">{{ replyText }}</div>
      </div>
      <button type="button" title="Hủy trả lời" @click="$emit('cancel-reply')"
        class="text-gray-400 hover:text-gray-650 p-1 rounded-full hover:bg-gray-200/50 cursor-pointer shrink-0 ml-2">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Main Composer Box (Integrated seamlessly into bottom of panel) -->
    <div class="flex flex-col bg-transparent relative transition-all overflow-visible"
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
        class="absolute z-50 bottom-full left-0 right-0 mb-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-[220px] overflow-y-auto divide-y divide-gray-100">
        <div class="px-3 py-1.5 bg-gray-50 text-[13px] font-extrabold text-gray-400 uppercase tracking-wider">
          {{ trigger === '#' ? 'Chọn dự án' : 'Gắn thẻ thành viên hoặc nhóm' }}
        </div>
        <button v-for="(item, index) in suggestions" :key="`${item.type}-${item.id}`" type="button"
          @mousedown.prevent @click="selectSuggestion(item)"
          class="w-full text-left px-3.5 py-2 text-[16px] font-bold flex justify-between items-center cursor-pointer hover:bg-emerald-50 hover:text-emerald-800"
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
      <div class="flex items-center justify-between px-3.5 py-2 bg-[#F9F4EE] rounded-t-[14px] border-b border-gray-300/40 select-none">
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

          <Teleport to="body">
            <!-- Backdrop overlay -->
            <div v-if="isProjectPickerOpen"
              class="fixed inset-0 bg-black/25 z-[9998] backdrop-blur-[1px]"
              @click="dismissProjectPicker"></div>

            <div v-if="isProjectPickerOpen"
              class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] w-[min(340px,calc(100vw-32px))] bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[360px]"
            >
              <div class="px-3.5 py-2.5 text-sm font-black text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <span>Chọn dự án</span>
                <button type="button" @click="dismissProjectPicker" class="text-gray-400 hover:text-gray-700 p-1">
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

              <div ref="projectPickerListRef" class="max-h-56 overflow-y-auto divide-y divide-gray-100 flex-1">
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
          </Teleport>
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
          <button type="button" title="Bỏ tệp" @click="removeAttachment(index)"
            class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-gray-700 hover:bg-rose-600 text-white flex items-center justify-center text-[9px] cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Textarea Input Area: full width with bottom submit button when typing/attaching -->
      <div class="flex flex-col px-3.5 py-2 bg-[#ebe6df] rounded-b-[14px]">
        <textarea ref="textareaRef" v-model="messageModel" rows="1"
          name="chat_activity_message"
          id="activity-composer-textarea"
          placeholder="Báo thông tin cho đồng đội"
          :disabled="!projects.length"
          class="w-full min-h-[36px] max-h-[140px] overflow-y-auto bg-transparent border-0 focus:ring-0 focus:outline-none text-[16px] sm:text-[18px] font-normal text-gray-900 resize-none p-0 placeholder-gray-500 leading-relaxed disabled:opacity-50 disabled:cursor-not-allowed"
          autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
          data-lpignore="true" data-1p-ignore="true" data-form-type="other" aria-autocomplete="none"
          @input="handleInput" @keydown="handleKeydown"
          @paste="handlePaste"></textarea>
        
        <div v-if="messageModel?.trim() || attachments.length" class="flex justify-end pt-1.5">
          <button @click="$emit('submit')" :disabled="submitting || !canSubmit" type="button"
            title="Gửi cập nhật (Hú hú)"
            class="w-8 h-8 rounded-xl flex items-center justify-center text-white shadow-xs transition-colors shrink-0 cursor-pointer"
            :class="submitting || !canSubmit ? 'bg-gray-300 cursor-not-allowed' : 'bg-[#45A246] hover:bg-[#3a903b]'">
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
  submitting: { type: Boolean, default: false },
  replyText: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'update:projectId', 'submit', 'cancel-reply'])
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

const hasText = ref(Boolean(messageModel.value?.trim()))
const canSubmit = computed(() => props.projects.length > 0 && (hasText.value || attachments.value.length > 0))

const resizeTextarea = () => {
  const textarea = textareaRef.value
  if (!textarea) return
  textarea.style.height = 'auto'
  const contentHeight = textarea.scrollHeight
  textarea.style.height = `${Math.min(140, Math.max(36, contentHeight))}px`
}

const compressImage = file => new Promise(resolve => {
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

const addAttachment = async file => {
  if (!file) return
  const isImage = file.type.startsWith('image/')
  const processedFile = isImage ? await compressImage(file) : file
  attachments.value.push({
    key: `${Date.now()}-${Math.random()}`,
    file: processedFile,
    name: file.name,
    isImage,
    preview: isImage ? URL.createObjectURL(processedFile) : null,
    uploaded: null,
  })
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
  for (const file of files) {
    await addAttachment(file)
  }
}

const handleFileSelection = async event => {
  for (const file of Array.from(event.target.files || [])) await addAttachment(file)
  event.target.value = ''
}

const handlePaste = async event => {
  const items = Array.from(event.clipboardData?.items || [])
  const files = items.map(item => item.getAsFile()).filter(Boolean)
  if (!files.length) return
  event.preventDefault()
  for (const [index, file] of files.entries()) {
    if (file.type.startsWith('image/')) {
      const extension = file.type.split('/')[1] || 'png'
      await addAttachment(new File([file], file.name || `pasted_image_${Date.now()}_${index + 1}.${extension}`, { type: file.type }))
    } else {
      await addAttachment(file)
    }
  }
}

const removeAttachment = index => {
  const attachment = attachments.value[index]
  if (attachment?.preview) URL.revokeObjectURL(attachment.preview)
  attachments.value.splice(index, 1)
}

const escapeHtml = value => String(value || '')
  .replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;')

const buildAttachmentHtml = async () => {
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

const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
const matches = (value) => {
  const normalizedQuery = normalize(query.value)
  if (!normalizedQuery) return true
  const normalizedValue = normalize(value)

  if (normalizedQuery.includes(' ')) {
    return normalizedValue.includes(normalizedQuery)
  }

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
  const otherUsers = props.users.filter(user => String(user.id) !== currentUserId)

  if (matches('all')) items.push({ type: 'all', id: 'all', title: '@all', token: 'all', subtitle: `Tất cả ${otherUsers.length} thành viên` })
  otherUsers.filter(user => matches(user.name) || matches(String(user.email || '').split('@')[0])).forEach(user => {
    items.push({ type: 'member', id: user.id, title: user.name, subtitle: 'Thành viên' })
  })
  props.groups.filter(group => matches(group.name) || matches(group.description)).forEach(group => {
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
  if (!String(value || '').trim()) {
    isAutoExpanded.value = false
    hasText.value = false
  } else {
    hasText.value = true
  }
  nextTick(resizeTextarea)
})

const handleInput = event => {
  nextTick(resizeTextarea)
  const text = event.target.value
  hasText.value = Boolean(text.trim())
  const cursor = event.target.selectionStart ?? text.length
  lastCursorPosition.value = cursor
  const match = text.substring(0, cursor).match(/([@#])([^@#]{0,30})$/)
  if (!match) {
    showSuggestions.value = false
    return
  }
  trigger.value = match[1]
  query.value = match[2]
  suggestionIndex.value = 0
  showSuggestions.value = true
}

const selectSuggestion = item => {
  const textarea = textareaRef.value
  if (!textarea) return

  const text = message.value
  const cursor = lastCursorPosition.value ?? textarea.selectionStart ?? text.length
  const before = text.substring(0, cursor)
  const after = text.substring(cursor)
  const tokenMatch = before.match(/([@#])([^@#]{0,30})$/)
  const prefix = tokenMatch ? before.substring(0, tokenMatch.index) : before
  let replacement = ''

  if (item.type === 'project') {
    projectId.value = item.id
    replacement = prefix
  } else {
    replacement = `${prefix}@${item.token || item.title} `
  }

  const finalValue = replacement + after

  textarea.value = finalValue
  textarea.dispatchEvent(new Event('input'))

  showSuggestions.value = false
  lastCursorPosition.value = null
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(replacement.length, replacement.length)
  })
}

const handleKeydown = event => {
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
  if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey && !event.metaKey) {
    event.preventDefault()
    emit('submit')
  }
}

onUnmounted(clearAttachments)

const focus = () => nextTick(() => textareaRef.value?.focus())
defineExpose({ focus, buildAttachmentHtml, clearAttachments })
</script>
