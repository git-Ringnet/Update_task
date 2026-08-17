<template>
  <!-- Toggle Segmented Control Mode -->
  <div v-if="isToggle" class="flex items-center bg-gray-200/60 p-0.5 rounded-full border border-gray-200/80 shadow-3xs" @click.stop>
    <!-- White / Neutral Option -->
    <button
      type="button"
      @click="selectHealth('yellow')"
      class="w-7 h-7 rounded-full flex items-center justify-center transition-all cursor-pointer select-none focus:outline-none"
      :class="modelValue !== 'red' ? 'bg-gray-400 text-white shadow-3xs' : 'text-gray-400 hover:text-gray-600'"
      title="Mặc định"
    >
      <i class="fa-solid fa-face-meh text-[17px]"></i>
    </button>

    <!-- Red / Angry Option -->
    <button
      type="button"
      @click="selectHealth('red')"
      class="w-7 h-7 rounded-full flex items-center justify-center transition-all cursor-pointer select-none focus:outline-none"
      :class="modelValue === 'red' ? 'bg-rose-500 text-white shadow-3xs' : 'text-gray-400 hover:text-rose-500'"
      title="Needs Care"
    >
      <i class="fa-solid fa-face-frown text-[17px]"></i>
    </button>
  </div>

  <!-- Standard Popover Mode -->
  <div v-else class="relative inline-block text-left" ref="containerRef">
    <!-- Standard Health Badge Button: Icon and optional Label -->
    <button 
      @click.stop="toggleOpen"
      type="button"
      class="rounded-full flex items-center justify-center transition-all duration-150 hover:scale-105 focus:outline-none cursor-pointer gap-2 select-none"
      :class="showLabel ? 'px-3 py-1 bg-gray-50 border border-gray-200' : 'w-8 h-8'"
      :title="`Trạng thái: ${healthText(modelValue)} - Click để thay đổi`"
    >
      <i v-if="modelValue === 'green'" class="fa-solid fa-face-smile text-[22px] text-[#45A246]"></i>
      <i v-else-if="modelValue === 'yellow' || modelValue === 'white'" class="fa-solid fa-face-meh text-[22px] text-gray-400"></i>
      <i v-else-if="modelValue === 'red'" class="fa-solid fa-face-frown text-[22px] text-rose-500"></i>
      <span v-if="showLabel" class="text-xs font-bold" :class="labelColor(modelValue)">
        {{ healthLabel(modelValue) }}
      </span>
    </button>

    <!-- Popover selector for selecting 3 statuses: compact horizontal pill above the button -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-y-1"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 translate-y-1"
    >
      <div 
        v-if="isOpen"
        class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 z-50 bg-white rounded-full shadow-lg border border-gray-200 p-1 flex items-center gap-1.5 ring-1 ring-black/5"
      >
        <!-- White option (Default) -->
        <button
          @click.stop="selectHealth('yellow')"
          type="button"
          class="w-7.5 h-7.5 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer"
          title="Mặc định"
        >
          <i class="fa-solid fa-face-meh text-[19px] text-gray-400"></i>
        </button>

        <!-- Red option (Needs Care) -->
        <button
          @click.stop="selectHealth('red')"
          type="button"
          class="w-7.5 h-7.5 rounded-full hover:bg-rose-50 flex items-center justify-center transition-colors cursor-pointer"
          title="Needs Care"
        >
          <i class="fa-solid fa-face-frown text-[19px] text-rose-500"></i>
        </button>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: 'yellow', // 'yellow' (white/default), 'red', 'green'
  },
  showLabel: {
    type: Boolean,
    default: false
  },
  isToggle: {
    type: Boolean,
    default: false
  }
})

const labelColor = (val) => {
  if (val === 'green') return 'text-gray-400'
  if (val === 'yellow' || val === 'white') return 'text-gray-400'
  if (val === 'red') return 'text-rose-600'
  return 'text-gray-500'
}

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const containerRef = ref(null)

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const selectHealth = (color) => {
  emit('update:modelValue', color)
  emit('change', color)
  isOpen.value = false
}

const healthLabel = (val) => {
  if (val === 'green') return 'Mặc định'
  if (val === 'yellow' || val === 'white') return 'Mặc định'
  if (val === 'red') return 'Needs Care'
  return val
}

const healthText = (val) => {
  if (val === 'green') return '⚪ Mặc định'
  if (val === 'yellow' || val === 'white') return '⚪ Mặc định'
  if (val === 'red') return '🔴 Needs Care'
  return val
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

