<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="close"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
      <div class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
        
        <!-- Modal Header -->
        <div class="rounded-t-2xl bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img src="/cactus-logo-square.png" alt="Logo Xương Rồng" class="w-8 h-8 object-contain" />
            <h3 class="text-lg font-bold text-gray-900" id="modal-title">{{ editProject ? 'Cập Nhật Dự Án' : 'Tạo Dự Án Mới' }}</h3>
          </div>
          <button @click="close" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4 rounded-b-2xl bg-white">
          <!-- Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tên dự án <span class="text-rose-500">*</span></label>
            <input
              ref="titleInputRef"
              v-model="form.title"
              type="text"
              required
              placeholder="VD: Dự án camera cho Nội thất Hòa Phát"
              class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              @keydown="handleTitleKeydown"
            />
          </div>

          <!-- Customer Dropdown Searchable -->
          <div class="relative" ref="dropdownRef">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Khách hàng <span class="text-rose-500">*</span></label>
            
            <!-- Autocomplete Input Box -->
            <div class="relative">
              <input
                ref="customerInputRef"
                v-model="searchQuery"
                @focus="handleInputFocus"
                @input="isOpenDropdown = true; highlightedIndex = 0"
                @keydown="handleCustomerKeydown"
                type="text"
                placeholder="-- Gõ để tìm kiếm khách hàng (Enter để tạo mới) --"
                class="w-full px-3.5 py-2 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white"
                :class="isOpenDropdown ? 'ring-2 ring-emerald-500/20 border-emerald-500' : ''"
              />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer pointer-events-none">
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="isOpenDropdown ? 'rotate-180' : ''"></i>
              </span>
            </div>

            <!-- Dropdown Options Panel -->
            <div
              v-if="isOpenDropdown"
              class="absolute left-0 right-0 mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg z-50 p-2.5 space-y-2 animate-fade-in-up"
            >
              <!-- Options List -->
              <div class="max-h-44 overflow-y-auto space-y-0.5 scrollbar-thin customer-dropdown-list">
                <button
                  v-for="(c, index) in filteredCustomers"
                  :key="c.id"
                  type="button"
                  @click="selectCustomer(c)"
                  @mouseenter="highlightedIndex = index"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg text-xs font-semibold transition-colors flex items-center justify-between cursor-pointer',
                    highlightedIndex === index ? 'bg-emerald-100 text-emerald-950 highlighted-customer' : 'hover:bg-emerald-50 hover:text-emerald-950'
                  ]"
                >
                  <span class="truncate pr-4">{{ c.name }} {{ c.code ? `(${c.code})` : '' }}</span>
                  <i v-if="form.customer_id === c.id" class="fa-solid fa-check text-[10px] text-emerald-600 flex-shrink-0"></i>
                </button>

                <!-- No matches - Quick create hint -->
                <div v-if="filteredCustomers.length === 0" class="py-3 px-2 text-center">
                  <div class="text-xs text-gray-400 font-medium mb-1">Không tìm thấy "{{ searchQuery }}"</div>
                  <div class="text-[11px] text-emerald-700 font-bold">
                    <kbd class="px-1.5 py-0.5 bg-emerald-100 rounded text-[10px]">Enter</kbd> để tạo khách hàng mới
                  </div>
                  <button
                    type="button"
                    @click="openCustomerModal"
                    class="mt-2 text-gray-600 hover:text-gray-900 text-xs font-bold inline-flex items-center gap-1 cursor-pointer focus:outline-none border-b border-dashed border-gray-300 hover:border-gray-700 pb-0.5"
                  >
                    <Plus class="w-3.5 h-3.5" />
                    Hoặc thêm thủ công
                  </button>
                </div>
              </div>
            </div>
          </div>


           <!-- Health - Chỉ icon (Chỉ hiện khi chỉnh sửa) -->
          <div v-if="editProject">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Health</label>
            <div class="grid grid-cols-2 gap-3">
              <!-- Yellow (Neutral) -->
              <label 
                class="flex items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.health === 'yellow' ? 'border-gray-400 bg-white ring-2 ring-gray-400/20 scale-105 shadow-sm' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input type="radio" v-model="form.health" value="yellow" class="hidden" />
                <i class="fa-solid fa-face-meh text-3xl text-gray-400"></i>
              </label>

              <!-- Red (Sad) -->
              <label 
                class="flex items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.health === 'red' ? 'border-rose-400 bg-rose-50/80 ring-2 ring-rose-400/20 scale-105' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input type="radio" v-model="form.health" value="red" class="hidden" />
                <i class="fa-solid fa-face-frown text-3xl text-rose-500"></i>
              </label>
            </div>
          </div>

          <!-- Trạng thái Dự án (Tracking Status) - Không có màu (Chỉ hiện khi chỉnh sửa) -->
          <div v-if="editProject">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Trạng thái</label>
            <div class="grid grid-cols-3 gap-3">
              <!-- Đang theo -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.tracking_status === 'following' ? 'border-gray-400 bg-gray-100 font-bold text-gray-900 ring-2 ring-gray-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.tracking_status" value="following" class="hidden" />
                <span class="text-xs">Đang theo</span>
              </label>

              <!-- Bỏ theo -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.tracking_status === 'not_following' ? 'border-gray-400 bg-gray-100 font-bold text-gray-900 ring-2 ring-gray-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.tracking_status" value="not_following" class="hidden" />
                <span class="text-xs">Bỏ theo</span>
              </label>

              <!-- Hoàn thành -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.tracking_status === 'completed' ? 'border-gray-400 bg-gray-100 font-bold text-gray-900 ring-2 ring-gray-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.tracking_status" value="completed" class="hidden" />
                <span class="text-xs">Hoàn thành</span>
              </label>
            </div>
          </div>

          <!-- Thành viên dự án -->
          <div>
          <div class="flex items-center justify-between gap-3 mb-2">
              <label class="block text-sm font-semibold text-gray-700">Thành viên dự án</label>
              <span class="text-[10px] font-bold text-gray-400">Có thể thêm/xóa sau</span>
            </div>
            <MemberPicker
              ref="memberPickerRef"
              v-model="form.member_ids"
              :users="users"
              :groups="mentionGroups"
              :creator-id="effectiveCreatorId"
              placeholder="@+tên thành viên muốn thêm"
            />
            <p class="mt-2 text-[11px] leading-relaxed text-gray-400 font-medium">
              Thành viên được thêm sẽ xem được dự án và toàn bộ hoạt động liên quan.
            </p>
          </div>

          <label
            v-if="authStore.user?.is_system_admin"
            class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 cursor-pointer"
          >
            <input v-model="form.hidden_from_admin" type="checkbox" class="mt-0.5 h-4 w-4 accent-amber-600" />
            <span>
              <span class="block text-sm font-semibold text-amber-950">Ẩn dự án với Admin</span>
              <span class="block mt-0.5 text-xs text-amber-800">Admin thường sẽ không thấy dự án này và không nhận thông báo liên quan.</span>
            </span>
          </label>

          <!-- Modal Footer -->
          <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              :disabled="!isFormValid || isSubmitting"
              :class="[
                'px-5 py-2 text-sm font-medium text-white rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer',
                isFormValid && !isSubmitting ? 'bg-[#45A246] hover:bg-[#3a903b]' : 'bg-gray-300 cursor-not-allowed'
              ]"
            >
              <Plus class="w-4 h-4" v-if="!editProject" />
              <span>{{ editProject ? 'Lưu thay đổi' : 'Tạo dự án' }}</span>
            </button>
          </div>
        </form>

      </div>
    </div>

    <!-- Create Customer Modal (Nested Modal) -->
    <div v-if="isCustomerModalOpen" class="fixed inset-0 z-60 overflow-y-auto" aria-labelledby="customer-modal-title" role="dialog" aria-modal="true">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="isCustomerModalOpen = false"></div>

      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 z-10 animate-fade-in-up">
          
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-900" id="customer-modal-title">Thêm Mối Quan Hệ Mới</h3>
            <button type="button" @click="isCustomerModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Form Body -->
          <form @submit.prevent="handleCreateCustomer" class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên đối tác / Khách hàng *</label>
              <input
                v-model="customerForm.name"
                required
                type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
                placeholder="VD: Dell Technologies"
              />
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Phân loại *</label>
                <select
                  v-model="customerForm.type"
                  required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:border-emerald-500"
                >
                  <option value="customer">Khách hàng</option>
                  <option value="vendor">Vendor / Partner</option>
                  <option value="internal">Nội bộ</option>
                  <option value="other">Khác</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tình trạng *</label>
                <select
                  v-model="customerForm.status"
                  required
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:border-emerald-500"
                >
                  <option value="green">🟢 Xanh (Đang tốt)</option>
                  <option value="yellow">🟡 Vàng (Thiếu quan tâm)</option>
                  <option value="red">🔴 Đỏ (Bỏ mặc)</option>
                </select>
              </div>
            </div>


            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
              <button
                type="button"
                @click="isCustomerModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 font-semibold hover:text-gray-900 cursor-pointer"
              >
                Hủy
              </button>
              <button
                type="submit"
                :disabled="isCreatingCustomer"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <template v-if="isCreatingCustomer">
                  <span class="inline-block w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                  <span>Đang tạo...</span>
                </template>
                <template v-else>
                  <span>Tạo ngay</span>
                </template>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { X, Plus } from 'lucide-vue-next'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import MemberPicker from './MemberPicker.vue'

const authStore = useAuthStore()

const props = defineProps({
  isOpen: Boolean,
  customers: Array,
  users: Array,
  editProject: Object,
})

const emit = defineEmits(['close', 'submit', 'customer-created'])

// Refs for keyboard navigation
const titleInputRef = ref(null)
const customerInputRef = ref(null)
const memberPickerRef = ref(null)
const highlightedIndex = ref(-1)

// Project Form States
const isSubmitting = ref(false)
const form = reactive({
  title: '',
  customer_id: '',
  lead_id: null,
  health: 'yellow',
  tracking_status: 'following',
  is_pinned: false,
  hidden_from_admin: false,
  member_ids: [],
})
const mentionGroups = ref([])

// Global keydown handler for the modal
const handleModalKeydown = (event) => {
  // If Enter is pressed and form is valid, submit
  if (event.key === 'Enter' && isFormValid.value && !isSubmitting.value) {
    // Don't submit if dropdown is open or if we're in customer input and dropdown should handle it
    if (isOpenDropdown.value) return
    
    // Don't submit if focus is on title input (let it focus customer instead)
    if (document.activeElement === titleInputRef.value) return
    
    // Submit the form
    event.preventDefault()
    handleSubmit()
  }
}

watch(() => props.isOpen, async (newVal) => {
  if (newVal) {
    console.log('ProjectModal opened, users:', props.users, 'current user:', authStore.user)
    
    if (props.editProject) {
      form.title = props.editProject.title
      form.customer_id = props.editProject.customer_id
      form.lead_id = props.editProject.lead_id
      form.health = props.editProject.health
      form.tracking_status = props.editProject.tracking_status || 'following'
      form.hidden_from_admin = Boolean(props.editProject.hidden_from_admin)
      const c = props.customers.find(item => item.id === form.customer_id)
      searchQuery.value = c ? `${c.name} ${c.code ? `(${c.code})` : ''}` : ''

      form.member_ids = props.editProject.members ? props.editProject.members.map(m => m.id) : []
      const creatorId = props.editProject.creator_id || props.editProject.created_by
      const creatorCanBeMember = props.users.some(user => String(user.id) === String(creatorId))
      if (creatorId && creatorCanBeMember && !form.member_ids.includes(creatorId)) {
        form.member_ids.push(creatorId)
      }
    } else {
      form.title = ''
      form.customer_id = ''
      // Default lead to current user
      form.lead_id = authStore.user?.is_admin ? null : (authStore.user?.id || null)
      console.log('Setting default lead_id to:', form.lead_id)
      form.health = 'yellow'
      form.tracking_status = 'following'
      form.hidden_from_admin = false
      searchQuery.value = ''
      form.member_ids = authStore.user?.id && !authStore.user?.is_admin ? [authStore.user.id] : []
    }
    
    // Auto-focus title input when modal opens
    await nextTick()
    titleInputRef.value?.focus()
    highlightedIndex.value = -1
    
    // Add global keydown listener
    window.addEventListener('keydown', handleModalKeydown)
  } else {
    // Remove global keydown listener when modal closes
    window.removeEventListener('keydown', handleModalKeydown)
  }
})

// Searchable Dropdown States
const isOpenDropdown = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)

const normalizeCustomerSearch = (value) => String(value || '')
  .toLocaleLowerCase('vi-VN')
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .replace(/đ/g, 'd')
  .replace(/\s+/g, ' ')
  .trim()

// Filter customers by query string
const filteredCustomers = computed(() => {
  const q = normalizeCustomerSearch(searchQuery.value)
  if (!q) return props.customers

  const c = props.customers.find(item => item.id === form.customer_id)
  const currentSelectedName = c ? normalizeCustomerSearch(`${c.name} ${c.code ? `(${c.code})` : ''}`) : ''
  
  if (q === currentSelectedName) {
    return props.customers
  }

  return props.customers.filter(c => {
    const combined = `${c.name} ${c.code ? `(${c.code})` : ''}`
    return normalizeCustomerSearch(combined).includes(q)
  })
})

const handleInputFocus = (event) => {
  isOpenDropdown.value = true
  event.target.select()
}

const selectCustomer = (c) => {
  form.customer_id = c.id
  searchQuery.value = `${c.name} ${c.code ? `(${c.code})` : ''}`
  isOpenDropdown.value = false
  highlightedIndex.value = -1
  
  nextTick(() => {
    memberPickerRef.value?.focus()
  })
}

// Keyboard navigation for customer dropdown
const handleCustomerKeydown = (event) => {
  if (!isOpenDropdown.value && event.key !== 'Enter') {
    isOpenDropdown.value = true
    return
  }

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    if (filteredCustomers.value.length > 0) {
      highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredCustomers.value.length - 1)
      scrollToHighlighted()
    }
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    if (filteredCustomers.value.length > 0) {
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
      scrollToHighlighted()
    }
  } else if (event.key === 'Enter') {
    event.preventDefault()
    
    // If dropdown is closed and customer is already selected, submit the form
    if (!isOpenDropdown.value && form.customer_id && form.title.trim()) {
      handleSubmit()
      return
    }
    
    // If there's a highlighted customer, select it
    if (highlightedIndex.value >= 0 && filteredCustomers.value[highlightedIndex.value]) {
      selectCustomer(filteredCustomers.value[highlightedIndex.value])
    }
    // If no match found and user typed something, create new customer
    else if (filteredCustomers.value.length === 0 && searchQuery.value.trim()) {
      quickCreateCustomer()
    }
    // If there's exactly one match, select it
    else if (filteredCustomers.value.length === 1) {
      selectCustomer(filteredCustomers.value[0])
    }
  } else if (event.key === 'Escape') {
    event.preventDefault()
    isOpenDropdown.value = false
    highlightedIndex.value = -1
  }
}

const scrollToHighlighted = () => {
  nextTick(() => {
    const dropdown = document.querySelector('.customer-dropdown-list')
    const highlighted = dropdown?.querySelector('.highlighted-customer')
    if (highlighted && dropdown) {
      const dropdownRect = dropdown.getBoundingClientRect()
      const highlightedRect = highlighted.getBoundingClientRect()
      
      if (highlightedRect.bottom > dropdownRect.bottom) {
        highlighted.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
      } else if (highlightedRect.top < dropdownRect.top) {
        highlighted.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
      }
    }
  })
}

// Quick create customer from search query
const quickCreateCustomer = async () => {
  const name = searchQuery.value.trim()
  if (!name) return
  
  isCreatingCustomer.value = true
  try {
    const res = await axios.post('/api/customers', {
      name: name,
      type: 'customer',
      status: 'green',
      user_id: authStore.user?.id
    })
    const newCust = res.data
    
    // Automatically select the new customer
    form.customer_id = newCust.id
    searchQuery.value = `${newCust.name} ${newCust.code ? `(${newCust.code})` : ''}`
    isOpenDropdown.value = false
    
    // Blur customer input after quick creation so next Enter will submit form
    customerInputRef.value?.blur()
    
    // Emit notification to parent to reload lookup data
    emit('customer-created', newCust)
  } catch (err) {
    console.error('Failed to create customer:', err)
  } finally {
    isCreatingCustomer.value = false
  }
}

// Handle Enter key on title input
const handleTitleKeydown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    customerInputRef.value?.focus()
  }
}

// Inline Customer modal states
const isCustomerModalOpen = ref(false)
const isCreatingCustomer = ref(false)
const customerForm = reactive({
  name: '',
  code: '',
  type: 'customer',
  status: 'green',
})

const openCustomerModal = () => {
  isCustomerModalOpen.value = true
  isOpenDropdown.value = false
}

const handleCreateCustomer = async () => {
  isCreatingCustomer.value = true
  try {
    const res = await axios.post('/api/customers', {
      ...customerForm,
      user_id: authStore.user?.id
    })
    const newCust = res.data
    
    // Reset forms
    customerForm.name = ''
    customerForm.code = ''
    isCustomerModalOpen.value = false
    
    // Automatically select the new customer in our form
    form.customer_id = newCust.id
    searchQuery.value = `${newCust.name} ${newCust.code ? `(${newCust.code})` : ''}`
    
    // Emit notification to parent to reload lookup data
    emit('customer-created', newCust)
  } catch (err) {
    console.error('Failed to create customer inline:', err)
  } finally {
    isCreatingCustomer.value = false
  }
}

// Click outside handler to close search dropdown
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpenDropdown.value = false
    highlightedIndex.value = -1
    const c = props.customers.find(item => item.id === form.customer_id)
    searchQuery.value = c ? `${c.name} ${c.code ? `(${c.code})` : ''}` : ''
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside)
  axios.get('/api/mention-groups').then(res => { mentionGroups.value = res.data || [] }).catch(() => {})
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
  window.removeEventListener('keydown', handleModalKeydown)
})

const close = () => {
  emit('close')
}

const handleSubmit = async () => {
  if (!form.title || !form.customer_id) return
  
  isSubmitting.value = true
  try {
    const payload = { ...form }
    if (!authStore.user?.is_system_admin) {
      delete payload.hidden_from_admin
    }
    emit('submit', payload)
    form.title = ''
    form.customer_id = ''
    close()
  } finally {
    isSubmitting.value = false
  }
}

// Check if form is valid for submission
const isFormValid = computed(() => {
  return form.title.trim() && form.customer_id
})

const effectiveCreatorId = computed(() => {
  return props.editProject?.creator_id || props.editProject?.created_by || authStore.user?.id || null
})

const isCreator = (userId) => {
  if (props.editProject) {
    return props.editProject.creator_id && String(props.editProject.creator_id) === String(userId)
  }
  return authStore.user?.id && String(authStore.user.id) === String(userId)
}
</script>
