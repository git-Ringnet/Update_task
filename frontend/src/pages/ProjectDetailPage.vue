<template>
  <div class="min-h-screen bg-[#f8faf9] pb-24">
    <!-- Navbar Component -->
    <Navbar />

    <!-- Full-page loading spinner to prevent mock lead flicker -->
    <div v-if="!project" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 flex flex-col items-center justify-center gap-4">
      <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <span class="text-gray-500 font-semibold text-sm">Đang tải chi tiết dự án...</span>
    </div>

    <!-- Real Content -->
    <main v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      
      <!-- Back Link & Header Title -->
      <div>
        <router-link to="/projects" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium mb-3 transition-colors">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại dự án</span>
        </router-link>

        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight font-heading flex items-center gap-3">
              <span class="w-4 h-4 rounded-full inline-block flex-shrink-0 animate-pulse" :class="statusDotClass(project.health)"></span>
              <span>{{ project.title }}</span>
            </h1>
            
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-sm text-gray-500">
              <p>Khách hàng: <strong class="text-gray-800 font-bold">{{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}</strong></p>
              
              <!-- Compact Inline Lead Selector (Saves space, highly interactive) -->
              <span class="text-gray-300">|</span>
              <div class="relative inline-block" ref="leadDropdownRef">
                <span class="text-gray-400 font-medium">Lead phụ trách:</span>
                <button
                  @click.stop="toggleLeadSelect"
                  type="button"
                  class="inline-flex items-center gap-1.5 ml-1.5 px-2.5 py-1 hover:bg-emerald-50 border border-dashed border-emerald-300 hover:border-emerald-500 rounded-xl text-emerald-800 font-bold transition-all cursor-pointer text-xs"
                >
                  <img
                    :src="project.lead ? (project.lead.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120') : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                    class="w-5 h-5 rounded-full object-cover border border-emerald-100 shadow-3xs"
                  />
                  <span>{{ project.lead ? project.lead.name : 'Chưa giao' }}</span>
                  <i class="fa-solid fa-chevron-down text-[9px] text-emerald-600 transition-transform" :class="{ 'rotate-180': isLeadSelectOpen }"></i>
                </button>
                
                <!-- Lead Dropdown Selection -->
                <div
                  v-if="isLeadSelectOpen"
                  class="absolute left-24 top-full mt-1.5 w-44 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5 max-h-48 overflow-y-auto"
                >
                  <div class="px-3 py-1 text-[9px] font-bold text-gray-400 uppercase tracking-wider select-none border-b border-gray-100 mb-1">
                    Chuyển Lead Dự Án
                  </div>
                  <button
                    v-for="u in users"
                    :key="u.id"
                    @click.stop="updateLead(u.id)"
                    type="button"
                    class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer"
                  >
                    <img :src="u.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'" class="w-5 h-5 rounded-full object-cover border border-emerald-100" />
                    <span class="truncate flex-1">{{ u.name }}</span>
                    <i v-if="project.lead && project.lead.id === u.id" class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Operations Menu -->
          <div class="relative animate-fade-in-up" ref="actionMenuDropdownRef">
            <button
              @click.stop="toggleActionMenu"
              type="button"
              class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
            >
              <i class="fa-solid fa-ellipsis text-lg"></i>
            </button>

            <!-- Actions Dropdown -->
            <div
              v-if="isActionMenuOpen"
              class="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5"
            >
              <!-- Edit button -->
              <button
                @click.stop="handleEditProject"
                type="button"
                class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer select-none"
              >
                <i class="fa-solid fa-pen text-emerald-600"></i>
                <span>Chỉnh sửa</span>
              </button>

              <!-- Delete button -->
              <button
                @click.stop="handleDeleteProject"
                type="button"
                :disabled="!canDeleteProject"
                class="w-full text-left px-3.5 py-2 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer select-none disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                :title="canDeleteProject ? 'Xóa dự án' : 'Không thể xóa dự án đã có cập nhật hoặc nhiệm vụ'"
              >
                <i class="fa-solid fa-trash-can" :class="canDeleteProject ? 'text-rose-500' : 'text-rose-300'"></i>
                <span>Xóa dự án</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN GRID: LEFT (Cập nhật) & RIGHT (Cột mốc + Tasks) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN (2 Cols wide) - Cập nhật hoạt động thực tế -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- CARD: CẬP NHẬT (Log Activity & Chat) -->
          <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">CẬP NHẬT HOẠT ĐỘNG</h3>

            <!-- Input quick update -->
            <form @submit.prevent="handleQuickUpdate" class="mb-6">
              <div class="relative">
                <input
                  v-model="updateContentText"
                  type="text"
                  placeholder="Gõ cập nhật nhanh... (Enter để lưu)"
                  class="w-full px-4 py-3 bg-white border border-emerald-500 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 shadow-2xs"
                />
              </div>
            </form>

            <!-- Activity Logs Feed -->
            <div class="space-y-4">
              <div
                v-for="log in activityLogs"
                :key="log.id"
                class="flex items-start gap-4 py-3 border-b border-gray-50 last:border-0"
              >
                <!-- Types color highlight indicator -->
                <div 
                  class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5"
                  :class="{
                    'bg-emerald-100 text-emerald-700': log.type === 'comment',
                    'bg-amber-100 text-amber-700': log.type === 'health_update',
                    'bg-blue-100 text-blue-700': log.type === 'status_change'
                  }"
                >
                  <i v-if="log.type === 'comment'" class="fa-solid fa-comment-dots"></i>
                  <i v-else-if="log.type === 'health_update'" class="fa-solid fa-circle-info"></i>
                  <i v-else class="fa-solid fa-rotate"></i>
                </div>
                <span class="text-xs font-semibold text-gray-400 w-24 flex-shrink-0 pt-1">{{ formatRelativeTime(log.created_at) }}</span>
                <img 
                  :src="log.user ? (log.user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120') : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'" 
                  class="w-7 h-7 rounded-full object-cover border border-gray-200 flex-shrink-0 shadow-3xs" 
                />
                <div class="flex-1">
                  <span class="font-bold text-gray-900 text-sm mr-3">{{ log.user ? log.user.name : 'Hệ thống' }}</span>
                  <span class="text-sm text-gray-700 font-medium leading-relaxed">{{ log.content }}</span>
                </div>
              </div>

              <!-- Empty activity state -->
              <div v-if="activityLogs.length === 0" class="py-8 text-center text-gray-400 text-xs font-medium">
                Chưa có cập nhật hoạt động nào cho dự án này.
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN (1 Col wide) - Cột mốc & Tasks -->
        <div class="space-y-6">
          
          <!-- CARD 3: CỘT MỐC (Milestones Timeline) -->
          <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">CỘT MỐC</h3>
              <button 
                @click="isAddMilestoneOpen = !isAddMilestoneOpen" 
                type="button" 
                class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 cursor-pointer"
              >
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Thêm cột mốc</span>
              </button>
            </div>

            <!-- Add Milestone Inline Form -->
            <form v-if="isAddMilestoneOpen" @submit.prevent="handleAddMilestone" class="mb-5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl space-y-2.5 animate-fade-in-up">
              <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Tên cột mốc *</label>
                <input 
                  v-model="newMilestone.title" 
                  type="text" 
                  required
                  placeholder="VD: Nghiệm thu đợt 1" 
                  class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Hạn chót (Có hoặc không đều được)</label>
                <input 
                  v-model="newMilestone.due_date" 
                  type="date" 
                  class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
                />
              </div>
              <div class="flex items-center justify-end gap-2 pt-1">
                <button @click="isAddMilestoneOpen = false" type="button" class="px-2.5 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Hủy</button>
                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-3xs cursor-pointer">Lưu mốc</button>
              </div>
            </form>

            <!-- Milestones Connected Line Timeline -->
            <div 
              v-if="project.milestones && project.milestones.length > 0"
              class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-emerald-500"
            >
              <div 
                v-for="ms in project.milestones" 
                :key="ms.id" 
                class="relative flex items-start justify-between gap-2"
              >
                <!-- Tích xanh marker -->
                <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] ring-4 ring-white shadow-3xs">
                  <i class="fa-solid fa-check"></i>
                </span>
                
                <!-- Editing form inline -->
                <div v-if="editingMilestoneId === ms.id" class="w-full space-y-2.5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl animate-fade-in-up">
                  <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tên cột mốc *</label>
                    <input v-model="editingMilestoneData.title" type="text" required class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" />
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Hạn chót</label>
                    <input v-model="editingMilestoneData.due_date" type="date" class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" />
                  </div>
                  <div class="flex items-center justify-end gap-2 text-xs pt-1">
                    <button @click="cancelEditMilestone" type="button" class="px-2 py-1 text-gray-500 hover:text-gray-700 font-semibold cursor-pointer">Hủy</button>
                    <button @click="handleUpdateMilestone(ms.id)" type="button" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-3xs cursor-pointer">Lưu</button>
                  </div>
                </div>

                <!-- Display mode with Hover edit/delete buttons -->
                <div v-else class="min-w-0 flex-1 group">
                  <div class="flex items-center gap-2">
                    <div class="font-bold text-gray-900 text-sm leading-snug">{{ ms.title }}</div>
                    
                    <!-- Edit & Delete hover controls -->
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2">
                      <button @click.stop="startEditMilestone(ms)" type="button" class="text-gray-400 hover:text-emerald-600 text-xs p-0.5 cursor-pointer" title="Sửa cột mốc">
                        <i class="fa-solid fa-pen"></i>
                      </button>
                      <button @click.stop="handleDeleteMilestone(ms.id)" type="button" class="text-gray-400 hover:text-rose-600 text-xs p-0.5 cursor-pointer" title="Xóa cột mốc">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="text-[10px] text-gray-400 font-semibold mt-1">
                    {{ ms.creator ? ms.creator.name : 'Lead' }} 
                    <span v-if="ms.due_date"> • Hạn: {{ formatDate(ms.due_date) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Milestones state -->
            <div v-else class="py-6 text-center text-gray-400 text-xs font-medium border border-dashed border-gray-200 rounded-xl">
              Chưa có cột mốc nào. Nhấp "+ Thêm cột mốc" để tạo.
            </div>
          </div>

          <!-- CARD 4: TASKS (Công việc với Assignee) -->
          <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">TASKS</h3>
              <button 
                @click="isAddTaskOpen = !isAddTaskOpen" 
                type="button" 
                class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1 cursor-pointer"
              >
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Thêm task</span>
              </button>
            </div>

            <!-- Add Task Inline Form -->
            <form v-if="isAddTaskOpen" @submit.prevent="handleAddTask" class="mb-5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl space-y-2.5 animate-fade-in-up">
              <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Tên công việc *</label>
                <input 
                  v-model="newTask.title" 
                  type="text" 
                  required
                  placeholder="VD: Cấu hình hệ thống tổng đài" 
                  class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Giao việc cho (Assignee)</label>
                <select v-model="newTask.assignee_id" class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                  <option value="">-- Chọn thành viên --</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Hạn chót</label>
                <input 
                  v-model="newTask.due_date" 
                  type="date" 
                  class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" 
                />
              </div>
              <div class="flex items-center justify-end gap-2 pt-1">
                <button @click="isAddTaskOpen = false" type="button" class="px-2.5 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Hủy</button>
                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-3xs cursor-pointer">Thêm task</button>
              </div>
            </form>

            <div class="space-y-3">
              <div
                v-for="task in project.tasks"
                :key="task.id"
                class="p-2.5 rounded-xl hover:bg-gray-50 transition-colors group"
              >
                <!-- Editing mode inline -->
                <div v-if="editingTaskId === task.id" class="space-y-2.5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl animate-fade-in-up">
                  <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tên công việc *</label>
                    <input v-model="editingTaskData.title" type="text" required class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" />
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Giao việc cho (Assignee)</label>
                    <select v-model="editingTaskData.assignee_id" class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                      <option value="">-- Chưa giao --</option>
                      <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Hạn chót</label>
                    <input v-model="editingTaskData.due_date" type="date" class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" />
                  </div>
                  <div class="flex items-center justify-end gap-2 text-xs pt-1">
                    <button @click="cancelEditTask" type="button" class="px-2 py-1 text-gray-500 hover:text-gray-700 font-semibold cursor-pointer">Hủy</button>
                    <button @click="handleUpdateTask(task)" type="button" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-3xs cursor-pointer">Lưu</button>
                  </div>
                </div>

                <!-- Display Mode -->
                <div v-else class="w-full space-y-1">
                  <!-- Row 1: Checkbox & Task Title -->
                  <div class="flex items-start gap-2.5 min-w-0">
                    <input 
                      type="checkbox" 
                      :checked="task.status === 'done'" 
                      @change="toggleTaskStatus(task)"
                      class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4 h-4 mt-0.5 flex-shrink-0" 
                    />
                    <span class="text-sm font-semibold text-gray-800 leading-snug break-words" :class="{ 'line-through text-gray-400 font-medium': task.status === 'done' }">
                      {{ task.title }}
                    </span>
                  </div>

                  <!-- Row 2: Assignee, Due Date & Actions -->
                  <div class="flex items-center justify-between pl-6.5 text-[11px] text-gray-500 font-medium">
                    <div class="flex items-center gap-3">
                      <!-- Assignee -->
                      <div class="flex items-center gap-1.5">
                        <template v-if="task.assignee">
                          <img
                            :src="task.assignee.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                            class="w-5 h-5 rounded-full object-cover border border-emerald-100 shadow-3xs"
                          />
                          <span class="font-bold text-gray-600 text-xs">{{ task.assignee.name }}</span>
                        </template>
                        <span v-else class="text-[10px] text-gray-400 italic">Chưa giao</span>
                      </div>

                      <!-- Date calendar -->
                      <div v-if="task.due_date" class="flex items-center gap-1 text-gray-400 font-bold bg-gray-100/70 px-1.5 py-0.5 rounded">
                        <i class="fa-regular fa-calendar text-[10px]"></i>
                        <span>{{ formatDate(task.due_date) }}</span>
                      </div>
                    </div>

                    <!-- Edit & Delete hover controls -->
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2">
                      <button @click.stop="startEditTask(task)" type="button" class="text-gray-400 hover:text-emerald-600 text-xs p-0.5 cursor-pointer" title="Sửa công việc">
                        <i class="fa-solid fa-pen text-[10px]"></i>
                      </button>
                      <button @click.stop="handleDeleteTask(task.id)" type="button" class="text-gray-400 hover:text-rose-600 text-xs p-0.5 cursor-pointer" title="Xóa công việc">
                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty state for tasks -->
              <div v-if="!project.tasks || project.tasks.length === 0" class="py-6 text-center text-gray-400 text-xs font-medium border border-dashed border-gray-200 rounded-xl">
                Chưa có công việc nào cho dự án này.
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Bottom Navigation Bar -->
    <BottomNav />

    <!-- Project Modal for editing -->
    <ProjectModal
      :is-open="isModalOpen"
      :customers="customers"
      :users="users"
      :edit-project="project"
      @close="handleCloseModal"
      @submit="handleUpdateProjectSubmit"
      @customer-created="fetchCustomers"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import ProjectModal from '../components/ProjectModal.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const authStore = useAuthStore()
const router = useRouter()
const toast = useToastStore()

const route = useRoute()
const projectId = route.params.id || 1

// We initialize project as null to show full loading spinner first
const project = ref(null)
const users = ref([])
const customers = ref([])
const activityLogs = ref([])

// Dropdown/Modal States
const isActionMenuOpen = ref(false)
const actionMenuDropdownRef = ref(null)
const isModalOpen = ref(false)

// Form states
const isLeadSelectOpen = ref(false)
const leadDropdownRef = ref(null)

const isAddMilestoneOpen = ref(false)
const newMilestone = reactive({
  title: '',
  description: '',
  due_date: ''
})

const isAddTaskOpen = ref(false)
const newTask = reactive({
  title: '',
  assignee_id: '',
  due_date: ''
})

const editingMilestoneId = ref(null)
const editingMilestoneData = reactive({
  title: '',
  description: '',
  due_date: ''
})

const editingTaskId = ref(null)
const editingTaskData = reactive({
  title: '',
  assignee_id: '',
  due_date: ''
})

const updateContentText = ref('')

// Load data
const fetchProjectDetail = async () => {
  try {
    const res = await axios.get(`/api/projects/${projectId}`)
    if (res.data) {
      project.value = res.data
    }
  } catch (err) {
    console.error('Failed to fetch project detail:', err)
  }
}

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users')
    users.value = res.data
  } catch (err) {
    console.error('Failed to fetch users:', err)
  }
}

const fetchComments = async () => {
  try {
    const res = await axios.get('/api/comments', { params: { project_id: projectId } })
    activityLogs.value = res.data
  } catch (err) {
    console.error('Failed to fetch comments:', err)
  }
}

// Actions
const toggleLeadSelect = () => {
  isLeadSelectOpen.value = !isLeadSelectOpen.value
}

const updateLead = async (userId) => {
  try {
    await axios.put(`/api/projects/${projectId}`, { lead_id: userId })
    toast.success('Đã chuyển lead dự án!')
    await fetchProjectDetail()
    isLeadSelectOpen.value = false
  } catch (err) {
    console.error('Failed to update lead:', err)
    toast.error('Chuyển lead dự án thất bại!')
  }
}

const handleQuickUpdate = async () => {
  if (!updateContentText.value.trim()) return
  const text = updateContentText.value
  updateContentText.value = ''
  try {
    await axios.post('/api/comments', {
      project_id: projectId,
      user_id: authStore.user?.id || 3,
      content: text,
      type: 'comment'
    })
    toast.success('Gửi cập nhật hoạt động thành công!')
    await fetchComments()
  } catch (err) {
    console.error('Failed to post comment:', err)
    toast.error('Gửi cập nhật hoạt động thất bại!')
  }
}

const toggleTaskStatus = async (task) => {
  const newStatus = task.status === 'done' ? 'todo' : 'done'
  // Optimistic update
  task.status = newStatus
  try {
    await axios.patch(`/api/tasks/${task.id}/status`, { 
      status: newStatus,
      user_id: authStore.user?.id || 3
    })
    toast.success(newStatus === 'done' ? 'Đã hoàn thành công việc!' : 'Đã mở lại công việc!')
    await fetchProjectDetail()
    await fetchComments()
  } catch (err) {
    console.error('Failed to toggle task status:', err)
    toast.error('Cập nhật trạng thái công việc thất bại!')
    await fetchProjectDetail()
  }
}

const handleAddMilestone = async () => {
  if (!newMilestone.title.trim()) return
  try {
    await axios.post(`/api/projects/${projectId}/milestones`, {
      title: newMilestone.title,
      description: newMilestone.description || null,
      due_date: newMilestone.due_date || null,
      is_completed: true
    })
    toast.success('Đã thêm cột mốc mới!')
    newMilestone.title = ''
    newMilestone.description = ''
    newMilestone.due_date = ''
    isAddMilestoneOpen.value = false
    await fetchProjectDetail()
  } catch (err) {
    console.error('Failed to add milestone:', err)
    toast.error('Thêm cột mốc thất bại!')
  }
}

const handleAddTask = async () => {
  if (!newTask.title.trim()) return
  try {
    await axios.post('/api/tasks', {
      project_id: projectId,
      assignee_id: newTask.assignee_id || null,
      title: newTask.title,
      status: 'todo',
      priority: 'medium',
      due_date: newTask.due_date || null,
      created_by: authStore.user?.id || 3
    })
    toast.success('Đã thêm công việc mới!')
    newTask.title = ''
    newTask.assignee_id = ''
    newTask.due_date = ''
    isAddTaskOpen.value = false
    await fetchProjectDetail()
    await fetchComments()
  } catch (err) {
    console.error('Failed to add task:', err)
    toast.error('Thêm công việc thất bại!')
  }
}

// Milestone edit/delete handlers
const startEditMilestone = (ms) => {
  editingMilestoneId.value = ms.id
  editingMilestoneData.title = ms.title
  editingMilestoneData.description = ms.description || ''
  editingMilestoneData.due_date = ms.due_date ? new Date(ms.due_date).toISOString().substring(0, 10) : ''
}

const cancelEditMilestone = () => {
  editingMilestoneId.value = null
}

const handleUpdateMilestone = async (id) => {
  if (!editingMilestoneData.title.trim()) return
  try {
    await axios.put(`/api/milestones/${id}`, {
      title: editingMilestoneData.title,
      description: editingMilestoneData.description || null,
      due_date: editingMilestoneData.due_date || null,
      is_completed: true
    })
    toast.success('Đã cập nhật cột mốc!')
    editingMilestoneId.value = null
    await fetchProjectDetail()
  } catch (err) {
    console.error('Failed to update milestone:', err)
    toast.error('Cập nhật cột mốc thất bại!')
  }
}

const handleDeleteMilestone = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa cột mốc này?')) return
  try {
    await axios.delete(`/api/milestones/${id}`)
    toast.success('Đã xóa cột mốc!')
    await fetchProjectDetail()
  } catch (err) {
    console.error('Failed to delete milestone:', err)
    toast.error('Xóa cột mốc thất bại!')
  }
}

// Task edit/delete handlers
const startEditTask = (task) => {
  editingTaskId.value = task.id
  editingTaskData.title = task.title
  editingTaskData.assignee_id = task.assignee_id || ''
  editingTaskData.due_date = task.due_date ? new Date(task.due_date).toISOString().substring(0, 10) : ''
}

const cancelEditTask = () => {
  editingTaskId.value = null
}

const handleUpdateTask = async (task) => {
  if (!editingTaskData.title.trim()) return
  try {
    await axios.put(`/api/tasks/${task.id}`, {
      title: editingTaskData.title,
      assignee_id: editingTaskData.assignee_id || null,
      due_date: editingTaskData.due_date || null,
      status: task.status,
      priority: task.priority
    })
    toast.success('Đã cập nhật công việc!')
    editingTaskId.value = null
    await fetchProjectDetail()
    await fetchComments()
  } catch (err) {
    console.error('Failed to update task:', err)
    toast.error('Cập nhật công việc thất bại!')
  }
}

const handleDeleteTask = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa công việc này?')) return
  try {
    await axios.delete(`/api/tasks/${id}`)
    toast.success('Đã xóa công việc!')
    await fetchProjectDetail()
    await fetchComments()
  } catch (err) {
    console.error('Failed to delete task:', err)
    toast.error('Xóa công việc thất bại!')
  }
}

const fetchCustomers = async () => {
  try {
    const res = await axios.get('/api/customers')
    customers.value = res.data.customers || res.data
  } catch (err) {
    console.error('Failed to fetch customers:', err)
  }
}

const toggleActionMenu = () => {
  isActionMenuOpen.value = !isActionMenuOpen.value
}

const handleEditProject = () => {
  isModalOpen.value = true
  isActionMenuOpen.value = false
}

const handleCloseModal = () => {
  isModalOpen.value = false
}

const canDeleteProject = computed(() => {
  if (!project.value) return false
  const hasMilestones = project.value.milestones && project.value.milestones.length > 0
  const hasTasksList = project.value.tasks && project.value.tasks.length > 0
  const realComments = activityLogs.value.filter(log => {
    return log.content && !log.content.startsWith('Đã tạo dự án mới')
  })
  const hasComments = realComments.length > 0
  return !hasMilestones && !hasTasksList && !hasComments
})

const handleDeleteProject = async () => {
  if (!canDeleteProject.value) return
  if (!confirm(`Bạn có chắc chắn muốn xóa dự án "${project.value.title}" không?`)) return
  try {
    await axios.delete(`/api/projects/${projectId}`)
    toast.success('Xóa dự án thành công!')
    router.push('/projects')
  } catch (err) {
    console.error('Failed to delete project:', err)
    toast.error(err.response?.data?.message || 'Không thể xóa dự án.')
  }
}

const handleUpdateProjectSubmit = async (data) => {
  try {
    await axios.put(`/api/projects/${projectId}`, data)
    toast.success('Cập nhật dự án thành công!')
    await fetchProjectDetail()
    isModalOpen.value = false
  } catch (err) {
    console.error('Failed to update project:', err)
    toast.error('Cập nhật dự án thất bại!')
  }
}

const handleMouseUp = (e) => {
  if (leadDropdownRef.value && !leadDropdownRef.value.contains(e.target)) {
    isLeadSelectOpen.value = false
  }
  if (actionMenuDropdownRef.value && !actionMenuDropdownRef.value.contains(e.target)) {
    isActionMenuOpen.value = false
  }
}

// Helpers
const statusDotClass = (status) => {
  if (status === 'yellow') return 'bg-amber-400 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  if (status === 'green') return 'bg-emerald-500 health-dot-green'
  return 'bg-gray-400'
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
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
  Promise.all([
    fetchProjectDetail(),
    fetchUsers(),
    fetchComments(),
    fetchCustomers()
  ])
  window.addEventListener('mouseup', handleMouseUp)
})

onUnmounted(() => {
  window.removeEventListener('mouseup', handleMouseUp)
})
</script>
