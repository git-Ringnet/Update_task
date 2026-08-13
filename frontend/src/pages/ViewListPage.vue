<template>
  <div class="min-h-screen flex flex-col pb-12 transition-colors duration-200"
       :class="viewMode === 'notes' ? 'sticky-board-bg' : 'bg-[#f4f5f0]'">
    <!-- Navbar Component -->
    <Navbar @search="handleSearch" />

    <!-- Main Container -->
    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 w-full">
      
      <!-- Header (Top Center) -->
      <div class="flex flex-col items-center text-center mt-1 mb-6 select-none">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight font-heading mb-1">Help IT Managers become badass.</h1>
        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">You must think about how IT managers appear in the eyes of their audiences</p>
      </div>

      <!-- Main Layout Grid: 12-col Grid (Left Panel 3 | Center Panel 5 or 9 | Right Panel 4 or hidden) -->
      <div class="grid grid-cols-1 gap-6 items-start"
           :class="viewMode === 'notes' ? 'lg:grid-cols-12' : 'lg:grid-cols-12'">
        
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
            class="w-full bg-[#f1f5f9] hover:bg-[#e2e8f0] border border-gray-200 text-slate-800 font-extrabold text-sm rounded-xl p-3.5 flex items-center justify-center transition-colors shadow-3xs cursor-pointer focus:outline-none select-none"
            :class="isGroupedByCustomer ? 'ring-2 ring-emerald-500/30 border-emerald-500 bg-emerald-50/40 text-emerald-950' : ''"
            title="Chuyển đổi chế độ xem (Ctrl + B)"
          >
            <div class="flex items-center gap-2.5">
              <i class="fa-solid fa-right-left text-xs" :class="isGroupedByCustomer ? 'text-emerald-600' : 'text-slate-500'"></i>
              <span>Đổi kiểu xem</span>
            </div>
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

        <!-- CENTER PANEL: Projects List (Block 2 - Wider Column, expands when notes view) -->
        <section class="space-y-3.5 select-none"
                 :class="viewMode === 'notes' ? 'lg:col-span-9' : 'lg:col-span-5'">

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
                  class="flex items-center gap-2.5 transition-all duration-150 rounded-2xl group/project-row"
                  :class="{
                    'opacity-40 scale-[0.98]': draggedGroupedIndex === pIdx && draggedGroupId === group.name,
                    'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverGroupedIndex === pIdx && dragOverGroupId === group.name && (draggedGroupedIndex !== pIdx || draggedGroupId !== group.name)
                  }"
                >
                  <!-- Checkbox for multi-select (hidden by default, show on hover or when any checkbox is clicked) -->
                  <input
                    type="checkbox"
                    :checked="isSelected(project.id)"
                    @click.stop="toggleProjectSelect(project.id)"
                    class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer flex-shrink-0 transition-opacity duration-200"
                    :class="showAllCheckboxes ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100'"
                  />

                  <!-- Colored Project Rectangular Card (Identical to default mode) -->
                  <div
                    @click="goToProjectDetail(project.id, $event)"
                    class="flex-1 rounded-lg p-4 flex items-start justify-between gap-4 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden border min-w-0"
                    :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]"
                  >
                    <div class="min-w-0 flex-1">
                      <div class="font-extrabold text-gray-900 text-sm sm:text-base leading-snug break-words min-w-0">
                        {{ project.title }}
                      </div>
                    </div>

                    <div class="flex-shrink-0">
                      <!-- Pin/Star Button -->
                      <button 
                        @click.stop="togglePinProject(project)" 
                        type="button" 
                        class="p-1 cursor-pointer transition-colors"
                        :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'"
                      >
                        <i 
                          class="text-lg transition-colors" 
                          :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"
                        ></i>
                      </button>
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

          <!-- Sticky Notes View (Grid Layout) -->
          <div 
            v-else-if="viewMode === 'notes'" 
            ref="scrollContainerNotes"
            class="overflow-y-auto pr-1 pt-4 pb-8 max-h-[calc(100vh-200px)] scrollbar-none"
          >
            <div class="sticky-grid">
              <div
                v-for="project in displayedProjects"
                :key="project.id"
                :data-project-id="project.id"
                @click="goToProjectDetail(project.id, $event)"
                class="note-card"
                :class="getStickyNoteStyle(project)"
              >
                <!-- Pin top center -->
                <span class="note-pin" :class="getStickyNotePinStyle(project)"></span>

                <!-- Star icon (top right) -->
                <button 
                  @click.stop="togglePinProject(project)" 
                  type="button" 
                  class="note-star"
                  :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'"
                >
                  <i 
                    class="text-lg transition-colors" 
                    :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"
                  ></i>
                </button>

                <!-- Content upper -->
                <div>
                  <div class="note-header-tag">DỰ ÁN</div>
                  <h3 class="note-title-text">
                    {{ project.title }}
                  </h3>
                </div>

                <!-- Content lower (Divider & Customer Name) -->
                <div>
                  <div class="note-divider-line"></div>
                  <div class="note-sub-text">
                    {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty state for notes mode -->
            <div v-if="displayedProjects.length === 0" class="bg-white/80 backdrop-blur-xs border border-gray-200 rounded-2xl py-12 text-center text-gray-500 text-sm font-bold shadow-3xs select-none">
              Không có dự án đã ghim nào.
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
                class="flex items-center gap-2.5 transition-all duration-150 rounded-2xl group/project-row"
                :class="{
                  'opacity-40 scale-[0.98]': draggedProjectIndex === index,
                  'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverIndex === index && draggedProjectIndex !== index
                }"
              >
                <!-- Checkbox for multi-select (hidden by default, show on hover or when any checkbox is clicked) -->
                <input
                  type="checkbox"
                  :checked="isSelected(project.id)"
                  @click.stop="toggleProjectSelect(project.id)"
                  class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer flex-shrink-0 transition-opacity duration-200"
                  :class="showAllCheckboxes ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100'"
                />

                <!-- Card Container -->
                <div
                  @click="goToProjectDetail(project.id, $event)"
                  class="flex-1 rounded-lg p-4 flex items-start justify-between gap-4 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden border min-w-0"
                  :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]"
                >
                  <div class="min-w-0 flex-1">
                    <div class="font-extrabold text-gray-900 text-sm sm:text-base leading-snug break-words min-w-0">
                      {{ project.title }}
                    </div>
                    <div class="text-xs text-gray-700 font-bold mt-1 uppercase tracking-wider">
                      {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
                    </div>
                  </div>

                  <div class="flex-shrink-0">
                    <!-- Pin/Star Button -->
                    <button 
                      @click.stop="togglePinProject(project)" 
                      type="button" 
                      class="p-1 cursor-pointer transition-colors"
                      :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'"
                    >
                      <i 
                        class="text-lg transition-colors" 
                        :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"
                      ></i>
                    </button>
                  </div>
                </div>
              </div>
            </transition-group>

            <!-- Empty projects state -->
            <div v-if="displayedProjects.length === 0" class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-450 text-sm font-semibold shadow-3xs select-none">
              Không tìm thấy dự án nào trong mục này.
            </div>

            <!-- Load More Button -->
            <div v-if="displayedProjects.length < projectStore.projects.filter(p => p.tracking_status === 'following').length" class="flex justify-center pt-2">
              <button
                @click="loadMore"
                type="button"
                class="px-5 py-2.5 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-500 text-gray-700 hover:text-emerald-700 font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-2 shadow-3xs focus:outline-none"
              >
                <i class="fa-solid fa-angles-down text-[10px]"></i>
                <span>Xem thêm dự án (Còn {{ projectStore.projects.filter(p => p.tracking_status === 'following').length - displayedProjects.length }} dự án)</span>
              </button>
            </div>
          </div>
        </section>

        <!-- RIGHT PANEL: Hoạt động gần đây (Block 3 - Hidden in notes view) -->
        <section 
          v-if="viewMode !== 'notes'"
          class="lg:col-span-4 bg-white border border-gray-200 rounded-3xl p-5 shadow-3xs flex flex-col h-[calc(100vh-200px)] select-none"
        >
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
            <button @click="selectedProjectIds = []; showAllCheckboxes = false" type="button" class="text-[10px] sm:text-[11px] text-gray-400 hover:text-gray-600 font-bold cursor-pointer leading-tight mt-0.5">Bỏ chọn</button>
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
}

const openLeadMenuId = ref(null)
const editingProject = ref(null)
const isModalOpen = ref(false)

// Search input ref for keyboard shortcuts
const searchInputRef = ref(null)
const scrollContainerDefault = ref(null)
const scrollContainerGrouped = ref(null)
const scrollContainerNotes = ref(null)

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
    isProfileModalOpen.value = false
  } catch (err) {
    console.error('Failed to update profile:', err)
  }
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (err) {
    console.error('Failed to logout:', err)
  }
}

const handleCloseModal = () => {
  isModalOpen.value = false
  editingProject.value = null
}

const goToProjectDetail = (projectId, event) => {
  if (event.ctrlKey || event.metaKey) return
  
  // Double-click navigates to detail
  if (event.detail === 2) {
    router.push(`/projects/${projectId}`)
    return
  }
  
  // Single click toggles checkbox selection
  toggleProjectSelect(projectId)
}

// Pagination for ViewListPage (Infinite Scroll)
const displayLimit = ref(20)

// Compute filtering logic
const displayedProjects = computed(() => {
  let list = [...projectStore.projects]

  // Filter to only show projects with tracking_status = 'following'
  list = list.filter(p => p.tracking_status === 'following')

  // In notes view, ONLY show pinned projects
  if (viewMode.value === 'notes') {
    list = list.filter(p => p.is_pinned == 1 || p.is_pinned === true)
  }

  if (projectStore.searchQuery) {
    const q = removeVietnameseAccents(projectStore.searchQuery)
    list = list.filter(p => {
      const title = removeVietnameseAccents(p.title)
      const customerName = p.customer ? removeVietnameseAccents(p.customer.name) : ''
      return title.includes(q) || customerName.includes(q)
    })
  }

  // Filter based on active sidebar view
  if (activeViewId.value !== 'all') {
    const activeViewObj = customViews.value.find(v => v.id === activeViewId.value)
    if (activeViewObj) {
      list = list.filter(p => activeViewObj.projectIds.includes(p.id))
    }
  }

  // Always sort pinned projects to the top instantly for all view modes
  list.sort((a, b) => {
    const aPinned = (a.is_pinned == 1 || a.is_pinned === true) ? 1 : 0
    const bPinned = (b.is_pinned == 1 || b.is_pinned === true) ? 1 : 0
    if (bPinned !== aPinned) {
      return bPinned - aPinned
    }
    const aOrder = a.sort_order ?? 999999
    const bOrder = b.sort_order ?? 999999
    if (aOrder !== bOrder) {
      return aOrder - bOrder
    }
    return new Date(b.last_activity_at || 0) - new Date(a.last_activity_at || 0)
  })
  
  // Apply limit for pagination (load 20 initially, then load more)
  return list.slice(0, displayLimit.value)
})

// Load more function
const loadMore = () => {
  displayLimit.value += 20
}

// Function to remove Vietnamese accents for fuzzy search
const removeVietnameseAccents = (str) => {
  if (!str) return ''
  return str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
}

// Switcher view mode (Grouped by Customer)
const isGroupedByCustomer = ref(false)
const viewMode = ref('list') // 'list', 'grouped', 'notes'

const toggleCustomerGroup = () => {
  // Cycle through view modes: list -> grouped -> notes -> list
  if (viewMode.value === 'list') {
    viewMode.value = 'grouped'
    isGroupedByCustomer.value = true
  } else if (viewMode.value === 'grouped') {
    viewMode.value = 'notes'
    isGroupedByCustomer.value = false
  } else {
    viewMode.value = 'list'
    isGroupedByCustomer.value = false
  }
}

// Pinned Customers logic
const pinnedCustomerNames = ref(JSON.parse(localStorage.getItem('pinned_customers') || '[]'))

const togglePinCustomer = (customerName) => {
  const idx = pinnedCustomerNames.value.indexOf(customerName)
  if (idx > -1) {
    pinnedCustomerNames.value.splice(idx, 1)
  } else {
    pinnedCustomerNames.value.push(customerName)
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
  // Only use HEALTH to determine card color, NOT tracking_status!

  if (health === 'green') {
    return 'bg-[#86efac] border-emerald-400 text-emerald-950 font-bold shadow-3xs'
  } else if (health === 'red') {
    return 'bg-[#fca5a5] border-rose-400 text-rose-950 font-bold shadow-3xs'
  } else if (health === 'yellow') {
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
  } catch (err) {
    console.error('Failed to reorder projects:', err)
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
  } catch (err) {
    console.error('Failed to reorder grouped projects:', err)
  }
}

// Pinned & star functions
const togglePinProject = async (project) => {
  if (!project?.id) return
  try {
    await projectStore.togglePin(project.id)
  } catch (err) {
    console.error('Failed to toggle pin:', err)
    toast.error('Thay đổi ghim thất bại!')
  }
}

const getProjectStatusStyle = (project) => {
  const health = project.health || 'green'
  
  // Card color based on HEALTH (Sức khỏe) - Darker colors with thicker borders and less rounded corners
  let cardBg = ''
  let borderClass = ''
  
  if (health === 'green') {
    cardBg = 'bg-[#86efac] border-[#4ade80]' // Darker green
    borderClass = 'border-2' // Thicker border
  } else if (health === 'red') {
    cardBg = 'bg-[#fca5a5] border-[#f87171]' // Darker red
    borderClass = 'border-2' // Thicker border
  } else { // yellow
    cardBg = 'bg-[#fcd34d] border-[#fbbf24]' // Darker yellow
    borderClass = 'border-2' // Thicker border
  }
  
  return {
    cardBg,
    borderClass,
    badgeText: '', // No badge text needed
    badgeClass: '' // No badge class needed
  }
}

const getStickyNoteStyle = (project) => {
  const health = project.health
  if (health === 'green') return 'note-green'
  if (health === 'red') return 'note-red'
  if (health === 'white') return 'note-white'
  return 'note-yellow'
}

const getStickyNotePinStyle = (project) => {
  const health = project.health
  if (health === 'green') return 'pin-green'
  if (health === 'red') return 'pin-red'
  if (health === 'white') return 'pin-grey'
  return 'pin-yellow'
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
  } catch (err) {
    console.error('Failed to update project health:', err)
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
    await projectStore.fetchProjects(true)
    openLeadMenuId.value = null
  } catch (err) {
    console.error('Failed to update project lead inline:', err)
  }
}

const handleCreateProject = async (data) => {
  try {
    if (editingProject.value) {
      await axios.put(`/api/projects/${editingProject.value.id}`, data)
    } else {
      await projectStore.createProject(data)
    }
    await projectStore.fetchProjects(true)
    handleCloseModal()
  } catch (err) {
    console.error('Failed to save project:', err)
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
    
    // Close bulk action menu if open
    if (activeBulkMenu.value) {
      activeBulkMenu.value = null
      event.preventDefault()
      return
    }
    
    // Clear selections and hide checkboxes if any projects are selected
    if (selectedProjectIds.value.length > 0) {
      selectedProjectIds.value = []
      showAllCheckboxes.value = false
      event.preventDefault()
      return
    }
    
    // Clear search query if it has content
    if (projectStore.searchQuery) {
      projectStore.searchQuery = ''
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
    showAllCheckboxes.value = false
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
const showAllCheckboxes = ref(false)

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
  // Show all checkboxes when drag selecting
  if (newSelectedIds.length > 0) {
    showAllCheckboxes.value = true
  }
}

const toggleProjectSelect = (id) => {
  const idx = selectedProjectIds.value.indexOf(id)
  if (idx > -1) {
    selectedProjectIds.value.splice(idx, 1)
    // If no selections left, hide checkboxes again
    if (selectedProjectIds.value.length === 0) {
      showAllCheckboxes.value = false
    }
  } else {
    selectedProjectIds.value.push(id)
    // Show all checkboxes when any checkbox is clicked
    showAllCheckboxes.value = true
  }
}

const isAllSelected = computed(() => {
  return displayedProjects.value.length > 0 && selectedProjectIds.value.length === displayedProjects.value.length
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedProjectIds.value = []
    showAllCheckboxes.value = false
  } else {
    selectedProjectIds.value = displayedProjects.value.map(p => p.id)
    showAllCheckboxes.value = true
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
    .replace(/<img[^>]*>/gi, '')
    .replace(/<a[^>]*>📎\s*Tệp đính kèm:[^<]*<\/a>/gi, '')
    .replace(/<span[^>]*>📎\s*Tệp đính kèm:[^<]*<\/span>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/<br\s*\/?>/gi, ' ')
    .trim()
}

const parseCommentImages = (content) => {
  if (!content) return []
  const matches = []
  
  // 1. Markdown images ![name](url)
  const mdRegex = /!\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = mdRegex.exec(content)) !== null) {
    matches.push({ name: m[1] || 'Hình ảnh', url: m[2] })
  }

  // 2. HTML <img> tags <img src="url" ...>
  const htmlRegex = /<img[^>]+src="([^"]*)"[^>]*>/gi
  while ((m = htmlRegex.exec(content)) !== null) {
    let fileName = 'Hình ảnh đính kèm'
    const src = m[1]
    if (src && !src.startsWith('data:')) {
      const urlParts = src.split('/')
      const lastPart = urlParts[urlParts.length - 1]?.split('?')[0]
      if (lastPart) fileName = decodeURIComponent(lastPart)
    }
    matches.push({ name: fileName, url: src })
  }

  return matches
}

const parseCommentFiles = (content) => {
  if (!content) return []
  const matches = []

  // 1. Markdown files 📎 [name](url)
  const mdRegex = /📎\s*\[(.*?)\]\((.*?)\)/g
  let m
  while ((m = mdRegex.exec(content)) !== null) {
    matches.push({ name: m[1], url: m[2] })
  }

  // 2. HTML file spans <span...>📎 Tệp đính kèm: name</span>
  const htmlRegex = /<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi
  while ((m = htmlRegex.exec(content)) !== null) {
    const rawName = m[1].trim()
    matches.push({ name: rawName, url: '#' })
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
  // Save selected IDs before clearing
  const idsToUpdate = [...selectedProjectIds.value]
  
  // ONLY update health, do NOT change tracking_status!
  // Optimistically update local project state
  idsToUpdate.forEach(id => {
    const p = projectStore.projects.find(proj => proj.id === id)
    if (p) {
      p.health = color
      // Keep tracking_status as-is, do NOT modify it
    }
  })

  // Optimistic update already done
  selectedProjectIds.value = []
  showAllCheckboxes.value = false
  activeBulkMenu.value = null

  try {
    // Fire and forget - update in background
    await Promise.all(idsToUpdate.map(id => 
      axios.put(`/api/projects/${id}`, { 
        health: color
      })
    ))
  } catch (err) {
    console.error(err)
    // On error, refresh to get correct state from server
    await projectStore.fetchProjects(true)
  }
}

const bulkUpdateLead = async (userId) => {
  try {
    await Promise.all(selectedProjectIds.value.map(id => 
      axios.put(`/api/projects/${id}`, { lead_id: userId })
    ))
    await projectStore.fetchProjects(true)
    selectedProjectIds.value = []
    showAllCheckboxes.value = false
    activeBulkMenu.value = null
  } catch (err) {
    console.error(err)
  }
}

const bulkUpdateStatus = async (status) => {
  // Save selected IDs before clearing
  const idsToUpdate = [...selectedProjectIds.value]
  
  // ONLY update tracking_status, do NOT change health!
  // Optimistically update local project state
  idsToUpdate.forEach(id => {
    const p = projectStore.projects.find(proj => proj.id === id)
    if (p) {
      p.tracking_status = status
      // Keep health as-is, do NOT modify it
    }
  })

  // Optimistic update already done
  selectedProjectIds.value = []
  showAllCheckboxes.value = false
  activeBulkMenu.value = null

  try {
    // Fire and forget - update in background
    await Promise.all(idsToUpdate.map(id => 
      axios.put(`/api/projects/${id}`, { 
        tracking_status: status
      })
    ))
  } catch (err) {
    console.error(err)
    // On error, refresh to get correct state from server
    await projectStore.fetchProjects(true)
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
    if (scrollContainerNotes.value) {
      scrollContainerNotes.value.addEventListener('scroll', handleScroll)
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
  if (scrollContainerNotes.value) {
    scrollContainerNotes.value.removeEventListener('scroll', handleScroll)
  }
})
</script>

<style scoped>
/* Sticky Board background with dot matrix grid */
.sticky-board-bg {
  background-color: #f6f4ef !important;
  background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.04) 1px, transparent 0) !important;
  background-size: 22px 22px !important;
}

/* Grid layout matching sticky-notes.html */
.sticky-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 260px));
  gap: 28px 32px;
  justify-content: start;
  padding-top: 16px;
}

@media (max-width: 780px) {
  .sticky-grid {
    grid-template-columns: repeat(2, minmax(180px, 1fr));
  }
}
@media (max-width: 520px) {
  .sticky-grid {
    grid-template-columns: 1fr;
  }
}

/* Note Card - 1:1 Aspect ratio square paper note with textures and shadows */
.note-card {
  position: relative;
  aspect-ratio: 1 / 1;
  padding: 30px 20px 20px;
  border-radius: 2px;
  isolation: isolate;
  box-shadow:
    0 0.5px 0 rgba(255,255,255,0.6) inset,
    0 2px 4px rgba(0,0,0,0.06),
    0 8px 16px -4px rgba(0,0,0,0.12);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  transform: rotate(var(--tilt, 0deg)) translateZ(0);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
  user-select: none;
  contain: layout style;
  will-change: transform;
}

/* Optimized Paper Grain Texture (no heavy SVG turbulence or mix-blend-mode for 60fps scrolling) */
.note-card::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background-image: radial-gradient(rgba(0,0,0,0.04) 1px, transparent 0);
  background-size: 8px 8px;
  opacity: 0.4;
}

/* Streamlined Wrinkle/Crease overlay for GPU hardware acceleration */
.note-card::after {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 2;
  pointer-events: none;
  background:
    radial-gradient(ellipse 28px 10px at 50% 0, rgba(0,0,0,0.1), rgba(0,0,0,0) 75%),
    linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 15%),
    linear-gradient(225deg, rgba(0,0,0,0.04) 0%, rgba(0,0,0,0) 20%);
}

.note-card:nth-child(6n+2)::after,
.note-card:nth-child(6n+4)::after,
.note-card:nth-child(6n+5)::after { transform: scaleX(-1); }
.note-card:nth-child(6n+3)::after { filter: brightness(1.08); }

.note-card:hover {
  transform: rotate(0deg) translateY(-4px) scale(1.015) !important;
  box-shadow:
    0 0.5px 0 rgba(255,255,255,0.6) inset,
    0 1px 1px rgba(0,0,0,0.08),
    0 4px 4px -1px rgba(0,0,0,0.08),
    0 12px 14px -6px rgba(0,0,0,0.15),
    0 26px 30px -14px rgba(0,0,0,0.22);
}

.note-card > * {
  position: relative;
  z-index: 3;
}

/* Subtle tilts for natural sticky note look */
.note-card:nth-child(6n+1) { --tilt: -0.6deg; }
.note-card:nth-child(6n+2) { --tilt: 0.5deg; }
.note-card:nth-child(6n+3) { --tilt: -0.4deg; }
.note-card:nth-child(6n+4) { --tilt: 0.7deg; }
.note-card:nth-child(6n+5) { --tilt: -0.5deg; }
.note-card:nth-child(6n+6) { --tilt: 0.4deg; }

/* Metallic dome pushpin at top center */
.note-pin {
  position: absolute;
  top: -9px;
  left: 50%;
  transform: translateX(-50%);
  width: 16px;
  height: 16px;
  border-radius: 50%;
  z-index: 4;
  box-shadow:
    0 4px 5px -1px rgba(0,0,0,0.35),
    0 2px 2px rgba(0,0,0,0.2),
    inset -2px -2px 3px rgba(0,0,0,0.25),
    inset 2px 2px 3px rgba(255,255,255,0.55);
}

.note-pin::before {
  content: "";
  position: absolute;
  top: 2.5px;
  left: 3px;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: rgba(255,255,255,0.85);
  filter: blur(0.3px);
}

/* Pin color gradients */
.pin-yellow { background: radial-gradient(circle at 35% 30%, #ffe082, #e5a11c); }
.pin-grey   { background: radial-gradient(circle at 35% 30%, #e0e0e0, #a6a6a6); }
.pin-red    { background: radial-gradient(circle at 35% 30%, #ff8a80, #d32f2f); }
.pin-green  { background: radial-gradient(circle at 35% 30%, #a5d6a7, #388e3c); }

/* Star button at top right */
.note-star {
  position: absolute;
  top: 14px;
  right: 14px;
  cursor: pointer;
  background: transparent;
  border: none;
  padding: 4px;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s ease;
}
.note-star:hover {
  transform: scale(1.15);
}

/* Note inner content typography */
.note-header-tag {
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-weight: 700;
  color: rgba(0,0,0,0.45);
  margin-bottom: 6px;
}

.note-title-text {
  font-size: 19px;
  font-weight: 800;
  line-height: 1.28;
  margin: 0;
  color: #23211d;
  word-break: break-word;
}

.note-divider-line {
  border-top: 1px solid rgba(0,0,0,0.12);
  margin-bottom: 8px;
}

.note-sub-text {
  font-size: 14px;
  color: rgba(0,0,0,0.65);
  font-weight: 600;
}

/* Note color themes (Brighter, vibrant pastel tones) */
.note-yellow { background: #ffd643; }
.note-yellow .note-sub-text { color: rgba(0,0,0,0.55); }

.note-white  { background: #ffffff; border: 1px solid rgba(0,0,0,0.06); }
.note-white .note-sub-text { color: rgba(0,0,0,0.55); }

.note-red    { background: #ff8a80; }
.note-red .note-title-text, .note-red .note-sub-text { color: #2c1410; }
.note-red .note-sub-text { color: rgba(30,10,5,0.65); }

.note-green  { background: #9de39b; }
.note-green .note-title-text, .note-green .note-sub-text { color: #16301a; }
.note-green .note-sub-text { color: rgba(15,35,20,0.65); }
</style>
