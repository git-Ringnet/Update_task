<template>
  <div ref="pickerRef" class="space-y-3">
    <div class="relative">
      <div
        class="flex items-center gap-2 px-3.5 py-2.5 bg-white border rounded-xl transition-all"
        :class="isOpen ? 'border-emerald-500 ring-2 ring-emerald-500/15' : 'border-gray-200 hover:border-gray-300'"
      >
        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input
          ref="inputRef"
          v-model="query"
          type="text"
          :placeholder="placeholder"
          autocomplete="off"
          autocorrect="off"
          autocapitalize="off"
          spellcheck="false"
          class="w-full bg-transparent text-sm font-semibold text-gray-800 placeholder-gray-400 outline-none border-0 p-0"
          @focus="openSuggestions"
          @input="openSuggestions"
          @keydown.stop="handleKeydown"
        />
        <span class="text-[10px] font-black text-gray-400 whitespace-nowrap">{{ selectedUsers.length }} đã chọn</span>
      </div>

      <div
        v-if="isOpen"
        class="absolute left-0 right-0 z-[80] bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden ring-1 ring-black/5"
        :class="openUpward ? 'bottom-full mb-1.5' : 'top-full mt-1.5'"
      >
        <div class="px-3 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
          <span class="text-[10px] font-black uppercase tracking-wide text-gray-500">Gợi ý thành viên</span>
          <span class="text-[10px] font-bold text-emerald-600">↑ ↓ Enter</span>
        </div>
        <div v-if="filteredUsers.length" class="max-h-52 overflow-y-auto p-1.5">
          <button
            v-for="(user, index) in filteredUsers"
            :key="user.id"
            type="button"
            class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-left transition-colors"
            :class="index === highlightedIndex ? 'bg-emerald-50 text-emerald-900' : 'hover:bg-gray-50 text-gray-800'"
            @mousedown.prevent="selectUser(user)"
            @mouseenter="highlightedIndex = index"
          >
            <img :src="user.avatar || '/default-avatar.png'" class="w-8 h-8 rounded-full object-cover border border-gray-200" />
            <span class="min-w-0 flex-1">
              <span class="block text-xs font-extrabold truncate">{{ user.name }}</span>
              <span class="block text-[10px] text-gray-400 truncate">@{{ username(user) }}</span>
            </span>
            <i class="fa-solid fa-plus text-xs text-emerald-600"></i>
          </button>
        </div>
        <div v-else class="px-4 py-5 text-center text-xs font-semibold text-gray-400">
          {{ query.trim() ? `Không tìm thấy “${query.replace(/^@/, '')}”` : 'Tất cả thành viên đã được chọn' }}
        </div>
      </div>
    </div>

    <div v-if="selectedUsers.length" class="flex flex-wrap gap-2">
      <div
        v-for="user in selectedUsers"
        :key="user.id"
        class="group flex items-center gap-2 pl-1.5 pr-2 py-1.5 bg-emerald-50/70 border border-emerald-100 rounded-full"
      >
        <img :src="user.avatar || '/default-avatar.png'" class="w-7 h-7 rounded-full object-cover border-2 border-white shadow-sm" />
        <span class="text-xs font-extrabold text-gray-800 max-w-32 truncate">{{ user.name }}</span>
        <span v-if="isCreator(user.id)" class="text-[9px] font-black text-emerald-700">Người tạo</span>
        <button
          v-else
          type="button"
          class="w-5 h-5 rounded-full text-gray-400 hover:text-white hover:bg-rose-500 flex items-center justify-center transition-colors"
          :title="`Xóa ${user.name} khỏi dự án`"
          @click="removeUser(user.id)"
        >
          <i class="fa-solid fa-xmark text-[10px]"></i>
        </button>
      </div>
    </div>

    <div v-else class="flex items-center gap-3 px-3 py-3 border border-dashed border-gray-200 rounded-xl bg-gray-50/60">
      <div class="flex -space-x-2">
        <span v-for="index in 3" :key="index" class="w-7 h-7 rounded-full bg-white border-2 border-gray-100"></span>
      </div>
      <span class="text-xs font-semibold text-gray-400">Chưa có thành viên nào được thêm</span>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  creatorId: { type: [Number, String], default: null },
  placeholder: { type: String, default: 'Nhập tên, email hoặc tên đăng nhập...' },
})

const emit = defineEmits(['update:modelValue'])
const pickerRef = ref(null)
const inputRef = ref(null)
const query = ref('')
const isOpen = ref(false)
const highlightedIndex = ref(0)
const openUpward = ref(false)

const normalize = (value) => String(value || '')
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .toLowerCase()

const username = (user) => String(user.email || '').split('@')[0] || normalize(user.name).replace(/\s+/g, '')
const selectedIds = computed(() => props.modelValue.map(Number))
const selectedUsers = computed(() => props.users.filter(user => selectedIds.value.includes(Number(user.id))))
const filteredUsers = computed(() => {
  const term = normalize(query.value.replace(/^@/, '').trim())
  return props.users.filter(user => {
    if (selectedIds.value.includes(Number(user.id))) return false
    if (!term) return true
    const nameMatch = normalize(user.name).startsWith(term) ||
                      normalize(user.name).split(/\s+/).some(word => word.startsWith(term))
    const emailMatch = normalize(user.email).startsWith(term)
    const usernameMatch = normalize(username(user)).startsWith(term)
    return nameMatch || emailMatch || usernameMatch
  })
})

const isCreator = (userId) => props.creatorId && String(props.creatorId) === String(userId)
const updateIds = (ids) => emit('update:modelValue', [...new Set(ids.map(Number))])

let skipNextOpen = false

const openSuggestions = () => {
  if (skipNextOpen) {
    skipNextOpen = false
    return
  }
  isOpen.value = true
  highlightedIndex.value = 0
  nextTick(() => {
    const rect = inputRef.value?.getBoundingClientRect()
    openUpward.value = Boolean(rect && window.innerHeight - rect.bottom < 260 && rect.top > 260)
  })
}

const selectUser = (user) => {
  updateIds([...selectedIds.value, user.id])
  query.value = ''
  highlightedIndex.value = 0
  isOpen.value = false
  skipNextOpen = true
  nextTick(() => {
    inputRef.value?.focus()
    setTimeout(() => {
      skipNextOpen = false
    }, 100)
  })
}

const removeUser = (userId) => {
  if (isCreator(userId)) return
  updateIds(selectedIds.value.filter(id => Number(id) !== Number(userId)))
}

const handleKeydown = (event) => {
  if (event.key === 'ArrowDown') {
    event.preventDefault()
    if (!isOpen.value) {
      openSuggestions()
    } else if (filteredUsers.value.length) {
      highlightedIndex.value = (highlightedIndex.value + 1) % filteredUsers.value.length
    }
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    if (!isOpen.value) {
      openSuggestions()
    } else if (filteredUsers.value.length) {
      highlightedIndex.value = (highlightedIndex.value - 1 + filteredUsers.value.length) % filteredUsers.value.length
    }
  } else if (event.key === 'Enter') {
    if (isOpen.value) {
      event.preventDefault()
      const user = filteredUsers.value[highlightedIndex.value]
      if (user) selectUser(user)
    }
  } else if (event.key === 'Escape') {
    if (isOpen.value) {
      event.preventDefault()
      isOpen.value = false
    }
  } else if (event.key === 'Backspace' && !query.value && selectedUsers.value.length) {
    const removable = [...selectedUsers.value].reverse().find(user => !isCreator(user.id))
    if (removable) removeUser(removable.id)
  }
}

const handleOutsideClick = (event) => {
  if (pickerRef.value && !pickerRef.value.contains(event.target)) isOpen.value = false
}

onMounted(() => document.addEventListener('click', handleOutsideClick, true))
onUnmounted(() => document.removeEventListener('click', handleOutsideClick, true))

defineExpose({
  focus: () => {
    inputRef.value?.focus()
  }
})
</script>
