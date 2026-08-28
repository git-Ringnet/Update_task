<template>
  <div class="min-h-screen flex flex-col pb-12 transition-colors duration-200"
    :class="viewMode === 'notes' ? 'sticky-board-bg' : 'bg-[#F9F4EE]'">
    <!-- Navbar Component -->
    <Navbar @search="handleSearch" />

    <!-- Main Container -->
    <main class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-4 w-full">

      <!-- Header (Top Center) -->
      <div class="view-page-intro flex flex-col items-center text-center mt-1 mb-6 select-none">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight font-heading mb-1">Help IT Managers become badass.
        </h1>
        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">You must think about how IT managers
          appear in the eyes of their audiences</p>
      </div>



      <!-- Main Layout Container -->
      <div class="view-page-layout" :class="viewMode === 'notes'
        ? 'grid grid-cols-1 lg:grid-cols-[390px_minmax(0,1fr)] gap-10 w-full lg:w-[1280px] mx-auto items-start'
        : 'view-standard-layout w-full flex justify-center items-start gap-10'">

        <!-- LEFT PANEL: Actions, Switcher & Search (Block 1) -->
        <aside ref="viewActionsRef" class="view-actions" :class="[
          viewMode === 'notes' ? 'space-y-3.5 select-none flex flex-col items-end w-full lg:-translate-x-[42px]' : 'space-y-3.5 select-none flex flex-col items-end w-[390px] flex-shrink-0',
          mobileHomeTab === 'projects' ? '' : 'mobile-home-panel-hidden'
        ]">
          <!-- Button Tạo dự án -->
          <button @click="isModalOpen = true" type="button"
            class="mobile-icon-button create-project-action w-fit bg-transparent hover:bg-gray-200/40 border-2 border-[#4d4d4d] text-gray-900 font-extrabold text-sm rounded-md px-4.5 py-2.5 flex items-center justify-center gap-1 transition-colors cursor-pointer focus:outline-none select-none"
            title="Tạo dự án mới (Ctrl + K)">
            <span class="text-base font-normal mr-0.5">+</span>
            <span>Tạo dự án mới</span>
          </button>

          <!-- Project / Customer Switcher (Simple Button) -->
          <button @click="toggleCustomerGroup" type="button" :disabled="isViewModeChanging"
            class="mobile-icon-button view-switch-action w-fit bg-transparent hover:bg-gray-200/40 border-2 border-[#4d4d4d] text-gray-900 font-extrabold text-sm rounded-md px-4.5 py-2.5 flex items-center justify-center transition-colors cursor-pointer focus:outline-none select-none"
            :class="isGroupedByCustomer ? 'bg-emerald-50/10' : ''" title="Chuyển đổi chế độ xem (Ctrl + B)">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-list-ol text-sm text-[#4d4d4d]"></i>
              <span>Đổi kiểu xem</span>
            </div>
          </button>

          <!-- Tìm kiếm gì đó -->
          <div class="search-input-wrapper relative w-full max-w-[270px]"
            :class="{ 'mobile-search-open': isMobileSearchOpen }">
            <i
              class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#4d4d4d] text-sm"></i>
            <input ref="searchInputRef" v-model="projectStore.searchQuery" type="text" placeholder="Tìm kiếm"
              class="w-full bg-transparent border-2 border-[#4d4d4d] rounded-md pl-10 pr-4 py-2.5 text-sm font-extrabold text-gray-900 focus:outline-none placeholder-gray-400" />
          </div>
          <button type="button" class="mobile-search-toggle mobile-icon-button"
            @click="isMobileSearchOpen = !isMobileSearchOpen" title="Tìm kiếm">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
          </button>

          <!-- Keyboard shortcuts are collapsed by default to leave room for TV. -->
          <div class="keyboard-hints relative w-full max-w-[270px] flex justify-end">
            <button type="button" @click="toggleShortcutHints" class="shortcut-hints-toggle" title="Phím tắt">
              <i class="fa-solid fa-keyboard"></i>
            </button>
            <div v-if="isShortcutHintsOpen"
              class="shortcut-hints-popover bg-transparent border border-gray-300 rounded-xl p-3 shadow-3xs w-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="fa-solid fa-keyboard text-emerald-600 text-sm"></i>
                <span class="text-xs font-black text-gray-900 uppercase tracking-wider">Phím tắt</span>
              </div>
              <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Tạo dự án mới</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl
                    + K</kbd>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Tìm kiếm</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl
                    + F</kbd>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Chọn tất cả</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl
                    + A</kbd>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Bỏ chọn tất cả</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl
                    + Shift + A</kbd>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Chuyển đổi view</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">Ctrl
                    + B</kbd>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600 font-semibold">Đóng/Thoát</span>
                  <kbd
                    class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded text-gray-700 font-mono text-[10px]">ESC</kbd>
                </div>
              </div>
            </div>
          </div>

          <!-- Xương Rồng TV: only broadcasts from the last 24 hours are returned by the API. -->
          <Teleport to="body">
            <section v-if="tvPositionReady && currentBroadcast" :key="`tv-${viewMode}`"
              class="tv-broadcast-panel select-none" :class="{ 'is-notes-view': viewMode === 'notes' }"
              :style="viewMode === 'notes'
                ? { left: `${tvPanelLeft}px`, top: 'auto', bottom: '64px', transform: 'scale(1)', transformOrigin: 'right bottom' }
                : { left: `${tvPanelLeft}px`, top: tvPanelTop === null ? 'auto' : `${tvPanelTop}px`, bottom: tvPanelTop === null ? '64px' : 'auto', transform: `scale(${tvPanelScale})`, transformOrigin: tvPanelTop === null ? 'right bottom' : 'right top' }"
              aria-label="Xương Rồng TV">
              <div class="tv-broadcast-frame" :class="currentBroadcast?.type === 'bad' ? 'is-bad' : 'is-good'">
                <div class="tv-broadcast-screen">
                  <template v-if="currentBroadcast && currentBroadcast.type !== 'bad'">
                    <div v-if="currentBroadcast.recipient">
                      <div class="flex items-center gap-2.5">
                        <img :src="broadcastPerson?.avatar || defaultAvatar"
                          :alt="broadcastPerson?.name || 'Thành viên'" class="tv-broadcast-avatar" />
                        <div class="min-w-0">
                          <p class="tv-broadcast-name">{{ broadcastPerson?.name || 'Xương Rồng' }}</p>
                          <p class="tv-broadcast-label tv-broadcast-label-green">
                            <i class="fa-solid fa-star tv-broadcast-star"></i> ĐỈNH CỦA CHÓP
                          </p>
                        </div>
                      </div>
                      <p class="tv-broadcast-content">{{ currentBroadcast.content }}</p>
                    </div>
                    <div v-else class="tv-broadcast-good">
                      <p class="tv-broadcast-good-label"><i class="fa-solid fa-circle-check"></i> ĐỈNH CỦA CHÓP</p>
                      <p class="tv-broadcast-good-content">{{ currentBroadcast.content }}</p>
                    </div>
                  </template>
                  <template v-else-if="currentBroadcast">
                    <div class="tv-broadcast-bad">
                      <p class="tv-broadcast-bad-label"><i class="fa-solid fa-circle-exclamation"></i> KHÔNG TỐT</p>
                      <p class="tv-broadcast-bad-content">{{ currentBroadcast.content }}</p>
                    </div>
                  </template>
                  <div v-else class="tv-broadcast-empty">
                    <i class="fa-solid fa-tv"></i>
                    <span>Chưa có phát sóng mới</span>
                  </div>
                </div>
                <div class="tv-broadcast-console">
                  <span></span><span></span><span></span>
                  <button type="button" @click="previousBroadcast" :disabled="broadcasts.length < 2"
                    title="Bản tin trước"><i class="fa-solid fa-chevron-left"></i></button>
                  <button type="button" @click="nextBroadcast" :disabled="broadcasts.length < 2"
                    title="Bản tin tiếp theo"><i class="fa-solid fa-chevron-right"></i></button>
                  <i class="tv-broadcast-power"></i>
                </div>
                <div class="tv-broadcast-leg tv-broadcast-leg-left"></div>
                <div class="tv-broadcast-leg tv-broadcast-leg-right"></div>
              </div>
              <div class="tv-broadcast-controls">
                <p class="tv-broadcast-status">{{ broadcasts.length ? `Bản tin ${currentBroadcastIndex +
                  1}/${broadcasts.length}` :
                  'Đang chờ bản tin' }}</p>
              </div>
            </section>
          </Teleport>
        </aside>

        <!-- Dynamic/Faded Wrapper for panels on mobile -->
        <div class="mobile-panels-container">
          <!-- CENTER PANEL: Projects List (Block 2 - Wider Column, expands when notes view) -->
          <section ref="projectListPanelRef" class="project-list-panel"
            :class="[viewMode === 'notes' ? 'space-y-3.5 select-none w-full' : 'space-y-3.5 select-none w-[420px] flex-shrink-0', mobileHomeTab === 'projects' ? '' : 'mobile-home-panel-hidden']">

            <!-- Skeleton Loading State -->
            <div v-if="projectStore.isLoading && displayedProjects.length === 0"
              class="space-y-3 max-h-[calc(100vh-200px)]">
              <div v-for="i in 3" :key="'sk-proj-' + i"
                class="rounded-2xl p-4 bg-white/80 border border-gray-200 animate-pulse flex items-center justify-between h-20 shadow-3xs">
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
            <div v-else-if="isGroupedByCustomer" ref="scrollContainerGrouped" @scroll="handleScroll"
              class="project-scroll-container space-y-6 max-h-[calc(100vh-226px)] overflow-y-auto scrollbar-none md:ml-[-16px] md:mr-[-16px]">
              <div v-for="group in projectsByCustomer" :key="group.name" class="space-y-2.5">
                <!-- Customer Header -->
                <div class="flex items-center gap-2 pt-1 select-none md:pl-4">
                  <h3 class="text-xl font-black text-gray-900 tracking-tight font-heading">{{ group.name }}</h3>
                  <button @click.stop="togglePinCustomer(group.name)" type="button"
                    class="p-1 transition-colors hover:opacity-80"
                    :title="group.is_pinned ? 'Bỏ ghim khách hàng' : 'Ghim khách hàng'">
                    <i class="text-base transition-colors"
                      :class="group.is_pinned ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-400 hover:text-gray-600'"></i>
                  </button>
                </div>

                <!-- Customer Projects List -->
                <div class="space-y-2">
                  <div v-for="(project, pIdx) in group.projects" :key="project.id" :data-project-id="project.id"
                    draggable="true" @dragstart="onGroupedDragStart($event, project, group, pIdx)"
                    @dragover.prevent="onGroupedDragOver($event, pIdx)" @dragleave="onGroupedDragLeave"
                    @drop="onGroupedDrop($event, group, pIdx)" @dragend="onGroupedDragEnd"
                    class="flex items-center transition-all duration-150 rounded-2xl group/project-row relative w-full"
                    :class="{
                      'opacity-40 scale-[0.98]': draggedGroupedIndex === pIdx && draggedGroupId === group.name,
                      'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverGroupedIndex === pIdx && dragOverGroupId === group.name && (draggedGroupedIndex !== pIdx || draggedGroupId !== group.name)
                    }">

                    <!-- Same multi-select behavior as the default project view -->
                    <input type="checkbox" :checked="isSelected(project.id)"
                      @click.stop="toggleProjectSelect(project.id, $event)"
                      class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer transition-opacity duration-200 absolute left-2 md:left-0 top-1/2 -translate-y-1/2"
                      :class="showAllCheckboxes ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100'" />

                    <!-- Colored Project Rectangular Card (Identical to default mode) -->
                    <div @click="goToProjectDetail(project.id, $event)"
                      class="project-card w-full md:w-[380px] ml-auto md:mx-auto rounded-lg p-4 flex items-center justify-between gap-1 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden min-w-0"
                      :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]">
                      <div class="min-w-0 flex-1">
                        <div class="font-extrabold text-gray-900 text-sm sm:text-base leading-snug break-words min-w-0">
                          {{ project.title }}
                        </div>
                      </div>

                      <div class="flex-shrink-0">
                        <!-- Pin/Star Button -->
                        <button @click.stop="togglePinProject(project)" type="button"
                          class="p-1 cursor-pointer transition-opacity"
                          :class="Boolean(project.is_pinned) ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100 focus-visible:opacity-100'"
                          :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'">
                          <i class="text-lg transition-colors"
                            :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty state for grouped mode -->
              <div v-if="projectsByCustomer.length === 0"
                class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-450 text-sm font-semibold shadow-3xs select-none">
                Không tìm thấy dự án nào trong mục này.
              </div>
            </div>

            <!-- Sticky Notes View (Grid Layout) -->
            <div v-else-if="viewMode === 'notes'" ref="scrollContainerNotes" @scroll="handleScroll"
              class="project-scroll-container overflow-y-auto pr-1 pb-8 max-h-[calc(100vh-226px)] scrollbar-none">
              <div class="sticky-grid">
                <div v-for="project in displayedProjects" :key="project.id" :data-project-id="project.id"
                  @click="goToProjectDetail(project.id, $event)" class="note-card" :class="getStickyNoteStyle(project)">
                  <!-- Pin top center -->
                  <span class="note-pin" :class="getStickyNotePinStyle(project)"></span>

                  <!-- Checkmark (top left) when selected -->
                  <div v-if="isSelected(project.id)"
                    class="absolute top-[6px] left-[6px] z-20 flex items-center justify-center"
                    style="position: absolute !important; z-index: 20 !important;">
                    <i class="fa-solid fa-circle-check text-[#1A7A56] text-xl bg-white rounded-full shadow-3xs"></i>
                  </div>

                  <!-- Star icon (top right) -->
                  <button @click.stop="togglePinProject(project)" type="button" class="note-star"
                    :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'">
                    <i class="text-lg transition-colors"
                      :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"></i>
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
              <div v-if="displayedProjects.length === 0"
                class="bg-white/80 backdrop-blur-xs border border-gray-200 rounded-2xl py-12 text-center text-gray-500 text-sm font-bold shadow-3xs select-none">
                Không có dự án đã ghim nào.
              </div>
            </div>

            <!-- Default Cards list -->
            <div v-else ref="scrollContainerDefault" @scroll="handleScroll"
              class="project-scroll-container space-y-3.5 max-h-[calc(100vh-226px)] overflow-y-auto scrollbar-none ml-[-16px] mr-[-16px]">
              <transition-group enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2">
                <div v-for="(project, index) in displayedProjects" :key="project.id" :data-project-id="project.id"
                  draggable="true" @dragstart="onDragStart($event, project, index)"
                  @dragover.prevent="onDragOver($event, index)" @dragleave="onDragLeave($event)"
                  @drop="onDrop($event, index)" @dragend="onDragEnd($event)"
                  class="flex items-center transition-all duration-150 rounded-2xl group/project-row relative w-full"
                  :class="{
                    'opacity-40 scale-[0.98]': draggedProjectIndex === index,
                    'ring-2 ring-emerald-500 bg-emerald-50/50 p-1': dragOverIndex === index && draggedProjectIndex !== index
                  }">
                  <!-- Checkbox for multi-select (outside the card, but inside the scroll container) -->
                  <input type="checkbox" :checked="isSelected(project.id)"
                    @click.stop="toggleProjectSelect(project.id, $event)"
                    class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 cursor-pointer transition-opacity duration-200 absolute left-2 md:left-0 top-1/2 -translate-y-1/2"
                    :class="showAllCheckboxes ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100'" />

                  <!-- Card Container -->
                  <div @click="goToProjectDetail(project.id, $event)"
                    class="project-card w-full md:w-[380px] ml-auto md:mx-auto rounded-lg p-4 flex items-start justify-between gap-1 cursor-pointer shadow-3xs transition-shadow hover:shadow-2xs select-none relative overflow-hidden min-w-0"
                    :class="[getProjectStatusStyle(project).cardBg, getProjectStatusStyle(project).borderClass]">
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
                      <button @click.stop="togglePinProject(project)" type="button"
                        class="p-1 cursor-pointer transition-opacity"
                        :class="Boolean(project.is_pinned) ? 'opacity-100' : 'opacity-0 group-hover/project-row:opacity-100 focus-visible:opacity-100'"
                        :title="Boolean(project.is_pinned) ? 'Bỏ ghim dự án' : 'Ghim dự án'">
                        <i class="text-lg transition-colors"
                          :class="Boolean(project.is_pinned) ? 'fa-solid fa-star text-gray-600' : 'fa-regular fa-star text-gray-500 hover:text-gray-700'"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </transition-group>

              <!-- Empty projects state -->
              <div v-if="displayedProjects.length === 0"
                class="bg-white border border-gray-200 rounded-2xl py-12 text-center text-gray-450 text-sm font-semibold shadow-3xs select-none">
                Không tìm thấy dự án nào trong mục này.
              </div>

              <!-- Load More Button -->
              <div
                v-if="displayedProjects.length < projectStore.projects.filter(p => p.tracking_status === 'following').length"
                class="flex justify-center pt-2">
                <button @click="loadMore" type="button"
                  class="px-5 py-2.5 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-500 text-gray-700 hover:text-emerald-700 font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-2 shadow-3xs focus:outline-none">
                  <i class="fa-solid fa-angles-down text-[10px]"></i>
                  <span>Xem thêm dự án (Còn {{projectStore.projects.filter(p => p.tracking_status ===
                    'following').length
                    - displayedProjects.length}} dự án)</span>
                </button>
              </div>
            </div>
          </section>

          <!-- RIGHT PANEL: Hoạt động gần đây (Block 3 - Hidden in notes view) -->
          <section v-if="viewMode !== 'notes'"
            class="recent-activity-panel bg-transparent flex flex-col h-[calc(100vh-226px)] select-none w-[390px] flex-shrink-0"
            :class="mobileHomeTab === 'activities' ? '' : 'mobile-home-panel-hidden'">

            <!-- Header Actions: Lọc người nhắc đến & Tất cả hoạt động gần đây -->
            <div v-if="activities.length > 0" class="flex-shrink-0 flex gap-2 mb-2">
              <button @click="showMentionedActivities = !showMentionedActivities" type="button"
                :title="showMentionedActivities ? 'Hiện tất cả hoạt động' : 'Chỉ hiện hoạt động có nhắc đến bạn'"
                class="w-11 shrink-0 py-2 border rounded-xl font-extrabold text-sm transition-all cursor-pointer shadow-3xs focus:outline-none flex items-center justify-center"
                :class="showMentionedActivities
                  ? 'bg-emerald-600 border-emerald-600 text-white'
                  : 'bg-[#f6f4ef] hover:bg-emerald-50 border-gray-300 text-[#1A7A56]'">
                <i class="fa-solid fa-at"></i>
              </button>

              <button @click="router.push('/feed')" type="button"
                class="flex-1 py-2 bg-[#f6f4ef] hover:bg-gray-150 border border-gray-300 text-gray-900 font-extrabold text-xs rounded-xl transition-all cursor-pointer shadow-3xs focus:outline-none text-center">
                Tất cả hoạt động gần đây
              </button>
            </div>

            <!-- Skeleton Loading State -->
            <div v-if="isActivitiesLoading && displayedActivities.length === 0"
              class="space-y-3.5 flex-1 overflow-hidden pt-2">
              <div v-for="i in 4" :key="'sk-act-' + i" class="animate-pulse space-y-2 pb-3 border-b border-gray-100">
                <div class="flex justify-between items-center">
                  <div class="w-1/3 h-4 bg-gray-200 rounded-md"></div>
                  <div class="w-12 h-3 bg-gray-150 rounded-md"></div>
                </div>
                <div class="w-3/4 h-3 bg-gray-150 rounded-md"></div>
              </div>
            </div>

            <!-- Activity Feed List -->
            <div v-else class="flex-1 flex flex-col justify-between min-h-0">
              <div class="activity-feed-scroll space-y-0 overflow-y-auto scrollbar-none flex-1">
                <transition-group enter-active-class="transition duration-300 ease-out"
                  enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 -translate-y-2">
                  <div v-for="(log, idx) in displayedActivities" :key="log.id"
                    class="activity-log-item relative flex gap-3 select-none pb-6 group cursor-pointer"
                    @touchstart="handleTouchStart(log, $event)" @touchend="handleTouchEnd" @touchmove="handleTouchMove">

                    <!-- Absolute Timeline Line connecting avatars across padding boundaries -->
                    <div v-if="idx < displayedActivities.length - 1"
                      class="absolute top-11 bottom-2 left-[18px] w-[1.5px] bg-gray-300 z-0"></div>

                    <!-- Left Timeline column: Avatar -->
                    <div class="flex-shrink-0 w-9 z-10">
                      <img
                        :src="log.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=60'"
                        class="w-9 h-9 rounded-full object-cover border border-gray-250 relative z-10" />
                    </div>

                    <!-- Right content column -->
                    <div class="flex-1 min-w-0 pt-0.5 z-10">
                      <!-- Top Row: User Name + hỗ trợ Customer & Timestamp -->
                      <div class="flex items-center justify-between gap-2">
                        <div class="font-extrabold text-sm text-gray-900 truncate">
                          <span>{{ log.user ? log.user.name : 'Hệ thống' }}</span>
                          <template v-if="log.project?.customer">
                            <span class="font-extrabold text-gray-900"> hỗ trợ </span>
                            <span class="text-[#1A7A56] cursor-pointer hover:underline"
                              @click.stop="$router.push(`/customers/${log.project.customer.id}`)">{{
                                log.project.customer.name }}</span>
                          </template>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                          <button v-if="log.project_id || log.project?.id" @click="handleReplyToActivity(log)"
                            type="button" title="Trả lời hoạt động này"
                            class="opacity-0 group-hover:opacity-100 transition-all duration-200 bg-white hover:bg-emerald-600 text-gray-500 hover:text-white border border-gray-200 hover:border-emerald-600 cursor-pointer rounded-full h-6 w-6 flex items-center justify-center shadow-3xs focus:outline-none shrink-0"
                            :class="{ 'opacity-100': activeLogIdForMobileActions === log.id }">
                            <i class="fa-solid fa-reply text-[10px]"></i>
                          </button>
                          <span class="text-[11px] text-gray-400 font-bold">
                            {{ formatCommentRelativeTime(log.created_at) }}
                          </span>
                        </div>
                      </div>

                      <!-- Project Link/Title (Green Bold text matching Image 2) -->
                      <div v-if="log.project" @click="handleActivityProjectClick(log.project.id, $event)"
                        class="activity-project-link text-[#1A7A56] hover:underline font-extrabold text-sm cursor-pointer mt-1 mb-1 max-w-full truncate block"
                        :title="log.project.title">
                        {{ log.project.title }}
                      </div>

                      <!-- Comment Content (Plain text, no border card box) -->
                      <div class="text-xs font-semibold text-gray-700 leading-relaxed break-words mt-1 space-y-1.5">
                        <!-- Zalo Quote Reply Preview inside list feed -->
                        <div v-if="parseReplyInfo(log.content)"
                          class="bg-[#e1e3ea] px-2.5 py-1.5 rounded-r-md rounded-l-xs border-l-2 border-emerald-500 text-xs mb-1.5 select-none max-w-full">
                          <div class="text-[10px] font-bold text-gray-500 flex items-center gap-1">
                            <i class="fa-solid fa-reply text-[9px]"></i>
                            <span>{{ parseReplyInfo(log.content).user }}</span>
                          </div>
                          <div class="text-[10px] text-gray-450 truncate mt-0.5 max-w-[280px]">
                            {{ parseReplyInfo(log.content).text }}
                          </div>
                        </div>

                        <div v-if="parseCommentText(log.content)" class="whitespace-pre-line">
                          {{ parseCommentText(log.content) }}
                        </div>

                        <!-- Render Attachments -->
                        <div
                          v-if="parseCommentImages(log.content).length > 0 || parseCommentFiles(log.content).length > 0"
                          class="flex flex-wrap items-end gap-1.5 pt-0.5">
                          <!-- Images -->
                          <button v-for="(img, imgIdx) in parseCommentImages(log.content)" :key="'img-' + imgIdx"
                            type="button" @click.stop="openImagePreview(img.url)"
                            class="w-10 h-10 rounded border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-300 transition-all flex-shrink-0"
                            :title="'Xem ảnh: ' + img.name">
                            <img :src="img.url" class="w-full h-full object-cover" alt="" loading="lazy" />
                          </button>

                          <!-- Files -->
                          <a v-for="(file, fIdx) in parseCommentFiles(log.content)" :key="'file-' + fIdx"
                            :href="file.url" :download="file.name" target="_blank" @click.stop
                            class="w-7 h-9 rounded-sm border border-[#d4a574] bg-[#f5e6d0] hover:bg-[#edd9bc] flex flex-col items-center justify-end overflow-hidden cursor-pointer transition-colors flex-shrink-0"
                            :title="'Tải xuống: ' + file.name">
                            <i class="fa-solid fa-file text-[#c87828] text-[11px] mb-0.5"></i>
                            <span
                              class="text-[7px] font-bold text-[#8b5a2b] bg-[#e8c99a] w-full text-center py-0.5 leading-none">FILE</span>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </transition-group>

                <!-- Empty activities state -->
                <div v-if="displayedActivities.length === 0"
                  class="py-12 text-center text-gray-450 text-xs font-semibold">
                  Chưa có cập nhật hoạt động nào mới.
                </div>
              </div>

              <!-- Chat Input box: dùng chung cho dashboard và trang Có gì mới -->
              <ActivityComposer ref="activityComposerRef" v-model="chatMessage" v-model:project-id="chatProjectId"
                class="mt-2 flex-shrink-0" :projects="followingProjects" :users="projectStore.users"
                :groups="mentionGroups" :replying-to="replyingToLog"
                :reply-text="parseCommentText(replyingToLog?.content)" :submitting="isSubmittingChat"
                @submit="submitChat" @cancel-reply="cancelReply" />

              <div class="h-4 sm:h-1 flex-shrink-0"></div>
            </div>
          </section>
        </div>
      </div>
    </main>

    <!-- Drag Selection Box -->
    <div v-if="selectionBox.visible" :style="getSelectionBoxStyle"></div>

    <!-- Floating Bulk Update Action Bar (Command Bar at TOP matching image 2) -->
    <transition enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-10 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100 scale-100"
      leave-to-class="transform -translate-y-10 opacity-0 scale-95">
      <div v-if="selectedProjectIds.length > 0" ref="bulkActionBarRef"
        class="bulk-action-bar fixed top-20 sm:top-[88px] left-1/2 -translate-x-1/2 z-50 bg-[#fafaf7] sm:bg-white/95 backdrop-blur-md px-3.5 py-2.5 sm:px-6 sm:py-3 rounded-2xl shadow-2xl border border-gray-200/90 flex items-center gap-2.5 sm:gap-4 max-w-4xl select-none transition-all">
        <!-- LEFT: COUNT BADGE & TEXT -->
        <div class="flex items-center gap-2 sm:gap-2.5">
          <div
            class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#fbd37d] text-gray-900 font-extrabold flex items-center justify-center text-xs sm:text-sm flex-shrink-0 shadow-2xs">
            {{ selectedProjectIds.length }}
          </div>
          <div class="bulk-selection-summary flex flex-col items-start leading-none">
            <span class="text-xs sm:text-sm font-extrabold text-gray-900 leading-tight whitespace-nowrap">Đã chọn {{
              selectedProjectIds.length }} dự án</span>
            <button @click="selectedProjectIds = []; showAllCheckboxes = false" type="button"
              class="text-[10px] sm:text-[11px] text-gray-400 hover:text-gray-600 font-bold cursor-pointer leading-tight mt-0.5">Bỏ
              chọn</button>
          </div>
        </div>

        <!-- DIVIDER -->
        <div class="bulk-divider h-6 w-px bg-gray-200 flex-shrink-0 mx-0.5 sm:mx-1"></div>

        <!-- DESKTOP STATUS PILL BUTTONS (hidden on mobile) -->
        <div class="hidden sm:flex items-center gap-2">
          <button @click="selectBulkStatusOption('following')" type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'following' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'">
            <i class="fa-solid fa-flag text-xs"></i>
            <span>Đang theo</span>
          </button>

          <button @click="selectBulkStatusOption('not_following')" type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'not_following' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'">
            <i class="fa-solid fa-eye-slash text-xs"></i>
            <span>Không theo</span>
          </button>

          <button @click="selectBulkStatusOption('completed')" type="button"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs border"
            :class="selectedBulkStatus === 'completed' ? 'bg-[#fbd37d] text-gray-900 border-amber-300 hover:bg-[#fcd34d]' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200'">
            <i class="fa-regular fa-circle-check text-xs"></i>
            <span>Hoàn thành</span>
          </button>
        </div>

        <!-- MOBILE STATUS DROPDOWN MENU (shown on sm:hidden) -->
        <div class="bulk-status-control sm:hidden block relative">
          <button @click="isMobileStatusDropdownOpen = !isMobileStatusDropdownOpen" type="button"
            class="px-2.5 py-1.5 bg-white border border-gray-200 text-gray-800 font-extrabold text-xs rounded-xl flex items-center gap-1 shadow-2xs cursor-pointer">
            <span>{{ getBulkStatusLabel(selectedBulkStatus) }}</span>
            <i class="fa-solid fa-chevron-down text-[9px] text-gray-500"></i>
          </button>

          <div v-if="isMobileStatusDropdownOpen"
            class="absolute top-full mt-2 left-0 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-1.5 min-w-[135px] flex flex-col gap-1 ring-1 ring-black/5">
            <button @click="selectBulkStatusOption('following')" type="button"
              class="px-2.5 py-1.5 hover:bg-amber-50 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-solid fa-flag text-amber-500 text-xs"></i>
              <span>Đang theo</span>
            </button>
            <button @click="selectBulkStatusOption('not_following')" type="button"
              class="px-2.5 py-1.5 hover:bg-gray-100 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-solid fa-eye-slash text-gray-500 text-xs"></i>
              <span>Không theo</span>
            </button>
            <button @click="selectBulkStatusOption('completed')" type="button"
              class="px-2.5 py-1.5 hover:bg-emerald-50 text-xs font-bold rounded-lg text-left text-gray-800 flex items-center gap-2">
              <i class="fa-regular fa-circle-check text-emerald-600 text-xs"></i>
              <span>Hoàn thành</span>
            </button>
          </div>
        </div>

        <!-- DIVIDER -->
        <div class="bulk-divider h-6 w-px bg-gray-200 flex-shrink-0 mx-0.5 sm:mx-1"></div>

        <!-- RIGHT: SUBMIT "HÚ HÚ" BUTTON -->
        <button @click="goToBulkUpdate" type="button"
          class="bulk-submit px-3.5 py-1.5 sm:px-5 sm:py-2.5 bg-[#45A246] hover:bg-[#3a903b] text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 sm:gap-2 shadow-xs transition-colors cursor-pointer flex-shrink-0">
          <i class="fa-solid fa-dove text-sm"></i>
          <span>Hú Hú</span>
        </button>
      </div>
    </transition>



    <!-- Project Modal -->
    <ProjectModal :is-open="isModalOpen" :customers="projectStore.customers" :users="projectStore.users"
      :edit-project="editingProject" @close="handleCloseModal" @submit="handleCreateProject"
      @customer-created="projectStore.fetchAuxData()" />

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
              <img :src="editForm.avatar"
                class="w-20 h-20 rounded-full object-cover border-2 border-emerald-400 shadow-md" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tên tài khoản</label>
              <input v-model="editForm.name" required type="text"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
              <input v-model="editForm.email" required type="email"
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500" />
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" @click="isProfileModalOpen = false"
                class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl">
                Hủy
              </button>
              <button type="submit"
                class="px-5 py-2 bg-[#45A246] hover:bg-[#3a903b] text-white text-sm font-bold rounded-xl shadow-2xs transition-colors cursor-pointer">
                Lưu thay đổi
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <!-- Create View Modal -->
    <div v-if="isViewModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
      aria-modal="true">
      <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="isViewModalOpen = false">
      </div>

      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
          class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 flex flex-col max-h-[85vh]">

          <!-- Modal Header -->
          <div
            class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
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
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tên View <span
                  class="text-rose-500">*</span></label>
              <input v-model="newViewName" type="text" required placeholder="VD: Dự án hạ tầng, Dự án camera..."
                class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
            </div>

            <!-- Choose Projects (Master Data) -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Chọn các dự án hiển thị trong View
                này</label>

              <!-- Search box to filter projects inside modal -->
              <div class="relative mb-3">
                <i
                  class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input v-model="modalProjectSearchQuery" type="text" placeholder="Tìm nhanh tên dự án..."
                  class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-emerald-500 bg-gray-50/50" />
              </div>

              <!-- Projects list with checkboxes -->
              <div
                class="border border-gray-100 rounded-xl divide-y divide-gray-100 max-h-56 overflow-y-auto scrollbar-thin">
                <label v-for="p in filteredModalProjects" :key="p.id"
                  class="flex items-center gap-3 px-3.5 py-2.5 hover:bg-gray-50 cursor-pointer select-none transition-colors">
                  <input type="checkbox" :value="p.id" v-model="selectedViewProjectIds"
                    class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-4 h-4" />
                  <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-gray-888 truncate">{{ p.title }}</div>
                    <div class="text-[10px] text-gray-400 font-semibold mt-0.5">
                      {{ p.customer ? p.customer.name : 'Chưa phân khách hàng' }}
                    </div>
                  </div>
                </label>

                <div v-if="filteredModalProjects.length === 0"
                  class="p-6 text-center text-xs text-gray-400 font-medium">
                  Không tìm thấy dự án nào.
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/30">
            <button type="button" @click="isViewModalOpen = false"
              class="px-4.5 py-2 hover:bg-gray-100 text-gray-600 font-semibold text-xs rounded-xl transition-colors cursor-pointer">
              Hủy bỏ
            </button>
            <button type="button" @click="saveNewView" :disabled="!newViewName.trim()"
              class="px-5 py-2 bg-[#45A246] hover:bg-[#3a903b] disabled:bg-emerald-300 text-white font-bold text-xs rounded-xl transition-colors shadow-2xs cursor-pointer">
              Tạo View
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div v-if="activePreviewImage"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
      @click="activePreviewImage = null">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
        <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
        <button @click="activePreviewImage = null" type="button"
          class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 hover:bg-slate-900 text-white rounded-full flex items-center justify-center transition-colors shadow-lg cursor-pointer">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Bottom Navigation Tabs -->
    <div v-if="viewMode !== 'notes'" class="mobile-home-tabs">
      <button type="button" @click="mobileHomeTab = 'activities'"
        class="flex-1 rounded-lg py-2.5 text-sm font-extrabold transition-colors"
        :class="mobileHomeTab === 'activities' ? 'bg-emerald-600 text-white' : 'text-gray-500'">Hoạt động</button>
      <button type="button" @click="mobileHomeTab = 'projects'"
        class="flex-1 rounded-lg py-2.5 text-sm font-extrabold transition-colors"
        :class="mobileHomeTab === 'projects' ? 'bg-emerald-600 text-white' : 'text-gray-500'">Dự án</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import ActivityComposer from '../components/ActivityComposer.vue'
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
const defaultAvatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'
const viewActionsRef = ref(null)
const projectListPanelRef = ref(null)
const bulkActionBarRef = ref(null)
const tvPanelLeft = ref(32)
const tvPositionReady = ref(false)
const tvPanelTop = ref(null)
const tvPanelScale = ref(1)
const isShortcutHintsOpen = ref(false)
const mobileHomeTab = ref('activities')

const updateTvPosition = () => {
  const panel = viewActionsRef.value
  if (!panel || window.innerWidth < 768) return
  // The shortcut card is right-aligned inside this panel; align the TV's right
  // edge with it rather than with the browser edge.
  const rect = panel.getBoundingClientRect()
  // Visual TV height (including its controls/legs) is about 245px; using a
  // larger reserve made the low-height layout unnecessarily tiny.
  const tvHeight = 245
  const safeGap = 16
  // Keep a bottom band equal to the app header height.
  const reservedBottomSpace = 64
  const tvBottomOffset = reservedBottomSpace
  const bottomPositionTop = window.innerHeight - tvBottomOffset - tvHeight
  const firstAvailableTop = rect.bottom + safeGap

  tvPanelLeft.value = Math.max(16, Math.round(rect.right - 300))
  if (bottomPositionTop >= firstAvailableTop) {
    tvPanelTop.value = null
    tvPanelScale.value = 1
  } else {
    tvPanelTop.value = Math.round(firstAvailableTop)
    tvPanelScale.value = Math.max(0.55, Math.min(1, (window.innerHeight - firstAvailableTop - 8) / tvHeight))
  }
  tvPositionReady.value = true
}

const toggleShortcutHints = async () => {
  isShortcutHintsOpen.value = !isShortcutHintsOpen.value
  await nextTick()
  updateTvPosition()
}

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
const isMobileSearchOpen = ref(false)
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
  // Browsers emit a click after mouseup. When that mouseup completed a drag
  // selection, do not let the card click overwrite the selected range.
  if (suppressNextProjectClick) return

  // On mobile, clicking a project card navigates directly to the detail page.
  if (window.matchMedia?.('(max-width: 767px)').matches) {
    router.push(`/projects/${projectId}`)
    return
  }

  // Double-click navigates to detail on desktop
  if (event.detail === 2) {
    router.push(`/projects/${projectId}`)
    return
  }

  if (event?.shiftKey && selectProjectRange(projectId, event)) {
    return
  }

  // Single click selection logic
  const isCtrlOrMeta = event && (event.ctrlKey || event.metaKey)

  if (isCtrlOrMeta) {
    // Ctrl + Click: Toggle selection
    const idx = selectedProjectIds.value.indexOf(projectId)
    if (idx > -1) {
      selectedProjectIds.value.splice(idx, 1)
      if (selectedProjectIds.value.length === 0) {
        showAllCheckboxes.value = false
      }
    } else {
      selectedProjectIds.value.push(projectId)
      showAllCheckboxes.value = true
    }
    lastSelectionAnchorId.value = projectId
    selectionFocusId.value = projectId
  } else {
    // Normal Click: Select only this project
    selectedProjectIds.value = [projectId]
    showAllCheckboxes.value = true
    lastSelectionAnchorId.value = projectId
    selectionFocusId.value = projectId
  }
}

const handleActivityProjectClick = (projectId, event) => {
  const isShownOnHome = displayedProjects.value.some(project => Number(project.id) === Number(projectId))

  // Activities can reference completed or unfollowed projects, which are not
  // selectable from the home list. Open their detail instead of creating a
  // hidden selection and showing the command bar.
  if (!isShownOnHome) {
    router.push(`/projects/${projectId}`)
    return
  }

  goToProjectDetail(projectId, event)
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
  if (isGroupedByCustomer.value) {
    return list
  }
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
const isViewModeChanging = ref(false)

watch(viewMode, async () => {
  // Each view has a different actions-column position. Recalculate the TV
  // after Vue has applied the new layout so it never keeps the old view's
  // coordinates and overlaps project notes.
  tvPanelTop.value = null
  tvPanelScale.value = 1
  await nextTick()
  requestAnimationFrame(updateTvPosition)
})

watch(() => authStore.user?.view_mode, (newVal) => {
  // Keep the optimistic selection on screen until its save request settles.
  // This prevents an older profile response from briefly switching the layout back.
  if (isViewModeChanging.value) return

  if (newVal && ['list', 'grouped', 'notes'].includes(newVal)) {
    viewMode.value = newVal
    isGroupedByCustomer.value = (newVal === 'grouped')
  }
}, { immediate: true })

const toggleCustomerGroup = async () => {
  if (isViewModeChanging.value) return

  // Cycle through view modes: list -> grouped -> notes -> list
  let nextMode = 'list'
  if (viewMode.value === 'list') {
    nextMode = 'grouped'
  } else if (viewMode.value === 'grouped') {
    nextMode = 'notes'
  }

  const previousMode = viewMode.value
  isViewModeChanging.value = true
  viewMode.value = nextMode
  isGroupedByCustomer.value = (nextMode === 'grouped')
  // Clear the previous view's fixed TV coordinates before the new view paints.
  tvPanelTop.value = null
  tvPanelScale.value = 1
  await nextTick()
  requestAnimationFrame(updateTvPosition)

  try {
    await authStore.updateViewMode(nextMode)
  } catch (error) {
    // Keep the screen and saved preference consistent if the update fails.
    viewMode.value = previousMode
    isGroupedByCustomer.value = (previousMode === 'grouped')
  } finally {
    isViewModeChanging.value = false
  }
}

// Pinned Customers logic
const pinnedCustomerNames = ref([])

watch(() => authStore.user?.pinned_customers, (newVal) => {
  pinnedCustomerNames.value = newVal ? [...newVal] : []
}, { immediate: true })

const togglePinCustomer = async (customerName) => {
  const currentPinned = [...pinnedCustomerNames.value]
  const idx = currentPinned.indexOf(customerName)
  if (idx > -1) {
    currentPinned.splice(idx, 1)
  } else {
    currentPinned.push(customerName)
  }
  pinnedCustomerNames.value = currentPinned
  await authStore.updatePinnedCustomers(currentPinned)
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
  } else {
    return 'bg-white border-gray-200 text-gray-900 font-bold shadow-3xs'
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
  const health = project.health || 'yellow'

  let cardBg = ''
  let borderClass = ''

  if (health === 'red') {
    cardBg = 'bg-[#fca5a5]' // Darker red
    borderClass = 'border-2 border-[#f87171]'
  } else {
    cardBg = 'bg-transparent shadow-sm' // Transparent background for other health status
    borderClass = 'border-2 border-[#4d4d4d]' // Dark border matching the buttons
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
  if (health === 'green') return 'note-white'
  if (health === 'red') return 'note-red'
  return 'note-white'
}

const getStickyNotePinStyle = (project) => {
  const health = project.health
  if (health === 'green') return 'pin-grey'
  if (health === 'red') return 'pin-red'
  return 'pin-grey'
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
  if (health === 'yellow' || health === 'white') return 'bg-white border border-gray-300 shadow-3xs'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-white border border-gray-300 shadow-3xs'
  return 'bg-gray-400'
}

const getActivityStyle = (log) => {
  const health = log.project_health
  if (health === 'green') {
    return 'bg-white border-gray-300 border text-gray-800'
  } else if (health === 'red') {
    return 'bg-[#fca5a5] border-[#f87171] border-2 text-gray-900'
  }
  return 'bg-white border-gray-300 border text-gray-800'
}

const getProjectPillStyle = (log) => {
  const health = log.project_health
  if (health === 'green') {
    return 'bg-emerald-50/80 hover:bg-emerald-100/90 border-emerald-200/60 text-emerald-800'
  } else if (health === 'red') {
    return 'bg-[#f87171]/20 hover:bg-[#f87171]/30 border-[#f87171]/60 text-red-900'
  }
  return 'bg-emerald-50/80 hover:bg-emerald-100/90 border-emerald-200/60 text-emerald-800'
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

  // File Explorer-style range adjustment: while Shift remains pressed,
  // move only the range focus one project at a time and retain its anchor.
  if (moveProjectRangeFocus(event)) {
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
  if (viewActionsRef.value && !viewActionsRef.value.querySelector('.keyboard-hints')?.contains(e.target)) {
    isShortcutHintsOpen.value = false
  }

  // A selection is only kept while the user is working in the project list or
  // its command bar. Clicking elsewhere cancels the selection and closes it.
  if (
    selectedProjectIds.value.length > 0 &&
    !suppressNextOutsideSelectionClear &&
    !projectListPanelRef.value?.contains(e.target) &&
    !bulkActionBarRef.value?.contains(e.target) &&
    !e.target.closest?.('.recent-activity-panel') &&
    !e.target.closest?.('.activity-project-link')
  ) {
    selectedProjectIds.value = []
    showAllCheckboxes.value = false
  }
}

// Checkboxes and multi-select
const selectedProjectIds = ref([])
const showAllCheckboxes = ref(false)
const lastSelectionAnchorId = ref(null)
// The fixed end of a Shift range and its moving end are tracked separately,
// just like File Explorer's anchor and focus item.
const selectionFocusId = ref(null)
let suppressNextOutsideSelectionClear = false
let suppressNextProjectClick = false

watch(selectedProjectIds, (ids) => {
  if (ids.length === 0) {
    lastSelectionAnchorId.value = null
    selectionFocusId.value = null
  }
  fetchActivities()
}, { deep: true })

// Drag selection box functionality (Windows-style)
const isSelecting = ref(false)
let selectionHasMoved = false
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
  if (target.closest('input, textarea, select, button, a, [contenteditable="true"], .cursor-pointer, [draggable="true"]')) {
    return
  }

  isSelecting.value = true
  selectionHasMoved = false
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
  if (
    Math.abs(event.clientX - selectionBox.value.startX) > 3 ||
    Math.abs(event.clientY - selectionBox.value.startY) > 3
  ) {
    selectionHasMoved = true
  }

  // Check which project cards intersect with selection box
  checkProjectIntersections()
}

const endSelection = () => {
  if (!isSelecting.value) return

  isSelecting.value = false
  selectionBox.value.visible = false

  // The following click event can land outside the list when the pointer is
  // released. Keep the completed drag selection instead of treating it as an
  // outside click that cancels the command bar.
  if (selectionHasMoved && selectedProjectIds.value.length > 0) {
    suppressNextOutsideSelectionClear = true
    suppressNextProjectClick = true
    window.setTimeout(() => {
      suppressNextOutsideSelectionClear = false
      suppressNextProjectClick = false
    }, 0)
  }
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
    lastSelectionAnchorId.value = newSelectedIds[0]
    selectionFocusId.value = newSelectedIds[newSelectedIds.length - 1]
  }
}

const getProjectSelectionOrder = () => {
  const cardIds = Array.from(projectListPanelRef.value?.querySelectorAll('[data-project-id]') || [])
    .map(card => Number(card.getAttribute('data-project-id')))
    .filter(Number.isFinite)

  return [...new Set(cardIds)]
}

const selectProjectRange = (id, event = null) => {
  const anchorId = lastSelectionAnchorId.value
  if (anchorId === null || anchorId === undefined) return false

  const orderedIds = getProjectSelectionOrder()
  const from = orderedIds.indexOf(Number(anchorId))
  const to = orderedIds.indexOf(Number(id))
  if (from === -1 || to === -1) return false

  const range = orderedIds.slice(Math.min(from, to), Math.max(from, to) + 1)
  // Shift replaces the current selection with the range from the fixed anchor.
  // Ctrl/Cmd + Shift extends the existing selection. Shift only moves focus,
  // never the anchor, so another Shift operation can extend or contract the
  // same range in either direction.
  selectedProjectIds.value = event?.ctrlKey || event?.metaKey
    ? [...new Set([...selectedProjectIds.value, ...range])]
    : range
  showAllCheckboxes.value = true
  selectionFocusId.value = id
  return true
}

const toggleProjectSelect = (id, event) => {
  if (event?.shiftKey && selectProjectRange(id, event)) return

  const idx = selectedProjectIds.value.indexOf(id)
  if (idx > -1) {
    selectedProjectIds.value.splice(idx, 1)
    if (selectedProjectIds.value.length === 0) {
      showAllCheckboxes.value = false
    }
  } else {
    selectedProjectIds.value.push(id)
    showAllCheckboxes.value = true
  }
  lastSelectionAnchorId.value = id
  selectionFocusId.value = id
}

const moveProjectRangeFocus = (event) => {
  if (!event.shiftKey || !['ArrowUp', 'ArrowDown'].includes(event.key)) return false
  if (lastSelectionAnchorId.value === null || selectedProjectIds.value.length === 0) return false

  const orderedIds = getProjectSelectionOrder()
  const focusId = selectionFocusId.value ?? lastSelectionAnchorId.value
  const currentIndex = orderedIds.indexOf(Number(focusId))
  if (currentIndex === -1) return false

  const nextIndex = currentIndex + (event.key === 'ArrowDown' ? 1 : -1)
  if (nextIndex < 0 || nextIndex >= orderedIds.length) {
    event.preventDefault()
    return true
  }

  event.preventDefault()
  selectProjectRange(orderedIds[nextIndex], event)
  nextTick(() => {
    projectListPanelRef.value
      ?.querySelector(`[data-project-id="${orderedIds[nextIndex]}"]`)
      ?.scrollIntoView({ block: 'nearest' })
  })
  return true
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

const parseReplyInfo = (content) => {
  if (!content) return null
  const m = content.match(/^\[reply:(\{.*?\})\]/)
  if (m) {
    try {
      return JSON.parse(m[1])
    } catch (e) {
      console.error('Failed to parse reply info:', e)
    }
  }
  return null
}

const parseCommentText = (content) => {
  if (!content) return ''
  return content
    .replace(/^\[reply:\{.*?\}\]/, '')
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

  // 2. New server-backed attachment links.
  const htmlLinkRegex = /<a\b[^>]*\bhref=["']([^"']+)["'][^>]*>[\s\S]*?📎\s*Tệp đính kèm:\s*([^<]+)<\/a>/gi
  while ((m = htmlLinkRegex.exec(content)) !== null) {
    matches.push({ name: m[2].trim().replace(/&quot;/g, '"'), url: m[1] })
  }

  // 3. Legacy HTML file spans <span...>📎 Tệp đính kèm: name</span>
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
const showMentionedActivities = ref(false)
let activityRequestId = 0

// Chat / Reply logic for recent activities panel
const chatMessage = ref('')
const chatProjectId = ref(null)
const replyingToLog = ref(null)
const activityComposerRef = ref(null)
const activeLogIdForMobileActions = ref(null)

let touchTimer = null
let touchStarted = false

const handleTouchStart = (log, event) => {
  touchStarted = true
  touchTimer = setTimeout(() => {
    if (touchStarted) {
      if (navigator.vibrate) {
        navigator.vibrate(50)
      }
      activeLogIdForMobileActions.value = log.id
    }
  }, 500)
}

const handleTouchEnd = () => {
  touchStarted = false
  if (touchTimer) clearTimeout(touchTimer)
}

const handleTouchMove = () => {
  touchStarted = false
  if (touchTimer) clearTimeout(touchTimer)
}

const handleGlobalClick = (e) => {
  if (!e.target.closest('.activity-log-item')) {
    activeLogIdForMobileActions.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleGlobalClick)
})

onUnmounted(() => {
  document.removeEventListener('click', handleGlobalClick)
})

// Initialize chatProjectId once following projects are loaded
const followingProjects = computed(() => {
  return projectStore.projects.filter(p => p.tracking_status === 'following')
})

watch(() => followingProjects.value, (newProjects) => {
  if (newProjects && newProjects.length > 0) {
    if (!chatProjectId.value || !newProjects.some(p => p.id === chatProjectId.value)) {
      chatProjectId.value = newProjects[0].id
    }
  } else {
    chatProjectId.value = null
  }
}, { immediate: true })

const handleReplyToActivity = (log) => {
  replyingToLog.value = log
  chatProjectId.value = log.project_id || log.project?.id || followingProjects.value[0]?.id

  let mention = ''
  if (log.user) {
    mention = `@${log.user.name} `
  }

  chatMessage.value = mention

  nextTick(() => {
    activityComposerRef.value?.focus()
  })
}

const cancelReply = () => {
  replyingToLog.value = null
  chatMessage.value = ''
}

const isSubmittingChat = ref(false)

const submitChat = async () => {
  if (isSubmittingChat.value) return

  const pId = chatProjectId.value || followingProjects.value[0]?.id
  if (!pId) {
    toast.error('Vui lòng chọn hoặc tạo dự án để gửi cập nhật.')
    return
  }

  isSubmittingChat.value = true

  try {
    const attachmentHtml = await activityComposerRef.value?.buildAttachmentHtml() || ''
    if (!chatMessage.value.trim() && !attachmentHtml) return

    let finalContent = chatMessage.value + attachmentHtml
    if (replyingToLog.value) {
      const replyMeta = {
        user: replyingToLog.value.user ? replyingToLog.value.user.name : 'Hệ thống',
        text: parseCommentText(replyingToLog.value.content)
      }
      finalContent = `[reply:${JSON.stringify(replyMeta)}]` + finalContent
    }

    await axios.post('/api/comments', {
      project_id: pId,
      content: finalContent,
    })

    toast.success('Gửi cập nhật hoạt động thành công!')
    chatMessage.value = ''
    replyingToLog.value = null
    activityComposerRef.value?.clearAttachments()
    fetchActivities()
  } catch (err) {
    console.error('Failed to submit chat:', err)
    toast.error(err.response?.data?.message || err.message || 'Gửi cập nhật thất bại. Vui lòng thử lại.')
  } finally {
    isSubmittingChat.value = false
  }
}

const mentionGroups = ref([])

// The TV API already filters out broadcasts older than 24 hours. Records remain
// in the database; they simply stop being returned to this screen.
const broadcasts = ref([])
const currentBroadcastIndex = ref(0)
const currentBroadcast = computed(() => broadcasts.value[currentBroadcastIndex.value] || null)
const broadcastPerson = computed(() => currentBroadcast.value?.recipient || null)

const fetchBroadcasts = async () => {
  try {
    const res = await axios.get('/api/tv/broadcasts')
    const nextBroadcasts = res.data || []
    broadcasts.value = nextBroadcasts
    if (currentBroadcastIndex.value >= nextBroadcasts.length) currentBroadcastIndex.value = 0
  } catch (err) {
    console.error('Failed to load TV broadcasts:', err)
  }
}

const previousBroadcast = () => {
  if (broadcasts.value.length < 2) return
  currentBroadcastIndex.value = (currentBroadcastIndex.value - 1 + broadcasts.value.length) % broadcasts.value.length
}

const nextBroadcast = () => {
  if (broadcasts.value.length < 2) return
  currentBroadcastIndex.value = (currentBroadcastIndex.value + 1) % broadcasts.value.length
}

const displayedActivities = computed(() => {
  let list = activities.value

  if (selectedProjectIds.value.length > 0) {
    list = list.filter(log => {
      const pId = log.project_id || log.project?.id
      return pId && selectedProjectIds.value.includes(Number(pId))
    })
  }

  if (showMentionedActivities.value) {
    const mention = `@${(currentUser.value?.name || '').trim()}`.toLocaleLowerCase('vi-VN')
    if (!mention || mention === '@') return []
    list = list.filter(log => String(log.content || '').toLocaleLowerCase('vi-VN').includes(mention))
  }

  return list
})

async function fetchActivities() {
  const requestId = ++activityRequestId
  try {
    // The unfiltered dashboard stays compact. Once projects are selected, show
    // their complete activity window for the last seven days instead.
    const params = selectedProjectIds.value.length > 0
      ? { project_ids: selectedProjectIds.value, days: 7 }
      : { limit: 15 }
    const res = await axios.get('/api/comments', { params })
    const filtered = (res.data || []).filter(c => {
      return Boolean(c.project_id)
    })

    // Ignore a slower response for an older filter and avoid rebuilding the
    // timeline every four seconds when its records have not changed.
    if (requestId !== activityRequestId) return
    const isUnchanged = filtered.length === activities.value.length && filtered.every((activity, index) => {
      const current = activities.value[index]
      return current
        && activity.id === current.id
        && activity.updated_at === current.updated_at
        && activity.content === current.content
    })
    if (!isUnchanged) {
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
    await axios.put('/api/projects/bulk', {
      project_ids: idsToUpdate,
      health: color
    })
  } catch (err) {
    console.error(err)
    // On error, refresh to get correct state from server
    await projectStore.fetchProjects(true)
  }
}

const bulkUpdateLead = async (userId) => {
  try {
    await axios.put('/api/projects/bulk', {
      project_ids: selectedProjectIds.value,
      lead_id: userId
    })
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
  const previousStatuses = new Map(
    idsToUpdate.map(id => [id, projectStore.projects.find(project => project.id === id)?.tracking_status])
  )

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
    await axios.put('/api/projects/bulk', {
      project_ids: idsToUpdate,
      tracking_status: status
    })
    // The home screen only renders following projects. Fetch immediately after
    // saving so an in-flight polling response cannot put completed projects
    // back into the list temporarily.
    await projectStore.fetchProjects(true)
    toast.success('Đã cập nhật trạng thái dự án.')
  } catch (err) {
    console.error(err)
    // Restore the optimistic state before refreshing when the update fails.
    previousStatuses.forEach((previousStatus, id) => {
      const project = projectStore.projects.find(p => p.id === id)
      if (project && previousStatus) project.tracking_status = previousStatus
    })
    await projectStore.fetchProjects(true)
    toast.error('Không thể cập nhật trạng thái dự án. Vui lòng thử lại.')
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
let broadcastRotationTimer = null

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
  projectStore.activePage = 'home'
  projectStore.activeStatus = null
  loadCustomViews()
  await Promise.all([
    projectStore.fetchProjects(),
    projectStore.fetchAuxData(),
    fetchActivities(),
    fetchBroadcasts(),
    axios.get('/api/mention-groups').then(res => { mentionGroups.value = res.data || [] }).catch(() => { }),
  ])
  requestAnimationFrame(updateTvPosition)

  window.addEventListener('keydown', handleGlobalKeydown)
  window.addEventListener('click', closeAllDropdowns)

  // Drag selection events
  window.addEventListener('mousedown', startSelection)
  window.addEventListener('mousemove', updateSelection)
  window.addEventListener('mouseup', endSelection)
  window.addEventListener('resize', updateTvPosition)

  pollTimer = setInterval(() => {
    projectStore.fetchProjects(true)
    fetchActivities()
    fetchBroadcasts()
  }, 4000)

  broadcastRotationTimer = setInterval(() => {
    if (broadcasts.value.length > 1) {
      currentBroadcastIndex.value = (currentBroadcastIndex.value + 1) % broadcasts.value.length
    }
  }, 300000) // 5 minutes
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
  if (broadcastRotationTimer) clearInterval(broadcastRotationTimer)
  // Search is local to this screen; do not carry a hidden filter to the next visit.
  projectStore.searchQuery = ''
  isMobileSearchOpen.value = false
  window.removeEventListener('keydown', handleGlobalKeydown)
  window.removeEventListener('click', closeAllDropdowns)
  window.removeEventListener('resize', updateTvPosition)

  // Clean up drag selection events
  window.removeEventListener('mousedown', startSelection)
  window.removeEventListener('mousemove', updateSelection)
  window.removeEventListener('mouseup', endSelection)
})
</script>

<style scoped>
.view-page-intro {
  display: flex;
}

.mobile-panels-container {
  display: contents;
}

.mobile-home-tabs {
  display: none;
}

.activity-feed-scroll {
  border-radius: 12px;
  padding: 8px;
}

.shortcut-hints-toggle {
  width: 42px;
  height: 42px;
  border: 1px solid #cfd4d1;
  border-radius: 10px;
  color: #168343;
  background: #f9f4ee;
  font-size: 16px;
  cursor: pointer;
  transition: all .15s ease;
}

.shortcut-hints-toggle:hover {
  border-color: #45a246;
  background: #edf8ee;
}

.shortcut-hints-popover {
  position: absolute;
  top: 50px;
  right: 0;
  z-index: 40;
  background: #f9f4ee;
}

@media (max-width: 767px) {
  .view-page-intro {
    display: none !important;
  }
}

@media (max-width: 767px) {
  .view-page-layout {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 0 !important;
    height: calc(100vh - 64px - 58px - env(safe-area-inset-bottom, 0px)) !important;
    overflow: hidden !important;
    padding-bottom: 0 !important;
  }

  .view-actions {
    width: 100% !important;
    flex-direction: row !important;
    flex-wrap: wrap;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    margin-bottom: 12px;
  }

  .view-actions>* {
    margin-top: 0 !important;
  }

  .mobile-icon-button {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px;
    padding: 0 !important;
    border-radius: 10px !important;
    gap: 0 !important;
  }

  .create-project-action>span:last-child,
  .view-switch-action span {
    display: none;
  }

  .search-input-wrapper {
    display: none;
    order: 10;
    flex-basis: 100%;
    max-width: none !important;
    width: 100% !important;
  }

  .search-input-wrapper.mobile-search-open {
    display: block;
  }

  .mobile-search-toggle {
    display: inline-flex !important;
    border: 2px solid #4d4d4d;
    background: transparent;
    color: #4d4d4d;
    align-items: center;
    justify-content: center;
  }

  .keyboard-hints {
    display: none;
  }

  .mobile-panels-container {
    display: grid !important;
    grid-template-columns: 1fr;
    position: relative;
    width: 100%;
    flex: 1 !important;
    overflow: hidden !important;
  }

  .project-list-panel,
  .recent-activity-panel {
    grid-column: 1;
    grid-row: 1;
    width: 100% !important;
    max-width: none !important;
    justify-self: stretch !important;
    flex-shrink: 1 !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    padding: 0 16px 12px 16px !important;
    background-color: #F9F4EE !important;
    transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.22s;
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
  }

  .mobile-home-panel-hidden {
    display: none !important;
  }

  .recent-activity-panel.mobile-home-panel-hidden,
  .project-list-panel.mobile-home-panel-hidden {
    opacity: 0 !important;
    transform: translateY(12px) !important;
    visibility: hidden !important;
    pointer-events: none !important;
    display: flex !important;
  }

  .view-actions.mobile-home-panel-hidden {
    display: none !important;
  }

  .mobile-home-tabs {
    position: fixed !important;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 45;
    display: flex !important;
    background: #ffffff;
    border-top: 2px solid #4d4d4d;
    padding: 0;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden !important;
  }

  .mobile-home-tabs button {
    border-radius: 0 !important;
    padding-top: 17px !important;
    padding-bottom: calc(17px + env(safe-area-inset-bottom, 0px)) !important;
  }

  .project-scroll-container {
    max-height: none !important;
    overflow-y: auto !important;
    margin-left: -16px !important;
    margin-right: -16px !important;
    padding-left: 0px !important;
    padding-right: 0px !important;
    padding-bottom: 24px !important;
    flex: 1;
  }

  .project-card {
    width: calc(100% - 50px) !important;
    margin-left: auto !important;
    padding: 12px !important;
    min-height: 64px;
  }

  .project-list-panel input[type="checkbox"] {
    opacity: 1 !important;
    width: 24px !important;
    height: 24px !important;
    left: 8px !important;
    cursor: pointer;
  }

  .sticky-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .note-card {
    padding: 28px 12px 12px;
  }

  .note-pin {
    top: 6px;
    width: 14px;
    height: 14px;
  }

  .note-star {
    top: 6px;
    right: 5px;
    padding: 3px;
  }

  .note-header-tag {
    font-size: 9px;
    margin-bottom: 4px;
  }

  .note-title-text {
    font-size: 14px;
    line-height: 1.2;
  }

  .note-divider-line {
    margin-bottom: 5px;
  }

  .note-sub-text {
    font-size: 11px;
  }

  .bulk-action-bar {
    top: 84px !important;
    bottom: auto;
    width: calc(100% - 24px);
    max-width: 420px;
    padding: 8px 10px !important;
    justify-content: space-between;
    gap: 8px !important;
  }

  .bulk-selection-summary>span {
    display: none;
  }

  .bulk-selection-summary button {
    margin-top: 0 !important;
  }

  .bulk-divider {
    display: none;
  }

  .bulk-status-control {
    flex-shrink: 0;
  }

  .bulk-submit {
    flex-shrink: 0;
  }
}

@media (min-width: 768px) {
  .mobile-search-toggle {
    display: none !important;
  }
}

/* The normal view has three fixed-width columns (about 1,280px in total).
   Do not let that row overflow on a zoomed or narrower desktop viewport:
   keep the project list centered until there is room for all three columns. */
@media (min-width: 768px) and (max-width: 1279px) {
  .view-standard-layout {
    flex-direction: column !important;
    align-items: center !important;
    gap: 0 !important;
  }

  .view-standard-layout .view-actions,
  .view-standard-layout .recent-activity-panel {
    display: none !important;
  }

  .view-standard-layout .project-list-panel {
    width: min(420px, 100%) !important;
    max-width: 100% !important;
  }
}

.main-grid-standard {
  display: grid;
  grid-template-columns: 1fr;
}

.main-grid-notes {
  display: grid;
  grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
  .main-grid-standard {
    grid-template-columns: minmax(0, 1fr) 420px minmax(0, 1fr) !important;
  }

  .main-grid-notes {
    grid-template-columns: 300px minmax(0, 1fr) !important;
  }
}

/* Sticky Board background with dot matrix grid */
.sticky-board-bg {
  background-color: #f6f4ef !important;
  background-image: radial-gradient(circle at 1px 1px, rgba(0, 0, 0, 0.04) 1px, transparent 0) !important;
  background-size: 22px 22px !important;
}

/* Notes have no activity column, so use the same fixed bottom anchor as the
   other views while keeping the TV aligned with the actions column. */
@media (min-width: 768px) {
  .sticky-board-bg .tv-broadcast-panel {
    position: fixed !important;
    width: 300px;
    left: max(16px, calc(50vw - 592px)) !important;
    top: auto !important;
    bottom: 64px !important;
    transform: none !important;
    transform-origin: right bottom !important;
  }
}

/* Grid layout matching sticky-notes.html */
.sticky-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 260px));
  gap: 28px 32px;
  justify-content: start;
}

/* The desktop board always uses three notes per row. The note column takes
   the remaining width beside the fixed actions column, so cards can shrink
   evenly instead of wrapping after two. */
@media (min-width: 1280px) {
  .sticky-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
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
    0 0.5px 0 rgba(255, 255, 255, 0.6) inset,
    0 2px 4px rgba(0, 0, 0, 0.06),
    0 8px 16px -4px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  /* Use the same composited state as hover from the first paint. This avoids
     the browser switching text rasterization only after the pointer enters. */
  transform: translateY(-4px) scale(1.015);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
  user-select: none;
  contain: layout style;
  will-change: auto;
  -webkit-font-smoothing: antialiased;
  text-rendering: geometricPrecision;
}

/* Optimized Paper Grain Texture (no heavy SVG turbulence or mix-blend-mode for 60fps scrolling) */
.note-card::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background-image: radial-gradient(rgba(0, 0, 0, 0.04) 1px, transparent 0);
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
    linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 15%),
    linear-gradient(225deg, rgba(0, 0, 0, 0.04) 0%, rgba(0, 0, 0, 0) 20%);
}

.note-card:nth-child(6n+2)::after,
.note-card:nth-child(6n+4)::after,
.note-card:nth-child(6n+5)::after {
  transform: scaleX(-1);
}

.note-card:nth-child(6n+3)::after {
  filter: brightness(1.08);
}

.note-card:hover {
  transform: translateY(-4px) scale(1.015) !important;
  box-shadow:
    0 0.5px 0 rgba(255, 255, 255, 0.6) inset,
    0 1px 1px rgba(0, 0, 0, 0.08),
    0 4px 4px -1px rgba(0, 0, 0, 0.08),
    0 12px 14px -6px rgba(0, 0, 0, 0.15),
    0 26px 30px -14px rgba(0, 0, 0, 0.22);
}

.note-card>* {
  position: relative;
  z-index: 3;
}

/* Metallic dome pushpin at top center */
.note-pin {
  position: absolute;
  top: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 16px;
  height: 16px;
  border-radius: 50%;
  z-index: 4;
  box-shadow:
    0 4px 5px -1px rgba(0, 0, 0, 0.35),
    0 2px 2px rgba(0, 0, 0, 0.2),
    inset -2px -2px 3px rgba(0, 0, 0, 0.25),
    inset 2px 2px 3px rgba(255, 255, 255, 0.55);
}

.note-pin::before {
  content: "";
  position: absolute;
  top: 2.5px;
  left: 3px;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.85);
  filter: blur(0.3px);
}

/* Pin color gradients */
.pin-yellow {
  background: radial-gradient(circle at 35% 30%, #ffe082, #e5a11c);
}

.pin-grey {
  background: radial-gradient(circle at 35% 30%, #e0e0e0, #a6a6a6);
}

.pin-red {
  background: radial-gradient(circle at 35% 30%, #ff8a80, #d32f2f);
}

.pin-green {
  background: radial-gradient(circle at 35% 30%, #a5d6a7, #388e3c);
}

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
  color: rgba(0, 0, 0, 0.45);
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
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  margin-bottom: 8px;
}

.note-sub-text {
  font-size: 14px;
  color: rgba(0, 0, 0, 0.65);
  font-weight: 600;
}

/* Note color themes (Brighter, vibrant pastel tones) */
.note-yellow {
  background: #ffd643;
}

.note-yellow .note-sub-text {
  color: rgba(0, 0, 0, 0.55);
}

.note-white {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.note-white .note-sub-text {
  color: rgba(0, 0, 0, 0.55);
}

.note-red {
  background: #ff8a80;
}

.note-red .note-title-text,
.note-red .note-sub-text {
  color: #2c1410;
}

.note-red .note-sub-text {
  color: rgba(30, 10, 5, 0.65);
}

.note-green {
  background: #9de39b;
}

.note-green .note-title-text,
.note-green .note-sub-text {
  color: #16301a;
}

.note-green .note-sub-text {
  color: rgba(15, 35, 20, 0.65);
}

.tv-broadcast-frame {
  position: relative;
  width: 300px;
  aspect-ratio: 1.35;
  padding: 12px 12px 38px;
  border: 2px solid #424542;
  border-radius: 28px;
  background: linear-gradient(145deg, #4a4d4a, #171918 65%);
  box-shadow: inset 0 3px 4px rgba(255, 255, 255, .22), inset 0 -3px 7px #050606, 0 16px 20px rgba(0, 0, 0, .2);
}

.tv-broadcast-panel {
  position: fixed;
  z-index: 30;
  bottom: 64px;
  width: 300px;
}

.tv-broadcast-screen {
  height: 100%;
  min-height: 0;
  padding: 18px;
  overflow: hidden;
  border-radius: 21px;
  color: #eef8ef;
  background: radial-gradient(circle at 15% 12%, rgba(81, 255, 126, .16), transparent 30%), #0c1d14;
  box-shadow: inset 0 0 18px rgba(0, 0, 0, .55);
}

.tv-broadcast-frame.is-bad .tv-broadcast-screen {
  background: radial-gradient(circle at 15% 12%, rgba(255, 105, 105, .18), transparent 30%), #251111;
}

.tv-broadcast-avatar {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border: 3px solid #71df8b;
  border-radius: 999px;
  box-shadow: 0 0 10px rgba(87, 235, 120, .65);
}

.tv-broadcast-frame.is-bad .tv-broadcast-avatar {
  border-color: #f58a86;
  box-shadow: 0 0 8px rgba(245, 110, 104, .65);
}

.tv-broadcast-label {
  margin: 0;
  color: #b4c9b7;
  font-size: 10px;
  font-weight: 900;
  letter-spacing: .14em;
}

.tv-broadcast-label-green {
  color: #57eb78 !important;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .1em;
  margin-top: 2px;
}

.tv-broadcast-label-standalone {
  padding-top: 2px;
}

.tv-broadcast-name {
  margin: 2px 0 0;
  overflow: hidden;
  color: white;
  font-size: 14px;
  font-weight: 900;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tv-broadcast-star {
  margin-right: 4px;
  color: #f5dc55;
  font-size: 11px;
  filter: drop-shadow(0 0 3px rgba(245, 220, 85, .7));
}

.tv-broadcast-content {
  display: -webkit-box;
  margin: 18px 0 0;
  overflow: hidden;
  font-size: 14px;
  font-weight: 700;
  line-height: 1.36;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.tv-broadcast-bad {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 13px;
}

.tv-broadcast-bad-label {
  margin: 0;
  color: #ff8d88;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .1em;
}

.tv-broadcast-bad-label i {
  margin-right: 5px;
  color: #f1554e;
  font-size: 13px;
}

.tv-broadcast-bad-content {
  display: -webkit-box;
  margin: 0;
  overflow: hidden;
  color: #fff4f2;
  font-size: 16px;
  font-weight: 800;
  line-height: 1.4;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 4;
}

.tv-broadcast-good {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 13px;
}

.tv-broadcast-good-label {
  margin: 0;
  color: #8df29c;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .1em;
}

.tv-broadcast-good-label i {
  margin-right: 5px;
  color: #57eb78;
  font-size: 13px;
}

.tv-broadcast-good-content {
  display: -webkit-box;
  margin: 0;
  overflow: hidden;
  color: #eef8ef;
  font-size: 16px;
  font-weight: 800;
  line-height: 1.4;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 4;
}

.tv-broadcast-empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 7px;
  color: #b3c7b7;
  font-size: 11px;
  font-weight: 800;
  text-align: center;
}

.tv-broadcast-empty i {
  color: #7be293;
  font-size: 23px;
}

.tv-broadcast-console {
  position: absolute;
  right: 13px;
  bottom: 8px;
  left: 13px;
  display: flex;
  align-items: center;
  gap: 3px;
  height: 12px;
}

.tv-broadcast-console span {
  width: 45px;
  height: 2px;
  border-radius: 999px;
  background: #080a09;
  box-shadow: 0 1px rgba(255, 255, 255, .1);
}

.tv-broadcast-console button {
  width: 20px;
  height: 20px;
  padding: 0;
  border: 1px solid #737773;
  border-radius: 50%;
  color: #e8eee8;
  background: #1b1e1c;
  font-size: 8px;
  cursor: pointer;
}

.tv-broadcast-console button:first-of-type {
  margin-left: auto;
}

.tv-broadcast-console button:hover:not(:disabled) {
  color: #8df29c;
  border-color: #8df29c;
}

.tv-broadcast-console button:disabled {
  opacity: .35;
  cursor: not-allowed;
}

.tv-broadcast-console .tv-broadcast-power {
  width: 7px;
  height: 7px;
  margin-left: 6px;
  border-radius: 50%;
  background: #7aea89;
  box-shadow: 0 0 5px #7aea89;
}

.tv-broadcast-leg {
  position: absolute;
  z-index: -1;
  bottom: -19px;
  width: 16px;
  height: 28px;
  border: 1px solid #0b0c0b;
  border-radius: 3px 3px 5px 5px;
  background: linear-gradient(90deg, #101211, #292b29 55%, #0d0f0e);
  box-shadow: inset 0 2px 2px rgba(255, 255, 255, .12), 0 5px 6px rgba(0, 0, 0, .24);
}

.tv-broadcast-leg-left {
  left: 37px;
  transform: skew(-14deg) rotate(5deg);
}

.tv-broadcast-leg-right {
  right: 37px;
  transform: skew(14deg) rotate(-5deg);
}

.tv-broadcast-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 6px;
}

.tv-broadcast-status {
  min-width: 68px;
  margin: 0;
  color: #9aa49b;
  font-size: 9px;
  font-weight: 800;
  text-align: center;
}

/* Mobile/tablet layout: keep sticky notes in two columns and pin actions touch-visible. */
@media (max-width: 1023px) {
  .sticky-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 12px !important;
  }

  .project-card button {
    opacity: 1 !important;
  }
}

@media (max-width: 767px) {
  .tv-broadcast-panel {
    display: none;
  }
}
</style>
