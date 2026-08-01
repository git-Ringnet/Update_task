<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-24">
    <div>
      <!-- Navbar Component -->
      <Navbar @search="handleSearch" />

      <!-- Main Container -->
      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Page Title -->
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-6 font-heading">Dự án</h1>

        <!-- Status Filter Tabs & Action Button -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
          
          <!-- Filter Tabs -->
          <div class="flex items-center gap-3">
            <!-- Đang theo -->
            <button
              @click="setTab('following')"
              type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="projectStore.activeStatus === 'following'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'"
            >
              <span>Đang theo</span>
              <span 
                class="px-2 py-0.5 rounded-md text-xs font-bold transition-all"
                :class="projectStore.activeStatus === 'following' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'"
              >
                <template v-if="projectStore.isLoading && !projectStore.counts.following">
                  <span class="inline-block w-3 h-3 bg-white/40 rounded-full animate-pulse"></span>
                </template>
                <template v-else>
                  {{ projectStore.counts.following }}
                </template>
              </span>
            </button>

            <!-- Không theo -->
            <button
              @click="setTab('not_following')"
              type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="projectStore.activeStatus === 'not_following'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'"
            >
              <span>Không theo</span>
              <span 
                class="px-2 py-0.5 rounded-md text-xs font-bold transition-all"
                :class="projectStore.activeStatus === 'not_following' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'"
              >
                <template v-if="projectStore.isLoading && !projectStore.counts.not_following">
                  <span class="inline-block w-3 h-3 bg-white/40 rounded-full animate-pulse"></span>
                </template>
                <template v-else>
                  {{ projectStore.counts.not_following }}
                </template>
              </span>
            </button>

            <!-- Hoàn thành -->
            <button
              @click="setTab('completed')"
              type="button"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-150 flex items-center gap-2"
              :class="projectStore.activeStatus === 'completed'
                ? 'bg-emerald-100 text-emerald-800 shadow-2xs font-semibold'
                : 'bg-gray-100/80 text-gray-600 hover:bg-gray-200/70'"
            >
              <span>Hoàn thành</span>
              <span 
                class="px-2 py-0.5 rounded-md text-xs font-bold transition-all"
                :class="projectStore.activeStatus === 'completed' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700'"
              >
                <template v-if="projectStore.isLoading && !projectStore.counts.completed">
                  <span class="inline-block w-3 h-3 bg-white/40 rounded-full animate-pulse"></span>
                </template>
                <template v-else>
                  {{ projectStore.counts.completed }}
                </template>
              </span>
            </button>
          </div>

          <!-- Add Project Button -->
          <button
            @click="isModalOpen = true"
            type="button"
            class="px-5 py-2.5 bg-[#2d8a39] hover:bg-[#236e2d] text-white font-semibold text-sm rounded-xl shadow-xs transition-colors flex items-center gap-2"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Thêm dự án</span>
          </button>
        </div>

        <!-- Table Card Container -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs select-none">
          <div class="overflow-visible">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                  <th scope="col" class="py-3.5 px-6 text-center select-none w-12">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4.5 h-4.5"
                    />
                  </th>
                  <th scope="col" class="py-3.5 px-6">DỰ ÁN</th>
                  <th scope="col" class="py-3.5 px-6 text-center">HEALTH</th>
                  <th scope="col" class="py-3.5 px-6">LEAD HIỆN TẠI</th>
                  <th scope="col" class="py-3.5 px-6">CẬP NHẬT</th>
                  <th scope="col" class="py-3.5 px-4 text-center">
                    <i class="fa-solid fa-thumbtack text-gray-400"></i>
                  </th>
                  <th scope="col" class="py-3.5 px-4 text-center">THAO TÁC</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Skeleton Loading Rows -->
                <template v-if="projectStore.isLoading">
                  <tr v-for="i in 5" :key="'skeleton-' + i" class="animate-pulse">
                    <td class="py-4 px-6 text-center align-middle">
                      <div class="w-4 h-4 bg-gray-200 rounded-md mx-auto"></div>
                    </td>
                    <td class="py-4 px-6">
                      <div class="h-4 bg-gray-200/80 rounded-md w-3/4 mb-2"></div>
                      <div class="h-3 bg-gray-100/90 rounded-md w-1/2"></div>
                    </td>
                    <td class="py-4 px-6 text-center align-middle">
                      <div class="w-4 h-4 bg-gray-200/80 rounded-full mx-auto"></div>
                    </td>
                    <td class="py-4 px-6 align-middle">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-gray-200/80 rounded-full"></div>
                        <div class="h-3.5 bg-gray-200/80 rounded-md w-16"></div>
                      </div>
                    </td>
                    <td class="py-4 px-6 align-middle">
                      <div class="h-3.5 bg-gray-200/80 rounded-md w-24"></div>
                    </td>
                    <td class="py-4 px-4 text-center align-middle">
                      <div class="w-5 h-5 bg-gray-100/80 rounded-md mx-auto"></div>
                    </td>
                    <td class="py-4 px-4 text-center align-middle">
                      <div class="w-7 h-5 bg-gray-100/80 rounded-md mx-auto"></div>
                    </td>
                  </tr>
                </template>

                <!-- Empty state -->
                <tr v-else-if="projectStore.projects.length === 0">
                  <td colspan="7" class="py-12 text-center text-gray-400">
                    Không tìm thấy dự án nào trong mục này.
                  </td>
                </tr>

                <!-- Project List Rows with Smooth Staggered Fade In -->
                <tr
                  v-for="(project, index) in displayedProjects"
                  :key="project.id"
                  class="hover:bg-emerald-50/20 transition-colors group animate-fade-in-up cursor-pointer"
                  :class="{ 
                    'bg-emerald-50/30 hover:bg-emerald-50/50': selectedProjectIds.includes(project.id),
                    'relative z-30': openActionMenuId === project.id || openLeadMenuId === project.id
                  }"
                  :style="{ animationDelay: `${index * 45}ms` }"
                  @mousedown="handleMouseDown($event, project)"
                  @mouseenter="handleMouseEnter(project)"
                  @click="goToProjectDetail(project.id, $event)"
                >
                  <!-- Checkbox Selection -->
                  <td class="py-4 px-6 text-center align-middle select-none w-12">
                    <input
                      type="checkbox"
                      :checked="selectedProjectIds.includes(project.id)"
                      @change="toggleProjectSelection(project.id)"
                      @click.stop
                      @mousedown.stop
                      class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4.5 h-4.5"
                    />
                  </td>

                  <!-- Title & Customer -->
                  <td class="py-4 px-6 max-w-[250px] md:max-w-md">
                    <div class="block group-hover:text-emerald-700">
                      <div class="font-bold text-gray-900 text-base leading-snug font-heading break-words">
                        {{ project.title }}
                      </div>
                      <div class="text-xs text-gray-500 font-medium mt-0.5 break-words">
                        {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                      </div>
                    </div>
                  </td>

                  <!-- Health Status Selector (1 Dot default, click opens 3 dots) -->
                  <td class="py-4 px-6 text-center align-middle">
                    <HealthStatusSelector
                      :model-value="project.health"
                      @change="(newColor) => handleHealthChange(project.id, newColor)"
                    />
                  </td>

                  <!-- Lead hiện tại -->
                  <td class="py-4 px-6 align-middle relative">
                    <div
                      @mousedown.stop
                      @click.stop="toggleLeadMenu(project.id)"
                      class="inline-flex items-center gap-2 px-2.5 py-1.5 hover:bg-emerald-50/70 border border-transparent hover:border-emerald-200/50 rounded-xl transition-all cursor-pointer select-none group"
                    >
                      <template v-if="project.lead">
                        <img
                          :src="project.lead.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                          :alt="project.lead.name"
                          class="w-6.5 h-6.5 rounded-full object-cover border border-emerald-200 shadow-3xs"
                        />
                        <span class="font-bold text-gray-800 text-sm group-hover:text-emerald-950 transition-colors border-b border-dashed border-gray-300 group-hover:border-emerald-600 pb-0.5">
                          {{ project.lead.name }}
                        </span>
                      </template>
                      <span v-else class="text-xs text-gray-400 font-semibold italic border-b border-dashed border-gray-300 pb-0.5">Chưa giao</span>
                    </div>

                    <!-- Lead Dropdown Selection -->
                    <div
                      v-if="openLeadMenuId === project.id"
                      @mousedown.stop @click.stop
                      class="absolute left-6 top-full mt-1.5 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5 max-h-48 overflow-y-auto"
                    >
                      <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider select-none border-b border-gray-100 mb-1">
                        Chuyển lead dự án
                      </div>
                      
                      <!-- Unassigned Option -->
                      <button
                        @click.stop="handleUpdateLead(project.id, null)"
                        type="button"
                        class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-500 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer"
                      >
                        <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px]">
                          <i class="fa-solid fa-user-slash text-gray-400"></i>
                        </span>
                        <span>Không giao cho ai</span>
                      </button>

                      <!-- User Options List -->
                      <button
                        v-for="u in projectStore.users"
                        :key="u.id"
                        @click.stop="handleUpdateLead(project.id, u.id)"
                        type="button"
                        class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer"
                      >
                        <img
                          :src="u.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                          :alt="u.name"
                          class="w-6 h-6 rounded-full object-cover border border-emerald-200"
                        />
                        <span class="truncate flex-1">{{ u.name }}</span>
                        <i v-if="project.lead && project.lead.id === u.id" class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                      </button>
                    </div>
                  </td>

                  <!-- Cập nhật (Relative Time) -->
                  <td class="py-4 px-6 text-gray-500 font-normal text-sm align-middle">
                    {{ formatRelativeTime(project.last_activity_at || project.updated_at) }}
                  </td>

                  <!-- Pin column: FontAwesome fa-thumbtack -->
                  <td class="py-4 px-4 text-center align-middle">
                    <button
                      @click.stop="handleTogglePin(project.id)"
                      @mousedown.stop
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
                      :title="project.is_pinned ? 'Bỏ ghim' : 'Ghim dự án'"
                    >
                      <i 
                        class="fa-solid fa-thumbtack text-base transition-transform group-hover:scale-110"
                        :class="project.is_pinned ? 'text-emerald-600' : 'text-gray-300 hover:text-gray-500 -rotate-45'"
                      ></i>
                    </button>
                  </td>

                  <!-- Actions column -->
                  <td class="py-4 px-4 text-center align-middle relative">
                    <button
                      @click.stop="toggleActionMenu(project.id)"
                      @mousedown.stop
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                    >
                      <i class="fa-solid fa-ellipsis text-base"></i>
                    </button>

                    <!-- Actions Dropdown -->
                    <div
                      v-if="openActionMenuId === project.id"
                      @click.stop
                      @mousedown.stop
                      class="absolute right-2 top-full mt-1 w-36 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5"
                    >
                      <!-- Edit button -->
                      <button
                        @click.stop="handleEditProject(project)"
                        type="button"
                        class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer select-none"
                      >
                        <i class="fa-solid fa-pen text-emerald-600"></i>
                        <span>Sửa</span>
                      </button>

                      <!-- Delete button -->
                      <button
                        @click.stop="handleDeleteProject(project)"
                        type="button"
                        :disabled="!canDeleteProject(project)"
                        class="w-full text-left px-3.5 py-2 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer select-none disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                        :title="canDeleteProject(project) ? 'Xóa dự án' : 'Không thể xóa dự án đã có cập nhật hoặc nhiệm vụ'"
                      >
                        <i class="fa-solid fa-trash-can" :class="canDeleteProject(project) ? 'text-rose-500' : 'text-rose-300'"></i>
                        <span>Xóa</span>
                      </button>
                    </div>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

          <!-- Load more container -->
          <div v-if="projectStore.projects.length > displayLimit" class="p-4 border-t border-gray-100 flex justify-center bg-white">
            <button
              @click="displayLimit += 15"
              type="button"
              class="px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100/80 text-emerald-800 font-extrabold text-xs rounded-xl shadow-3xs transition-all cursor-pointer flex items-center gap-1.5 focus:outline-none"
            >
              <i class="fa-solid fa-angles-down text-[10px]"></i>
              <span>Xem thêm dự án (Còn {{ projectStore.projects.length - displayLimit }} dự án)</span>
            </button>
          </div>

          <!-- Bottom Footer Banner matching mockup -->
          <div class="bg-[#f2faf3] px-6 py-3 border-t border-emerald-100/60 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs font-medium text-emerald-800">
            <div class="flex items-center gap-2">
              <i class="fa-regular fa-lightbulb text-emerald-600 text-sm"></i>
              <span>
                Nhấp vào <span class="text-rose-500 font-bold">🔴</span> <span class="text-amber-500 font-bold">🟡</span> <span class="text-emerald-500 font-bold">🟢</span> để cập nhật tình trạng dự án
              </span>
            </div>

            <button
              @click="scrollToTop"
              type="button"
              class="flex items-center gap-1.5 text-emerald-700 hover:text-emerald-900 font-semibold transition-colors"
            >
              <i class="fa-solid fa-arrow-up text-xs"></i>
              <span>Lên đầu trang</span>
            </button>
          </div>

        </div>

      </main>
    </div>

    <!-- Project Modal -->
    <ProjectModal
      :is-open="isModalOpen"
      :customers="projectStore.customers"
      :users="projectStore.users"
      :edit-project="editingProject"
      @close="handleCloseModal"
      @submit="handleCreateProject"
      @customer-created="projectStore.fetchAuxData()"
    />

    <!-- Floating Action Bar for Bulk Update -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-10 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-10 opacity-0"
    >
      <div
        v-if="selectedProjectIds.length > 0"
        class="fixed bottom-20 left-1/2 -translate-x-1/2 z-40 bg-white/95 backdrop-blur-md px-6 py-4 rounded-2xl shadow-xl border border-emerald-100/80 flex items-center gap-6 w-[90%] max-w-md justify-between"
      >
        <span class="text-sm font-semibold text-emerald-800">
          Đã chọn <strong class="text-emerald-950 font-bold">{{ selectedProjectIds.length }}</strong> dự án
        </span>
        <div class="flex items-center gap-3">
          <button
            @click="selectedProjectIds = []"
            type="button"
            class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl transition-colors"
          >
            Hủy chọn
          </button>
          <button
            @click="goToBulkUpdate"
            type="button"
            class="px-4 py-2 bg-[#2d8a39] hover:bg-[#236e2d] text-white font-semibold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
          >
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Cập nhật</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- Bottom Navigation Bar -->
    <BottomNav />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import HealthStatusSelector from '../components/HealthStatusSelector.vue'
import ProjectModal from '../components/ProjectModal.vue'
import BottomNav from '../components/BottomNav.vue'
import { useProjectStore } from '../stores/project'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'

const projectStore = useProjectStore()
const router = useRouter()
const toast = useToastStore()
const confirmStore = useConfirmStore()

const openActionMenuId = ref(null)
const openLeadMenuId = ref(null)
const editingProject = ref(null)

const toggleActionMenu = (projectId) => {
  if (openActionMenuId.value === projectId) {
    openActionMenuId.value = null
  } else {
    openActionMenuId.value = projectId
    openLeadMenuId.value = null
  }
}

const toggleLeadMenu = (projectId) => {
  if (openLeadMenuId.value === projectId) {
    openLeadMenuId.value = null
  } else {
    openLeadMenuId.value = projectId
    openActionMenuId.value = null
  }
}

const handleUpdateLead = async (projectId, newLeadId) => {
  try {
    await axios.put(`/api/projects/${projectId}`, { lead_id: newLeadId })
    toast.success('Đã chuyển lead dự án!')
    await projectStore.fetchProjects(true)
    openLeadMenuId.value = null
  } catch (err) {
    console.error('Failed to update project lead inline:', err)
    toast.error('Chuyển lead dự án thất bại!')
  }
}

const handleEditProject = (project) => {
  editingProject.value = project
  isModalOpen.value = true
  openActionMenuId.value = null
  openLeadMenuId.value = null
}

const handleCloseModal = () => {
  isModalOpen.value = false
  editingProject.value = null
}

const canDeleteProject = (project) => {
  return (project.comments_count ?? 0) <= 1 && (project.tasks_count ?? 0) === 0
}

const handleDeleteProject = async (project) => {
  if (!canDeleteProject(project)) return
  const confirmed = await confirmStore.show({
    title: 'Xóa dự án',
    message: `Bạn có chắc chắn muốn xóa dự án "${project.title}" không?`
  })
  if (!confirmed) return
  try {
    await projectStore.deleteProject(project.id)
    toast.success('Xóa dự án thành công!')
    openActionMenuId.value = null
  } catch (err) {
    console.error('Failed to delete project:', err)
    toast.error(err.response?.data?.message || 'Không thể xóa dự án.')
  }
}

const closeAllActionMenus = () => {
  openActionMenuId.value = null
  openLeadMenuId.value = null
}

const goToProjectDetail = (projectId, event) => {
  if (event.ctrlKey || event.metaKey) return
  router.push(`/projects/${projectId}`)
}
const isModalOpen = ref(false)

const selectedProjectIds = ref([])
const isDragging = ref(false)
const dragStartVal = ref(true)

const displayLimit = ref(15)
const displayedProjects = computed(() => {
  return projectStore.projects.slice(0, displayLimit.value)
})

const isAllSelected = computed(() => {
  if (projectStore.projects.length === 0) return false
  return projectStore.projects.every(p => selectedProjectIds.value.includes(p.id))
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedProjectIds.value = []
  } else {
    selectedProjectIds.value = projectStore.projects.map(p => p.id)
  }
}

const toggleProjectSelection = (projectId) => {
  if (selectedProjectIds.value.includes(projectId)) {
    selectedProjectIds.value = selectedProjectIds.value.filter(id => id !== projectId)
  } else {
    selectedProjectIds.value.push(projectId)
  }
}

const handleMouseDown = (event, project) => {
  if (event.ctrlKey || event.metaKey) {
    event.preventDefault()
    isDragging.value = true
    const isAlreadySelected = selectedProjectIds.value.includes(project.id)
    dragStartVal.value = !isAlreadySelected
    
    if (dragStartVal.value) {
      if (!selectedProjectIds.value.includes(project.id)) {
        selectedProjectIds.value.push(project.id)
      }
    } else {
      selectedProjectIds.value = selectedProjectIds.value.filter(id => id !== project.id)
    }
  }
}

const handleMouseEnter = (project) => {
  if (isDragging.value) {
    if (dragStartVal.value) {
      if (!selectedProjectIds.value.includes(project.id)) {
        selectedProjectIds.value.push(project.id)
      }
    } else {
      selectedProjectIds.value = selectedProjectIds.value.filter(id => id !== project.id)
    }
  }
}

const handleMouseUp = () => {
  isDragging.value = false
}

const goToBulkUpdate = () => {
  if (selectedProjectIds.value.length === 0) return
  router.push({
    path: '/projects/update',
    query: { ids: selectedProjectIds.value.join(',') }
  })
}

let pollTimer = null

const setTab = (status) => {
  displayLimit.value = 15
  if (projectStore.activeStatus === status) {
    projectStore.activeStatus = null
  } else {
    projectStore.activeStatus = status
  }
  projectStore.fetchProjects()
}

const handleSearch = (query) => {
  displayLimit.value = 15
  projectStore.searchQuery = query
  projectStore.fetchProjects()
}

const handleHealthChange = async (projectId, newColor) => {
  try {
    await projectStore.updateHealth(projectId, newColor)
    toast.success('Đã cập nhật tình trạng sức khỏe dự án!')
  } catch (err) {
    console.error('Failed to update project health:', err)
    toast.error('Cập nhật tình trạng sức khỏe thất bại!')
  }
}

const handleTogglePin = async (projectId) => {
  try {
    const isPinned = await projectStore.togglePin(projectId)
    if (isPinned) {
      toast.success('Đã ghim dự án lên đầu!')
    } else {
      toast.success('Đã bỏ ghim dự án!')
    }
  } catch (err) {
    console.error('Failed to toggle project pin:', err)
    toast.error('Thay đổi ghim thất bại!')
  }
}

const handleCreateProject = async (data) => {
  if (editingProject.value) {
    try {
      await axios.put(`/api/projects/${editingProject.value.id}`, data)
      toast.success('Cập nhật dự án thành công!')
      await projectStore.fetchProjects(true)
      handleCloseModal()
    } catch (err) {
      console.error('Failed to update project:', err)
      toast.error('Cập nhật dự án thất bại!')
    }
  } else {
    try {
      await projectStore.createProject(data)
      toast.success('Tạo dự án mới thành công!')
      handleCloseModal()
    } catch (err) {
      console.error('Failed to create project:', err)
      toast.error('Tạo dự án mới thất bại!')
    }
  }
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatRelativeTime = (dateStr) => {
  if (!dateStr) return 'Vừa xong'
  const date = new Date(dateStr)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)

  if (diffSec < 60) return 'Vừa xong'
  const diffMin = Math.floor(diffSec / 60)
  if (diffMin < 60) return `${diffMin} phút trước`
  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours} giờ trước`
  const diffDays = Math.floor(diffHours / 24)
  return `${diffDays} ngày trước`
}

onMounted(() => {
  projectStore.fetchProjects()
  projectStore.fetchAuxData()

  window.addEventListener('mouseup', handleMouseUp)
  window.addEventListener('click', closeAllActionMenus)

  pollTimer = setInterval(() => {
    projectStore.fetchProjects(true)
  }, 3000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
  window.removeEventListener('mouseup', handleMouseUp)
  window.removeEventListener('click', closeAllActionMenus)
})
</script>
