<template>
  <div class="min-h-screen bg-[#f8faf9] flex flex-col pb-12">
    <!-- Navbar Component -->
    <Navbar @search="handleSearch" />

    <!-- Main Container -->
    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 w-full">
      
      <!-- Header (Top Center) -->
      <div class="flex flex-col items-center text-center mt-1 mb-6 select-none">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight font-heading mb-1">Help IT Managers become badass.</h1>
        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">You must think about how IT managers appear in the eyes of their audiences</p>
      </div>

      <!-- Main Layout Grid: 12-col Grid (Left Panel 3 | Center Panel 5 | Right Panel 4) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT PANEL: Actions, Switcher & Search (Block 1) -->
        <aside class="lg:col-span-3 space-y-3.5 select-none">
          <!-- Button Tạo dự án -->
          <button
            @click="isModalOpen = true"
            type="button"
            class="w-full bg-[#10b981] hover:bg-emerald-600 text-white font-extrabold text-sm rounded-xl p-3.5 flex items-center justify-center gap-2.5 transition-colors shadow-3xs cursor-pointer focus:outline-none select-none"
            title="Tạo dự án mới (Ctrl + K)"
          >
            <i class="fa-solid fa-square-plus text-base"></i>
            <span>Tạo dự án</span>
          </button>

          <!-- Project / Customer Switcher (Simple Button) -->
          <button
            @click="toggleCustomerGroup"
            type="button"
            class="w-full bg-[#f1f5f9] hover:bg-[#e2e8f0] border border-gray-200 text-slate-800 font-extrabold text-sm rounded-xl p-3.5 flex items-center justify-between transition-colors shadow-3xs cursor-pointer focus:outline-none select-none"
            :class="isGroupedByCustomer ? 'ring-2 ring-emerald-500/30 border-emerald-500 bg-emerald-50/40 text-emerald-950' : ''"
            title="Chuyển đổi chế độ xem (Ctrl + B)"
          >
            <div class="flex items-center gap-2.5">
              <i class="fa-solid fa-right-left text-xs" :class="isGroupedByCustomer ? 'text-emerald-600' : 'text-slate-500'"></i>
              <span>Project / Customer Switcher</span>
            </div>
            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md" :class="isGroupedByCustomer ? 'bg-emerald-200/80 text-emerald-900' : 'bg-gray-200 text-gray-600'">
              {{ isGroupedByCustomer ? 'Theo Khách Hàng' : 'Mặc định' }}
            </span>
          </button>

          <!-- Tìm kiếm gì đó -->
          <div class="relative shadow-3xs rounded-xl overflow-hidden">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              ref="searchInputRef"
              v-model="projectStore.searchQuery"
              type="text"
              placeholder="Tìm kiếm gì đó (Ctrl + F)"
              class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-3.5 text-sm font-bold focus:outline-none focus:border-emerald-500 placeholder-gray-400"
            />
          </div>

          <!-- Keyboard Shortcuts Hint -->
          <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-3xs">
            <div class="flex items-center gap-2 mb-2">
              <i class="fa-solid fa-keyboard text-emerald-600 text-sm"></i>
              <span class="text-xs font-black text-gray-900 uppercase tracking-wider">Phím tắt</span>
            </div>
            <div class="space-y-1.5 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Tạo dự án mới</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl + K</kbd>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Tìm kiếm</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl + F</kbd>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Chọn tất cả</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl + A</kbd>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Bỏ chọn tất cả</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl + Shift + A</kbd>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Chuyển đổi view</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl + B</kbd>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 font-semibold">Đóng/Thoát</span>
                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">ESC</kbd>
              </div>
            </div>
          </div>
        </aside>

        <!-- CENTER PANEL: Projects List (Block 2 - Wider Column) -->
        <section class="lg:col-span-5 space-y-3.5 select-none">

          <!-- Skeleton Loading State -->
          <div v-if="projectStore.isLoading && displayedProjects.length === 0" class="space-y-3 max-h-[calc(100vh-200px)]">
            <div v-for="i in 3" :key="'sk-proj-' + i" class="rounded-2xl p-4 bg-white/80 border border-gray-200 animate-pulse flex items-center justify-between h-20 shadow-3xs">
              <div class="flex items-center gap-3.5 flex-1">
                <div class="w-4.5 h-4.5 bg-gray-200 rounded"></div>
                <div class="space-y-2 flex-1">
                  <div class="w-1/2 h-4 bg-gray-200 rounded-md"></div>
                  <div class="w-1/4 h-3 bg-gray-150 rounded-md"></div>
                </div>
              </div>
              <div class="w-16 h-5 bg-gray-200 rounded-md"></div>
            </div>
          </div>

          <!-- Grouped by Customer Mode (Matches Mockup) -->
          <div 
            v-else-if="isGroupedByCustomer" 
            ref="scrollContainerGrouped"
            class="space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto pr-1 scrollbar-none"
          >
            <div v-for="group in projectsByCustomer" :key="group.name" class="space-y-2.5">
              <!-- Customer Header -->
              <div class="flex items-center gap-2 pt-1 select-none">
                <h3 class="text-xl font-black text-gray-900 tracking-tight font-heading">{{ group.name }}</h3>
                <button 
                  @click.stop="togglePinCustomer(group.name)"
                  type="button"
                  class="p-1 transition-colors hover:opacity-80"
                  :title="group.is_pinned ? 'Bỏ ghim khách hàng' : 'Ghim khách hàng'"
                >
                  <i 
                    class="text-base transition-colors"
                    :class="group.is_pinned ? 'fa-solid fa-star text-amber-500' : 'fa-regular fa-star text-gray-400 hover:text-amber-500'"
                  ></i>
                </button>
              </div>

              <!-- Customer Projects List -->
              <div class="space-y-2">
                <div
                  v-for="(project, pIdx) in group.projects"
                  :key="project.id"
                  :data-project-id="project.id"
                  draggable="true"
                  @dragstart="onGroupedDragStart($event, project, group, pIdx)"
                  @dragover.prevent="onGroupedDragOver($event, pIdx)"
                  @dragleave="onGroupedDragLeave"
                  @drop="onGroupedDrop($event, group, pIdx)"
                  @dragend="onGroupedDragEnd"
                  class="flex items-center gap-2.5 transition-all duration-150 rounded-2xl"
                  :class="{
                    'opacity-40 scale-[0.98]': draggedGroupedIndex === pIdx && draggedGroupId === group.name,
                    'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverGroupedIndex === pIdx && dragOverGroupId === group.name && (draggedGroupedIndex !== pIdx || draggedGroupId !== group.name)
                  }"
                >
                  <!-- Drag Handle Icon -->
                  <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-emerald-600 p-1 flex-shrink-0 transition-colors select-none" title="Kéo giữ chuột để sắp xếp vị trí hàng">
                    <i class="fa-solid fa-grip-vertical text-sm"></i>
                  </div>

                  <!-- Checkbox for multi-select -->
                  <input
                    type="checkbox"
                    :checked="isSelected(project.id)"
                    @click.stop="toggleProjectSelect(project.id)"
                    class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer flex-shrink-0"
                  />

                  <!-- Colored Project Rectangular Card (Identical to default mode) -->
                  <div
                    @click="goToProjectDetail(project.id, $event)"
                    class="flex-1 rounded-2xl p-4 flex items-center justify-between gap-4 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden border min-w-0"
                    :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]"
                  >
                    <div class="min-w-0 flex-1">
                      <div class="font-extrabold text-gray-900 text-sm sm:text-base leading-snug break-all min-w-0">
                        {{ project.title }}
                      </div>
                    </div>

                    <div class="flex flex-col items-end justify-between h-11 flex-shrink-0">
                      <!-- Pin/Star Button -->
                      <button 
                        @click.stop="togglePinProject(project)" 
                        type="button" 
                        class="-mt-1 -mr-1 p-1 cursor-pointer transition-colors"
                        :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'"
                      >
                        <i 
                          class="text-sm transition-colors" 
                          :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-amber-500' : 'fa-regular fa-star text-gray-300 hover:text-amber-500'"
                        ></i>
                      </button>

                      <!-- Status Badge -->
                      <span 
                        class="text-xs font-black px-2.5 py-0.5 rounded-md"
                        :class="[getProjectStatusStyle(project).badgeClass]"
                      >
                        {{ getProjectStatusStyle(project).badgeText }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty state for grouped mode -->
            <div v-if="projectsByCustomer.length === 0" class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-450 text-sm font-semibold shadow-3xs select-none">
              Không tìm thấy dự án nào trong mục này.
            </div>
          </div>

          <!-- Default Cards list -->
          <div 
            v-else 
            ref="scrollContainerDefault"
            class="space-y-3.5 max-h-[calc(100vh-200px)] overflow-y-auto pr-1 scrollbar-none"
          >
            <transition-group
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="opacity-0 translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              <div
                v-for="(project, index) in displayedProjects"
                :key="project.id"
                :data-project-id="project.id"
                draggable="true"
                @dragstart="onDragStart($event, project, index)"
                @dragover.prevent="onDragOver($event, index)"
                @dragleave="onDragLeave($event)"
                @drop="onDrop($event, index)"
                @dragend="onDragEnd($event)"
                class="flex items-center gap-2.5 transition-all duration-150 rounded-2xl"
                :class="{
                  'opacity-40 scale-[0.98]': draggedProjectIndex === index,
                  'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverIndex === index && draggedProjectIndex !== index
                }"
              >
                <!-- Drag Handle Icon -->
                <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-emerald-600 p-1 flex-shrink-0 transition-colors select-none" title="Kéo giữ chuột để sắp xếp vị trí hàng">
                  <i class="fa-solid fa-grip-vertical text-sm"></i>
                </div>

                <!-- Checkbox for multi-select (Outside on the left) -->
                <input
                  type="checkbox"
                  :checked="isSelected(project.id)"
                  @click.stop="toggleProjectSelect(project.id)"
                  class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer flex-shrink-0"
                />

                <!-- Card Container -->
                <div
                  @click="goToProjectDetail(project.id, $event)"
                  class="flex-1 rounded-2xl p-4 flex items-center justify-between gap-4 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden border min-w-0"
                  :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]"
                >
                  <div class="min-w-0 flex-1">
                    <div class="font-extrabold text-gray-900 text-sm sm:text-base leading-snug break-all min-w-0">
                      {{ project.title }}
                    </div>
                    <div class="text-xs text-gray-500 font-bold mt-1 uppercase tracking-wider">
                      {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                    </div>
                  </div>

                  <div class="flex flex-col items-end justify-between h-11 flex-shrink-0">
                    <!-- Pin/Star Button -->
                    <button 
                      @click.stop="togglePinProject(project)" 
                      type="button" 
                      class="-mt-1 -mr-1 p-1 cursor-pointer transition-colors"
                      :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'"
                    >
                      <i 
                        class="text-sm transition-colors" 
                        :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-amber-500' : 'fa-regular fa-star text-gray-300 hover:text-amber-500'"
                      ></i>
                    </button>

                    <!-- Status Badge -->
                    <span 
                      class="text-xs font-black px-2.5 py-0.5 rounded-md"
                      :class="[getProjectStatusStyle(project).badgeClass]"
                    >
                      {{ getProjectStatusStyle(project).badgeText }}
                    </span>
                  </div>
                </div>
              </div>
            </transition-group>

            <!-- Empty projects state -->
            <div v-if="displayedProjects.length === 0" class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-450 text-sm font-semibold shadow-3xs select-none">
              Không tìm thấy dự án nào trong mục này.
            </div>

            <!-- Load More Button -->
            <div v-if="displayedProjects.length < projectStore.projects.length" class="flex justify-center pt-2">
              <button
                @click="loadMore"
                type="button"
                class="px-5 py-2.5 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-500 text-gray-700 hover:text-emerald-700 font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-2 shadow-3xs focus:outline-none"
              >
                <i class="fa-solid fa-angles-down text-[10px]"></i>
                <span>Xem thêm dự án (Còn {{ projectStore.projects.length - displayedProjects.length }} dự án)</span>
              </button>
            </div>
          </div>
        </section>

        <!-- RIGHT PANEL: Hoạt động gần đây (Block 3) -->
        <section class="lg:col-span-4 bg-white border border-gray-200 rounded-3xl p-5 shadow-3xs flex flex-col h-[calc(100vh-200px)] select-none">
          <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider pb-3 flex items-center justify-between border-b border-gray-100 flex-shrink-0">
            <span>Hoạt động gần đây</span>
            <button @click="router.push('/feed')" type="button" class="text-xs text-emerald-700 hover:underline font-bold">Chi tiết →</button>
          </h2>

          <!-- Skeleton Loading State -->
          <div v-if="isActivitiesLoading && activities.length === 0" class="space-y-3.5 flex-1 overflow-hidden pt-2">
            <div v-for="i in 4" :key="'sk-act-' + i" class="animate-pulse space-y-2 pb-3 border-b border-gray-100">
              <div class="flex justify-between items-center">
                <div class="w-1/3 h-4 bg-gray-200 rounded-md"></div>
                <div class="w-12 h-3 bg-gray-150 rounded-md"></div>
              </div>
              <div class="w-3/4 h-3 bg-gray-150 rounded-md"></div>
            </div>
          </div>

          <!-- Activity Feed List -->
          <div v-else class="space-y-3 flex-1 overflow-y-auto pr-1 pt-2 scrollbar-thin">
            <transition-group
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="opacity-0 translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              <div 
                v-for="log in activities.slice(0, 15)" 
                :key="log.id" 
                class="bg-white border border-gray-100 rounded-2xl p-3.5 hover:border-emerald-200 transition-all shadow-3xs hover:shadow-2xs"
              >
                <!-- Top Row: User Avatar/Name & Timestamp -->
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 min-w-0">
                    <img 
                      :src="log.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=60'" 
                      class="w-6 h-6 rounded-full object-cover border border-gray-200 flex-shrink-0" 
                    />
                    <span class="font-extrabold text-xs sm:text-sm text-gray-900 truncate">
                      {{ log.user ? log.user.name : 'Hệ thống' }}
                    </span>
                  </div>
                  <span class="text-[11px] text-gray-400 font-bold flex-shrink-0">
                    {{ formatCommentRelativeTime(log.created_at) }}
                  </span>
                </div>

                <!-- Project Link Pill (Sleek Truncated Badge) -->
                <div 
                  v-if="log.project" 
                  @click="goToProjectDetail(log.project.id, $event)" 
                  class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50/80 hover:bg-emerald-100/90 border border-emerald-200/60 rounded-lg text-xs font-extrabold text-emerald-800 cursor-pointer transition-all max-w-full"
                  :title="log.project.title"
                >
                  <i class="fa-solid fa-folder-closed text-[11px] text-emerald-600 flex-shrink-0"></i>
                  <span class="truncate min-w-0">{{ log.project.title }}</span>
                </div>

                <!-- Comment Content Box with Compact Image & File Attachment Pills -->
                <div class="mt-2 text-xs font-semibold text-gray-700 leading-relaxed break-words bg-gray-50/70 border border-gray-100 rounded-xl p-2.5 space-y-2">
                  <div v-if="parseCommentText(log.content)" class="whitespace-pre-line">
                    {{ parseCommentText(log.content) }}
                  </div>

                  <!-- Render Compact Image Pills -->
                  <div v-if="parseCommentImages(log.content).length > 0" class="flex flex-wrap gap-1.5 pt-0.5">
                    <button 
                      v-for="(img, imgIdx) in parseCommentImages(log.content)" 
                      :key="imgIdx" 
                      type="button"
                      @click.stop="openImagePreview(img.url)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100/90 border border-emerald-200/80 text-emerald-800 rounded-lg text-xs font-bold transition-colors cursor-pointer max-w-full select-none shadow-3xs"
                      :title="img.name"
                    >
                      <i class="fa-solid fa-image text-emerald-600 text-[11px]"></i>
                      <span class="truncate max-w-[150px]">{{ img.name }}</span>
                      <i class="fa-solid fa-expand text-[9px] text-emerald-500 ml-0.5"></i>
                    </button>
                  </div>

                  <!-- Render Compact File Pills -->
                  <div v-if="parseCommentFiles(log.content).length > 0" class="flex flex-wrap gap-1.5 pt-0.5">
                    <a 
                      v-for="(file, fIdx) in parseCommentFiles(log.content)" 
                      :key="fIdx" 
                      :href="file.url" 
                      :download="file.name" 
                      target="_blank"
                      @click.stop
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200/90 border border-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors cursor-pointer max-w-full select-none shadow-3xs"
                      :title="file.name"
                    >
                      <i class="fa-solid fa-paperclip text-slate-500 text-[11px]"></i>
                      <span class="truncate max-w-[150px]">{{ file.name }}</span>
                      <i class="fa-solid fa-download text-[9px] text-slate-400 ml-0.5"></i>
                    </a>
                  </div>
                </div>
              </div>
            </transition-group>

            <!-- Empty activities state -->
            <div v-if="activities.length === 0" class="py-12 text-center text-gray-450 text-xs font-semibold">
              Chưa có cập nhật hoạt động nào mới.
            </div>
          </div>
        </section>

      </div>

    </main>

    <!-- Drag Selection Box -->
    <div v-if="selectionBox.visible" :style="getSelectionBoxStyle"></div>

    <!-- Floating Dark Modal Bulk Option Bar at Eye Level -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-8 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100 scale-100"
      leave-to-class="transform -translate-y-8 opacity-0 scale-95"
    >
      <div
        v-if="selectedProjectIds.length > 0"
        class="fixed top-16 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 text-white backdrop-blur-lg px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-5 w-[92%] max-w-3xl justify-between select-none"
      >
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-xs shadow-md">
            {{ selectedProjectIds.length }}
          </div>
          <div>
            <div class="text-xs font-bold text-slate-200 leading-none">Dự án được chọn</div>
            <button @click="selectedProjectIds = []" type="button" class="text-[10px] text-emerald-400 hover:text-emerald-300 underline font-bold cursor-pointer leading-tight">Hủy chọn</button>
          </div>
        </div>

        <!-- Bulk Action Buttons Group -->
        <div class="flex items-center flex-wrap gap-2 flex-1 justify-end">
          

          <!-- Bulk Move Lead Option -->
          <div class="relative">
            <button
              @click="toggleBulkMenu('lead')"
              type="button"
              class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all cursor-pointer shadow-xs"
            >
              <i class="fa-solid fa-user-pen text-blue-400"></i>
              <span>Move Lead</span>
            </button>
            <div v-if="activeBulkMenu === 'lead'" class="absolute top-full mt-2 right-0 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 py-1.5 flex flex-col gap-0.5 max-h-48 overflow-y-auto min-w-[170px]">
              <div class="px-3 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 mb-1">Chuyển lead cho</div>
              <button @click="bulkUpdateLead(null)" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800 text-xs font-bold text-slate-400 text-left"><i class="fa-solid fa-user-slash"></i> Không giao</button>
              <button v-for="u in projectStore.users" :key="u.id" @click="bulkUpdateLead(u.id)" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800 text-xs font-bold text-slate-200 text-left"><img :src="u.avatar" class="w-4 h-4 rounded-full" /> {{ u.name }}</button>
            </div>
          </div>

          <!-- Bulk Status Update (Update Status) -->
          <div class="relative">
            <button
              @click="toggleBulkMenu('status')"
              type="button"
              class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all cursor-pointer shadow-xs"
            >
              <i class="fa-solid fa-box-archive text-purple-400"></i>
              <span>Update Status</span>
            </button>
            <div v-if="activeBulkMenu === 'status'" class="absolute top-full mt-2 right-0 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 p-2 flex flex-col gap-1 min-w-[150px]">
              <button @click="bulkUpdateStatus('completed')" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800 text-xs font-bold rounded-lg text-emerald-400 text-left"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 flex-shrink-0"></span> Hoàn thành</button>
              <button @click="bulkUpdateStatus('following')" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800 text-xs font-bold rounded-lg text-amber-400 text-left"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 flex-shrink-0"></span> Đang theo</button>
              <button @click="bulkUpdateStatus('not_following')" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800 text-xs font-bold rounded-lg text-rose-400 text-left"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 flex-shrink-0"></span> Bỏ theo</button>
            </div>
          </div>

          <!-- Bulk Post Update Option -->
          <div>
            <button
              @click="goToBulkUpdate"
              type="button"
              class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl flex items-center gap-2 transition-all cursor-pointer shadow-md"
            >
              <i class="fa-solid fa-message text-white"></i>
              <span>Post Update</span>
            </button>
          </div>

        </div>
      </div>
    </transition>



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

    <!-- Edit Profile Modal -->
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="isProfileModalOpen = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-gray-100">
          
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900">Chỉnh Sửa Thông Tin Tài Khoản</h3>
            <button @click="isProfileModalOpen = false" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <form @submit.prevent="handleSaveProfile" class="space-y-4">
            <div class="flex justify-center mb-2">
              <img
                :src="editForm.avatar"
                class="w-20 h-20 rounded-full object-cover border-2 border-emerald-400 shadow-md"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên tài khoản</label>
              <input
                v-model="editForm.name"
                required
                type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
              <input
                v-model="editForm.email"
                required
                type="email"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">URL Ảnh đại diện (Avatar)</label>
              <input
                v-model="editForm.avatar"
                type="url"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button
                type="button"
                @click="isProfileModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl"
              >
                Hủy
              </button>
              <button
                type="submit"
                class="px-5 py-2 bg-[#10b981] hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-2xs transition-colors cursor-pointer"
              >
                Lưu thay đổi
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- Create View Modal -->
    <div v-if="isViewModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="isViewModalOpen = false"></div>

      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 flex flex-col max-h-[85vh]">
          
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-filter text-emerald-600 text-lg"></i>
              <h3 class="text-lg font-bold text-gray-900">Tạo View Mới</h3>
            </div>
            <button @click="isViewModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <!-- Form Body -->
          <div class="p-6 space-y-4 overflow-y-auto flex-1 scrollbar-thin">
            <!-- View Name -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tên View <span class="text-rose-500">*</span></label>
              <input
                v-model="newViewName"
                type="text"
                required
                placeholder="VD: Dự án hạ tầng, Dự án camera..."
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <!-- Choose Projects (Master Data) -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Chọn các dự án hiển thị trong View này</label>
              
              <!-- Search box to filter projects inside modal -->
              <div class="relative mb-3">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input
                  v-model="modalProjectSearchQuery"
                  type="text"
                  placeholder="Tìm nhanh tên dự án..."
                  class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-emerald-500 bg-gray-50/50"
                />
              </div>

              <!-- Projects list with checkboxes -->
              <div class="border border-gray-100 rounded-xl divide-y divide-gray-100 max-h-56 overflow-y-auto scrollbar-thin">
                <label
                  v-for="p in filteredModalProjects"
                  :key="p.id"
                  class="flex items-center gap-3 px-3.5 py-2.5 hover:bg-gray-50 cursor-pointer select-none transition-colors"
                >
                  <input
                    type="checkbox"
                    :value="p.id"
                    v-model="selectedViewProjectIds"
                    class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4 h-4"
                  />
                  <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-gray-888 truncate">{{ p.title }}</div>
                    <div class="text-[10px] text-gray-400 font-semibold mt-0.5">{{ p.customer ? p.customer.name : 'Chưa phân khách hàng' }}</div>
                  </div>
                </label>

                <div v-if="filteredModalProjects.length === 0" class="p-6 text-center text-xs text-gray-400 font-medium">
                  Không tìm thấy dự án nào.
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/30">
            <button
              type="button"
              @click="isViewModalOpen = false"
              class="px-4.5 py-2 hover:bg-gray-100 text-gray-600 font-semibold text-xs rounded-xl transition-colors cursor-pointer"
            >
              Hủy bỏ
            </button>
            <button
              type="button"
              @click="saveNewView"
              :disabled="!newViewName.trim()"
              class="px-5 py-2 bg-[#10b981] hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-bold text-xs rounded-xl transition-colors shadow-2xs cursor-pointer"
            >
              Tạo View
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div v-if="activePreviewImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" @click="activePreviewImage = null">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
        <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
        <button 
          @click="activePreviewImage = null" 
          type="button" 
          class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 hover:bg-slate-900 text-white rounded-full flex items-center justify-center transition-colors shadow-lg cursor-pointer"
        >
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
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
const toast = useToastStore()
const confirmStore = useConfirmStore()

// Views & filter states
const activeViewId = ref('all')
const isSidebarCollapsed = ref(false)

const customViews = ref([])
const isViewModalOpen = ref(false)
const newViewName = ref('')
const selectedViewProjectIds = ref([])
const modalProjectSearchQuery = ref('')

const openViewModal = () => {
  newViewName.value = ''
  selectedViewProjectIds.value = []
  modalProjectSearchQuery.value = ''
  isViewModalOpen.value = true
}

const filteredModalProjects = computed(() => {
  const q = modalProjectSearchQuery.value.trim().toLowerCase()
  if (!q) return projectStore.projects
  return projectStore.projects.filter(p => p.title.toLowerCase().includes(q))
})

const getProjectsForView = (view) => {
  return projectStore.projects.filter(p => view.projectIds.includes(p.id))
}

const saveNewView = () => {
  const name = newViewName.value.trim()
  if (!name) return

  const viewId = 'view_' + Date.now()
  const newView = {
    id: viewId,
    name: name,
    projectIds: [...selectedViewProjectIds.value]
  }

  customViews.value.push(newView)
  localStorage.setItem('custom_views', JSON.stringify(customViews.value))
  activeViewId.value = viewId
  isViewModalOpen.value = false
  toast.success('Tạo View mới thành công!')
}

const loadCustomViews = () => {
  const stored = localStorage.getItem('custom_views')
  if (stored) {
    try {
      customViews.value = JSON.parse(stored)
    } catch (e) {
      console.error('Failed to parse custom views:', e)
    }
  }
}

const deleteCustomView = (id) => {
  customViews.value = customViews.value.filter(v => v.id !== id)
  localStorage.setItem('custom_views', JSON.stringify(customViews.value))
  if (activeViewId.value === id) {
    activeViewId.value = 'all'
  }
  toast.success('Đã xóa View!')
}

const openLeadMenuId = ref(null)
const editingProject = ref(null)
const isModalOpen = ref(false)

// Search input ref for keyboard shortcuts
const searchInputRef = ref(null)
const scrollContainerDefault = ref(null)
const scrollContainerGrouped = ref(null)

// Profile dropdown & modal states
const isProfileDropdownOpen = ref(false)
const isProfileModalOpen = ref(false)
const profileDropdownRef = ref(null)

const currentUser = computed(() => authStore.user || {
  name: 'Minh',
  email: 'minh@xuongrong.vn',
  avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120'
})

const editForm = reactive({
  name: '',
  email: '',
  avatar: ''
})

const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value
}

const openEditProfile = () => {
  editForm.name = currentUser.value.name
  editForm.email = currentUser.value.email
  editForm.avatar = currentUser.value.avatar
  isProfileModalOpen.value = true
  isProfileDropdownOpen.value = false
}

const handleSaveProfile = async () => {
  try {
    await authStore.updateProfile(editForm)
    toast.success('Cập nhật thông tin thành công!')
    isProfileModalOpen.value = false
  } catch (err) {
    console.error('Failed to update profile:', err)
    toast.error('Cập nhật thông tin thất bại!')
  }
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
    toast.success('Đăng xuất thành công!')
  } catch (err) {
    console.error('Failed to logout:', err)
    toast.error('Đăng xuất thất bại!')
  }
}

const handleCloseModal = () => {
  isModalOpen.value = false
  editingProject.value = null
}

const goToProjectDetail = (projectId, event) => {
  if (event.ctrlKey || event.metaKey) return
  router.push(`/projects/${projectId}`)
}

// Pagination for ViewListPage (Infinite Scroll)
const displayLimit = ref(20)

// Compute filtering logic
const displayedProjects = computed(() => {
  let list = [...projectStore.projects]

  if (projectStore.searchQuery) {
    const q = projectStore.searchQuery.toLowerCase()
    list = list.filter(p => 
      p.title.toLowerCase().includes(q) || 
      (p.customer && p.customer.name.toLowerCase().includes(q))
    )
  }

  // Filter based on active sidebar view
  if (activeViewId.value !== 'all') {
    const activeViewObj = customViews.value.find(v => v.id === activeViewId.value)
    if (activeViewObj) {
      list = list.filter(p => activeViewObj.projectIds.includes(p.id))
    }
  }

  // Backend already sorts by: pinned -> sort_order -> last_activity_at DESC
  // So we keep the order from backend (20 latest projects)
  // No need to sort again here
  
  // Apply limit for pagination (load 20 initially, then load more)
  return list.slice(0, displayLimit.value)
})

// Load more function
const loadMore = () => {
  displayLimit.value += 20
}

// Switcher view mode (Grouped by Customer)
const isGroupedByCustomer = ref(false)

const toggleCustomerGroup = () => {
  isGroupedByCustomer.value = !isGroupedByCustomer.value
  if (isGroupedByCustomer.value) {
    toast.success('Đã xếp dự án theo Khách hàng')
  } else {
    toast.success('Đã hiển thị danh sách dự án mặc định')
  }
}

// Pinned Customers logic
const pinnedCustomerNames = ref(JSON.parse(localStorage.getItem('pinned_customers') || '[]'))

const togglePinCustomer = (customerName) => {
  const idx = pinnedCustomerNames.value.indexOf(customerName)
  if (idx > -1) {
    pinnedCustomerNames.value.splice(idx, 1)
    toast.success(`Đã bỏ ghim khách hàng ${customerName}`)
  } else {
    pinnedCustomerNames.value.push(customerName)
    toast.success(`Đã ghim khách hàng ${customerName}`)
  }
  localStorage.setItem('pinned_customers', JSON.stringify(pinnedCustomerNames.value))
}

const projectsByCustomer = computed(() => {
  const groups = {}
  displayedProjects.value.forEach(p => {
    const custName = p.customer ? p.customer.name : 'Khác'
    if (!groups[custName]) {
      groups[custName] = []
    }
    groups[custName].push(p)
  })

  // Map to array - keep original order from backend (already sorted by last_activity_at)
  const result = Object.keys(groups).map(custName => {
    const isPinned = pinnedCustomerNames.value.includes(custName)
    return {
      name: custName,
      is_pinned: isPinned,
      projects: groups[custName] // Keep backend order
    }
  })

  // Sort customer groups (pinned customers top first)
  return result.sort((a, b) => (b.is_pinned ? 1 : 0) - (a.is_pinned ? 1 : 0))
})

const getGroupedProjectCardStyle = (project) => {
  const health = project.health
  const status = project.tracking_status

  if (health === 'green' || status === 'completed') {
    return 'bg-[#86efac] border-emerald-400 text-emerald-950 font-bold shadow-3xs'
  } else if (health === 'red' || status === 'not_following' || status === 'unfollowing') {
    return 'bg-[#fca5a5] border-rose-400 text-rose-950 font-bold shadow-3xs'
  } else if (health === 'yellow' || status === 'following') {
    return 'bg-[#fde047] border-amber-400 text-amber-950 font-bold shadow-3xs'
  } else {
    return 'bg-white border-gray-300 text-gray-900 font-bold shadow-3xs'
  }
}

// Drag & Drop Reordering handlers (Default Mode)
const draggedProjectIndex = ref(null)
const dragOverIndex = ref(null)

const onDragStart = (event, project, index) => {
  draggedProjectIndex.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

const onDragOver = (event, index) => {
  dragOverIndex.value = index
}

const onDragLeave = () => {
  dragOverIndex.value = null
}

const onDragEnd = () => {
  draggedProjectIndex.value = null
  dragOverIndex.value = null
}

const onDrop = async (event, dropIndex) => {
  if (draggedProjectIndex.value === null || draggedProjectIndex.value === dropIndex) {
    onDragEnd()
    return
  }

  const currentList = [...displayedProjects.value]
  const [movedItem] = currentList.splice(draggedProjectIndex.value, 1)
  currentList.splice(dropIndex, 0, movedItem)

  const remaining = projectStore.projects.filter(p => !currentList.some(cl => cl.id === p.id))
  projectStore.projects = [...currentList, ...remaining]

  onDragEnd()

  try {
    const ids = projectStore.projects.map(p => p.id)
    await axios.post('/api/projects/reorder', { project_ids: ids })
    toast.success('Đã cập nhật vị trí dự án!')
  } catch (err) {
    console.error('Failed to reorder projects:', err)
    toast.error('Lưu vị trí thất bại!')
  }
}

// Drag & Drop Reordering handlers (Grouped Mode)
const draggedGroupedIndex = ref(null)
const draggedGroupId = ref(null)
const dragOverGroupedIndex = ref(null)
const dragOverGroupId = ref(null)

const onGroupedDragStart = (event, project, group, pIdx) => {
  draggedGroupedIndex.value = pIdx
  draggedGroupId.value = group.name
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

const onGroupedDragOver = (event, pIdx) => {
  dragOverGroupedIndex.value = pIdx
}

const onGroupedDragLeave = () => {
  dragOverGroupedIndex.value = null
}

const onGroupedDragEnd = () => {
  draggedGroupedIndex.value = null
  draggedGroupId.value = null
  dragOverGroupedIndex.value = null
  dragOverGroupId.value = null
}

const onGroupedDrop = async (event, group, dropIdx) => {
  if (draggedGroupedIndex.value === null || (draggedGroupedIndex.value === dropIdx && draggedGroupId.value === group.name)) {
    onGroupedDragEnd()
    return
  }

  const groupProjects = [...group.projects]
  const [movedItem] = groupProjects.splice(draggedGroupedIndex.value, 1)
  groupProjects.splice(dropIdx, 0, movedItem)

  group.projects = groupProjects

  const reorderedIds = groupProjects.map(p => p.id)
  const remainingStoreProjects = projectStore.projects.filter(p => !reorderedIds.includes(p.id))
  projectStore.projects = [...groupProjects, ...remainingStoreProjects]

  onGroupedDragEnd()

  try {
    const ids = projectStore.projects.map(p => p.id)
    await axios.post('/api/projects/reorder', { project_ids: ids })
    toast.success('Đã cập nhật vị trí dự án!')
  } catch (err) {
    console.error('Failed to reorder grouped projects:', err)
    toast.error('Lưu vị trí thất bại!')
  }
}

// Pinned & star functions
const togglePinProject = async (project) => {
  try {
    const isCurrentlyPinned = project.is_pinned == 1 || project.is_pinned === true
    const nextVal = isCurrentlyPinned ? 0 : 1
    
    // Optimistic update - Update UI immediately
    project.is_pinned = nextVal
    
    // Synchronize with store array
    const storeP = projectStore.projects.find(p => p.id === project.id)
    if (storeP) storeP.is_pinned = nextVal

    // Fire and forget - don't wait for response
    axios.put(`/api/projects/${project.id}`, { is_pinned: nextVal }).catch(err => {
      // Only revert on error
      console.error(err)
      project.is_pinned = isCurrentlyPinned
      if (storeP) storeP.is_pinned = isCurrentlyPinned
      toast.error('Thao tác thất bại!')
    })
    
    // Show success immediately
    toast.success(nextVal ? 'Đã ghim dự án!' : 'Đã bỏ ghim dự án!')
  } catch (err) {
    console.error(err)
    toast.error('Thao tác thất bại!')
  }
}

const getProjectStatusStyle = (project) => {
  const health = project.health || 'green'
  const status = project.tracking_status || 'following'
  
  // Card color based on HEALTH (Sức khỏe)
  let cardBg = ''
  let borderClass = ''
  
  if (health === 'green') {
    cardBg = 'bg-[#ecfdf5] border-emerald-200/80'
    borderClass = 'border-t-4 border-t-emerald-500'
  } else if (health === 'red') {
    cardBg = 'bg-[#fff1f2] border-rose-200/80'
    borderClass = 'border-t-4 border-t-rose-500'
  } else { // yellow
    cardBg = 'bg-[#fffbeb] border-amber-200/80'
    borderClass = 'border-t-4 border-t-amber-500'
  }
  
  // Badge text based on TRACKING_STATUS (Trạng thái) - no colors
  let badgeText = ''
  if (status === 'completed') {
    badgeText = 'Hoàn thành'
  } else if (status === 'not_following') {
    badgeText = 'Bỏ theo'
  } else { // following
    badgeText = 'Đang theo'
  }
  
  return {
    cardBg,
    borderClass,
    badgeText,
    badgeClass: 'bg-gray-100/90 text-gray-700 font-bold'
  }
}

const isSelected = (id) => selectedProjectIds.value.includes(id)

// Counts
const pinnedCount = computed(() => {
  return projectStore.projects.filter(p => p.is_pinned).length
})

const setViewFilter = (view) => {
  activeViewId.value = 'all'
}

const getLatestComment = (project) => {
  if (!project.comments || project.comments.length === 0) return null
  const sorted = [...project.comments].sort((a, b) => b.id - a.id)
  return sorted[0]
}

const statusDotClass = (health) => {
  if (health === 'yellow') return 'bg-amber-400'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-emerald-500'
  return 'bg-gray-400'
}

const formatActivityTime = (dateTimeStr) => {
  if (!dateTimeStr) return { top: 'Today', bottom: '' }
  const date = new Date(dateTimeStr)
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

const handleSearch = (query) => {
  displayLimit.value = 20 // Reset limit on search
  projectStore.searchQuery = query
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

const toggleLeadMenu = (projectId) => {
  if (openLeadMenuId.value === projectId) {
    openLeadMenuId.value = null
  } else {
    openLeadMenuId.value = projectId
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

const handleCreateProject = async (data) => {
  try {
    if (editingProject.value) {
      await axios.put(`/api/projects/${editingProject.value.id}`, data)
      toast.success('Cập nhật dự án thành công!')
    } else {
      await projectStore.createProject(data)
      toast.success('Tạo dự án mới thành công!')
    }
    await projectStore.fetchProjects(true)
    handleCloseModal()
  } catch (err) {
    console.error('Failed to save project:', err)
    toast.error('Lưu dự án thất bại!')
  }
}

const handleGlobalKeydown = (event) => {
  // ESC key - Always handle first for closing modals/popups
  if (event.key === 'Escape') {
    // Close modals first
    if (isModalOpen.value) {
      isModalOpen.value = false
      event.preventDefault()
      return
    }
    if (isViewModalOpen.value) {
      isViewModalOpen.value = false
      event.preventDefault()
      return
    }
    if (isProfileModalOpen.value) {
      isProfileModalOpen.value = false
      event.preventDefault()
      return
    }
    
    // Blur search input
    if (document.activeElement === searchInputRef.value) {
      searchInputRef.value?.blur()
      event.preventDefault()
      return
    }
    
    return
  }

  // Check if user is typing in an input field (but allow Ctrl shortcuts)
  const active = document.activeElement
  const isTyping = active && (
    active.tagName === 'INPUT' || 
    active.tagName === 'TEXTAREA' || 
    active.tagName === 'SELECT' || 
    active.isContentEditable
  )
  
  // Ctrl/Cmd + K: Open create project modal
  if ((event.ctrlKey || event.metaKey) && (event.key === 'k' || event.key === 'K') && !event.shiftKey) {
    event.preventDefault()
    event.stopPropagation()
    isModalOpen.value = true
    return
  }
  
  // Ctrl/Cmd + F: Focus search input
  if ((event.ctrlKey || event.metaKey) && (event.key === 'f' || event.key === 'F')) {
    event.preventDefault()
    event.stopPropagation()
    searchInputRef.value?.focus()
    return
  }
  
  // Ctrl/Cmd + A: Select all displayed projects (not when typing unless in search)
  if ((event.ctrlKey || event.metaKey) && (event.key === 'a' || event.key === 'A') && !event.shiftKey) {
    // Allow normal Ctrl+A in input fields
    if (isTyping) {
      return
    }
    event.preventDefault()
    event.stopPropagation()
    toggleSelectAll()
    return
  }
  
  // Ctrl/Cmd + Shift + A: Deselect all
  if ((event.ctrlKey || event.metaKey) && event.shiftKey && (event.key === 'a' || event.key === 'A')) {
    event.preventDefault()
    event.stopPropagation()
    selectedProjectIds.value = []
    return
  }
  
  // Ctrl/Cmd + B: Toggle project/customer view
  if ((event.ctrlKey || event.metaKey) && (event.key === 'b' || event.key === 'B') && !event.shiftKey) {
    event.preventDefault()
    event.stopPropagation()
    toggleCustomerGroup()
    return
  }
  
  // Don't handle single-key shortcuts if user is typing
  if (isTyping) {
    return
  }

  // Single key shortcuts (only when not typing)
  const key = event.key.toLowerCase()
  if (key === '1') {
    setViewFilter('high_impact')
  } else if (key === 'n') {
    isModalOpen.value = true
  } else if (key === 'v') {
    isSidebarCollapsed.value = !isSidebarCollapsed.value
  }
}

const closeAllDropdowns = (e) => {
  openLeadMenuId.value = null
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    isProfileDropdownOpen.value = false
  }
}

// Checkboxes and multi-select
const selectedProjectIds = ref([])

// Drag selection box functionality (Windows-style)
const isSelecting = ref(false)
const selectionBox = ref({
  startX: 0,
  startY: 0,
  currentX: 0,
  currentY: 0,
  visible: false
})
const selectionContainerRef = ref(null)

const getSelectionBoxStyle = computed(() => {
  if (!selectionBox.value.visible) return { display: 'none' }
  
  const left = Math.min(selectionBox.value.startX, selectionBox.value.currentX)
  const top = Math.min(selectionBox.value.startY, selectionBox.value.currentY)
  const width = Math.abs(selectionBox.value.currentX - selectionBox.value.startX)
  const height = Math.abs(selectionBox.value.currentY - selectionBox.value.startY)
  
  return {
    position: 'fixed',
    left: `${left}px`,
    top: `${top}px`,
    width: `${width}px`,
    height: `${height}px`,
    backgroundColor: 'rgba(59, 130, 246, 0.2)',
    border: '1px solid rgba(59, 130, 246, 0.6)',
    pointerEvents: 'none',
    zIndex: 9999
  }
})

const startSelection = (event) => {
  // Only start on left mouse button and not on interactive elements
  if (event.button !== 0) return
  
  const target = event.target
  // Don't start selection if clicking on interactive elements
  if (target.closest('input, button, a, .cursor-pointer, [draggable="true"]')) {
    return
  }
  
  isSelecting.value = true
  selectionBox.value = {
    startX: event.clientX,
    startY: event.clientY,
    currentX: event.clientX,
    currentY: event.clientY,
    visible: true
  }
  
  // Prevent text selection
  event.preventDefault()
}

const updateSelection = (event) => {
  if (!isSelecting.value) return
  
  selectionBox.value.currentX = event.clientX
  selectionBox.value.currentY = event.clientY
  
  // Check which project cards intersect with selection box
  checkProjectIntersections()
}

const endSelection = () => {
  if (!isSelecting.value) return
  
  isSelecting.value = false
  selectionBox.value.visible = false
}

const checkProjectIntersections = () => {
  if (!isSelecting.value) return
  
  const boxLeft = Math.min(selectionBox.value.startX, selectionBox.value.currentX)
  const boxRight = Math.max(selectionBox.value.startX, selectionBox.value.currentX)
  const boxTop = Math.min(selectionBox.value.startY, selectionBox.value.currentY)
  const boxBottom = Math.max(selectionBox.value.startY, selectionBox.value.currentY)
  
  // Get all project card elements
  const projectCards = document.querySelectorAll('[data-project-id]')
  const newSelectedIds = []
  
  projectCards.forEach(card => {
    const rect = card.getBoundingClientRect()
    
    // Check if selection box intersects with project card
    const intersects = !(
      rect.right < boxLeft ||
      rect.left > boxRight ||
      rect.bottom < boxTop ||
      rect.top > boxBottom
    )
    
    if (intersects) {
      const projectId = parseInt(card.getAttribute('data-project-id'))
      if (!newSelectedIds.includes(projectId)) {
        newSelectedIds.push(projectId)
      }
    }
  })
  
  selectedProjectIds.value = newSelectedIds
}

const toggleProjectSelect = (id) => {
  const idx = selectedProjectIds.value.indexOf(id)
  if (idx > -1) {
    selectedProjectIds.value.splice(idx, 1)
  } else {
    selectedProjectIds.value.push(id)
  }
}

const isAllSelected = computed(() => {
  return displayedProjects.value.length > 0 && selectedProjectIds.value.length === displayedProjects.value.length
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedProjectIds.value = []
  } else {
    selectedProjectIds.value = displayedProjects.value.map(p => p.id)
  }
}

// Comment content attachment parsers
const activePreviewImage = ref(null)

const openImagePreview = (url) => {
  activePreviewImage.value = url
}

const parseCommentText = (content) => {
  if (!content) return ''
  return content
    .replace(/!\[.*?\]\((.*?)\)/g, '')
    .replace(/📎\s*\[(.*?)\]\((.*?)\)/g, '')
    .trim()
}

const parseCommentImages = (content) => {
  if (!content) return []
  const matches = []
  const regex = /!\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = regex.exec(content)) !== null) {
    matches.push({ name: m[1] || 'Hình ảnh', url: m[2] })
  }
  return matches
}

const parseCommentFiles = (content) => {
  if (!content) return []
  const matches = []
  const regex = /📎\s*\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = regex.exec(content)) !== null) {
    matches.push({ name: m[1], url: m[2] })
  }
  return matches
}

// Activities feed fetching and styling
const activities = ref([])
const isActivitiesLoading = ref(true)

const fetchActivities = async () => {
  try {
    const res = await axios.get('/api/comments')
    const filtered = (res.data || []).filter(c => {
      if (!c.project_id) return false
      if (c.content && c.content.includes('Đã tạo dự án mới')) return false
      return true
    })
    if (JSON.stringify(filtered) !== JSON.stringify(activities.value)) {
      activities.value = filtered
    }
  } catch (err) {
    console.error('Failed to load activities:', err)
  } finally {
    isActivitiesLoading.value = false
  }
}

const formatCommentRelativeTime = (dateStr) => {
  if (!dateStr) return '1h'
  const date = new Date(dateStr)
  const now = new Date()
  const diffSec = Math.floor((now - date) / 1000)

  if (diffSec < 60) return 'Vừa xong'
  const diffMin = Math.floor(diffSec / 60)
  if (diffMin < 60) return `${diffMin}m`
  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours}h`
  const diffDays = Math.floor(diffHours / 24)
  return `${diffDays}d`
}

// Bulk options
const activeBulkMenu = ref(null)
const toggleBulkMenu = (menu) => {
  if (activeBulkMenu.value === menu) {
    activeBulkMenu.value = null
  } else {
    activeBulkMenu.value = menu
  }
}

const bulkUpdateHealth = async (color) => {
  let trackingStatus = 'following'
  if (color === 'green') trackingStatus = 'completed'
  if (color === 'red') trackingStatus = 'not_following'

  // Optimistically update local project state so card background & badge change instantly!
  selectedProjectIds.value.forEach(id => {
    const p = projectStore.projects.find(proj => proj.id === id)
    if (p) {
      p.health = color
      p.tracking_status = trackingStatus
    }
  })

  try {
    await Promise.all(selectedProjectIds.value.map(id => 
      axios.put(`/api/projects/${id}`, { 
        health: color,
        tracking_status: trackingStatus
      })
    ))
    toast.success(`Đã cập nhật tình trạng sức khỏe cho ${selectedProjectIds.value.length} dự án!`)
    await projectStore.fetchProjects(true)
    selectedProjectIds.value = []
    activeBulkMenu.value = null
  } catch (err) {
    console.error(err)
    toast.error('Cập nhật thất bại!')
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
    console.error(err)
    toast.error('Chuyển lead thất bại!')
  }
}

const bulkUpdateStatus = async (status) => {
  let healthColor = 'yellow'
  if (status === 'completed') healthColor = 'green'
  if (status === 'not_following') healthColor = 'red'

  // Optimistically update local project state so card background & badge change instantly!
  selectedProjectIds.value.forEach(id => {
    const p = projectStore.projects.find(proj => proj.id === id)
    if (p) {
      p.health = healthColor
      p.tracking_status = status
    }
  })

  try {
    await Promise.all(selectedProjectIds.value.map(id => 
      axios.put(`/api/projects/${id}`, { 
        tracking_status: status,
        health: healthColor
      })
    ))
    toast.success(`Đã cập nhật trạng thái cho ${selectedProjectIds.value.length} dự án!`)
    await projectStore.fetchProjects(true)
    selectedProjectIds.value = []
    activeBulkMenu.value = null
  } catch (err) {
    console.error(err)
    toast.error('Cập nhật thất bại!')
  }
}

const goToBulkUpdate = () => {
  if (selectedProjectIds.value.length === 0) return
  router.push(`/projects/update?ids=${selectedProjectIds.value.join(',')}`)
}

let pollTimer = null

// Infinite scroll handler
const handleScroll = (event) => {
  const container = event.target
  if (!container) return
  
  const { scrollTop, scrollHeight, clientHeight } = container
  // Load more when scrolled to bottom (with 100px threshold)
  if (scrollTop + clientHeight >= scrollHeight - 100) {
    if (displayedProjects.value.length < projectStore.projects.length) {
      loadMore()
    }
  }
}

onMounted(async () => {
  projectStore.activeStatus = null
  loadCustomViews()
  await projectStore.fetchProjects()
  await projectStore.fetchAuxData()
  await fetchActivities()

  window.addEventListener('keydown', handleGlobalKeydown)
  window.addEventListener('click', closeAllDropdowns)
  
  // Drag selection events
  window.addEventListener('mousedown', startSelection)
  window.addEventListener('mousemove', updateSelection)
  window.addEventListener('mouseup', endSelection)
  
  // Infinite scroll - wait for refs to be available
  setTimeout(() => {
    if (scrollContainerDefault.value) {
      scrollContainerDefault.value.addEventListener('scroll', handleScroll)
    }
    if (scrollContainerGrouped.value) {
      scrollContainerGrouped.value.addEventListener('scroll', handleScroll)
    }
  }, 100)

  pollTimer = setInterval(() => {
    projectStore.fetchProjects(true)
    fetchActivities()
  }, 4000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
  window.removeEventListener('keydown', handleGlobalKeydown)
  window.removeEventListener('click', closeAllDropdowns)
  
  // Clean up drag selection events
  window.removeEventListener('mousedown', startSelection)
  window.removeEventListener('mousemove', updateSelection)
  window.removeEventListener('mouseup', endSelection)
  
  // Clean up infinite scroll
  if (scrollContainerDefault.value) {
    scrollContainerDefault.value.removeEventListener('scroll', handleScroll)
  }
  if (scrollContainerGrouped.value) {
    scrollContainerGrouped.value.removeEventListener('scroll', handleScroll)
  }
})
</script>
