<template>
  <div class="flex flex-col gap-2.5">
    <div v-if="replyingTo"
      class="flex items-start justify-between bg-[#e1e3ea] px-3.5 py-2.5 rounded-xl border-l-3 border-[#0068FF] text-xs shadow-3xs">
      <div class="flex-1 min-w-0">
        <div class="text-[10px] font-extrabold text-[#0068FF] uppercase tracking-wider flex items-center gap-1">
          <i class="fa-solid fa-reply text-[9px]"></i>
          <span>Trả lời {{ replyingTo.user?.name || 'Hệ thống' }}</span>
        </div>
        <div class="text-[11px] text-gray-500 truncate mt-0.5">{{ replyText }}</div>
      </div>
      <button type="button" title="Hủy trả lời" @click="$emit('cancel-reply')"
        class="text-gray-400 hover:text-gray-650 p-1 rounded-full hover:bg-gray-200/50 cursor-pointer shrink-0 ml-2">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <div class="flex flex-col bg-white rounded-xl border border-gray-300 shadow-sm relative">
      <div v-if="showSuggestions && suggestions.length"
        class="absolute z-50 bottom-full left-0 right-0 mb-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-[220px] overflow-y-auto divide-y divide-gray-100">
        <div class="px-3 py-1.5 bg-gray-50 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider">
          {{ trigger === '#' ? 'Chọn dự án' : 'Gắn thẻ thành viên hoặc nhóm' }}
        </div>
        <button v-for="(item, index) in suggestions" :key="`${item.type}-${item.id}`" type="button"
          @mousedown.prevent @click="selectSuggestion(item)"
          class="w-full text-left px-3.5 py-2 text-xs font-bold flex justify-between items-center cursor-pointer hover:bg-emerald-50 hover:text-emerald-800"
          :class="index === suggestionIndex ? 'bg-emerald-50 text-emerald-800' : 'text-gray-700'">
          <span class="truncate flex-1 flex items-center gap-1.5">
            <i v-if="item.type === 'project'" class="fa-solid fa-folder text-emerald-600 text-[10px]"></i>
            <i v-else-if="item.type === 'all'" class="fa-solid fa-users text-emerald-600 text-[10px]"></i>
            <i v-else-if="item.type === 'member'" class="fa-solid fa-user text-blue-500 text-[10px]"></i>
            <i v-else class="fa-solid fa-users text-amber-500 text-[10px]"></i>
            <span>{{ item.title }}</span>
          </span>
          <span class="text-[10px] text-gray-400 font-semibold truncate max-w-[130px] shrink-0 ml-2">
            {{ item.subtitle }}
          </span>
        </button>
      </div>

      <div class="flex items-center justify-between px-3.5 py-1.5 bg-gray-50 border-b border-gray-200 rounded-t-xl select-none">
        <button @click="toggleExpanded" type="button"
          class="text-gray-400 hover:text-gray-650 text-xs cursor-pointer p-0.5"
          :title="isExpanded ? 'Thu nhỏ khung chat' : 'Mở rộng khung chat'">
          <i :class="isExpanded ? 'fa-solid fa-compress' : 'fa-solid fa-expand'"></i>
        </button>

        <div class="flex items-center gap-1.5 ml-2 min-w-0">
          <span class="text-[9px] font-extrabold text-gray-400 uppercase tracking-wider shrink-0">Dự án:</span>
          <div class="relative min-w-0">
            <input ref="projectPickerInputRef" v-model="projectSearch" type="text"
              :placeholder="projects.length ? 'Tìm dự án...' : 'Chưa có dự án...'"
              class="w-[150px] sm:w-[180px] text-base sm:text-[11px] font-bold text-[#1A7A56] bg-emerald-50/50 border border-emerald-200/30 hover:border-emerald-500/50 rounded-full pl-2.5 pr-7 py-0.5 focus:ring-1 focus:ring-emerald-400 focus:outline-none truncate"
              autocomplete="off" @focus="openProjectPicker" @input="isProjectPickerOpen = true"
              @blur="closeProjectPicker" @keydown.esc.prevent="dismissProjectPicker" />
            <button type="button" tabindex="-1" @mousedown.prevent="toggleProjectPicker"
              class="absolute inset-y-0 right-0 w-7 flex items-center justify-center text-[#1A7A56] cursor-pointer">
              <i class="fa-solid fa-chevron-down text-[9px] transition-transform"
                :class="isProjectPickerOpen ? 'rotate-180' : ''"></i>
            </button>

            <div v-if="isProjectPickerOpen"
              class="absolute z-[70] bottom-full right-0 mb-1 w-[min(320px,calc(100vw-32px))] bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
              <div class="px-3 py-1.5 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                Tìm và chọn dự án
              </div>
              <div class="max-h-52 overflow-y-auto divide-y divide-gray-100">
                <button v-for="project in filteredPickerProjects" :key="`picker-${project.id}`" type="button"
                  @mousedown.prevent="selectProjectFromPicker(project)"
                  class="w-full px-3 py-2.5 flex items-center justify-between gap-3 text-left hover:bg-emerald-50 cursor-pointer">
                  <span class="min-w-0">
                    <span class="block text-xs font-bold text-gray-800 truncate">{{ project.title }}</span>
                    <span v-if="project.customer?.name" class="block text-[10px] text-gray-400 truncate mt-0.5">
                      {{ project.customer.name }}
                    </span>
                  </span>
                  <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                    :class="String(project.id) === String(projectId) ? 'border-emerald-600' : 'border-gray-400'">
                    <span v-if="String(project.id) === String(projectId)" class="w-2 h-2 rounded-full bg-emerald-600"></span>
                  </span>
                </button>
                <div v-if="filteredPickerProjects.length === 0"
                  class="px-3 py-5 text-center text-xs font-semibold text-gray-400">
                  Không tìm thấy dự án phù hợp.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="attachments.length" class="flex flex-wrap gap-2 px-3 pt-2">
        <div v-for="(attachment, index) in attachments" :key="attachment.key"
          class="relative flex-shrink-0">
          <img v-if="attachment.isImage" :src="attachment.preview" :alt="attachment.name"
            class="w-11 h-11 rounded-lg object-cover border border-gray-200 bg-gray-50" />
          <div v-else
            class="h-11 max-w-[150px] px-2 rounded-lg border border-amber-200 bg-amber-50 flex items-center gap-1.5 text-[10px] font-bold text-amber-800">
            <i class="fa-solid fa-file text-amber-600"></i>
            <span class="truncate">{{ attachment.name }}</span>
          </div>
          <button type="button" title="Bỏ tệp" @click="removeAttachment(index)"
            class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-gray-700 hover:bg-rose-600 text-white flex items-center justify-center text-[9px] cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <div class="flex flex-col p-2 rounded-b-xl bg-white">
        <textarea ref="textareaRef" v-model="message" rows="2"
          placeholder="Nhập cập nhật; gõ @ để gắn thẻ, # để chọn dự án..."
          class="w-full min-h-[44px] max-h-[200px] overflow-y-auto bg-transparent border-0 focus:ring-0 focus:outline-none text-base sm:text-xs font-semibold text-gray-800 resize-none p-0.5 placeholder-gray-400 leading-relaxed"
          autocomplete="one-time-code" spellcheck="false" @input="handleInput" @keydown="handleKeydown"
          @paste="handlePaste"></textarea>
        <div class="flex items-center justify-end gap-2 mt-1">
          <input ref="fileInputRef" type="file" multiple
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" class="hidden" @change="handleFileSelection" />
          <button type="button" title="Đính kèm ảnh hoặc tệp" @click="fileInputRef?.click()"
            class="text-gray-400 hover:text-[#1A7A56] cursor-pointer h-7 w-7 rounded-lg hover:bg-gray-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-paperclip text-sm"></i>
          </button>
          <button @click="$emit('submit')" :disabled="submitting || !canSubmit" type="button"
            class="px-3 py-2 text-white font-extrabold text-xs rounded-xl flex items-center gap-1.5 shadow-xs transition-colors shrink-0"
            :class="submitting || !canSubmit ? 'bg-gray-300 cursor-not-allowed' : 'bg-[#45A246] hover:bg-[#3a903b] cursor-pointer'">
            <i class="fa-solid fa-dove text-xs"></i>
            <span>{{ submitting ? '...' : 'Hú Hú' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'
import axios from 'axios'

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
const message = computed({ get: () => messageModel.value, set: value => { messageModel.value = value } })
const projectId = computed({ get: () => projectModel.value, set: value => { projectModel.value = value } })

const textareaRef = ref(null)
const projectPickerInputRef = ref(null)
const isExpanded = ref(false)
const isAutoExpanded = ref(false)
const isProjectPickerOpen = ref(false)
const projectSearch = ref('')
const showSuggestions = ref(false)
const trigger = ref('')
const query = ref('')
const suggestionIndex = ref(0)
const fileInputRef = ref(null)
const attachments = ref([])
const canSubmit = computed(() => Boolean(message.value.trim()) || attachments.value.length > 0)

const resizeTextarea = () => {
  const textarea = textareaRef.value
  if (!textarea) return
  textarea.style.height = 'auto'
  const contentHeight = textarea.scrollHeight
  isAutoExpanded.value = contentHeight > 64

  // Automatic growth uses the former expand-button size. Explicit expansion
  // opens the larger editor, independently of the message length.
  if (isExpanded.value) {
    textarea.style.height = '200px'
  } else if (isAutoExpanded.value) {
    textarea.style.height = '100px'
  } else {
    textarea.style.height = `${Math.min(64, Math.max(44, contentHeight))}px`
  }
}

const toggleExpanded = () => {
  isExpanded.value = !isExpanded.value
  nextTick(resizeTextarea)
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

const handleFileSelection = async event => {
  for (const file of Array.from(event.target.files || [])) await addAttachment(file)
  event.target.value = ''
}

const handlePaste = async event => {
  const imageFiles = Array.from(event.clipboardData?.items || [])
    .filter(item => item.type.startsWith('image/'))
    .map(item => item.getAsFile())
    .filter(Boolean)
  if (!imageFiles.length) return
  event.preventDefault()
  for (const [index, file] of imageFiles.entries()) {
    const extension = file.type.split('/')[1] || 'png'
    await addAttachment(new File([file], `pasted_image_${Date.now()}_${index + 1}.${extension}`, { type: file.type }))
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
  const initials = normalizedValue.split(/\s+/).filter(Boolean).map(word => word[0]).join('')
  return normalizedValue.includes(normalizedQuery) || initials.includes(normalizedQuery)
}

const selectedProject = computed(() => props.projects.find(project => String(project.id) === String(projectId.value)) || null)
const filteredPickerProjects = computed(() => {
  const rawQuery = projectSearch.value.trim()
  const queryToUse = rawQuery === selectedProject.value?.title ? '' : rawQuery
  if (!queryToUse) return props.projects
  const normalizedQuery = normalize(queryToUse)
  return props.projects.filter(project => {
    const title = normalize(project.title)
    const customer = normalize(project.customer?.name)
    return title.includes(normalizedQuery) || customer.includes(normalizedQuery)
  })
})

const syncProjectSearch = () => {
  if (!isProjectPickerOpen.value) projectSearch.value = selectedProject.value?.title || ''
}

const openProjectPicker = event => {
  isProjectPickerOpen.value = true
  nextTick(() => event?.target?.select())
}

const closeProjectPicker = () => {
  window.setTimeout(() => {
    isProjectPickerOpen.value = false
    syncProjectSearch()
  }, 120)
}

const dismissProjectPicker = () => {
  isProjectPickerOpen.value = false
  syncProjectSearch()
  projectPickerInputRef.value?.blur()
}

const toggleProjectPicker = () => {
  if (isProjectPickerOpen.value) {
    dismissProjectPicker()
  } else {
    projectPickerInputRef.value?.focus()
  }
}

const selectProjectFromPicker = project => {
  projectId.value = project.id
  projectSearch.value = project.title
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
  if (matches('all')) items.push({ type: 'all', id: 'all', title: '@all', token: 'all', subtitle: `Tất cả ${props.users.length} thành viên` })
  props.users.filter(user => matches(user.name) || matches(String(user.email || '').split('@')[0])).forEach(user => {
    items.push({ type: 'member', id: user.id, title: user.name, subtitle: 'Thành viên' })
  })
  props.groups.filter(group => matches(group.name) || matches(group.description)).forEach(group => {
    items.push({ type: 'group', id: group.id, title: group.name, subtitle: group.description || 'Nhóm nhắc tên' })
  })
  return items.slice(0, 10)
})

watch(() => props.projects, projects => {
  if (!projectModel.value && projects.length) projectModel.value = projects[0].id
}, { immediate: true })

watch([projectModel, () => props.projects], syncProjectSearch, { immediate: true })

watch(messageModel, value => {
  if (!String(value || '').trim()) isAutoExpanded.value = false
  nextTick(resizeTextarea)
})

const handleInput = event => {
  nextTick(resizeTextarea)
  const text = event.target.value
  const cursor = event.target.selectionStart ?? text.length
  const match = text.substring(0, cursor).match(/([@#])([^\s@#]*)$/)
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
  const text = message.value
  const cursor = textarea?.selectionStart ?? text.length
  const before = text.substring(0, cursor)
  const after = text.substring(cursor)
  const tokenMatch = before.match(/([@#])([^\s@#]*)$/)
  const prefix = tokenMatch ? before.substring(0, tokenMatch.index) : before
  let replacement = ''

  if (item.type === 'project') {
    projectId.value = item.id
    replacement = prefix
  } else {
    replacement = `${prefix}@${item.token || item.title} `
  }

  message.value = replacement + after
  showSuggestions.value = false
  nextTick(() => {
    textarea?.focus()
    textarea?.setSelectionRange(replacement.length, replacement.length)
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
