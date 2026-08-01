import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConfirmStore = defineStore('confirm', () => {
  const isOpen = ref(false)
  const title = ref('Xác nhận')
  const message = ref('')
  
  let resolveCallback = null

  const show = (options = {}) => {
    isOpen.value = true
    title.value = options.title || 'Xác nhận'
    message.value = options.message || 'Bạn có chắc chắn muốn thực hiện hành động này?'
    
    return new Promise((resolve) => {
      resolveCallback = resolve
    })
  }

  const confirm = () => {
    isOpen.value = false
    if (resolveCallback) resolveCallback(true)
  }

  const cancel = () => {
    isOpen.value = false
    if (resolveCallback) resolveCallback(false)
  }

  return {
    isOpen,
    title,
    message,
    show,
    confirm,
    cancel
  }
})
