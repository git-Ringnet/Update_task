<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-24">
    <div>
      <!-- Navbar Component -->
      <Navbar />

      <!-- Main Container -->
      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Page Title -->
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading">Công việc</h1>

        <!-- SECTION 1: DỰ ÁN BẠN ĐANG LEAD -->
        <div>
          <!-- Section Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider font-heading">
                DỰ ÁN BẠN ĐANG LEAD
              </h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                {{ leadProjects.length }}
              </span>
            </div>

          </div>

          <!-- Section 1 Table Card Container -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                    <th scope="col" class="py-3.5 px-6">DỰ ÁN</th>
                    <th scope="col" class="py-3.5 px-6 text-center">TÌNH TRẠNG</th>
                    <th scope="col" class="py-3.5 px-6">CẬP NHẬT</th>
                    <th scope="col" class="py-3.5 px-4 text-center">
                      <i class="fa-solid fa-thumbtack text-gray-400"></i>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <!-- Skeleton Loading -->
                  <template v-if="isLoading">
                    <tr v-for="i in 3" :key="'skeleton-lead-' + i" class="animate-pulse">
                      <td class="py-4 px-6">
                        <div class="h-4 bg-gray-200/80 rounded-md w-3/4 mb-2"></div>
                        <div class="h-3 bg-gray-100/90 rounded-md w-1/2"></div>
                      </td>
                      <td class="py-4 px-6 text-center align-middle">
                        <div class="w-4 h-4 bg-gray-200/80 rounded-full mx-auto"></div>
                      </td>
                      <td class="py-4 px-6 align-middle">
                        <div class="h-4 bg-gray-200/80 rounded-md w-24"></div>
                      </td>
                      <td class="py-4 px-4 text-center align-middle">
                        <div class="w-4 h-4 bg-gray-100/80 rounded-md mx-auto"></div>
                      </td>
                      <td class="py-4 px-4 text-right align-middle">
                        <div class="w-4 h-4 bg-gray-100/80 rounded-md ml-auto"></div>
                      </td>
                    </tr>
                  </template>

                  <!-- Empty State -->
                  <tr v-else-if="leadProjects.length === 0">
                    <td colspan="5" class="py-8 text-center text-gray-400">
                      Bạn chưa làm lead dự án nào.
                    </td>
                  </tr>

                  <!-- Lead Projects Rows -->
                  <tr
                    v-for="(project, index) in leadProjects"
                    :key="project.id"
                    class="hover:bg-emerald-50/20 transition-colors group animate-fade-in-up"
                    :style="{ animationDelay: `${index * 45}ms` }"
                  >
                    <!-- Title & Customer -->
                    <td class="py-4 px-6 max-w-xs md:max-w-md">
                      <router-link :to="`/projects/${project.id}`" class="block group-hover:text-emerald-700">
                        <div class="font-bold text-gray-900 text-base leading-snug font-heading flex items-start gap-2">
                          <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0 mt-1.5" :class="statusDotClass(project.health)"></span>
                          <span class="break-words min-w-0 flex-1">{{ project.title }}</span>
                        </div>
                        <div class="text-xs text-gray-500 font-medium mt-0.5 pl-4 break-words">
                          {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                        </div>
                      </router-link>
                    </td>

                    <!-- Status (Centered Color Dot selector) -->
                    <td class="py-4 px-6 text-center align-middle">
                      <HealthStatusSelector
                        :model-value="project.health"
                        @change="(newColor) => handleHealthChange(project.id, newColor)"
                      />
                    </td>

                    <!-- Relative Time -->
                    <td class="py-4 px-6 text-gray-500 font-normal text-sm align-middle">
                      {{ formatRelativeTime(project.last_activity_at || project.updated_at) }}
                    </td>

                    <!-- Pin icon -->
                    <td class="py-4 px-4 text-center align-middle">
                      <button
                        @click="handleTogglePin(project.id)"
                        type="button"
                        class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
                      >
                        <i 
                          class="fa-solid fa-thumbtack text-base transition-transform group-hover:scale-110"
                          :class="project.is_pinned ? 'text-emerald-600' : 'text-gray-300 hover:text-gray-500 -rotate-45'"
                        ></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Giao diện Điện thoại: Danh sách Dự án đang lead (Ẩn trên Desktop) -->
            <div class="block md:hidden divide-y divide-gray-100 bg-white">
              <!-- Skeleton loading state -->
              <template v-if="isLoading && leadProjects.length === 0">
                <div v-for="i in 2" :key="'skeleton-lead-card-' + i" class="p-4 animate-pulse space-y-3">
                  <div class="flex items-center justify-between">
                    <div class="h-4 bg-gray-200 rounded-md w-2/3"></div>
                    <div class="w-4 h-4 bg-gray-200 rounded-md"></div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-3.5 h-3.5 bg-gray-200 rounded-full"></div>
                    <div class="h-3 bg-gray-100 rounded-md w-20"></div>
                  </div>
                </div>
              </template>

              <!-- Empty state -->
              <div v-else-if="leadProjects.length === 0" class="p-8 text-center text-gray-400 text-sm">
                Bạn chưa làm lead dự án nào.
              </div>

              <!-- Card List -->
              <div
                v-for="(project, index) in leadProjects"
                :key="'lead-card-' + project.id"
                class="p-4 hover:bg-emerald-50/20 active:bg-emerald-50/30 transition-colors flex flex-col gap-2.5 relative cursor-pointer group"
                @click="$router.push(`/projects/${project.id}`)"
              >
                <!-- Row 1: Title + Pin -->
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h3 class="font-bold text-gray-900 text-base leading-snug break-words font-heading group-hover:text-emerald-700 flex items-start gap-2">
                      <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0 mt-1.5" :class="statusDotClass(project.health)"></span>
                      <span class="break-words min-w-0 flex-1">{{ project.title }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 font-semibold mt-0.5 pl-4 break-words">
                      {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                    </p>
                  </div>

                  <!-- Pin Button -->
                  <div class="flex-shrink-0" @click.stop>
                    <button
                      @click="handleTogglePin(project.id)"
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
                    >
                      <i 
                        class="fa-solid fa-thumbtack text-base"
                        :class="project.is_pinned ? 'text-emerald-600' : 'text-gray-300 -rotate-45'"
                      ></i>
                    </button>
                  </div>
                </div>

                <!-- Row 2: Status indicator + Last activity time -->
                <div class="flex items-center justify-between gap-3 pt-1 text-xs text-gray-500 pl-4">
                  <div class="flex items-center gap-3">
                    <!-- Health status selector -->
                    <div @click.stop class="flex items-center">
                      <HealthStatusSelector
                        :model-value="project.health"
                        @change="(newColor) => handleHealthChange(project.id, newColor)"
                      />
                    </div>

                    <!-- Divider -->
                    <span class="text-gray-300">•</span>

                    <!-- Time -->
                    <span class="font-medium text-[11px]">
                      Cập nhật {{ formatRelativeTime(project.last_activity_at || project.updated_at) }}
                    </span>
                  </div>

                  <!-- Right chevron -->
                  <div class="flex-shrink-0 text-gray-300">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SECTION 2: TASK ĐƯỢC ASSIGNED CHO BẠN -->
        <div>
          <!-- Section Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider font-heading">
                TASK ĐƯỢC ASSIGNED CHO BẠN
              </h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                {{ assignedTasks.length }}
              </span>
            </div>

          </div>

          <!-- Section 2 Tasks List Card Container -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-xs divide-y divide-gray-100 overflow-hidden">
            <!-- Skeleton Loading -->
            <template v-if="isLoading">
              <div v-for="i in 5" :key="'skeleton-task-' + i" class="p-4 flex items-center justify-between animate-pulse">
                <div class="flex items-center gap-3">
                  <div class="w-5 h-5 rounded-full bg-gray-200"></div>
                  <div>
                    <div class="h-4 bg-gray-200/80 rounded-md w-64 mb-1"></div>
                    <div class="h-3 bg-gray-100/90 rounded-md w-40"></div>
                  </div>
                </div>
                <div class="w-20 h-4 bg-gray-100 rounded-md"></div>
              </div>
            </template>

            <!-- Empty State -->
            <div v-else-if="assignedTasks.length === 0" class="p-8 text-center text-gray-400">
              Bạn chưa có công việc nào được giao.
            </div>

            <!-- Tasks List Rows matching mockup 7 -->
            <div
              v-for="(task, index) in assignedTasks"
              :key="task.id"
              @click="openTaskDetail(task)"
              class="p-4 hover:bg-emerald-50/20 transition-colors flex items-center justify-between gap-4 cursor-pointer group animate-fade-in-up"
              :style="{ animationDelay: `${index * 45}ms` }"
            >
              <!-- Left: Checkbox + Title + Project Subtitle -->
              <div class="flex items-center gap-3.5 min-w-0">
                <button
                  @click.stop="toggleTaskSelect(task)"
                  type="button"
                  class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors flex-shrink-0 cursor-pointer"
                  :class="task.status === 'done' || selectedTaskIds.includes(task.id) ? 'border-emerald-600 bg-emerald-600 text-white shadow-3xs' : 'border-gray-300 hover:border-emerald-500'"
                >
                  <i v-if="task.status === 'done' || selectedTaskIds.includes(task.id)" class="fa-solid fa-check text-[10px]"></i>
                </button>

                <div class="min-w-0">
                  <div 
                    class="font-bold text-gray-900 text-base leading-snug font-heading truncate group-hover:text-emerald-700"
                    :class="{ 'line-through text-gray-400': task.status === 'done' }"
                  >
                    {{ task.title }}
                  </div>
                  <div 
                    class="text-xs font-semibold mt-0.5 truncate"
                    :class="projectTitleColorClass(task.project ? task.project.health : 'green')"
                  >
                    {{ task.project ? task.project.title : 'Dự án' }}
                  </div>
                </div>
              </div>

              <!-- Right: Timestamp -->
              <div class="flex items-center gap-6 flex-shrink-0">
                <!-- Timestamp with Clock or Calendar icon -->
                <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                  <i class="fa-regular fa-clock text-xs"></i>
                  <span>{{ formatRelativeTime(task.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </main>

    </div>

    <!-- Task Detail Drawer with Realtime Discussion (Replacing Zalo chat) -->
    <div v-if="selectedTask" class="fixed inset-0 z-50 overflow-hidden">
      <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs" @click="selectedTask = null"></div>
      <div class="absolute inset-y-0 right-0 max-w-full flex pl-0 sm:pl-10">
        <div class="w-screen sm:max-w-md bg-white shadow-2xl flex flex-col justify-between border-l border-gray-100">
          
          <!-- Drawer Header -->
          <div class="p-6 border-b border-gray-100 bg-emerald-50/40 flex items-center justify-between">
            <div>
              <span class="text-xs font-bold text-emerald-700 uppercase">Thảo Luận Realtime</span>
              <h3 class="text-lg font-bold text-gray-900">{{ selectedTask.title }}</h3>
            </div>
            <button @click="selectedTask = null" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <!-- Comments Feed -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div v-if="taskComments.length === 0" class="text-center py-10 text-gray-400 text-sm">
              Chưa có thảo luận nào. Hãy nhập tin nhắn bên dưới để trao đổi!
            </div>

            <div
              v-for="comment in taskComments"
              :key="comment.id"
              class="flex items-start gap-3"
            >
              <img
                :src="comment.user ? comment.user.avatar : 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'"
                class="w-8 h-8 rounded-full object-cover border border-emerald-200 flex-shrink-0"
              />
              <div class="bg-gray-100/80 p-3 rounded-2xl rounded-tl-none max-w-xs">
                <div class="flex items-center justify-between gap-2 mb-1">
                  <span class="font-bold text-xs text-gray-900">{{ comment.user ? comment.user.name : 'Thành viên' }}</span>
                  <span class="text-[10px] text-gray-400">{{ formatRelativeTime(comment.created_at) }}</span>
                </div>
                <p class="text-xs text-gray-800 leading-relaxed">{{ comment.content }}</p>
              </div>
            </div>
          </div>

          <!-- Send Message Input -->
          <div class="p-4 border-t border-gray-100 bg-white">
            <form @submit.prevent="sendComment" class="flex items-center gap-2">
              <input
                v-model="newCommentText"
                type="text"
                placeholder="Nhập tin nhắn thảo luận..."
                class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
              <button
                type="submit"
                class="p-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors"
              >
                <i class="fa-solid fa-paper-plane text-sm"></i>
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <ProjectModal
      :is-open="isProjectModalOpen"
      :customers="customers"
      :users="users"
      @close="isProjectModalOpen = false"
      @submit="handleCreateProject"
    />

    <!-- Create Task Modal -->
    <div v-if="isTaskModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="isTaskModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">Tạo Công Việc Mới</h3>
            <button @click="isTaskModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>
          <form @submit.prevent="handleCreateTask" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Dự án thuộc về *</label>
              <select v-model="taskForm.project_id" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white">
                <option value="" disabled>-- Chọn dự án --</option>
                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên công việc *</label>
              <input v-model="taskForm.title" required type="text" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" placeholder="VD: Gửi cấu hình và hướng dẫn sử dụng" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Người thực hiện</label>
                <select v-model="taskForm.assignee_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white">
                  <option :value="null">-- Chưa giao --</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Độ ưu tiên</label>
                <select v-model="taskForm.priority" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-white">
                  <option value="medium">Bình thường</option>
                  <option value="high">Cao (Cờ đỏ)</option>
                  <option value="urgent">Gấp (Urgent)</option>
                </select>
              </div>
            </div>
            <div class="pt-4 flex items-center justify-end gap-2">
              <button type="button" @click="isTaskModalOpen = false" class="px-4 py-2 text-sm text-gray-600">Hủy</button>
              <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold">Tạo ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Floating Bulk Task Completion Bar -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-8 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-8 opacity-0"
    >
      <div
        v-if="selectedTaskIds.length > 0"
        class="fixed bottom-20 left-1/2 -translate-x-1/2 z-40 bg-white/95 backdrop-blur-md px-6 py-4 rounded-2xl shadow-xl border border-emerald-100/80 flex items-center gap-6 w-[90%] max-w-md justify-between"
      >
        <span class="text-sm font-semibold text-emerald-800">
          Đã chọn <strong class="text-emerald-950 font-bold">{{ selectedTaskIds.length }}</strong> công việc
        </span>
        <div class="flex items-center gap-3">
          <button
            @click="selectedTaskIds = []"
            type="button"
            class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl transition-colors cursor-pointer focus:outline-none"
          >
            Hủy chọn
          </button>
          <button
            @click="goToBulkTaskComplete"
            type="button"
            class="px-4 py-2 bg-[#2d8a39] hover:bg-[#236e2d] text-white font-semibold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer focus:outline-none"
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
import { ref, reactive, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import HealthStatusSelector from '../components/HealthStatusSelector.vue'
import ProjectModal from '../components/ProjectModal.vue'
import { useProjectStore } from '../stores/project'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const projectStore = useProjectStore()
const authStore = useAuthStore()
const toast = useToastStore()

const leadProjects = ref([])
const assignedTasks = ref([])
const projects = ref([])
const customers = ref([])
const users = ref([])
const isLoading = ref(false)

const isProjectModalOpen = ref(false)
const isTaskModalOpen = ref(false)

const selectedTask = ref(null)
const taskComments = ref([])
const newCommentText = ref('')

const selectedTaskIds = ref([])

const taskForm = reactive({
  project_id: '',
  title: '',
  description: '',
  assignee_id: authStore.user?.id || 3, // Default current user
  status: 'todo',
  priority: 'high',
})

const fetchData = async (isSilent = false) => {
  if (!isSilent && leadProjects.value.length === 0) {
    isLoading.value = true
  }
  try {
    const [pRes, tRes, cRes, uRes] = await Promise.all([
      axios.get('/api/projects'),
      axios.get('/api/tasks'),
      axios.get('/api/customers'),
      axios.get('/api/users'),
    ])

    projects.value = pRes.data.projects
    customers.value = cRes.data.customers || cRes.data
    users.value = uRes.data

    // Filter projects lead by current logged in user
    leadProjects.value = projects.value.filter(p => Number(p.lead_id) === Number(authStore.user?.id))

    // Filter tasks assigned to current logged in user
    assignedTasks.value = tRes.data.filter(t => Number(t.assignee_id) === Number(authStore.user?.id))
  } catch (err) {
    console.error('Failed to fetch task page data:', err)
  } finally {
    isLoading.value = false
  }
}

const handleHealthChange = async (projectId, newColor) => {
  try {
    await projectStore.updateHealth(projectId, newColor)
    toast.success('Đã cập nhật tình trạng sức khỏe dự án!')
    fetchData(true)
  } catch (err) {
    console.error('Failed to update project health:', err)
    toast.error('Cập nhật tình trạng sức khỏe thất bại!')
  }
}

const handleTogglePin = async (projectId) => {
  try {
    await projectStore.togglePin(projectId)
    toast.success('Đã thay đổi ghim dự án!')
    fetchData(true)
  } catch (err) {
    console.error('Failed to toggle pin:', err)
    toast.error('Thay đổi ghim thất bại!')
  }
}

const toggleTaskSelect = async (task) => {
  if (task.status === 'done') {
    // Unchecking a completed task -> immediately set to todo on server
    try {
      await axios.patch(`/api/tasks/${task.id}/status`, { 
        status: 'todo',
        user_id: authStore.user?.id || 3
      })
      toast.success('Đã mở lại công việc!')
      fetchData(true)
    } catch (err) {
      console.error('Failed to toggle task status:', err)
      toast.error('Cập nhật trạng thái công việc thất bại!')
      fetchData(true)
    }
  } else {
    // Toggle selection for bulk complete
    const idx = selectedTaskIds.value.indexOf(task.id)
    if (idx > -1) {
      selectedTaskIds.value.splice(idx, 1)
    } else {
      selectedTaskIds.value.push(task.id)
    }
  }
}

const goToBulkTaskComplete = () => {
  if (selectedTaskIds.value.length === 0) return
  router.push({
    path: '/tasks/complete',
    query: { ids: selectedTaskIds.value.join(',') }
  })
}

const handleCreateProject = async (data) => {
  try {
    await projectStore.createProject(data)
    toast.success('Tạo dự án mới thành công!')
    isProjectModalOpen.value = false
    fetchData(true)
  } catch (err) {
    console.error('Failed to create project:', err)
    toast.error('Tạo dự án mới thất bại!')
  }
}

const handleCreateTask = async () => {
  try {
    await axios.post('/api/tasks', taskForm)
    toast.success('Tạo công việc mới thành công!')
    taskForm.title = ''
    isTaskModalOpen.value = false
    fetchData(true)
  } catch (err) {
    console.error('Failed to create task:', err)
    toast.error('Tạo công việc mới thất bại!')
  }
}

const openTaskDetail = async (task) => {
  selectedTask.value = task
  const res = await axios.get('/api/comments', {
    params: { project_id: task.project_id, task_id: task.id }
  })
  taskComments.value = res.data
}

const sendComment = async () => {
  if (!newCommentText.value.trim() || !selectedTask.value) return
  try {
    const res = await axios.post('/api/comments', {
      project_id: selectedTask.value.project_id,
      task_id: selectedTask.value.id,
      user_id: authStore.user?.id || 3, // Default current user
      content: newCommentText.value,
    })
    toast.success('Gửi thảo luận thành công!')
    taskComments.value.push(res.data)
    newCommentText.value = ''
  } catch (err) {
    console.error('Failed to send comment:', err)
    toast.error('Gửi thảo luận thất bại!')
  }
}

const statusDotClass = (status) => {
  if (status === 'yellow') return 'bg-amber-400 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  if (status === 'green') return 'bg-emerald-500 health-dot-green'
  return 'bg-gray-400'
}

const projectTitleColorClass = (health) => {
  if (health === 'yellow') return 'text-amber-700'
  if (health === 'red') return 'text-rose-600'
  if (health === 'green') return 'text-emerald-700'
  return 'text-emerald-700'
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
  fetchData()
})
</script>
