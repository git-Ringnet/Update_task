<template>
  <div class="relative inline-block" ref="containerRef">
    <!-- Single Current Health Dot Button -->
    <button 
      @click.stop="toggleOpen"
      type="button"
      class="w-7 h-7 rounded-full flex items-center justify-center transition-all duration-150 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 group"
      :title="`Trạng thái: ${healthText(modelValue)} - Click để thay đổi`"
    >
      <span 
        class="w-4 h-4 rounded-full transition-transform group-hover:scale-110 shadow-xs"
        :class="{
          'bg-amber-400 health-dot-yellow': modelValue === 'yellow',
          'bg-rose-500 health-dot-red': modelValue === 'red',
          'bg-emerald-500 health-dot-green': modelValue === 'green'
        }"
      ></span>
    </button>

    <!-- Popover selector for selecting 3 colors -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-x-1"
      enter-to-class="transform scale-100 opacity-100 translate-x-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-x-0"
      leave-to-class="transform scale-95 opacity-0 translate-x-1"
    >
      <div 
        v-if="isOpen"
        class="absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 bg-white rounded-full shadow-lg border border-gray-200 px-2 py-1 flex items-center gap-1.5 ring-1 ring-black/5"
      >
        <!-- Yellow option (Đang theo) -->
        <button
          @click.stop="selectHealth('yellow')"
          type="button"
          class="w-6 h-6 rounded-full flex items-center justify-center transition-transform hover:scale-125 focus:outline-none"
          :class="modelValue === 'yellow' ? 'ring-2 ring-amber-400 ring-offset-1' : 'opacity-70 hover:opacity-100'"
          title="🟡 Đang theo"
        >
          <span class="w-4 h-4 rounded-full bg-amber-400 health-dot-yellow"></span>
        </button>

        <!-- Red option (Không theo) -->
        <button
          @click.stop="selectHealth('red')"
          type="button"
          class="w-6 h-6 rounded-full flex items-center justify-center transition-transform hover:scale-125 focus:outline-none"
          :class="modelValue === 'red' ? 'ring-2 ring-rose-400 ring-offset-1' : 'opacity-70 hover:opacity-100'"
          title="🔴 Không theo"
        >
          <span class="w-4 h-4 rounded-full bg-rose-500 health-dot-red"></span>
        </button>

        <!-- Green option (Hoàn thành) -->
        <button
          @click.stop="selectHealth('green')"
          type="button"
          class="w-6 h-6 rounded-full flex items-center justify-center transition-transform hover:scale-125 focus:outline-none"
          :class="modelValue === 'green' ? 'ring-2 ring-emerald-400 ring-offset-1' : 'opacity-70 hover:opacity-100'"
          title="🟢 Hoàn thành"
        >
          <span class="w-4 h-4 rounded-full bg-emerald-500 health-dot-green"></span>
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
    default: 'yellow', // 'yellow', 'red', 'green'
  }
})

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

const healthText = (val) => {
  if (val === 'yellow') return '🟡 Đang theo'
  if (val === 'red') return '🔴 Không theo'
  if (val === 'green') return '🟢 Hoàn thành'
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
