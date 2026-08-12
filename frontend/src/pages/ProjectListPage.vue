<template>
  <div class="min-h-screen bg-[#f4f5f0] flex flex-col justify-between pb-24">
    <div>
      <!-- Navbar Component -->
      <Navbar @search="handleSearch" />

      <!-- Main Container -->
      <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Premium Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
          <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight leading-none font-heading">
              Dự án
            </h1>
          </div>
          
          <div class="flex items-center gap-2">
            <!-- Add Project Button -->
            <button
              @click="isModalOpen = true"
              type="button"
              class="px-5 py-2.5 bg-[#10b981] hover:bg-emerald-600 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-2xs cursor-pointer"
            >
              <i class="fa-solid fa-plus text-xs"></i>
              <span>Tạo dự án</span>
            </button>
            
            <!-- Search Toggle Button -->
            <button
              @click="isSearchOpen = !isSearchOpen"
              type="button"
              class="w-10 h-10 border border-gray-200 hover:bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 transition-colors cursor-pointer"
            >
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>

        <!-- Search Bar Drawer -->
        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform opacity-0 -translate-y-2"
          enter-to-class="transform opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform opacity-100 translate-y-0"
          leave-to-class="transform opacity-0 -translate-y-2"
        >
          <div v-if="isSearchOpen" class="mb-8 bg-gray-50/50 p-4 border border-gray-200/60 rounded-2xl">
            <div class="relative max-w-md">
              <input
                v-model="searchQueryLocal"
                @input="handleSearchLocal"
                type="text"
                placeholder="Tìm kiếm dự án theo tên hoặc khách hàng..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white"
              />
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
              </span>
            </div>
          </div>
        </transition>

        <!-- Main Desktop Table Card Container -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-3xs select-none">
          <div class="hidden md:block">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-100 text-xs font-black text-gray-450 uppercase tracking-wider bg-gray-50/50">
                  <th scope="col" class="py-4 px-6 text-center select-none w-12">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4.5 h-4.5"
                    />
                  </th>
                  <th scope="col" class="py-4 px-6 w-44">Time</th>
                  <th scope="col" class="py-4 px-6 w-56">Relationship</th>
                  <th scope="col" class="py-4 px-6 min-w-[240px]">Project</th>
                  <th scope="col" class="py-4 px-6 w-32 text-center">Health</th>
                  <th scope="col" class="py-4 px-6 w-48 text-center">Lead</th>
                  <th scope="col" class="py-4 px-6 min-w-[320px]">Update</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Skeleton Loading Rows -->
                <template v-if="projectStore.isLoading && displayedProjects.length === 0">
                  <tr v-for="i in 5" :key="'skeleton-' + i" class="animate-pulse">
                    <td class="py-5 px-6 text-center align-middle">
                      <div class="w-4.5 h-4.5 bg-gray-200 rounded-md mx-auto"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-md w-28"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-md w-32"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-md w-40"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-full w-12"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-md w-28"></div>
                    </td>
                    <td class="py-5 px-6">
                      <div class="h-4 bg-gray-200 rounded-md w-64"></div>
                    </td>
                  </tr>
                </template>

                <!-- Empty state -->
                <tr v-else-if="displayedProjects.length === 0">
                  <td colspan="7" class="py-16 text-center text-gray-400 font-medium">
                    Không tìm thấy dự án nào trong mục này.
                  </td>
                </tr>

                <!-- Project List Rows -->
                <tr
                  v-for="(project, index) in displayedProjects"
                  :key="project.id"
                  class="hover:bg-emerald-50/10 transition-colors group animate-fade-in-up cursor-pointer"
                  :class="{ 
                    'bg-emerald-50/20 hover:bg-emerald-50/30': selectedProjectIds.includes(project.id),
                    'relative z-30': openLeadMenuId === project.id || openStatusMenuId === project.id
                  }"
                  :style="{ animationDelay: `${index * 35}ms` }"
                  @mousedown="handleMouseDown($event, project)"
                  @mouseenter="handleMouseEnter(project)"
                  @click="goToProjectDetail(project.id, $event)"
                >
                  <!-- Checkbox Selection -->
                  <td class="py-4 px-6 text-center align-middle select-none w-12" @click.stop>
                    <input
                      type="checkbox"
                      :checked="selectedProjectIds.includes(project.id)"
                      @change="toggleProjectSelection(project.id)"
                      class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4.5 h-4.5"
                    />
                  </td>

                  <!-- Time Column -->
                  <td class="py-4 px-6 align-middle">
                    <div class="min-w-[100px]">
                      <div :class="formatTimeColumn(project.last_activity_at || project.updated_at).topBold ? 'font-bold text-gray-900 text-sm' : 'text-xs text-gray-400 font-semibold'">
                        {{ formatTimeColumn(project.last_activity_at || project.updated_at).top }}
                      </div>
                      <div :class="formatTimeColumn(project.last_activity_at || project.updated_at).bottomBold ? 'font-bold text-gray-900 text-sm mt-0.5' : 'text-xs text-gray-400 font-semibold mt-0.5'">
                        {{ formatTimeColumn(project.last_activity_at || project.updated_at).bottom }}
                      </div>
                    </div>
                  </td>

                  <!-- Relationship Column -->
                  <td class="py-4 px-6 max-w-[220px]">
                    <div class="flex items-center gap-2.5">
                      <span class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center font-extrabold text-[11px] shadow-3xs flex-shrink-0">
                        {{ project.customer ? project.customer.name[0] : 'K' }}
                      </span>
                      <span class="text-gray-900 text-sm font-bold truncate">
                        {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                      </span>
                    </div>
                  </td>

                  <!-- Project Column -->
                  <td class="py-4 px-6 max-w-[300px] min-w-[200px]">
                    <div class="font-extrabold text-gray-900 text-sm leading-snug group-hover:text-emerald-700 transition-colors flex items-start gap-2 min-w-0">
                      <span v-if="project.tracking_status === 'completed'" class="w-2.5 h-2.5 rounded-full bg-emerald-500 flex-shrink-0 mt-1" title="Hoàn thành"></span>
                      <span v-else-if="project.tracking_status === 'following'" class="w-2.5 h-2.5 rounded-full bg-amber-400 flex-shrink-0 mt-1" title="Đang theo"></span>
                      <span v-else-if="project.tracking_status === 'not_following'" class="w-2.5 h-2.5 rounded-full bg-rose-500 flex-shrink-0 mt-1" title="Không theo"></span>
                      <span class="break-all min-w-0 flex-1">{{ project.title }}</span>
                    </div>
                  </td>

                  <!-- Health Selector (Smiley only) -->
                  <td class="py-4 px-6 align-middle text-center">
                    <div class="inline-flex justify-center">
                      <HealthStatusSelector
                        :model-value="project.health"
                        @change="(newColor) => handleHealthChange(project.id, newColor)"
                      />
                    </div>
                  </td>

                  <!-- Lead Info -->
                  <td class="py-4 px-6 align-middle relative text-center" @click.stop>
                    <div
                      @click="toggleLeadMenu(project.id)"
                      class="inline-flex items-center gap-2 px-2.5 py-1.5 hover:bg-emerald-50/70 border border-transparent hover:border-emerald-100 rounded-xl transition-all cursor-pointer select-none font-bold justify-center"
                    >
                      <img
                        :src="project.lead ? (project.lead.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120') : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                        :alt="project.lead ? project.lead.name : 'Chưa giao'"
                        class="w-6.5 h-6.5 rounded-full object-cover border border-emerald-100 shadow-3xs"
                      />
                      <span class="text-gray-808 text-sm font-bold border-b border-dashed border-gray-300 pb-0.5">
                        {{ project.lead ? project.lead.name : 'Chưa giao' }}
                      </span>
                    </div>

                    <!-- Lead Dropdown Selection -->
                    <div
                      v-if="openLeadMenuId === project.id"
                      class="absolute left-1/2 -translate-x-1/2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5 max-h-48 overflow-y-auto"
                      :class="index === 0 ? 'top-full mt-1.5' : 'bottom-full mb-1.5'"
                    >
                      <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider select-none border-b border-gray-100 mb-1">
                        Chuyển lead dự án
                      </div>
                      <button
                        @click="handleUpdateLead(project.id, null)"
                        type="button"
                        class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-500 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer"
                      >
                        <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px]">
                          <i class="fa-solid fa-user-slash text-gray-400"></i>
                        </span>
                        <span>Không giao cho ai</span>
                      </button>
                      <button
                        v-for="u in projectStore.users"
                        :key="u.id"
                        @click="handleUpdateLead(project.id, u.id)"
                        type="button"
                        class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer"
                      >
                        <img :src="u.avatar" :alt="u.name" class="w-6 h-6 rounded-full object-cover" />
                        <span class="truncate flex-1">{{ u.name }}</span>
                        <i v-if="project.lead && project.lead.id === u.id" class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                      </button>
                    </div>
                  </td>

                  <!-- Update (Latest Comment) -->
                  <td class="py-4 px-6 max-w-[450px]">
                    <div v-if="getLatestComment(project)">
                      <div class="font-bold text-gray-955 text-sm leading-snug break-words line-clamp-1 max-w-[400px]">
                        {{ getLatestComment(project).content }}
                      </div>
                      <div class="text-xs text-gray-400 font-semibold mt-1">
                        {{ getLatestComment(project).user ? getLatestComment(project).user.name : 'Thành viên' }}
                      </div>
                    </div>
                    <div v-else>
                      <div class="font-bold text-gray-400 text-sm leading-snug">
                        Chưa có hoạt động nào mới.
                      </div>
                      <div class="text-xs text-gray-400 font-semibold mt-1">
                        Hệ thống
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Phone View: Mobile Card List -->
          <div class="block md:hidden divide-y divide-gray-100 bg-white">
            <template v-if="projectStore.isLoading && displayedProjects.length === 0">
              <div v-for="i in 3" :key="'skeleton-card-' + i" class="p-5 animate-pulse space-y-3">
                <div class="h-4 bg-gray-250 rounded-md w-2/3"></div>
              </div>
            </template>

            <div v-else-if="displayedProjects.length === 0" class="py-12 text-center text-gray-400 font-medium">
              Không tìm thấy dự án nào trong mục này.
            </div>

            <div
              v-for="project in displayedProjects"
              :key="'card-' + project.id"
              class="p-5 hover:bg-emerald-50/10 transition-colors flex flex-col gap-3 relative cursor-pointer"
              :class="{ 'bg-emerald-50/20': selectedProjectIds.includes(project.id) }"
              @click="goToProjectDetail(project.id, $event)"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="font-extrabold text-gray-900 text-sm leading-snug break-all min-w-0">
                    {{ project.title }}
                  </h3>
                  <p class="text-xs text-gray-400 font-bold mt-0.5">
                    {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                  </p>
                </div>
                <input
                  type="checkbox"
                  :checked="selectedProjectIds.includes(project.id)"
                  @change="toggleProjectSelection(project.id)"
                  @click.stop
                  class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4 h-4 mt-1"
                />
              </div>

              <div class="flex items-center text-xs text-gray-500 pt-1 border-t border-gray-50">
                <HealthStatusSelector
                  :model-value="project.health"
                  @change="(newColor) => handleHealthChange(project.id, newColor)"
                />
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
            <div class="text-xs text-gray-500 font-semibold">
              Trang {{ currentPage }} / {{ totalPages }} ({{ projectStore.projects.length }} dự án)
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                type="button"
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-bold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
                :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700'"
              >
                <i class="fa-solid fa-chevron-left"></i>
              </button>
              
              <div class="flex items-center gap-1">
                <template v-for="page in totalPages" :key="page">
                  <button
                    v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                    @click="goToPage(page)"
                    type="button"
                    class="w-8 h-8 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                    :class="page === currentPage ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'"
                  >
                    {{ page }}
                  </button>
                  <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-1 text-gray-400">...</span>
                </template>
              </div>
              
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                type="button"
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-bold transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
                :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700'"
              >
                <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
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

    <!-- Floating Bulk Update Action Bar (Command Bar at TOP matching image 2) -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-10 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100 scale-100"
      leave-to-class="transform -translate-y-10 opacity-0 scale-95"
    >
      <div
        v-if="selectedProjectIds.length > 0"
        class="fixed top-20 sm:top-[88px] left-1/2 -translate-x-1/2 z-50 bg-[#fafaf7] sm:bg-white/95 backdrop-blur-md px-3.5 py-2.5 sm:px-6 sm:py-3 rounded-2xl shadow-2xl border border-gray-200/90 flex items-center gap-2.5 sm:gap-4 max-w-4xl select-none transition-all"
      >
        <!-- LEFT: COUNT BADGE & TEXT -->
        <div class="flex items-center gap-2 sm:gap-2.5">
          <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#fbd37d] text-gray-900 font-extrabold flex items-center justify-center text-xs sm:text-sm flex-shrink-0 shadow-2xs">
            {{ selectedProjectIds.length }}
          </div>
          <div class="flex flex-col items-start leading-none">
            <span class="text-xs sm:text-sm font-extrabold text-gray-900 leading-tight whitespace-nowrap">Đã chọn {{ selectedProjectIds.length }} dự án</span>
            <button @click="selectedProjectIds = []" type="button" class="text-[10px] sm:text-[11px] text-gray-400 hover:text-gray-600 font-bold cursor-pointer leading-tight mt-0.5">Bỏ chọn</button>
          </div>
        </div>

        <!-- DIVIDER -->
        <div class="h-6 w-px bg-gray-200 flex-shrink-0 mx-0.5 sm:mx-1"></div>

        <!-- DESKTOP STATUS PILL BUTTONS (hidden on mobile) -->
        <div class="hidden sm:flex items-center gap-2">
          <button
            @click="selectBulkStatusOption('following')"
            type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'following' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'"
          >
            <i class="fa-solid fa-flag text-xs"></i>
            <span>Đang theo</span>
          </button>

          <button
            @click="selectBulkStatusOption('not_following')"
            type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'not_following' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'"
          >
            <i class="fa-solid fa-eye-slash text-xs"></i>
            <span>Không theo</span>
          </button>

          <button
            @click="selectBulkStatusOption('completed')"
            type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'completed' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'"
          >
            <i class="fa-regular fa-circle-check text-xs"></i>
            <span>Hoàn thành</span>
          </button>
        </div>

        <!-- MOBILE STATUS DROPDOWN MENU (shown on sm:hidden) -->
        <div class="sm:hidden block relative">
          <button 
            @click="isMobileStatusDropdownOpen = !isMobileStatusDropdownOpen" 
            type="button" 
            class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-800 font-extrabold text-xs rounded-xl flex items-center gap-1 shadow-2xs cursor-pointer"
          >
            <span>{{ getBulkStatusLabel(selectedBulkStatus) }}</span>
            <i class="fa-solid fa-chevron-down text-[9px] text-gray-500"></i>
          </button>

          <div 
            v-if="isMobileStatusDropdownOpen" 
            class="absolute top-full mt-2 left-0 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-1.5 min-w-[135px] flex flex-col gap-1 ring-1 ring-black/5"
          >
            <button @click="selectBulkStatusOption('following')" type="button" class="px-2.5 py-1.5 hover:bg-amber-50 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-solid fa-flag text-amber-500 text-xs"></i>
              <span>Đang theo</span>
            </button>
            <button @click="selectBulkStatusOption('not_following')" type="button" class="px-2.5 py-1.5 hover:bg-gray-100 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-solid fa-eye-slash text-gray-500 text-xs"></i>
              <span>Không theo</span>
            </button>
            <button @click="selectBulkStatusOption('completed')" type="button" class="px-2.5 py-1.5 hover:bg-emerald-50 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-regular fa-circle-check text-emerald-600 text-xs"></i>
              <span>Hoàn thành</span>
            </button>
          </div>
        </div>

        <!-- DIVIDER -->
        <div class="h-6 w-px bg-gray-200 flex-shrink-0 mx-0.5 sm:mx-1"></div>

        <!-- RIGHT: SUBMIT "HÚ HÚ" BUTTON -->
        <button
          @click="goToBulkUpdate"
          type="button"
          class="px-3.5 py-1.5 sm:px-5 sm:py-2.5 bg-[#10b981] hover:bg-emerald-600 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 sm:gap-2 shadow-xs transition-colors cursor-pointer flex-shrink-0"
        >
          <i class="fa-solid fa-dove text-sm"></i>
          <span>Hú Hú</span>
        </button>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import HealthStatusSelector from '../components/HealthStatusSelector.vue'
import ProjectModal from '../components/ProjectModal.vue'
import { useProjectStore } from '../stores/project'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'
import { useAuthStore } from '../stores/auth'

const projectStore = useProjectStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToastStore()
const confirmStore = useConfirmStore()

const statusDotClass = (health) => {
  if (health === 'yellow') return 'bg-amber-400'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-emerald-500'
  return 'bg-gray-400'
}

const getLatestComment = (project) => {
  if (!project.comments || project.comments.length === 0) return null
  const sorted = [...project.comments].sort((a, b) => b.id - a.id)
  return sorted[0]
}

const formatTimeColumn = (dateStr) => {
  if (!dateStr) return { top: 'Today', bottom: '' }
  const date = new Date(dateStr)
  const today = new Date()
  const yesterday = new Date()
  yesterday.setDate(today.getDate() - 1)

  const pad = (n) => String(n).padStart(2, '0')
  const hours = date.getHours()
  const minutes = pad(date.getMinutes())
  const ampm = hours >= 12 ? 'PM' : 'AM'
  const displayHours = hours % 12 || 12
  const timeStr = `${displayHours}:${minutes} ${ampm}`

  if (date.toDateString() === today.toDateString()) {
    return { top: timeStr, bottom: 'Today', topBold: true }
  } else if (date.toDateString() === yesterday.toDateString()) {
    return { top: 'Yesterday', bottom: timeStr, bottomBold: true }
  } else {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    const month = months[date.getMonth()]
    const day = date.getDate()
    return { top: `${month} ${day}`, bottom: timeStr, bottomBold: true }
  }
}

const openActionMenuId = ref(null)
const openLeadMenuId = ref(null)
const openStatusMenuId = ref(null)
const editingProject = ref(null)

const isSearchOpen = ref(false)
const searchQueryLocal = ref('')
const isMoreMenuOpen = ref(false)
const activeBulkMenu = ref(null)

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

const toggleStatusMenu = (projectId) => {
  if (openStatusMenuId.value === projectId) {
    openStatusMenuId.value = null
  } else {
    openStatusMenuId.value = projectId
    openActionMenuId.value = null
    openLeadMenuId.value = null
  }
}

const handleUpdateStatus = async (projectId, status) => {
  try {
    await axios.put(`/api/projects/${projectId}`, { tracking_status: status })
    toast.success('Đã cập nhật trạng thái dự án!')
    await projectStore.fetchProjects(true)
    openStatusMenuId.value = null
  } catch (err) {
    console.error('Failed to update project status:', err)
    toast.error('Cập nhật trạng thái thất bại!')
  }
}

const toggleBulkMenu = (menu) => {
  activeBulkMenu.value = activeBulkMenu.value === menu ? null : menu
}

const bulkUpdateHealth = async (color) => {
  // Save selected IDs before clearing
  const idsToUpdate = [...selectedProjectIds.value]
  
  // Optimistically update local project state
  idsToUpdate.forEach(id => {
    const p = projectStore.projects.find(proj => proj.id === id)
    if (p) {
      p.health = color
    }
  })

  // Show success message immediately (optimistic update already done)
  toast.success(`Đã cập nhật sức khỏe cho ${idsToUpdate.length} dự án!`)
  selectedProjectIds.value = []
  activeBulkMenu.value = null

  try {
    // Fire and forget - update in background
    await Promise.all(idsToUpdate.map(id => 
      axios.patch(`/api/projects/${id}/health`, { health: color })
    ))
    // ❌ REMOVED: No need to fetch all projects again
  } catch (err) {
    console.error('Failed bulk update health:', err)
    toast.error('Cập nhật thất bại!')
    // On error, refresh to get correct state from server
    await projectStore.fetchProjects(true)
  }
}

const bulkUpdateLead = async (userId) => {
  try {
    await Promise.all(selectedProjectIds.value.map(id => 
      axios.put(`/api/projects/${id}`, { lead_id: userId })
    ))
    toast.success(`Đã chuyển lead cho ${selectedProjectIds.value.length} dự án!`)
    await projectStore.fetchProjects(true)
    selectedProjectIds.value = []
    activeBulkMenu.value = null
  } catch (err) {
    console.error('Failed bulk update lead:', err)
    toast.error('Chuyển lead thất bại!')
  }
}

const selectedBulkStatus = ref('following')
const isMobileStatusDropdownOpen = ref(false)

const getBulkStatusLabel = (status) => {
  if (status === 'following') return '🚩 Đang theo'
  if (status === 'not_following') return '🙈 Không theo'
  if (status === 'completed') return '✔️ Hoàn thành'
  return '🚩 Đang theo'
}

const selectBulkStatusOption = (status) => {
  selectedBulkStatus.value = status
  isMobileStatusDropdownOpen.value = false
  bulkUpdateStatus(status)
}

const bulkUpdateStatus = async (status) => {
  try {
    await Promise.all(selectedProjectIds.value.map(id => 
      axios.put(`/api/projects/${id}`, { tracking_status: status })
    ))
    toast.success(`Đã cập nhật trạng thái cho ${selectedProjectIds.value.length} dự án!`)
    await projectStore.fetchProjects(true)
    selectedProjectIds.value = []
    activeBulkMenu.value = null
  } catch (err) {
    console.error('Failed bulk update status:', err)
    toast.error('Cập nhật trạng thái thất bại!')
  }
}

const goToBulkUpdate = () => {
  if (selectedProjectIds.value.length === 0) {
    toast.error('Vui lòng chọn ít nhất một dự án!')
    return
  }
  router.push(`/projects/update?ids=${selectedProjectIds.value.join(',')}`)
}

const statusBadgeClass = (status) => {
  if (status === 'following') return 'bg-[#eef8f0] text-[#2d8a39] border-[#d8eedf]'
  if (status === 'not_following') return 'bg-[#fff7ed] text-[#c2410c] border-[#ffedd5]'
  if (status === 'completed') return 'bg-[#f3f4f6] text-[#374151] border-[#e5e7eb]'
  return 'bg-gray-50 text-gray-600 border-gray-250'
}

const statusText = (status) => {
  if (status === 'following') return 'Đang theo'
  if (status === 'not_following') return 'Không theo'
  if (status === 'completed') return 'Hoàn thành'
  return status
}

const getProjectIconAndBg = (title) => {
  const t = title.toLowerCase()
  if (t.includes('máy chủ') || t.includes('server')) {
    return { icon: 'fa-solid fa-server', text: 'text-emerald-600', bg: 'bg-emerald-50 border-emerald-100' }
  }
  if (t.includes('sao lưu') || t.includes('backup') || t.includes('cloud') || t.includes('dữ liệu')) {
    if (t.includes('báo cáo') || t.includes('phân tích')) {
      return { icon: 'fa-solid fa-chart-line', text: 'text-teal-600', bg: 'bg-teal-50 border-teal-100' }
    }
    return { icon: 'fa-solid fa-cloud', text: 'text-blue-600', bg: 'bg-blue-50 border-blue-100' }
  }
  if (t.includes('wifi') || t.includes('mạng') || t.includes('network')) {
    return { icon: 'fa-solid fa-wifi', text: 'text-amber-500', bg: 'bg-amber-50 border-amber-100' }
  }
  if (t.includes('camera') || t.includes('video') || t.includes('hình ảnh')) {
    return { icon: 'fa-solid fa-video', text: 'text-purple-600', bg: 'bg-purple-50 border-purple-100' }
  }
  if (t.includes('bảo mật') || t.includes('security') || t.includes('shield') || t.includes('an toàn')) {
    return { icon: 'fa-solid fa-shield-halved', text: 'text-rose-500', bg: 'bg-rose-50 border-rose-100' }
  }
  const hash = title.length % 5
  if (hash === 0) return { icon: 'fa-solid fa-diagram-project', text: 'text-indigo-600', bg: 'bg-indigo-50 border-indigo-100' }
  if (hash === 1) return { icon: 'fa-solid fa-list-check', text: 'text-cyan-600', bg: 'bg-cyan-50 border-cyan-100' }
  if (hash === 2) return { icon: 'fa-solid fa-briefcase', text: 'text-slate-600', bg: 'bg-slate-50 border-slate-100' }
  if (hash === 3) return { icon: 'fa-solid fa-cubes', text: 'text-emerald-600', bg: 'bg-emerald-50 border-emerald-100' }
  return { icon: 'fa-solid fa-laptop-code', text: 'text-fuchsia-600', bg: 'bg-fuchsia-50 border-fuchsia-100' }
}

const closeAllActionMenus = (e) => {
  openActionMenuId.value = null
  openLeadMenuId.value = null
  openStatusMenuId.value = null
  if (e && e.target) {
    const floatingBar = document.querySelector('.fixed.bottom-6')
    if (floatingBar && !floatingBar.contains(e.target)) {
      activeBulkMenu.value = null
    }
    const moreMenuBtn = document.querySelector('.relative button')
    const moreMenuDropdown = document.querySelector('.relative div')
    if (moreMenuBtn && !moreMenuBtn.contains(e.target) && moreMenuDropdown && !moreMenuDropdown.contains(e.target)) {
      isMoreMenuOpen.value = false
    }
  }
}

const goToProjectDetail = (projectId, event) => {
  if (event.ctrlKey || event.metaKey) return
  router.push(`/projects/${projectId}`)
}
const isModalOpen = ref(false)

const selectedProjectIds = ref([])
const isDragging = ref(false)
const dragStartVal = ref(true)

// Pagination state
const displayLimit = ref(20)
const currentPage = ref(1)
const itemsPerPage = 20

const displayedProjects = computed(() => {
  let list = projectStore.projects
  if (route.query.lead) {
    const leadId = Number(route.query.lead)
    list = list.filter(p => p.lead_id === leadId)
  }
  
  // Calculate pagination
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return list.slice(start, end)
})

const totalPages = computed(() => {
  let list = projectStore.projects
  if (route.query.lead) {
    const leadId = Number(route.query.lead)
    list = list.filter(p => p.lead_id === leadId)
  }
  return Math.ceil(list.length / itemsPerPage)
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

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
  if (!dateStr) return 'vừa xong'
  const date = new Date(dateStr)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'vừa xong'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  const diffWeeks = Math.floor(diffDays / 7)
  return `${diffWeeks}w ago`
}

onMounted(() => {
  projectStore.activeStatus = null
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
