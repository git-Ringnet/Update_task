<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="close"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
      <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img src="/cactus-logo.png" alt="Logo Xương Rồng" class="w-8 h-8 object-contain" />
            <h3 class="text-lg font-bold text-gray-900" id="modal-title">{{ editProject ? 'Cập Nhật Dự Án' : 'Tạo Dự Án Mới' }}</h3>
          </div>
          <button @click="close" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <!-- Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tên dự án <span class="text-rose-500">*</span></label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="VD: Dự án camera cho Nội thất Hòa Phát"
              class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            />
          </div>

          <!-- Customer Dropdown Searchable -->
          <div class="relative" ref="dropdownRef">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Khách hàng <span class="text-rose-500">*</span></label>
            
            <!-- Autocomplete Input Box -->
            <div class="relative">
              <input
                v-model="searchQuery"
                @focus="isOpenDropdown = true"
                @input="isOpenDropdown = true"
                type="text"
                placeholder="-- Gõ để tìm kiếm khách hàng --"
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
              <div class="max-h-44 overflow-y-auto space-y-0.5 scrollbar-thin">
                <button
                  v-for="c in filteredCustomers"
                  :key="c.id"
                  type="button"
                  @click="selectCustomer(c)"
                  class="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold hover:bg-emerald-50 hover:text-emerald-950 transition-colors flex items-center justify-between cursor-pointer"
                >
                  <span class="truncate pr-4">{{ c.name }} {{ c.code ? `(${c.code})` : '' }}</span>
                  <i v-if="form.customer_id === c.id" class="fa-solid fa-check text-[10px] text-emerald-600 flex-shrink-0"></i>
                </button>

                <!-- No matches -->
                <div v-if="filteredCustomers.length === 0" class="py-3 px-2 text-center">
                  <div class="text-xs text-gray-400 font-medium">Không tìm thấy khách hàng nào.</div>
                  <button
                    type="button"
                    @click="openCustomerModal"
                    class="mt-2 text-emerald-700 hover:text-emerald-900 text-xs font-bold inline-flex items-center gap-1 cursor-pointer focus:outline-none border-b border-dashed border-emerald-300 hover:border-emerald-700 pb-0.5"
                  >
                    <Plus class="w-3.5 h-3.5" />
                    Thêm khách hàng mới
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Lead -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Lead hiện tại</label>
            <select
              v-model="form.lead_id"
              class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white"
            >
              <option :value="null">-- Chọn lead --</option>
              <option v-for="u in users" :key="u.id" :value="u.id">
                {{ u.name }}
              </option>
            </select>
          </div>

          <!-- Trạng thái Dự án (Health Color) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Trạng thái Dự án (Health)</label>
            <div class="grid grid-cols-3 gap-3">
              <!-- Đang theo (Vàng) -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.health === 'yellow' ? 'border-amber-400 bg-amber-50/80 font-bold text-amber-900 ring-2 ring-amber-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.health" value="yellow" class="hidden" />
                <span class="w-3.5 h-3.5 rounded-full bg-amber-400 health-dot-yellow flex-shrink-0"></span>
                <span class="text-xs">Đang theo</span>
              </label>

              <!-- Không theo (Đỏ) -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.health === 'red' ? 'border-rose-400 bg-rose-50/80 font-bold text-rose-900 ring-2 ring-rose-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.health" value="red" class="hidden" />
                <span class="w-3.5 h-3.5 rounded-full bg-rose-500 health-dot-red flex-shrink-0"></span>
                <span class="text-xs">Bỏ theo</span>
              </label>

              <!-- Hoàn thành (Xanh) -->
              <label 
                class="flex items-center justify-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.health === 'green' ? 'border-emerald-400 bg-emerald-50/80 font-bold text-emerald-900 ring-2 ring-emerald-400/20' : 'border-gray-200 hover:bg-gray-50 text-gray-700'"
              >
                <input type="radio" v-model="form.health" value="green" class="hidden" />
                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 health-dot-green flex-shrink-0"></span>
                <span class="text-xs">Hoàn thành</span>
              </label>
            </div>
          </div>

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
              :disabled="isSubmitting || !form.customer_id"
              class="px-5 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
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
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { X, Plus } from 'lucide-vue-next'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const props = defineProps({
  isOpen: Boolean,
  customers: Array,
  users: Array,
  editProject: Object,
})

const emit = defineEmits(['close', 'submit', 'customer-created'])

// Project Form States
const isSubmitting = ref(false)
const form = reactive({
  title: '',
  customer_id: '',
  lead_id: null,
  health: 'yellow',
  is_pinned: false,
})

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    if (props.editProject) {
      form.title = props.editProject.title
      form.customer_id = props.editProject.customer_id
      form.lead_id = props.editProject.lead_id
      form.health = props.editProject.health
      const c = props.customers.find(item => item.id === form.customer_id)
      searchQuery.value = c ? `${c.name} ${c.code ? `(${c.code})` : ''}` : ''
    } else {
      form.title = ''
      form.customer_id = ''
      form.lead_id = null
      form.health = 'yellow'
      searchQuery.value = ''
    }
  }
})

// Searchable Dropdown States
const isOpenDropdown = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)

// Filter customers by query string
const filteredCustomers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return props.customers

  const c = props.customers.find(item => item.id === form.customer_id)
  const currentSelectedName = c ? `${c.name} ${c.code ? `(${c.code})` : ''}`.toLowerCase() : ''
  
  if (q === currentSelectedName) {
    return props.customers
  }

  return props.customers.filter(c => {
    return c.name.toLowerCase().includes(q) || (c.code && c.code.toLowerCase().includes(q))
  })
})

const selectCustomer = (c) => {
  form.customer_id = c.id
  searchQuery.value = `${c.name} ${c.code ? `(${c.code})` : ''}`
  isOpenDropdown.value = false
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
    const c = props.customers.find(item => item.id === form.customer_id)
    searchQuery.value = c ? `${c.name} ${c.code ? `(${c.code})` : ''}` : ''
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
})

const close = () => {
  emit('close')
}

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    emit('submit', { ...form })
    form.title = ''
    form.customer_id = ''
    close()
  } finally {
    isSubmitting.value = false
  }
}
</script>
