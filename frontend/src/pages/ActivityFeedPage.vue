<template>
  <div class="activity-feed-page flex flex-col bg-[#F9F4EE] overflow-hidden"
    :style="{ height: 'var(--vvh, 100dvh)', maxHeight: 'var(--vvh, 100dvh)' }">
    <Navbar class="max-md:hidden" />

    <main
      class="max-w-[800px] w-full mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6 pb-2 sm:pb-3 flex-1 flex flex-col min-h-0 overflow-visible relative"
      @dragenter.prevent="handleFeedDragOver"
      @dragover.prevent="handleFeedDragOver"
      @drop.prevent="handleFeedDrop">
      <!-- Header Row: Back Button & Vertically Centered Title -->
      <div class="relative flex items-center justify-center mb-3 sm:mb-4 min-h-[40px] flex-shrink-0">
        <button @click="goBack" type="button" title="Quay lại"
          class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-auto sm:h-auto rounded-xl sm:rounded-none flex items-center justify-center sm:gap-2 text-[16px] sm:text-[15px] text-gray-700 hover:text-emerald-700 font-extrabold transition-colors cursor-pointer focus:outline-none hover:bg-stone-200/60 sm:hover:bg-transparent">
          <i class="fa-solid fa-arrow-left text-[18px] sm:text-sm"></i>
          <span class="hidden sm:inline">Quay lại</span>
        </button>

        <h1 class="text-[20px] sm:text-[22px] font-black text-[#32312F] font-heading tracking-tight">
          {{ activeMainTab === 'actions' ? 'Hành động tiếp theo' : 'Hoạt động của đội' }}
        </h1>
      </div>

      <!-- Sub-tabs for System Admin when in Activities view -->
      <div v-if="authStore.user?.is_system_admin && activeMainTab === 'activities'"
        class="flex gap-2 mb-3 bg-stone-150 p-1 rounded-xl max-w-xs mx-auto w-full select-none border border-stone-200/50 flex-shrink-0">
        <button @click="activeTab = 'all'" type="button"
          class="flex-1 py-1.5 px-2.5 rounded-lg text-[11px] font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'all' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Tất cả
        </button>
        <button @click="activeTab = 'comments'" type="button"
          class="flex-1 py-1.5 px-2.5 rounded-lg text-[11px] font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'comments' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Bình luận
        </button>
        <button @click="activeTab = 'operations'" type="button"
          class="flex-1 py-1.5 px-2.5 rounded-lg text-[11px] font-bold transition-all cursor-pointer text-center"
          :class="activeTab === 'operations' ? 'bg-white text-emerald-800 shadow-3xs border border-stone-200' : 'text-gray-500 hover:text-emerald-700'">
          Lịch sử thao tác
        </button>
      </div>

      <!-- SCHEDULE TASKS VIEW (Hành động tiếp theo đầy đủ bao gồm quá hạn) -->
      <div v-if="activeMainTab === 'actions'" class="relative flex-1 min-h-0 flex flex-col mb-2 sm:mb-3">
        <!-- Skeleton Loading State -->
        <div v-if="isScheduleLoading && groupedScheduleTasks.length === 0" class="space-y-4 flex-1 overflow-hidden p-2">
          <div v-for="i in 3" :key="'sk-sched-full-' + i" class="animate-pulse space-y-2">
            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
            <div class="h-20 bg-white rounded-2xl border border-gray-100"></div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="groupedScheduleTasks.length === 0"
          class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs flex-1 flex flex-col items-center justify-center gap-2">
          <i class="fa-regular fa-calendar-check text-3xl text-gray-350"></i>
          <p class="text-gray-400 font-medium">Chưa có lịch trình hành động nào</p>
        </div>

        <!-- Grouped Schedule Tasks Feed (Full view: Quá hạn + Hôm nay + Tương lai) -->
        <div v-else ref="scheduleTasksScrollContainer" @scroll="handleScheduleScroll" class="activity-feed-scroll flex-1 min-h-0 overflow-y-auto scrollbar-hover pr-1 space-y-6">
          <!-- Past Loading Spinner -->
          <div v-if="isLoadingPastTasks" class="py-2.5 text-center text-xs text-gray-500 font-medium flex items-center justify-center gap-2">
            <i class="fa-solid fa-spinner fa-spin text-emerald-600"></i>
            <span>Đang tải hành động cũ hơn...</span>
          </div>

          <div v-for="group in groupedScheduleTasks" :key="group.dateKey" :data-group-key="group.dateKey" class="space-y-3">
            <!-- Deep Green Bold Date Header -->
            <h2 class="text-[18px] sm:text-[19px] font-black text-[#1A7A56] font-heading mb-4 pt-1">{{ group.dateLabel
              }}</h2>

            <!-- Tasks list in this Date Group -->
            <div class="space-y-0 pl-1">
              <div v-for="task in group.tasks" :key="task.id"
                @touchstart="handleScheduleTaskTouchStart(task, $event)"
                @touchend="handleScheduleTaskTouchEnd"
                @touchmove="handleScheduleTaskTouchMove"
                :class="activeScheduleTaskMenuId === task.id ? 'z-30' : 'z-1'"
                class="schedule-task-item relative flex items-start gap-3 pb-5 bg-transparent select-text group">

                <!-- Absolute Timeline Line connecting avatars -->
                <div class="absolute top-9 bottom-0 left-[15px] w-[1.5px] bg-gray-300 z-0"></div>

                <!-- User Avatar -->
                <div class="flex-shrink-0 w-8 z-10">
                  <img :src="task.creator?.avatar || task.assignee?.avatar || defaultAvatar"
                    :alt="task.creator?.name || 'Thành viên'" @error="$event.target.src = defaultAvatar"
                    class="w-8 h-8 rounded-full object-cover border border-gray-200 relative z-10 shadow-3xs bg-[#F9F4EE]" />
                </div>

                <!-- Details -->
                <div class="min-w-0 flex-1 pt-0 z-10">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <!-- Project Title Link -->
                    <div v-if="task.project" class="leading-snug flex-1 min-w-0">
                      <span @click="goToProject(task.project.id, $event)"
                        class="activity-project-link text-[#1A7A56] hover:underline font-extrabold text-[18px] sm:text-[19px] cursor-pointer max-w-full truncate inline-block w-fit align-middle"
                        :title="task.project.title">
                        {{ (task.project.customer?.name ? task.project.customer.name + ' - ' : '') + task.project.title }}
                      </span>
                    </div>
                    <div v-else class="flex-1"></div>

                    <!-- Right: 3-dots Menu Button on hover / mobile long-press (only if user has permission) -->
                    <div v-if="canEditScheduleTask(task) || canDeleteScheduleTask(task)"
                      class="relative shrink-0 flex items-center justify-end" @click.stop>
                      <button type="button"
                        @click.stop="toggleScheduleTaskMenu(task.id, $event)" title="Tùy chọn"
                        class="text-gray-400 hover:text-gray-800 hover:bg-gray-200/80 active:bg-gray-300/80 w-7 h-7 -my-1 -mr-1 rounded-lg flex items-center justify-center cursor-pointer transition-all active:scale-95 p-0"
                        :class="(activeScheduleTaskMenuId === task.id || activeScheduleTaskIdForMobile === task.id) ? 'flex text-gray-800 bg-gray-200/80' : 'hidden group-hover:flex'">
                        <i class="fa-solid fa-ellipsis-vertical text-[14px] leading-none"></i>
                      </button>

                      <!-- Dropdown Menu for Edit & Delete -->
                      <div v-if="activeScheduleTaskMenuId === task.id"
                        class="absolute top-full right-0 mt-1 z-50 bg-white border border-gray-200 rounded-xl shadow-lg py-1 min-w-[130px] animate-fade-in-up">
                        <button v-if="canEditScheduleTask(task)" type="button" @click.stop="openEditScheduleTaskModal(task)"
                          class="w-full text-left px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-100 flex items-center gap-2 cursor-pointer transition-colors">
                          <i class="fa-solid fa-pen-to-square text-xs text-emerald-600"></i>
                          <span>Chỉnh sửa</span>
                        </button>
                        <button v-if="canDeleteScheduleTask(task)" type="button"
                          @click.stop="handleDeleteScheduleTask(task)"
                          class="w-full text-left px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer transition-colors">
                          <i class="fa-solid fa-trash-can text-xs"></i>
                          <span>Xóa</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Task Content -->
                  <div v-if="parseCommentText(task.title || task.content)"
                    class="text-[16px] sm:text-[18px] text-gray-900 leading-relaxed break-words mt-0.5 space-y-1">
                    <div
                      :class="!isScheduleTaskExpanded(task.id) && isLongContent(parseCommentText(task.title || task.content)) ? 'line-clamp-4' : ''"
                      class="whitespace-pre-wrap font-normal text-gray-900 select-text cursor-text">
                      <i v-if="task.is_private" class="fa-solid fa-lock text-[13px] text-[#ea580c] mr-1.5 align-middle inline-block" title="Tin nhắn riêng tư"></i><span v-html="formatCommentTextWithMentions(task.title || task.content, projectStore.users, mentionGroups)"></span>
                    </div>
                    <button v-if="isLongContent(parseCommentText(task.title || task.content))"
                      @click.stop="toggleExpandScheduleTask(task.id)" type="button"
                      class="inline-block text-[13px] font-bold text-[#1A7A56] hover:text-emerald-800 hover:underline mt-0.5 cursor-pointer select-none">
                      {{ isScheduleTaskExpanded(task.id) ? 'Thu gọn' : '... Xem thêm' }}
                    </button>
                  </div>

                  <!-- Attachments (Images & Files side-by-side) -->
                  <div
                    v-if="parseCommentImages(task.title || task.content).length > 0 || parseCommentFiles(task.title || task.content).length > 0"
                    class="flex flex-wrap items-end gap-1.5 pt-1.5 pb-1">
                    <!-- Images -->
                    <button v-for="(img, imgIdx) in parseCommentImages(task.title || task.content)"
                      :key="'sched-full-img-' + imgIdx" type="button"
                      @click.stop="openImagePreview(img.url, parseCommentImages(task.title || task.content), imgIdx)"
                      class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all flex-shrink-0 shadow-3xs"
                      :title="'Xem ảnh: ' + img.name">
                      <img :src="img.url" class="w-full h-full object-cover" alt="" loading="lazy" decoding="async" />
                    </button>

                    <!-- Files -->
                    <a v-for="(file, fIdx) in parseCommentFiles(task.title || task.content)"
                      :key="'sched-full-file-' + fIdx" :href="file.url" :download="file.name" target="_blank"
                      @click.stop
                      class="w-8 h-10 rounded border border-[#d4a574] bg-[#f5e6d0] hover:bg-[#edd9bc] flex flex-col items-center justify-end overflow-hidden cursor-pointer transition-colors flex-shrink-0"
                      :title="'Tải xuống: ' + file.name">
                      <i class="fa-solid fa-file text-[#c87828] text-xs mb-0.5"></i>
                      <span
                        class="text-[8px] font-bold text-[#8b5a2b] bg-[#e8c99a] w-full text-center py-0.5 leading-none">FILE</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Future Loading Spinner -->
          <div v-if="isLoadingFutureTasks" class="py-2.5 text-center text-xs text-gray-500 font-medium flex items-center justify-center gap-2">
            <i class="fa-solid fa-spinner fa-spin text-emerald-600"></i>
            <span>Đang tải hành động tiếp theo...</span>
          </div>
        </div>
      </div>

      <!-- ACTIVITIES VIEW (Hoạt động của đội) -->
      <template v-else>
        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4 flex-1 overflow-hidden">
          <div v-for="i in 3" :key="'skel-' + i"
            class="bg-white rounded-2xl p-5 border border-gray-100 flex items-start gap-4 animate-pulse">
            <div class="w-12 h-4 bg-gray-200 rounded-md"></div>
            <div class="w-10 h-10 rounded-full bg-gray-200"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 w-1/4 rounded-md"></div>
              <div class="h-3 bg-gray-150 w-1/3 rounded-md"></div>
              <div class="h-12 bg-gray-100 w-full rounded-md"></div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredActivities.length === 0"
          class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs flex-1 flex items-center justify-center">
          <p class="text-gray-400 font-medium">Trong 7 ngày qua, chưa có hoạt động mới</p>
        </div>

        <!-- Grouped Activities Feed (Scrollable inner list, chat style) -->
        <div v-else class="relative flex-1 min-h-0 flex flex-col mb-2 sm:mb-3">
          <!-- Loading banner when locating quoted older message -->
          <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-if="isLoadingQuotedComment"
              class="absolute top-2 left-1/2 -translate-x-1/2 z-40 px-3.5 py-1.5 rounded-full bg-emerald-800/90 text-white text-xs font-bold shadow-lg flex items-center gap-2 backdrop-blur-sm pointer-events-none">
              <i class="fa-solid fa-circle-notch fa-spin text-xs text-emerald-300"></i>
              <span>Đang tải tin nhắn cũ...</span>
            </div>
          </transition>

          <div ref="activityFeedScrollRef" @scroll="handleFeedScroll"
            class="activity-feed-scroll flex-1 min-h-0 overflow-y-auto scrollbar-hover pr-1 space-y-6"
            style="-webkit-overflow-scrolling: touch; touch-action: pan-y; overscroll-behavior-y: contain;">
            <!-- Loading older comments indicator when scrolling up -->
            <div v-if="isLoadingOlderActivities"
              class="flex items-center justify-center py-2 text-xs text-gray-500 gap-2">
              <i class="fa-solid fa-circle-notch fa-spin text-emerald-600"></i>
              <span>Đang tải hoạt động cũ hơn...</span>
            </div>
            <div v-else-if="!hasMoreOlderActivities && filteredActivities.length >= 30"
              class="text-center py-1.5 mb-2 text-[12px] text-gray-400 font-semibold border-b border-gray-200/50">
              Đã hiển thị tất cả hoạt động
            </div>

            <div v-for="(group, dateStr) in groupedActivities" :key="dateStr" class="space-y-3">
              <!-- Date Header -->
              <h2 class="text-[18px] sm:text-[19px] font-black text-[#32312F] font-heading mb-5 pt-1">{{ dateStr }}</h2>

              <!-- Timeline list, matching the recent-activity panel -->
              <div class="space-y-0">
                <div v-for="(act, idx) in group" :key="act.id" :id="'activity-feed-item-' + act.id"
                  class="feed-activity-item relative flex gap-3 pb-5 group"
                  :class="activeActivityMenuId === act.id ? 'z-30' : 'z-1'">
                  <!-- Timeline vertical line (always displayed for all messages) -->
                  <div class="absolute top-10 bottom-0 left-[15px] w-[1.5px] bg-gray-300 z-0"></div>

                  <div class="flex-shrink-0 w-8 z-10">
                    <img
                      :src="act.user?.avatar || defaultAvatar"
                      @error="$event.target.src = defaultAvatar"
                      :alt="act.user?.name" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-3xs"
                      loading="lazy" decoding="async" />
                  </div>

                  <div class="flex-1 min-w-0 z-10">
                    <!-- Top Row: Avatar + User Name & supports & relative time / 3-dots menu -->
                    <div class="flex items-center justify-between gap-2 relative min-h-[26px]">
                      <div class="min-w-0 flex-1">
                        <span
                          class="font-extrabold text-[18px] sm:text-[19px] text-[#32312F] truncate leading-tight block">
                          <span>{{ act.user ? act.user.name : 'Thành viên' }}</span>
                          <template v-if="act.project?.customer">
                            <span class="font-bold text-[#32312F]">&nbsp;hỗ trợ&nbsp;</span>
                            <span class="text-[#1A7A56] hover:underline cursor-pointer font-extrabold"
                              :title="act.project.customer.name"
                              @click.stop="$router.push(`/customers/${act.project.customer.id}`)">
                              {{ act.project.customer.name }}
                            </span>
                          </template>
                        </span>
                      </div>

                      <!-- Right: Timestamp normally, 3-dots icon button on hover / when menu open -->
                      <div class="relative shrink-0 flex items-center justify-end min-h-[30px] min-w-[32px]"
                        @click.stop>
                        <!-- Relative Time (shown when not hovered and menu not active) -->
                        <span
                          class="text-[14px] sm:text-[15px] text-gray-400 font-medium whitespace-nowrap leading-none text-right"
                          :class="(activeActivityMenuId === act.id || activeActivityIdForMobileActions === act.id) ? 'hidden' : 'group-hover:hidden'">
                          {{ formatCommentRelativeTime(act.created_at) }}
                        </span>

                            <!-- 3-dots Menu Button (shown on hover or when menu is active) -->
                            <button type="button"
                              @click.stop="toggleActivityMenu(act.id, $event)" title="Tùy chọn"
                              class="text-gray-400 hover:text-gray-800 hover:bg-gray-200/80 active:bg-gray-300/80 w-8 h-8 -my-1 -mr-1 rounded-lg flex items-center justify-center cursor-pointer transition-all active:scale-95 p-0"
                              :class="(activeActivityMenuId === act.id || activeActivityIdForMobileActions === act.id) ? 'flex text-gray-800 bg-gray-200/80' : 'hidden group-hover:flex'">
                              <i class="fa-solid fa-ellipsis-vertical text-[15px] leading-none"></i>
                            </button>

                            <!-- Dropdown Menu for Action, Edit & Delete -->
                            <div v-if="activeActivityMenuId === act.id"
                              class="absolute top-full right-0 mt-1 z-50 bg-white border border-gray-200 rounded-xl shadow-lg py-1 min-w-[145px] animate-fade-in-up">
                              <button type="button" @click.stop="openCreateActionFromComment(act)"
                                class="w-full text-left px-3 py-1.5 text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 flex items-center gap-2 cursor-pointer transition-colors">
                                <i class="fa-regular fa-calendar-plus text-xs text-emerald-600"></i>
                                <span>Tạo hành động</span>
                              </button>
                              <button v-if="canEditComment(act)" type="button" @click.stop="handleStartEditComment(act)"
                                class="w-full text-left px-3 py-1.5 text-sm font-bold text-gray-700 hover:bg-gray-100 flex items-center gap-2 cursor-pointer transition-colors">
                                <i class="fa-solid fa-pen-to-square text-xs text-emerald-600"></i>
                                <span>Chỉnh sửa</span>
                              </button>
                              <button v-if="canDeleteComment(act)" type="button" @click.stop="handleDeleteComment(act.id)"
                                class="w-full text-left px-3 py-1.5 text-sm font-bold text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer transition-colors">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                                <span>Xóa</span>
                              </button>
                            </div>
                      </div>
                    </div>

                    <!-- Project title -->
                    <div v-if="act.project" class="leading-snug mt-0.5 mb-1 max-w-full">
                      <span @click="handleActivityProjectClick(act.project.id, $event)"
                        class="activity-project-link text-[#1A7A56] hover:underline font-extrabold text-[18px] sm:text-[19px] cursor-pointer max-w-full truncate inline-block w-fit align-middle"
                        :title="act.project.title">
                        {{ act.project.title }}
                      </span>
                    </div>

                    <!-- Comment content (Normal Display) -->
                    <div class="text-[16px] sm:text-[18px] text-gray-900 leading-relaxed break-words mt-0.5 space-y-1">
                      <!-- Zalo Quote Reply Preview inside activity feed page -->
                      <div v-if="parseReplyInfo(act.content)" @click.stop="scrollToComment(parseReplyInfo(act.content))"
                        class="bg-[#e1e3ea] px-2.5 py-1.5 rounded-r-md rounded-l-xs border-l-2 border-emerald-500 text-xs mb-1 select-none max-w-full cursor-pointer hover:bg-[#d5d7de] transition-colors">
                        <div class="text-[14px] font-bold text-gray-500 flex items-center gap-1">
                          <i class="fa-solid fa-reply text-xs"></i>
                          <span>{{ parseReplyInfo(act.content).user }}</span>
                        </div>
                        <div class="text-[14px] text-gray-450 truncate mt-0.5 max-w-[280px]">
                          {{ parseReplyInfo(act.content).text }}
                        </div>
                      </div>

                      <div v-if="parseCommentText(act.content)"
                        class="whitespace-pre-line font-normal text-gray-900 select-text cursor-text">
                        <div
                          :class="!isActivityExpanded(act.id) && isLongContent(parseCommentText(act.content)) ? 'line-clamp-4' : ''">
                          <i v-if="act.is_private" class="fa-solid fa-lock text-[13px] text-[#ea580c] mr-1.5 align-middle inline-block" title="Tin nhắn riêng tư"></i><span v-html="formatCommentTextWithMentions(act.content, projectStore.users, mentionGroups)"></span>
                        </div>
                        <button v-if="isLongContent(parseCommentText(act.content))"
                          @click.stop="toggleExpandActivity(act.id)" type="button"
                          class="inline-block text-[13px] font-bold text-[#1A7A56] hover:text-emerald-800 hover:underline mt-0.5 cursor-pointer select-none">
                          {{ isActivityExpanded(act.id) ? 'Thu gọn' : '... Xem thêm' }}
                        </button>
                      </div>

                      <!-- Attachments (Images & Files side-by-side) -->
                      <div
                        v-if="parseCommentImages(act.content).length > 0 || parseCommentFiles(act.content).length > 0"
                        class="flex flex-wrap items-end gap-1.5 pt-1 pb-1.5">
                        <!-- Images -->
                        <button v-for="(img, imgIdx) in parseCommentImages(act.content)" :key="'img-' + imgIdx"
                          type="button" @click.stop="openImagePreview(img.url, parseCommentImages(act.content), imgIdx)"
                          class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all flex-shrink-0 shadow-3xs"
                          :title="'Xem ảnh: ' + img.name">
                          <img :src="img.url" class="w-full h-full object-cover" alt=""
                            :loading="idx < 3 ? 'eager' : 'lazy'" decoding="async"
                            :fetchpriority="idx < 3 ? 'high' : 'auto'" />
                        </button>

                        <!-- Files -->
                        <a v-for="(file, fIdx) in parseCommentFiles(act.content)" :key="'file-' + fIdx" :href="file.url"
                          :download="file.name" target="_blank" @click.stop
                          class="w-8 h-10 rounded border border-[#d4a574] bg-[#f5e6d0] hover:bg-[#edd9bc] flex flex-col items-center justify-end overflow-hidden cursor-pointer transition-colors flex-shrink-0"
                          :title="'Tải xuống: ' + file.name">
                          <i class="fa-solid fa-file text-[#c87828] text-xs mb-0.5"></i>
                          <span
                            class="text-[8px] font-bold text-[#8b5a2b] bg-[#e8c99a] w-full text-center py-0.5 leading-none">FILE</span>
                        </a>
                      </div>
                    </div>

                    <!-- Bottom Actions: Reply & Private Reply buttons with text -->
                    <div class="flex items-center gap-2 mt-3 sm:mt-3.5 flex-wrap">
                      <button v-if="!act.is_private" @click.stop="handleReplyToActivity(act)" type="button"
                        :title="'Trả lời ' + (act.user?.name || 'thành viên')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-gray-500 hover:text-emerald-700 hover:bg-stone-200/60 active:bg-stone-300/80 cursor-pointer transition-all active:scale-95 -ml-1.5 select-none font-bold">
                        <i class="fa-solid fa-reply text-[18px] sm:text-[19px]"></i>
                        <span class="text-[13px] sm:text-[14px] leading-none">
                          <span class="sm:hidden">Trả lời</span>
                          <span class="hidden sm:inline">Trả lời {{ act.user ? act.user.name : 'thành viên' }}</span>
                        </span>
                      </button>

                      <button @click.stop="handlePrivateReplyToActivity(act)" type="button"
                        :title="'Trả lời riêng ' + (act.user?.name || 'thành viên')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[#ea580c] hover:text-orange-700 hover:bg-orange-100/60 active:bg-orange-200/80 cursor-pointer transition-all active:scale-95 select-none font-bold">
                        <i class="fa-solid fa-reply text-[18px] sm:text-[19px]"></i>
                        <span class="text-[13px] sm:text-[14px] leading-none">
                          <span class="sm:hidden">Trả lời riêng</span>
                          <span class="hidden sm:inline">Trả lời riêng {{ act.user ? act.user.name : 'thành viên' }}</span>
                        </span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating Scroll to Bottom Button -->
          <transition enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-90 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-90 translate-y-2">
            <button v-if="showScrollToBottom" @click="scrollToBottom(true)" type="button"
              title="Cuộn xuống tin nhắn mới nhất"
              class="absolute bottom-3 right-3 z-30 w-10 h-10 rounded-full bg-white/95 hover:bg-white text-gray-700 hover:text-emerald-700 shadow-xl border border-gray-200/80 flex items-center justify-center cursor-pointer transition-all hover:scale-105 active:scale-95 backdrop-blur-xs">
              <i class="fa-solid fa-chevron-down text-sm"></i>
            </button>
          </transition>
        </div>
      </template>

      <!-- Bottom Chat Composer with Solid Background and Border -->
      <div v-if="activeTab !== 'operations'"
        class="bg-[#F9F4EE] border-2 border-[#4d4d4d] rounded-2xl shadow-3xs overflow-visible flex-shrink-0">
        <ActivityComposer ref="activityComposerRef" v-model="chatMessage" v-model:project-id="chatProjectId"
          :projects="projectStore.projects" :users="projectStore.users" :groups="mentionGroups"
          :replying-to="replyingToActivity"
          :reply-text="replyingToActivity?.text || parseCommentText(replyingToActivity?.content)"
          :editing-comment="editingCommentLog" :submitting="isSubmittingChat" @submit="submitChat"
          @cancel-reply="cancelReply" @cancel-edit="cancelEdit" />
      </div>
    </main>

    <!-- Image Lightbox Modal with Zoom & Pan -->
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
      <div v-if="activePreviewImage"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8 bg-slate-950/85 backdrop-blur-md select-none"
        @mousedown.stop @mousemove.stop @mouseup.stop @click="closeImagePreview">
        <div class="relative w-[min(92vw,1100px)] h-[min(72vh,720px)] flex flex-col items-center justify-center"
          @click.stop>

          <!-- Top Bar: Image count badge + Zoom controls + Close button -->
          <div class="absolute top-3 left-3 right-3 z-20 flex items-center justify-between pointer-events-auto">
            <div class="flex items-center gap-2">
              <div v-if="previewModalImages.length > 1"
                class="px-3 py-1 bg-black/60 backdrop-blur-md text-white/90 text-xs sm:text-sm font-bold rounded-full border border-white/10 shadow-lg">
                {{ previewModalIndex + 1 }} / {{ previewModalImages.length }}
              </div>
            </div>

            <!-- Zoom controls & Close -->
            <div class="flex items-center gap-2">
              <div class="flex items-center bg-black/60 backdrop-blur-md rounded-full border border-white/15 px-1 py-0.5 shadow-xl text-white">
                <button type="button" @click.stop="zoomOutPreview" :disabled="previewZoomScale <= 1"
                  class="w-8 h-8 rounded-full flex items-center justify-center text-xs hover:bg-white/20 transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                  title="Thu nhỏ (-)">
                  <i class="fa-solid fa-magnifying-glass-minus"></i>
                </button>
                <button type="button" @click.stop="resetPreviewZoom"
                  class="px-2 py-0.5 text-xs font-bold hover:bg-white/20 rounded-full transition-all cursor-pointer"
                  title="Đặt lại kích thước (100%)">
                  {{ Math.round(previewZoomScale * 100) }}%
                </button>
                <button type="button" @click.stop="zoomInPreview" :disabled="previewZoomScale >= 5"
                  class="w-8 h-8 rounded-full flex items-center justify-center text-xs hover:bg-white/20 transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                  title="Phóng to (+)">
                  <i class="fa-solid fa-magnifying-glass-plus"></i>
                </button>
              </div>

              <button type="button" @click="closeImagePreview"
                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/20 hover:bg-rose-600 text-white flex items-center justify-center font-bold text-sm sm:text-base backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20"
                title="Đóng (Esc)">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>

          <!-- Main Image and Prev/Next Navigation -->
          <div ref="previewContainerRef"
            class="relative w-full h-full flex items-center justify-center rounded-2xl overflow-hidden bg-slate-900 select-none touch-none"
            @wheel.prevent="handlePreviewWheel"
            @mousedown="startPreviewPan"
            @mousemove="doPreviewPan"
            @mouseup="endPreviewPan"
            @mouseleave="endPreviewPan"
            @touchstart="handlePreviewTouchStart"
            @touchmove="handlePreviewTouchMove"
            @touchend="handlePreviewTouchEnd"
            @touchcancel="handlePreviewTouchEnd">
            <img :src="activePreviewImage"
              @click="handlePreviewImageClick"
              @dblclick="togglePreviewZoom"
              :style="{
                transform: `translate(${previewPanX}px, ${previewPanY}px) scale(${previewZoomScale})`,
                transition: isPreviewPanning ? 'none' : 'transform 0.18s cubic-bezier(0.25, 1, 0.5, 1)',
                cursor: previewZoomScale > 1 ? (isPreviewPanning ? 'grabbing' : 'grab') : 'zoom-in'
              }"
              class="w-full h-full object-contain pointer-events-auto select-none"
              draggable="false" />

            <!-- PREV BUTTON (shown when > 1 image and not zoomed in) -->
            <button v-if="previewModalImages.length > 1 && previewZoomScale <= 1" type="button" @click="prevPreviewImage"
              class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/85 text-white flex items-center justify-center font-bold text-base sm:text-lg backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20 hover:scale-110 active:scale-95"
              title="Ảnh trước (Phím ←)">
              <i class="fa-solid fa-chevron-left"></i>
            </button>

            <!-- NEXT BUTTON (shown when > 1 image and not zoomed in) -->
            <button v-if="previewModalImages.length > 1 && previewZoomScale <= 1" type="button" @click="nextPreviewImage"
              class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/85 text-white flex items-center justify-center font-bold text-base sm:text-lg backdrop-blur-md shadow-xl transition-all cursor-pointer border border-white/20 hover:scale-110 active:scale-95"
              title="Ảnh tiếp theo (Phím →)">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>

          <!-- Thumbnails / Dots strip at bottom (scrollbar-none hidden scrollbar) -->
          <div v-if="previewModalImages.length > 1"
            class="flex items-center justify-center gap-2 mt-4 max-w-full overflow-x-auto py-1 px-2 scrollbar-none">
            <button v-for="(pImg, pIdx) in previewModalImages" :key="'thumb-' + pIdx" type="button"
              @click="previewModalIndex = pIdx; resetPreviewZoom()"
              class="w-10 h-10 rounded-lg overflow-hidden border-2 transition-all cursor-pointer flex-shrink-0"
              :class="pIdx === previewModalIndex ? 'border-emerald-400 scale-110 shadow-lg ring-2 ring-emerald-400/50' : 'border-white/30 opacity-60 hover:opacity-100'">
              <img :src="pImg.url || pImg.src || pImg" class="w-full h-full object-cover" />
            </button>
          </div>

        </div>
      </div>
    </transition>

    <!-- Modal Tạo hành động tiếp theo từ tin nhắn / hoạt động -->
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="isActionModalOpen"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
        @click="isActionModalOpen = false">
        <div
          class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md overflow-hidden animate-fade-in-up"
          @click.stop>
          <!-- Modal Header -->
          <div class="px-5 py-3.5 bg-stone-100/70 border-b border-gray-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2 text-emerald-800 font-extrabold text-[17px]">
              <i class="fa-regular fa-calendar-plus text-emerald-600"></i>
              <span>Tạo hành động tiếp theo</span>
            </div>
            <button type="button" @click="isActionModalOpen = false"
              class="text-gray-400 hover:text-gray-700 p-1 rounded-full cursor-pointer">
              <i class="fa-solid fa-xmark text-sm"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-5 space-y-4">
            <!-- Project Info -->
            <div v-if="actionModalComment?.project" class="bg-stone-50 p-2.5 rounded-xl border border-stone-200/80 text-xs">
              <span class="text-gray-500 font-medium">Dự án: </span>
              <span class="font-bold text-[#1A7A56]">
                {{ (actionModalComment.project.customer?.name ? actionModalComment.project.customer.name + ' - ' : '') + actionModalComment.project.title }}
              </span>
            </div>

            <!-- Task Title / Content Textarea (Editable) -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                Nội dung hành động:
              </label>
              <textarea v-model="actionModalTitle" rows="3"
                placeholder="Nhập nội dung hành động tiếp theo..."
                class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:outline-none focus:border-emerald-500 bg-gray-50/50 resize-none"></textarea>
            </div>

            <!-- Attachments preview in create modal if any -->
            <div v-if="parseCommentImages(actionModalComment?.content).length > 0 || parseCommentFiles(actionModalComment?.content).length > 0"
              class="space-y-1.5">
              <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                Tệp đính kèm đi kèm:
              </label>
              <div class="flex flex-wrap items-center gap-2 p-2 rounded-xl bg-stone-50 border border-stone-200/80">
                <!-- Images -->
                <div v-for="(img, imgIdx) in parseCommentImages(actionModalComment.content)" :key="'act-modal-img-' + imgIdx"
                  class="w-10 h-10 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0">
                  <img :src="img.url" class="w-full h-full object-cover" />
                </div>
                <!-- Files -->
                <div v-for="(file, fIdx) in parseCommentFiles(actionModalComment.content)" :key="'act-modal-file-' + fIdx"
                  class="px-2 py-1 rounded-lg border border-[#d4a574] bg-[#f5e6d0] text-[11px] font-bold text-[#8b5a2b] flex items-center gap-1">
                  <i class="fa-solid fa-file text-[#c87828] text-xs"></i>
                  <span class="truncate max-w-[140px]">{{ file.name }}</span>
                </div>
              </div>
            </div>

            <!-- Date Selection -->
            <div class="space-y-2">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                Chọn thời gian thực hiện:
              </label>
              <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                <button type="button" @click="setActionModalQuickDate(0)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="actionModalDueDate === formatQuickDateISO(0) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Hôm nay</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(0) }}</span>
                </button>
                <button type="button" @click="setActionModalQuickDate(1)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="actionModalDueDate === formatQuickDateISO(1) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Ngày mai</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(1) }}</span>
                </button>
                <button type="button" @click="setActionModalQuickDate(2)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="actionModalDueDate === formatQuickDateISO(2) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Ngày mốt</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(2) }}</span>
                </button>
                <button type="button" @click="setActionModalQuickDate(7)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="actionModalDueDate === formatQuickDateISO(7) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>1 tuần nữa</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(7) }}</span>
                </button>
              </div>

              <div class="pt-2">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Hoặc chọn ngày cụ thể:</label>
                <input type="date" v-model="actionModalDueDate"
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:outline-none focus:border-emerald-500 bg-gray-50 cursor-pointer" />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2.5">
            <button type="button" @click="isActionModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200/80 cursor-pointer transition-colors">
              Hủy
            </button>
            <button type="button" @click="submitCreateActionFromComment" :disabled="isSubmittingActionModal || !actionModalDueDate"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 cursor-pointer transition-colors flex items-center gap-1.5 shadow-sm disabled:opacity-50">
              <i v-if="isSubmittingActionModal" class="fa-solid fa-spinner fa-spin text-xs"></i>
              <span>OK (Tạo hành động)</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Chỉnh sửa Hành động tiếp theo -->
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="isEditTaskModalOpen"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
        @click="isEditTaskModalOpen = false">
        <div
          class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md overflow-hidden animate-fade-in-up"
          @click.stop>
          <!-- Modal Header -->
          <div class="px-5 py-3.5 bg-stone-100/70 border-b border-gray-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2 text-emerald-800 font-extrabold text-[17px]">
              <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
              <span>Chỉnh sửa hành động tiếp theo</span>
            </div>
            <button type="button" @click="isEditTaskModalOpen = false"
              class="text-gray-400 hover:text-gray-700 p-1 rounded-full cursor-pointer">
              <i class="fa-solid fa-xmark text-sm"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-5 space-y-4">
            <!-- Project Info -->
            <div v-if="editingTask?.project" class="bg-stone-50 p-2.5 rounded-xl border border-stone-200/80 text-xs">
              <span class="text-gray-500 font-medium">Dự án: </span>
              <span class="font-bold text-[#1A7A56]">
                {{ (editingTask.project.customer?.name ? editingTask.project.customer.name + ' - ' : '') + editingTask.project.title }}
              </span>
            </div>

            <!-- Task Title / Content Textarea -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                Nội dung hành động:
              </label>
              <textarea v-model="editingTaskTitle" rows="3"
                placeholder="Nhập nội dung hành động tiếp theo..."
                class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 focus:outline-none focus:border-emerald-500 bg-gray-50/50 resize-none"></textarea>
            </div>

            <!-- Attachments preview in edit modal if any -->
            <div v-if="parseCommentImages(editingTask?.title || editingTask?.content).length > 0 || parseCommentFiles(editingTask?.title || editingTask?.content).length > 0"
              class="space-y-1.5">
              <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                Tệp đính kèm đi kèm:
              </label>
              <div class="flex flex-wrap items-center gap-2 p-2 rounded-xl bg-stone-50 border border-stone-200/80">
                <!-- Images -->
                <div v-for="(img, imgIdx) in parseCommentImages(editingTask.title || editingTask.content)" :key="'edit-modal-img-' + imgIdx"
                  class="w-10 h-10 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0">
                  <img :src="img.url" class="w-full h-full object-cover" />
                </div>
                <!-- Files -->
                <div v-for="(file, fIdx) in parseCommentFiles(editingTask.title || editingTask.content)" :key="'edit-modal-file-' + fIdx"
                  class="px-2 py-1 rounded-lg border border-[#d4a574] bg-[#f5e6d0] text-[11px] font-bold text-[#8b5a2b] flex items-center gap-1">
                  <i class="fa-solid fa-file text-[#c87828] text-xs"></i>
                  <span class="truncate max-w-[140px]">{{ file.name }}</span>
                </div>
              </div>
            </div>

            <!-- Date Selection -->
            <div class="space-y-2">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                Thời gian thực hiện:
              </label>
              <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                <button type="button" @click="setEditTaskModalQuickDate(0)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="editingTaskDueDate === formatQuickDateISO(0) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Hôm nay</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(0) }}</span>
                </button>
                <button type="button" @click="setEditTaskModalQuickDate(1)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="editingTaskDueDate === formatQuickDateISO(1) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Ngày mai</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(1) }}</span>
                </button>
                <button type="button" @click="setEditTaskModalQuickDate(2)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="editingTaskDueDate === formatQuickDateISO(2) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>Ngày mốt</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(2) }}</span>
                </button>
                <button type="button" @click="setEditTaskModalQuickDate(7)"
                  class="px-3 py-2 rounded-xl border transition-all text-left flex items-center justify-between cursor-pointer"
                  :class="editingTaskDueDate === formatQuickDateISO(7) ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-500 text-gray-700'">
                  <span>1 tuần nữa</span>
                  <span class="text-[11px] text-gray-400 font-medium">{{ getActionQuickDateLabel(7) }}</span>
                </button>
              </div>

              <div class="pt-2">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Hoặc chọn ngày cụ thể:</label>
                <input type="date" v-model="editingTaskDueDate"
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:outline-none focus:border-emerald-500 bg-gray-50 cursor-pointer" />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2.5">
            <button type="button" @click="isEditTaskModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200/80 cursor-pointer transition-colors">
              Hủy
            </button>
            <button type="button" @click="submitEditScheduleTask" :disabled="isSubmittingEditTask || !editingTaskTitle.trim() || !editingTaskDueDate"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 cursor-pointer transition-colors flex items-center gap-1.5 shadow-sm disabled:opacity-50">
              <i v-if="isSubmittingEditTask" class="fa-solid fa-spinner fa-spin text-xs"></i>
              <span>Lưu thay đổi</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import ActivityComposer from '../components/ActivityComposer.vue'
import { useAuthStore } from '../stores/auth'
import { useProjectStore } from '../stores/project'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'
import { formatCommentTextWithMentions } from '../utils/mentionFormatter'


const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const projectStore = useProjectStore()
const toast = useToastStore()
const confirmStore = useConfirmStore()

const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}
const ACTIVITIES_CACHE_KEY = 'cached_team_activities'
const getCachedActivities = () => {
  try {
    const cached = localStorage.getItem(ACTIVITIES_CACHE_KEY)
    return cached ? JSON.parse(cached) : []
  } catch {
    return []
  }
}
const cachedInitialActivities = getCachedActivities()
const activities = ref(cachedInitialActivities)
const isLoading = ref(cachedInitialActivities.length === 0)
const activeMainTab = ref(route.query.tab === 'actions' ? 'actions' : 'activities')
const activeTab = ref(route.query.tab && route.query.tab !== 'actions' ? route.query.tab : 'all')
const chatMessage = ref('')
const chatProjectId = ref(null)
const replyingToActivity = ref(null)
const isSubmittingChat = ref(false)
const activityComposerRef = ref(null)
const mentionGroups = ref([])
const activeActivityIdForMobileActions = ref(null)
const activityFeedScrollRef = ref(null)
const hasMoreOlderActivities = ref(true)
const isLoadingOlderActivities = ref(false)
const showScrollToBottom = ref(false)
const isLoadingQuotedComment = ref(false)
let activityTouchTimer = null
let activityTouchStarted = false
let ignoreActivityClickUntil = 0

// Schedule tasks state for 'actions' view (Tất cả hành động tiếp theo bao gồm quá hạn)
const SCHEDULE_CACHE_KEY = 'cached_schedule_tasks'
const getCachedScheduleTasks = () => {
  try {
    const cached = localStorage.getItem(SCHEDULE_CACHE_KEY)
    return cached ? JSON.parse(cached) : []
  } catch {
    return []
  }
}
const scheduleTasks = ref(getCachedScheduleTasks())
const isScheduleLoading = ref(false)
const hasMorePastTasks = ref(false)
const hasMoreFutureTasks = ref(false)
const isLoadingPastTasks = ref(false)
const isLoadingFutureTasks = ref(false)

const scheduleTasksScrollContainer = ref(null)

const scrollToTodayOrNearestSchedule = (smooth = false) => {
  nextTick(() => {
    requestAnimationFrame(() => {
      const container = scheduleTasksScrollContainer.value
      if (!container) return
      const groups = groupedScheduleTasks.value
      if (!groups || groups.length === 0) return

      let targetGroup = groups.find(g => g.order === 0)
      if (!targetGroup) targetGroup = groups.find(g => g.order > 0)
      if (!targetGroup && groups.length > 0) targetGroup = groups[groups.length - 1]

      if (targetGroup) {
        const targetEl = container.querySelector(`[data-group-key="${targetGroup.dateKey}"]`)
        if (targetEl) {
          const containerTop = container.getBoundingClientRect().top
          const targetTop = targetEl.getBoundingClientRect().top
          const offset = targetTop - containerTop + container.scrollTop
          if (smooth) {
            container.scrollTo({ top: Math.max(0, offset - 4), behavior: 'smooth' })
          } else {
            container.scrollTop = Math.max(0, offset - 4)
          }
        }
      }
    })
  })
}

const fetchScheduleTasks = async (silent = false) => {
  if (!silent && scheduleTasks.value.length === 0) {
    isScheduleLoading.value = true
  }
  try {
    const res = await axios.get('/api/tasks', {
      params: { view_mode: 'schedule', paginate: 1, limit: 15 }
    })
    const data = res.data?.tasks || (Array.isArray(res.data) ? res.data : [])
    hasMorePastTasks.value = Boolean(res.data?.has_more_past)
    hasMoreFutureTasks.value = Boolean(res.data?.has_more_future)
    scheduleTasks.value = data
    try {
      localStorage.setItem(SCHEDULE_CACHE_KEY, JSON.stringify(data))
    } catch {}
    scrollToTodayOrNearestSchedule()
    setTimeout(() => scrollToTodayOrNearestSchedule(), 100)
  } catch (err) {
    console.error('Failed to fetch schedule tasks:', err)
  } finally {
    isScheduleLoading.value = false
  }
}

const loadOlderScheduleTasks = async () => {
  if (isLoadingPastTasks.value || !hasMorePastTasks.value || scheduleTasks.value.length === 0) return

  const activeTasks = [...scheduleTasks.value].sort((a, b) => {
    return new Date(a.due_date).getTime() - new Date(b.due_date).getTime()
  })
  const oldestTask = activeTasks[0]
  if (!oldestTask) return

  isLoadingPastTasks.value = true
  const container = scheduleTasksScrollContainer.value
  const prevScrollHeight = container ? container.scrollHeight : 0
  const prevScrollTop = container ? container.scrollTop : 0

  try {
    const res = await axios.get('/api/tasks', {
      params: {
        view_mode: 'schedule',
        paginate: 1,
        direction: 'past',
        before_date: oldestTask.due_date,
        before_id: oldestTask.id,
        limit: 15
      }
    })
    const newOlderTasks = res.data?.tasks || []
    hasMorePastTasks.value = Boolean(res.data?.has_more_past)

    if (newOlderTasks.length > 0) {
      const existingIds = new Set(scheduleTasks.value.map(t => t.id))
      const uniqueNew = newOlderTasks.filter(t => !existingIds.has(t.id))
      if (uniqueNew.length > 0) {
        scheduleTasks.value = [...uniqueNew, ...scheduleTasks.value]

        // Keep scroll anchor so screen doesn't jump
        await nextTick()
        requestAnimationFrame(() => {
          if (container) {
            const heightDiff = container.scrollHeight - prevScrollHeight
            container.scrollTop = prevScrollTop + heightDiff
          }
        })
      }
    }
  } catch (err) {
    console.error('Failed to load older schedule tasks:', err)
  } finally {
    isLoadingPastTasks.value = false
  }
}

const loadFutureScheduleTasks = async () => {
  if (isLoadingFutureTasks.value || !hasMoreFutureTasks.value || scheduleTasks.value.length === 0) return

  const activeTasks = [...scheduleTasks.value].sort((a, b) => {
    return new Date(a.due_date).getTime() - new Date(b.due_date).getTime()
  })
  const latestTask = activeTasks[activeTasks.length - 1]
  if (!latestTask) return

  isLoadingFutureTasks.value = true
  try {
    const res = await axios.get('/api/tasks', {
      params: {
        view_mode: 'schedule',
        paginate: 1,
        direction: 'future',
        after_date: latestTask.due_date,
        after_id: latestTask.id,
        limit: 15
      }
    })
    const newFutureTasks = res.data?.tasks || []
    hasMoreFutureTasks.value = Boolean(res.data?.has_more_future)

    if (newFutureTasks.length > 0) {
      const existingIds = new Set(scheduleTasks.value.map(t => t.id))
      const uniqueNew = newFutureTasks.filter(t => !existingIds.has(t.id))
      if (uniqueNew.length > 0) {
        scheduleTasks.value = [...scheduleTasks.value, ...uniqueNew]
      }
    }
  } catch (err) {
    console.error('Failed to load future schedule tasks:', err)
  } finally {
    isLoadingFutureTasks.value = false
  }
}

const handleScheduleScroll = (event) => {
  const el = event?.target || scheduleTasksScrollContainer.value
  if (!el) return

  // Scrolling up: near top
  if (el.scrollTop <= 40 && hasMorePastTasks.value && !isLoadingPastTasks.value) {
    loadOlderScheduleTasks()
  }

  // Scrolling down: near bottom
  const distanceFromBottom = el.scrollHeight - el.scrollTop - el.clientHeight
  if (distanceFromBottom <= 50 && hasMoreFutureTasks.value && !isLoadingFutureTasks.value) {
    loadFutureScheduleTasks()
  }
}

const formatScheduleGroupKey = (dateStr) => {
  if (!dateStr) return { key: 'no_date', label: 'Chưa có ngày', order: 999999 }
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return { key: dateStr, label: dateStr, order: 999999 }

  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const target = new Date(d.getFullYear(), d.getMonth(), d.getDate())

  const diffDays = Math.round((target - today) / (1000 * 60 * 60 * 24))
  const day = d.getDate()
  const month = d.getMonth() + 1
  const year = d.getFullYear()
  const dateFormatted = `${day} tháng ${month}, ${year}`

  let label = ''
  if (diffDays === 0) {
    label = `Hôm nay - ${dateFormatted}`
  } else if (diffDays === 1) {
    label = `Mai - ${dateFormatted}`
  } else if (diffDays === 2) {
    label = `Mốt - ${dateFormatted}`
  } else if (diffDays === -1) {
    label = `Hôm qua - ${dateFormatted}`
  } else if (diffDays < -1) {
    label = `Quá hạn - ${dateFormatted}`
  } else {
    const dayOfWeek = d.getDay()
    const daysArr = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
    label = `${daysArr[dayOfWeek]} - ${dateFormatted}`
  }

  return {
    key: `${target.getTime()}`,
    label,
    order: diffDays
  }
}

// In full view of Tất cả hành động tiếp theo, show ALL active tasks sorted chronological (oldest past -> today -> future)
const groupedScheduleTasks = computed(() => {
  const activeTasks = scheduleTasks.value.filter(t => Boolean(t.due_date) && t.status !== 'done')
  const sorted = [...activeTasks].sort((a, b) => {
    const dateDiff = new Date(a.due_date).getTime() - new Date(b.due_date).getTime()
    if (dateDiff !== 0) return dateDiff
    const timeA = a.created_at ? new Date(a.created_at).getTime() : 0
    const timeB = b.created_at ? new Date(b.created_at).getTime() : 0
    return timeA - timeB
  })

  const groupMap = new Map()
  sorted.forEach(task => {
    const { key, label, order } = formatScheduleGroupKey(task.due_date)
    if (!groupMap.has(key)) {
      groupMap.set(key, {
        dateKey: key,
        dateLabel: label,
        order,
        tasks: []
      })
    }
    groupMap.get(key).tasks.push(task)
  })

  return Array.from(groupMap.values()).sort((a, b) => a.order - b.order)
})

watch(() => activeMainTab.value, (newTab) => {
  if (newTab === 'actions') {
    fetchScheduleTasks()
    scrollToTodayOrNearestSchedule()
    setTimeout(() => scrollToTodayOrNearestSchedule(), 100)
  }
})

watch(() => route.query.tab, (newTab) => {
  if (newTab === 'actions') {
    activeMainTab.value = 'actions'
    fetchScheduleTasks()
    scrollToTodayOrNearestSchedule()
    setTimeout(() => scrollToTodayOrNearestSchedule(), 100)
  } else if (newTab) {
    activeMainTab.value = 'activities'
    activeTab.value = newTab
  }
})

watch(() => groupedScheduleTasks.value.length, (newLen) => {
  if (newLen > 0 && activeMainTab.value === 'actions') {
    scrollToTodayOrNearestSchedule()
    setTimeout(() => scrollToTodayOrNearestSchedule(), 120)
  }
})

// Expanded state for long action items in schedule view
const expandedScheduleTaskIds = ref(new Set())
const toggleExpandScheduleTask = (taskId) => {
  const nextSet = new Set(expandedScheduleTaskIds.value)
  if (nextSet.has(taskId)) {
    nextSet.delete(taskId)
  } else {
    nextSet.add(taskId)
  }
  expandedScheduleTaskIds.value = nextSet
}
const isScheduleTaskExpanded = (taskId) => expandedScheduleTaskIds.value.has(taskId)

const switchMainTab = (tab) => {
  activeMainTab.value = tab
  router.replace({ query: { ...route.query, tab } })
  if (tab === 'actions') {
    fetchScheduleTasks()
    scrollToTodayOrNearestSchedule()
  } else {
    fetchActivities(true)
  }
}

// Expanded state for long activity messages
const expandedActivityIds = ref(new Set())
const toggleExpandActivity = (activityId) => {
  const nextSet = new Set(expandedActivityIds.value)
  if (nextSet.has(activityId)) {
    nextSet.delete(activityId)
  } else {
    nextSet.add(activityId)
  }
  expandedActivityIds.value = nextSet
}
const isActivityExpanded = (activityId) => expandedActivityIds.value.has(activityId)
const isLongContent = (text) => {
  if (!text) return false
  return text.length > 120 || text.split('\n').length > 3
}

watch(() => route.query.tab, (newTab) => {
  if (newTab === 'actions') {
    activeMainTab.value = 'actions'
    fetchScheduleTasks()
  } else {
    activeMainTab.value = 'activities'
    activeTab.value = newTab || 'all'
  }
}, { immediate: true })

let pollTimer = null

const scrollToBottom = (smooth = false) => {
  nextTick(() => {
    requestAnimationFrame(() => {
      if (activityFeedScrollRef.value) {
        if (smooth) {
          activityFeedScrollRef.value.scrollTo({
            top: activityFeedScrollRef.value.scrollHeight,
            behavior: 'smooth'
          })
        } else {
          activityFeedScrollRef.value.scrollTop = activityFeedScrollRef.value.scrollHeight
        }
      }
    })
  })
}

const handleFeedScroll = async (event) => {
  const el = event?.target || activityFeedScrollRef.value
  if (!el) return

  const distanceFromBottom = el.scrollHeight - el.scrollTop - el.clientHeight
  showScrollToBottom.value = distanceFromBottom > 160

  if (el.scrollTop <= 40 && !isLoadingOlderActivities.value && hasMoreOlderActivities.value && activities.value.length > 0) {
    await loadOlderActivities()
  }
}

const loadOlderActivities = async () => {
  if (isLoadingOlderActivities.value || !hasMoreOlderActivities.value || activities.value.length === 0) return

  isLoadingOlderActivities.value = true
  const minId = Math.min(...activities.value.map(a => Number(a.id) || Infinity))
  if (!minId || minId === Infinity) {
    isLoadingOlderActivities.value = false
    return
  }

  const container = activityFeedScrollRef.value
  const previousScrollHeight = container ? container.scrollHeight : 0
  const previousScrollTop = container ? container.scrollTop : 0

  try {
    const res = await axios.get('/api/comments', {
      params: { before_id: minId, limit: 30 }
    })
    const olderComments = (res.data || []).filter(c => Boolean(c.project_id))

    if (olderComments.length === 0) {
      hasMoreOlderActivities.value = false
    } else {
      const existingIds = new Set(activities.value.map(a => a.id))
      const newItems = olderComments.filter(a => !existingIds.has(a.id))
      if (newItems.length === 0) {
        hasMoreOlderActivities.value = false
      } else {
        activities.value = [...activities.value, ...newItems]
        if (olderComments.length < 30) {
          hasMoreOlderActivities.value = false
        }

        await nextTick()
        requestAnimationFrame(() => {
          if (container) {
            const newScrollHeight = container.scrollHeight
            container.scrollTop = (newScrollHeight - previousScrollHeight) + previousScrollTop
          }
        })
      }
    }
  } catch (err) {
    console.error('Failed to load older activities:', err)
  } finally {
    isLoadingOlderActivities.value = false
  }
}

const fetchActivities = async (silent = false) => {
  if (!silent) isLoading.value = true
  try {
    const res = await axios.get('/api/comments', { params: { limit: 50 } })
    // Filter only comments/activities associated with a project
    const filtered = (res.data || []).filter(c => c.project_id)
    activities.value = filtered
    hasMoreOlderActivities.value = filtered.length >= 50
    try {
      localStorage.setItem(ACTIVITIES_CACHE_KEY, JSON.stringify(filtered.slice(0, 50)))
    } catch {}
    if (!silent) {
      scrollToBottom(false)
    }
  } catch (err) {
    console.error('Failed to load activity feed:', err)
  } finally {
    if (!silent) isLoading.value = false
  }
}

let latestActivityRequest = null
const fetchLatestActivities = () => {
  if (latestActivityRequest || activities.value.length === 0) return latestActivityRequest
  const afterId = Math.max(...activities.value.map(activity => Number(activity.id) || 0))
  latestActivityRequest = axios.get('/api/comments', {
    params: { after_id: afterId, limit: 30 }
  }).then(res => {
    const incoming = (res.data || []).filter(comment => comment.project_id)
    if (!incoming.length) return
    const incomingIds = new Set(incoming.map(comment => comment.id))

    const container = activityFeedScrollRef.value
    const isNearBottom = container
      ? (container.scrollHeight - container.scrollTop - container.clientHeight <= 150)
      : true

    activities.value = [...incoming, ...activities.value.filter(comment => !incomingIds.has(comment.id))]

    if (isNearBottom) {
      scrollToBottom(true)
    }
  }).catch(err => {
    console.error('Failed to poll new activities:', err)
  }).finally(() => {
    latestActivityRequest = null
  })
  return latestActivityRequest
}

const handleReplyToActivity = (activity) => {
  activeActivityIdForMobileActions.value = null
  editingCommentLog.value = null
  replyingToActivity.value = {
    ...activity,
    is_private_reply: false
  }
  chatProjectId.value = activity.project_id || activity.project?.id || projectStore.projects[0]?.id
  chatMessage.value = activity.user?.name ? `@${activity.user.name} ` : ''
  activityComposerRef.value?.focus()
}

const handlePrivateReplyToActivity = (activity) => {
  activeActivityIdForMobileActions.value = null
  editingCommentLog.value = null
  replyingToActivity.value = {
    ...activity,
    is_private_reply: true
  }
  chatProjectId.value = activity.project_id || activity.project?.id || projectStore.projects[0]?.id
  chatMessage.value = activity.user?.name ? `"${activity.user.name} ` : ''
  activityComposerRef.value?.focus()
}

const handleActivityTouchStart = (activity) => {
  activityTouchStarted = true
  activityTouchTimer = window.setTimeout(() => {
    if (!activityTouchStarted) return
    activeActivityIdForMobileActions.value = activity.id
    ignoreActivityClickUntil = Date.now() + 700
    navigator.vibrate?.(50)
  }, 500)
}

const handleActivityTouchEnd = () => {
  activityTouchStarted = false
  if (activityTouchTimer) window.clearTimeout(activityTouchTimer)
}

const handleActivityTouchMove = () => {
  handleActivityTouchEnd()
}

const handleActivityClick = (activity) => {
  if (Date.now() < ignoreActivityClickUntil) return
  goToProject(activity.project_id)
}

const activeActivityMenuId = ref(null)
const isDeletingComment = ref(false)

const toggleActivityMenu = (id, event) => {
  event?.stopPropagation()
  activeActivityMenuId.value = activeActivityMenuId.value === id ? null : id
}

const canDeleteComment = (log) => {
  if (!log || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (log.user_id && Number(log.user_id) === Number(user.id)) || (log.user?.id && Number(log.user.id) === Number(user.id)))
}

const canEditComment = (log) => {
  if (!log || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (log.user_id && Number(log.user_id) === Number(user.id)) || (log.user?.id && Number(log.user.id) === Number(user.id)))
}

const editingCommentLog = ref(null)

const handleStartEditComment = (log) => {
  activeActivityMenuId.value = null
  activeActivityIdForMobileActions.value = null
  editingCommentLog.value = log

  // 1. Select project of this activity
  const pId = Number(log.project_id || log.project?.id)
  if (pId) {
    chatProjectId.value = pId
  }

  // 2. Check if this activity is a reply to another comment
  const replyInfo = parseReplyInfo(log.content)
  if (replyInfo) {
    replyingToActivity.value = {
      id: replyInfo.id,
      user: { name: replyInfo.user },
      content: replyInfo.text,
      text: replyInfo.text
    }
  } else {
    replyingToActivity.value = null
  }

  // 3. Populate chat text
  chatMessage.value = parseCommentText(log.content)

  // 4. Focus composer
  nextTick(() => {
    activityComposerRef.value?.focus()
  })
}

const cancelEdit = () => {
  editingCommentLog.value = null
  replyingToActivity.value = null
  chatMessage.value = ''
  activityComposerRef.value?.clearAttachments()
}

const handleDeleteComment = async (commentId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa hoạt động này?')) return
  try {
    isDeletingComment.value = true
    await axios.delete(`/api/comments/${commentId}`)
    activities.value = activities.value.filter(a => a.id !== commentId)
    if (editingCommentLog.value?.id === commentId) {
      cancelEdit()
    }
    activeActivityMenuId.value = null
    toast.success('Đã xóa hoạt động thành công')
  } catch (err) {
    console.error('Failed to delete comment:', err)
    toast.error(err.response?.data?.message || 'Không thể xóa hoạt động này')
  } finally {
    isDeletingComment.value = false
  }
}

const handleFeedDragOver = (e) => {
  e.preventDefault()
  if (e.dataTransfer) {
    e.dataTransfer.dropEffect = 'copy'
  }
}

const handleFeedDrop = async (e) => {
  e.preventDefault()
  e.stopPropagation()
  const droppedFiles = []
  if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
    droppedFiles.push(...Array.from(e.dataTransfer.files))
  } else if (e.dataTransfer?.items && e.dataTransfer.items.length > 0) {
    for (const item of Array.from(e.dataTransfer.items)) {
      if (item.kind === 'file') {
        const file = item.getAsFile()
        if (file) droppedFiles.push(file)
      }
    }
  }
  if (droppedFiles.length > 0 && activityComposerRef.value?.addAttachments) {
    await activityComposerRef.value.addAttachments(droppedFiles)
  }
}

const isActionModalOpen = ref(false)
const actionModalComment = ref(null)
const actionModalTitle = ref('')
const actionModalDueDate = ref('')
const isSubmittingActionModal = ref(false)

const formatQuickDateISO = (offsetDays) => {
  const d = new Date()
  d.setDate(d.getDate() + offsetDays)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const getActionQuickDateLabel = (offsetDays) => {
  const d = new Date()
  d.setDate(d.getDate() + offsetDays)
  return `${d.getDate()}/${d.getMonth() + 1}`
}

const openCreateActionFromComment = (comment) => {
  actionModalComment.value = comment
  actionModalTitle.value = parseCommentText(comment.content) || comment.text || ''
  actionModalDueDate.value = formatQuickDateISO(0) // default to today
  isActionModalOpen.value = true
  activeActivityMenuId.value = null
  activeActivityIdForMobileActions.value = null
}

const setActionModalQuickDate = (offsetDays) => {
  actionModalDueDate.value = formatQuickDateISO(offsetDays)
}

const submitCreateActionFromComment = async () => {
  if (!actionModalComment.value) return
  if (!actionModalTitle.value.trim()) {
    toast.error('Vui lòng nhập nội dung hành động!')
    return
  }
  if (!actionModalDueDate.value) {
    toast.error('Vui lòng chọn thời gian cho hành động!')
    return
  }
  const comment = actionModalComment.value
  const pId = comment.project_id || comment.project?.id
  if (!pId) {
    toast.error('Không tìm thấy thông tin dự án!')
    return
  }

  isSubmittingActionModal.value = true
  try {
    const rawAttachments = extractCommentAttachments(comment.content || comment.text || '')
    const finalTitle = (actionModalTitle.value.trim() + rawAttachments).trim()

    await axios.post('/api/tasks', {
      project_id: pId,
      comment_id: comment.id,
      title: finalTitle,
      due_date: actionModalDueDate.value,
      status: 'todo',
      priority: 'medium',
    })
    toast.success('Đã tạo hành động tiếp theo thành công!')
    isActionModalOpen.value = false
    actionModalComment.value = null
    actionModalTitle.value = ''
    fetchScheduleTasks(true)
  } catch (err) {
    console.error('Failed to create action from comment:', err)
    toast.error(err.response?.data?.message || 'Tạo hành động thất bại!')
  } finally {
    isSubmittingActionModal.value = false
  }
}

// Schedule Task Actions (Edit & Delete & Long-press for Mobile)
const activeScheduleTaskMenuId = ref(null)
const activeScheduleTaskIdForMobile = ref(null)
const isEditTaskModalOpen = ref(false)
const isSubmittingEditTask = ref(false)
const editingTask = ref(null)
const editingTaskTitle = ref('')
const editingTaskDueDate = ref('')

const toggleScheduleTaskMenu = (id, event) => {
  event?.stopPropagation()
  activeScheduleTaskMenuId.value = activeScheduleTaskMenuId.value === id ? null : id
}

let schedTouchTimer = null
let schedTouchStarted = false

const handleScheduleTaskTouchStart = (task, event) => {
  schedTouchStarted = true
  schedTouchTimer = setTimeout(() => {
    if (schedTouchStarted) {
      if (navigator.vibrate) {
        navigator.vibrate(50)
      }
      activeScheduleTaskIdForMobile.value = task.id
    }
  }, 500)
}

const handleScheduleTaskTouchEnd = () => {
  schedTouchStarted = false
  if (schedTouchTimer) clearTimeout(schedTouchTimer)
}

const handleScheduleTaskTouchMove = () => {
  schedTouchStarted = false
  if (schedTouchTimer) clearTimeout(schedTouchTimer)
}

const canEditScheduleTask = (task) => {
  if (!task || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (task.created_by && Number(task.created_by) === Number(user.id)) || (task.creator?.id && Number(task.creator.id) === Number(user.id)))
}

const canDeleteScheduleTask = (task) => {
  if (!task || !authStore.user) return false
  const user = authStore.user
  return Boolean(user.is_system_admin || user.is_admin || (task.created_by && Number(task.created_by) === Number(user.id)) || (task.creator?.id && Number(task.creator.id) === Number(user.id)))
}

const openEditScheduleTaskModal = (task) => {
  activeScheduleTaskMenuId.value = null
  activeScheduleTaskIdForMobile.value = null
  editingTask.value = task
  editingTaskTitle.value = parseCommentText(task.title || task.content) || task.title || ''
  editingTaskDueDate.value = task.due_date ? String(task.due_date).substring(0, 10) : ''
  isEditTaskModalOpen.value = true
}

const setEditTaskModalQuickDate = (offsetDays) => {
  editingTaskDueDate.value = formatQuickDateISO(offsetDays)
}

const submitEditScheduleTask = async () => {
  if (!editingTask.value) return
  if (!editingTaskTitle.value.trim()) {
    toast.error('Vui lòng nhập nội dung hành động!')
    return
  }
  if (!editingTaskDueDate.value) {
    toast.error('Vui lòng chọn ngày thực hiện!')
    return
  }

  isSubmittingEditTask.value = true
  try {
    const rawAttachments = extractCommentAttachments(editingTask.value.title || editingTask.value.content || '')
    const finalTitle = (editingTaskTitle.value.trim() + rawAttachments).trim()

    await axios.put(`/api/tasks/${editingTask.value.id}`, {
      title: finalTitle,
      due_date: editingTaskDueDate.value,
      status: editingTask.value.status || 'todo',
      priority: editingTask.value.priority || 'medium'
    })
    toast.success('Đã cập nhật hành động!')
    isEditTaskModalOpen.value = false
    editingTask.value = null
    fetchScheduleTasks(true)
    fetchActivities?.(true)
  } catch (err) {
    console.error('Failed to update schedule task:', err)
    toast.error(err.response?.data?.message || 'Cập nhật hành động thất bại!')
  } finally {
    isSubmittingEditTask.value = false
  }
}

const handleDeleteScheduleTask = async (task) => {
  activeScheduleTaskMenuId.value = null
  activeScheduleTaskIdForMobile.value = null

  const confirmed = await confirmStore.show({
    title: 'Xóa hành động',
    message: 'Bạn có chắc chắn muốn xóa hành động tiếp theo này?'
  })
  if (!confirmed) return

  try {
    await axios.delete(`/api/tasks/${task.id}`)
    toast.success('Đã xóa hành động!')
    fetchScheduleTasks(true)
    fetchActivities?.(true)
  } catch (err) {
    console.error('Failed to delete schedule task:', err)
    toast.error(err.response?.data?.message || 'Xóa hành động thất bại!')
  }
}

const handleOutsideActivityClick = (event) => {
  if (!event.target.closest?.('.feed-activity-item')) {
    activeActivityIdForMobileActions.value = null
    activeActivityMenuId.value = null
  }
  if (!event.target.closest?.('.schedule-task-item')) {
    activeScheduleTaskIdForMobile.value = null
    activeScheduleTaskMenuId.value = null
  }
}

const cancelReply = () => {
  if (editingCommentLog.value) {
    cancelEdit()
    return
  }
  replyingToActivity.value = null
  chatMessage.value = ''
}

const submitChat = async (payload = null) => {
  if (isSubmittingChat.value) return
  const projectId = chatProjectId.value || projectStore.projects[0]?.id
  if (!projectId) {
    toast.error('Vui lòng chọn hoặc tạo dự án để gửi cập nhật.')
    return
  }

  isSubmittingChat.value = true
  try {
    const attachmentHtml = await activityComposerRef.value?.buildAttachmentHtml() || ''
    if (!chatMessage.value.trim() && !attachmentHtml) return

    const rawDueDate = payload?.dueDate ?? activityComposerRef.value?.selectedDueDate?.value ?? (typeof activityComposerRef.value?.selectedDueDate === 'string' ? activityComposerRef.value?.selectedDueDate : null)
    const dueDate = typeof rawDueDate === 'string' && rawDueDate.trim() ? rawDueDate.trim() : null

    let content = chatMessage.value + attachmentHtml
    if (replyingToActivity.value) {
      const replyMeta = {
        id: replyingToActivity.value.id || null,
        user: replyingToActivity.value.user?.name || (typeof replyingToActivity.value.user === 'string' ? replyingToActivity.value.user : 'Hệ thống'),
        text: replyingToActivity.value.text || parseCommentText(replyingToActivity.value.content),
      }
      content = `[reply:${JSON.stringify(replyMeta)}]${content}`
    }

    if (editingCommentLog.value) {
      const editingId = editingCommentLog.value.id
      const rawContent = editingCommentLog.value.content || ''
      const htmlAttachments = rawContent.replace(/^\[reply:\{.*?\}\]/, '').match(/(<br\s*\/?>\s*(?:<img[^>]*>|<a\b[^>]*>[\s\S]*?<\/a>)|!\[.*?\]\(.*?\)|📎\s*\[.*?\]\(.*?\))/gi)
      if (htmlAttachments && htmlAttachments.length > 0 && !attachmentHtml) {
        content += htmlAttachments.join('')
      }

      const res = await axios.put(`/api/comments/${editingId}`, {
        content,
        project_id: projectId
      })

      const targetAct = activities.value.find(a => a.id === editingId)
      if (targetAct) {
        targetAct.content = res.data?.content || content
        if (res.data?.project) targetAct.project = res.data.project
        if (res.data?.project_id) targetAct.project_id = res.data.project_id
      }

      toast.success('Đã cập nhật hoạt động!')
      cancelEdit()
      fetchActivities(true)
      fetchScheduleTasks()
      broadcastLocalUpdate({ projectId, commentId: editingId })
    } else if (dueDate) {
      // Create task with due_date which automatically creates both the task in schedule AND notification comment
      await axios.post('/api/tasks', {
        project_id: projectId,
        title: content,
        due_date: dueDate,
        status: 'todo',
        priority: 'medium',
      })

      toast.success('Đã gửi cập nhật và thêm vào hành động tiếp theo!')
      chatMessage.value = ''
      replyingToActivity.value = null
      activityComposerRef.value?.clearAttachments()
      activityComposerRef.value?.clearDueDate?.()
      scrollToBottom(true)
      fetchActivities(true)
      fetchScheduleTasks()
      broadcastLocalUpdate({ projectId })
    } else {
      const res = await axios.post('/api/comments', { project_id: projectId, content })
      const createdActivity = res.data
      if (createdActivity?.id) {
        activities.value = [...activities.value.filter(item => item.id !== createdActivity.id), createdActivity]
      }
      toast.success('Gửi cập nhật hoạt động thành công!')
      chatMessage.value = ''
      replyingToActivity.value = null
      activityComposerRef.value?.clearAttachments()
      activityComposerRef.value?.clearDueDate?.()
      scrollToBottom(true)
      fetchScheduleTasks()
      // The POST response is rendered immediately; polling reconciles later.
      broadcastLocalUpdate({ projectId })
    }
  } catch (err) {
    console.error('Failed to submit activity:', err)
    toast.error(err.response?.data?.message || err.message || 'Thao tác thất bại. Vui lòng thử lại.')
  } finally {
    isSubmittingChat.value = false
  }
}

let realtimeBroadcastChannel = null
const realtimeSourceId = `activity-feed-${Date.now()}-${Math.random()}`

const handleRealtimeChannelMessage = (event) => {
  const data = event.data
  if (!data) return
  if (data.sourceId === realtimeSourceId) return
  if (data.type === 'PUSH_RECEIVED' || data.type === 'NOTIFICATION_CLICKED' || data.type === 'PROJECT_UPDATED') {
    fetchActivities(true)
    projectStore.fetchProjects(true)
  }
}

const handleServiceWorkerMessage = (event) => {
  const data = event.data
  if (!data) return
  if (data.type === 'PUSH_RECEIVED' || data.type === 'NOTIFICATION_CLICKED' || data.type === 'PROJECT_UPDATED') {
    fetchActivities(true)
    projectStore.fetchProjects(true)
  }
}

const broadcastLocalUpdate = (payload = {}) => {
  try {
    const ch = new BroadcastChannel('project_realtime_channel')
    ch.postMessage({
      type: 'PROJECT_UPDATED',
      sourceId: realtimeSourceId,
      ...payload
    })
    ch.close()
  } catch (e) { }
}

const filteredActivities = computed(() => {
  let list = activities.value

  if (authStore.user?.is_system_admin) {
    if (activeTab.value === 'comments') {
      list = list.filter(c => !c.type || c.type === 'comment')
    } else if (activeTab.value === 'operations') {
      list = list.filter(c => c.type && c.type !== 'comment')
    }
  }

  // Display oldest at top, newest at bottom (chat app style)
  return [...list].sort((a, b) => {
    const timeDiff = new Date(a.created_at || 0) - new Date(b.created_at || 0)
    if (timeDiff !== 0) return timeDiff
    return (Number(a.id) || 0) - (Number(b.id) || 0)
  })
})

// Group comments chronologically by date headers (Oldest dates first, Hôm nay last)
const groupedActivities = computed(() => {
  const groups = {}
  const list = filteredActivities.value

  list.forEach(item => {
    if (!item.created_at) return
    const date = new Date(item.created_at)
    const today = new Date()
    const yesterday = new Date()
    yesterday.setDate(today.getDate() - 1)

    let dateStr = ''
    if (date.toDateString() === today.toDateString()) {
      dateStr = 'Hôm nay'
    } else if (date.toDateString() === yesterday.toDateString()) {
      dateStr = 'Hôm qua'
    } else {
      const pad = (n) => String(n).padStart(2, '0')
      dateStr = `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`
    }

    if (!groups[dateStr]) {
      groups[dateStr] = []
    }
    groups[dateStr].push(item)
  })

  return groups
})

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const pad = (n) => String(n).padStart(2, '0')
  return `${pad(date.getHours())}:${pad(date.getMinutes())}`
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

const statusDotClass = (health) => {
  if (health === 'yellow' || health === 'white') return 'bg-white border border-gray-300 shadow-3xs'
  if (health === 'red') return 'bg-rose-500'
  if (health === 'green') return 'bg-white border border-gray-300 shadow-3xs'
  return 'bg-gray-400'
}

const getActivityStyle = (act) => {
  const health = act.project_health
  if (health === 'green') {
    return 'bg-white border-gray-300 border text-gray-800'
  } else if (health === 'red') {
    return 'bg-[#fca5a5] border-[#f87171] border-2 text-gray-900'
  }
  return 'bg-white border-gray-300 border text-gray-800'
}

const previewContainerRef = ref(null)
const previewModalImages = ref([])
const previewModalIndex = ref(0)
const previewZoomScale = ref(1)
const previewPanX = ref(0)
const previewPanY = ref(0)
const isPreviewPanning = ref(false)
let panStartX = 0
let panStartY = 0
let panInitialX = 0
let panInitialY = 0
let panHasMoved = false

const activePreviewImage = computed(() => {
  if (!previewModalImages.value || previewModalImages.value.length === 0) return null
  const item = previewModalImages.value[previewModalIndex.value]
  return typeof item === 'string' ? item : (item?.url || item?.src || null)
})

const clampPreviewPan = (scale = previewZoomScale.value, targetX = previewPanX.value, targetY = previewPanY.value, isDragging = false) => {
  if (scale <= 1) {
    previewPanX.value = 0
    previewPanY.value = 0
    return
  }
  const container = previewContainerRef.value
  const cWidth = container?.clientWidth || (typeof window !== 'undefined' ? window.innerWidth * 0.92 : 800)
  const cHeight = container?.clientHeight || (typeof window !== 'undefined' ? window.innerHeight * 0.72 : 600)
  const maxPanX = Math.max(0, (cWidth * (scale - 1)) / 2)
  const maxPanY = Math.max(0, (cHeight * (scale - 1)) / 2)

  if (isDragging) {
    const overdragX = 35
    const overdragY = 35
    previewPanX.value = Math.max(-maxPanX - overdragX, Math.min(maxPanX + overdragX, targetX))
    previewPanY.value = Math.max(-maxPanY - overdragY, Math.min(maxPanY + overdragY, targetY))
  } else {
    previewPanX.value = Math.max(-maxPanX, Math.min(maxPanX, targetX))
    previewPanY.value = Math.max(-maxPanY, Math.min(maxPanY, targetY))
  }
}

const resetPreviewZoom = () => {
  previewZoomScale.value = 1
  previewPanX.value = 0
  previewPanY.value = 0
  isPreviewPanning.value = false
  panHasMoved = false
}

const zoomInPreview = (e) => {
  if (e) e.stopPropagation()
  previewZoomScale.value = Math.min(+(previewZoomScale.value + 0.5).toFixed(2), 5)
  clampPreviewPan(previewZoomScale.value)
}

const zoomOutPreview = (e) => {
  if (e) e.stopPropagation()
  const nextScale = Math.max(+(previewZoomScale.value - 0.5).toFixed(2), 1)
  previewZoomScale.value = nextScale
  if (nextScale <= 1.05) {
    resetPreviewZoom()
  } else {
    clampPreviewPan(nextScale)
  }
}

const handlePreviewImageClick = (e) => {
  if (e) e.stopPropagation()
  if (panHasMoved) return
  if (previewZoomScale.value <= 1) {
    previewZoomScale.value = 2.5
    previewPanX.value = 0
    previewPanY.value = 0
  } else {
    resetPreviewZoom()
  }
}

const togglePreviewZoom = (e) => {
  if (e) e.stopPropagation()
  if (previewZoomScale.value > 1) {
    resetPreviewZoom()
  } else {
    previewZoomScale.value = 2.5
    previewPanX.value = 0
    previewPanY.value = 0
  }
}

const handlePreviewWheel = (e) => {
  if (!activePreviewImage.value) return
  e.preventDefault()
  e.stopPropagation()
  if (e.deltaY < 0) {
    previewZoomScale.value = Math.min(+(previewZoomScale.value + 0.25).toFixed(2), 5)
    clampPreviewPan(previewZoomScale.value)
  } else if (e.deltaY > 0) {
    const nextScale = Math.max(+(previewZoomScale.value - 0.25).toFixed(2), 1)
    previewZoomScale.value = nextScale
    if (nextScale <= 1.05) {
      resetPreviewZoom()
    } else {
      clampPreviewPan(nextScale)
    }
  }
}

const startPreviewPan = (e) => {
  if (e.button !== 0) return
  e.stopPropagation()
  e.preventDefault()
  panHasMoved = false
  panStartX = e.clientX
  panStartY = e.clientY
  panInitialX = previewPanX.value
  panInitialY = previewPanY.value
  if (previewZoomScale.value > 1) {
    isPreviewPanning.value = true
  }
}

const doPreviewPan = (e) => {
  if (e) e.stopPropagation()
  if (Math.abs(e.clientX - panStartX) > 4 || Math.abs(e.clientY - panStartY) > 4) {
    panHasMoved = true
  }
  if (!isPreviewPanning.value || previewZoomScale.value <= 1) return
  const rawX = panInitialX + (e.clientX - panStartX)
  const rawY = panInitialY + (e.clientY - panStartY)
  clampPreviewPan(previewZoomScale.value, rawX, rawY, true)
}

const endPreviewPan = (e) => {
  if (e) e.stopPropagation()
  isPreviewPanning.value = false
  if (previewZoomScale.value > 1.05) {
    clampPreviewPan(previewZoomScale.value)
  } else {
    resetPreviewZoom()
  }
}

const openImagePreview = (url, imagesList = [], initialIndex = 0) => {
  resetPreviewZoom()
  if (imagesList && imagesList.length > 0) {
    previewModalImages.value = imagesList
    const foundIdx = initialIndex >= 0 ? initialIndex : imagesList.findIndex(img => (img.url || img.src || img) === url)
    previewModalIndex.value = Math.max(0, foundIdx)
  } else if (url) {
    previewModalImages.value = [{ url, name: 'Ảnh đính kèm' }]
    previewModalIndex.value = 0
  }
}

const closeImagePreview = () => {
  resetPreviewZoom()
  previewModalImages.value = []
  previewModalIndex.value = 0
}

const prevPreviewImage = (e) => {
  if (e) e.stopPropagation()
  resetPreviewZoom()
  if (previewModalImages.value.length > 1) {
    previewModalIndex.value = (previewModalIndex.value - 1 + previewModalImages.value.length) % previewModalImages.value.length
  }
}

const nextPreviewImage = (e) => {
  if (e) e.stopPropagation()
  resetPreviewZoom()
  if (previewModalImages.value.length > 1) {
    previewModalIndex.value = (previewModalIndex.value + 1) % previewModalImages.value.length
  }
}

// Mobile touch gestures: pinch-to-zoom, pan & swipe
let touchInitialDistance = 0
let touchInitialScale = 1
let touchStartCenter = { x: 0, y: 0 }
let touchStartPan = { x: 0, y: 0 }
let singleTouchStart = { x: 0, y: 0 }
let singleTouchStartTime = 0
let touchHasMoved = false
let lastTapTimestamp = 0

const getTouchDistance = (t1, t2) => {
  const dx = t1.clientX - t2.clientX
  const dy = t1.clientY - t2.clientY
  return Math.hypot(dx, dy)
}

const getTouchCenter = (t1, t2) => {
  return {
    x: (t1.clientX + t2.clientX) / 2,
    y: (t1.clientY + t2.clientY) / 2
  }
}

const handlePreviewTouchStart = (e) => {
  if (!activePreviewImage.value) return
  touchHasMoved = false

  if (e.touches.length >= 2) {
    // 2-finger pinch gesture start
    isPreviewPanning.value = true
    touchInitialDistance = getTouchDistance(e.touches[0], e.touches[1])
    touchInitialScale = previewZoomScale.value
    touchStartCenter = getTouchCenter(e.touches[0], e.touches[1])
    touchStartPan = { x: previewPanX.value, y: previewPanY.value }
  } else if (e.touches.length === 1) {
    const t = e.touches[0]
    singleTouchStart = { x: t.clientX, y: t.clientY }
    singleTouchStartTime = Date.now()
    panInitialX = previewPanX.value
    panInitialY = previewPanY.value
    touchInitialDistance = 0
    if (previewZoomScale.value > 1) {
      isPreviewPanning.value = true
    }
  }
}

const handlePreviewTouchMove = (e) => {
  if (!activePreviewImage.value) return

  if (e.touches.length >= 2) {
    if (e.cancelable) e.preventDefault()
    touchHasMoved = true
    isPreviewPanning.value = true

    // Initialize initial distance if not already set (e.g. 2nd finger placed during move)
    if (!touchInitialDistance || touchInitialDistance <= 0) {
      touchInitialDistance = getTouchDistance(e.touches[0], e.touches[1])
      touchInitialScale = previewZoomScale.value
      touchStartCenter = getTouchCenter(e.touches[0], e.touches[1])
      touchStartPan = { x: previewPanX.value, y: previewPanY.value }
    }

    if (touchInitialDistance > 0) {
      const currentDist = getTouchDistance(e.touches[0], e.touches[1])
      const scaleFactor = currentDist / touchInitialDistance
      const newScale = Math.min(Math.max(+(touchInitialScale * scaleFactor).toFixed(3), 0.5), 6)
      previewZoomScale.value = newScale

      const curCenter = getTouchCenter(e.touches[0], e.touches[1])
      const rawPanX = touchStartPan.x + (curCenter.x - touchStartCenter.x)
      const rawPanY = touchStartPan.y + (curCenter.y - touchStartCenter.y)

      if (newScale > 1) {
        clampPreviewPan(newScale, rawPanX, rawPanY, true)
      } else {
        previewPanX.value = rawPanX * 0.2
        previewPanY.value = rawPanY * 0.2
      }
    }
  } else if (e.touches.length === 1) {
    const t = e.touches[0]
    const dx = t.clientX - singleTouchStart.x
    const dy = t.clientY - singleTouchStart.y
    if (Math.hypot(dx, dy) > 6) {
      touchHasMoved = true
    }
    if (previewZoomScale.value > 1 && isPreviewPanning.value) {
      if (e.cancelable) e.preventDefault()
      const rawPanX = panInitialX + dx
      const rawPanY = panInitialY + dy
      clampPreviewPan(previewZoomScale.value, rawPanX, rawPanY, true)
    }
  }
}

const handlePreviewTouchEnd = (e) => {
  if (e.touches.length === 0) {
    isPreviewPanning.value = false
    touchInitialDistance = 0

    // Auto spring back if zoomed out below or near normal
    if (previewZoomScale.value <= 1.05) {
      resetPreviewZoom()
    } else {
      if (previewZoomScale.value > 5) {
        previewZoomScale.value = 5
      }
      clampPreviewPan(previewZoomScale.value)
    }

    // Double tap detection
    if (!touchHasMoved && Date.now() - singleTouchStartTime < 250) {
      const now = Date.now()
      if (now - lastTapTimestamp < 300) {
        togglePreviewZoom()
        lastTapTimestamp = 0
        return
      }
      lastTapTimestamp = now
    } else if (previewZoomScale.value <= 1 && e.changedTouches && e.changedTouches[0]) {
      // Swipe left/right for next/prev image
      const endX = e.changedTouches[0].clientX
      const dx = endX - singleTouchStart.x
      const dt = Date.now() - singleTouchStartTime
      if (Math.abs(dx) > 40 && dt < 450) {
        if (dx < 0) {
          nextPreviewImage()
        } else {
          prevPreviewImage()
        }
      }
    }
  } else if (e.touches.length === 1) {
    // Transition from 2 fingers to 1 finger
    const t = e.touches[0]
    singleTouchStart = { x: t.clientX, y: t.clientY }
    singleTouchStartTime = Date.now()
    panInitialX = previewPanX.value
    panInitialY = previewPanY.value
    touchInitialDistance = 0
    if (previewZoomScale.value > 1) {
      isPreviewPanning.value = true
    }
  }
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
    matches.push({ name: m[1] || 'Tài liệu', url: m[2] })
  }

  // 2. New server-backed attachment links.
  const htmlLinkRegex = /<a\b[^>]*\bhref=["']([^"']+)["'][^>]*>[\s\S]*?📎\s*Tệp đính kèm:\s*([^<]+)<\/a>/gi
  while ((m = htmlLinkRegex.exec(content)) !== null) {
    matches.push({ name: m[2].trim().replace(/&quot;/g, '"') || 'Tài liệu', url: m[1] })
  }

  // 3. Legacy HTML file spans <span...>📎 Tệp đính kèm: name</span>
  const htmlRegex = /<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi
  while ((m = htmlRegex.exec(content)) !== null) {
    const rawName = m[1].trim()
    matches.push({ name: rawName, url: '#' })
  }

  return matches
}

const extractCommentAttachments = (content) => {
  if (!content) return ''
  const items = []

  // 1. Markdown images ![name](url)
  const mdImages = content.match(/!\[.*?\]\(.*?\)/g) || []
  items.push(...mdImages)

  // 2. HTML <img> tags
  const htmlImages = content.match(/<img[^>]+>/gi) || []
  items.push(...htmlImages)

  // 3. Markdown files 📎 [name](url)
  const mdFiles = content.match(/📎\s*\[.*?\]\(.*?\)/g) || []
  items.push(...mdFiles)

  // 4. HTML file links <a ...>...📎 Tệp đính kèm:...</a>
  const htmlLinks = content.match(/<a\b[^>]*\bhref=["'][^"']+["'][^>]*>[\s\S]*?📎\s*Tệp đính kèm:\s*[^<]+<\/a>/gi) || []
  items.push(...htmlLinks)

  // 5. Legacy HTML file spans
  const htmlSpans = content.match(/<span[^>]*>📎\s*Tệp đính kèm:[^<]*<\/span>/gi) || []
  items.push(...htmlSpans)

  return items.length > 0 ? (' ' + items.join(' ')) : ''
}

const selectProjectForChat = (projectId, event = null) => {
  const pId = Number(projectId)
  chatProjectId.value = pId

  nextTick(() => {
    activityComposerRef.value?.focus()
  })
}

const handleActivityProjectClick = (projectId, event) => {
  if (event?.altKey) {
    event.preventDefault?.()
    event.stopPropagation?.()
    selectProjectForChat(projectId, event)
    return
  }
  goToProject(projectId, event)
}

const goToProject = (projectId, event = null) => {
  if (event?.altKey) {
    event.preventDefault?.()
    event.stopPropagation?.()
    selectProjectForChat(projectId, event)
    return
  }
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

const handleKeydown = (e) => {
  if (activePreviewImage.value) {
    if (e.key === 'ArrowLeft' || e.code === 'ArrowLeft') {
      prevPreviewImage()
      e.preventDefault?.()
      return
    }
    if (e.key === 'ArrowRight' || e.code === 'ArrowRight') {
      nextPreviewImage()
      e.preventDefault?.()
      return
    }
    if (e.key === 'Escape' || e.code === 'Escape') {
      closeImagePreview()
      e.preventDefault?.()
      return
    }
  }

  if (e.key === 'Escape' || e.code === 'Escape') {
    closeImagePreview()
  }
}

const handleVisibilityOrFocus = () => {
  if (document.visibilityState === 'visible' && !isLoading.value) {
    fetchActivities(true)
  }
}

const isVirtualKeyboardOpen = ref(false)
let keyboardBlurTimeout = null

const resetWindowScroll = () => {
  if (typeof window === 'undefined') return
  if (window.scrollY !== 0 || document.documentElement.scrollTop !== 0 || document.body.scrollTop !== 0) {
    window.scrollTo(0, 0)
    document.documentElement.scrollTop = 0
    document.body.scrollTop = 0
  }
}

const updateKeyboardState = (fromFocus = false) => {
  if (typeof window === 'undefined') return
  if (window.innerWidth >= 768) {
    isVirtualKeyboardOpen.value = false
    document.documentElement.style.removeProperty('--vvh')
    return
  }
  const activeEl = document.activeElement
  const isInputFocused = Boolean(activeEl && (
    activeEl.tagName === 'INPUT' ||
    activeEl.tagName === 'TEXTAREA' ||
    activeEl.isContentEditable
  ))
  if (window.visualViewport) {
    const currentVVH = window.visualViewport.height
    document.documentElement.style.setProperty('--vvh', `${currentVVH}px`)
    const screenHeight = Math.max(window.innerHeight, window.screen?.height || 0, 600)
    const isHeightReduced = currentVVH < screenHeight * 0.82
    if (fromFocus || isInputFocused) {
      isVirtualKeyboardOpen.value = true
    } else {
      isVirtualKeyboardOpen.value = Boolean(isHeightReduced)
    }
  } else {
    isVirtualKeyboardOpen.value = isInputFocused
  }
  resetWindowScroll()
}

const handleVirtualKeyboardFocusIn = (e) => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  const target = e.target
  if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) {
    if (keyboardBlurTimeout) {
      clearTimeout(keyboardBlurTimeout)
      keyboardBlurTimeout = null
    }
    isVirtualKeyboardOpen.value = true
    updateKeyboardState(true)

    // Smooth multi-pass scroll reset as iOS virtual keyboard animates
    resetWindowScroll()
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 50)
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 150)
    setTimeout(() => {
      resetWindowScroll()
      scrollToBottom(false)
    }, 300)
  }
}

const handleVirtualKeyboardFocusOut = () => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  if (keyboardBlurTimeout) clearTimeout(keyboardBlurTimeout)
  keyboardBlurTimeout = setTimeout(() => {
    updateKeyboardState()
    resetWindowScroll()
  }, 120)
}

const handleVisualViewportChange = () => {
  if (typeof window === 'undefined' || window.innerWidth >= 768) return
  const activeEl = document.activeElement
  const isInputFocused = Boolean(activeEl && (
    activeEl.tagName === 'INPUT' ||
    activeEl.tagName === 'TEXTAREA' ||
    activeEl.isContentEditable
  ))
  if (window.visualViewport) {
    const currentVVH = window.visualViewport.height
    document.documentElement.style.setProperty('--vvh', `${currentVVH}px`)
    const screenHeight = Math.max(window.innerHeight, window.screen?.height || 0, 600)
    const isHeightReduced = currentVVH < screenHeight * 0.82
    if (!isHeightReduced) {
      isVirtualKeyboardOpen.value = false
    } else if (isInputFocused) {
      isVirtualKeyboardOpen.value = true
    }
  }
  resetWindowScroll()
}

onMounted(() => {
  updateKeyboardState()
  resetWindowScroll()
  projectStore.activePage = 'home'
  projectStore.activeStatus = null

  // 1. Fetch appropriate tab data immediately so the active feed renders instantly
  if (activeMainTab.value === 'actions') {
    fetchScheduleTasks()
    scrollToTodayOrNearestSchedule()
    setTimeout(() => scrollToTodayOrNearestSchedule(), 100)
    setTimeout(() => scrollToTodayOrNearestSchedule(), 350)
  } else {
    fetchActivities()
  }

  // 2. Load auxiliary and project data in background without blocking the feed
  if (projectStore.projects.length === 0) {
    projectStore.fetchProjects(true).catch(() => { })
  }
  if (projectStore.users.length === 0 || projectStore.customers.length === 0) {
    projectStore.fetchAuxData().catch(() => { })
  }
  axios.get('/api/mention-groups').then(res => { mentionGroups.value = res.data || [] }).catch(() => { })
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('click', handleOutsideActivityClick)
  document.addEventListener('focusin', handleVirtualKeyboardFocusIn)
  document.addEventListener('focusout', handleVirtualKeyboardFocusOut)
  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', handleVisualViewportChange)
    window.visualViewport.addEventListener('scroll', handleVisualViewportChange)
  }

  // Lightweight incremental polling according to active tab
  pollTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible' && !isSubmittingChat.value && !isLoading.value) {
      if (activeMainTab.value === 'actions') {
        fetchScheduleTasks(true)
      } else {
        fetchLatestActivities()
      }
    }
  }, 5000)

  document.addEventListener('visibilitychange', handleVisibilityOrFocus)
  window.addEventListener('focus', handleVisibilityOrFocus)

  // 1. Listen on BroadcastChannel for instant cross-tab and SW push sync
  try {
    realtimeBroadcastChannel = new BroadcastChannel('project_realtime_channel')
    realtimeBroadcastChannel.addEventListener('message', handleRealtimeChannelMessage)
  } catch (e) { }

  // 2. Listen on Service Worker postMessages
  if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', handleServiceWorkerMessage)
  }
})

onUnmounted(() => {
  if (activityTouchTimer) window.clearTimeout(activityTouchTimer)
  if (keyboardBlurTimeout) clearTimeout(keyboardBlurTimeout)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', handleOutsideActivityClick)
  document.removeEventListener('focusin', handleVirtualKeyboardFocusIn)
  document.removeEventListener('focusout', handleVirtualKeyboardFocusOut)
  if (window.visualViewport) {
    window.visualViewport.removeEventListener('resize', handleVisualViewportChange)
    window.visualViewport.removeEventListener('scroll', handleVisualViewportChange)
  }
  if (pollTimer) window.clearInterval(pollTimer)
  document.removeEventListener('visibilitychange', handleVisibilityOrFocus)
  window.removeEventListener('focus', handleVisibilityOrFocus)

  if (realtimeBroadcastChannel) {
    realtimeBroadcastChannel.removeEventListener('message', handleRealtimeChannelMessage)
    realtimeBroadcastChannel.close()
    realtimeBroadcastChannel = null
  }

  if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator) {
    navigator.serviceWorker.removeEventListener('message', handleServiceWorkerMessage)
  }
})

const scrollToComment = async (reply) => {
  if (!reply) return

  let rawId = reply.id
  let targetId = null

  if (rawId !== null && rawId !== undefined) {
    if (typeof rawId === 'number') {
      targetId = rawId
    } else if (typeof rawId === 'string') {
      const match = rawId.match(/^(?:comment-)?(\d+)$/)
      if (match) {
        targetId = parseInt(match[1], 10)
      } else {
        const num = parseInt(rawId, 10)
        if (!isNaN(num)) targetId = num
      }
    }
  }

  // Fallback for legacy comments without id in reply json
  if (!targetId && reply.user && reply.text) {
    const quoteTextNorm = reply.text.trim().toLowerCase()
    const foundAct = activities.value.find(act => {
      const creatorName = (act.user ? act.user.name : 'Thành viên').trim().toLowerCase()
      const actText = parseCommentText(act.content).trim().toLowerCase()
      return creatorName === reply.user.trim().toLowerCase() && actText.includes(quoteTextNorm)
    })
    if (foundAct) {
      targetId = foundAct.id
    }
  }

  if (!targetId) {
    toast.warning('Không tìm thấy thông tin tin nhắn gốc.')
    return
  }

  // 1. Try to find in current DOM
  let el = document.getElementById(`activity-feed-item-${targetId}`)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    el.classList.add('activity-card-highlight')
    setTimeout(() => {
      el.classList.remove('activity-card-highlight')
    }, 2500)
    return
  }

  // 2. Fetch exact comment directly from API /api/comments/{id}
  isLoadingQuotedComment.value = true
  try {
    const res = await axios.get(`/api/comments/${targetId}`)
    const targetComment = res.data

    if (targetComment && targetComment.id) {
      if (!activities.value.some(a => Number(a.id) === Number(targetComment.id))) {
        activities.value = [targetComment, ...activities.value]
      }

      await nextTick()
      requestAnimationFrame(() => {
        setTimeout(() => {
          const targetEl = document.getElementById(`activity-feed-item-${targetId}`)
          if (targetEl) {
            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
            targetEl.classList.add('activity-card-highlight')
            setTimeout(() => {
              targetEl.classList.remove('activity-card-highlight')
            }, 2500)
          } else {
            toast.warning('Tin nhắn gốc có thể đã bị xóa hoặc không còn tồn tại.')
          }
        }, 60)
      })
    } else {
      toast.warning('Tin nhắn gốc có thể đã bị xóa hoặc không còn tồn tại.')
    }
  } catch (err) {
    console.error('Failed to load target comment:', err)
    if (err.response?.status === 404) {
      toast.warning('Tin nhắn gốc đã bị xóa hoặc không còn tồn tại.')
    } else if (err.response?.status === 403) {
      toast.warning('Bạn không có quyền xem tin nhắn này.')
    } else {
      toast.warning('Không thể tải tin nhắn gốc.')
    }
  } finally {
    isLoadingQuotedComment.value = false
  }
}
</script>

<style scoped>
.activity-feed-page {
  height: 100vh;
  height: 100dvh;
  height: var(--vvh, 100dvh);
  max-height: var(--vvh, 100dvh);
  width: 100%;
  position: relative;
  overflow: hidden;
}

@media (max-width: 767px) {
  .activity-feed-page {
    height: var(--vvh, 100dvh) !important;
    max-height: var(--vvh, 100dvh) !important;
    min-height: 0 !important;
  }

  .activity-feed-page main {
    padding-top: calc(8px + env(safe-area-inset-top, 0px)) !important;
  }
}

@keyframes activity-card-flash {

  0%,
  100% {
    background-color: transparent;
  }

  15%,
  70% {
    background-color: #a7f3d0;
  }
}

.activity-card-highlight {
  animation: activity-card-flash 2.5s ease-in-out;
  border-radius: 12px;
}
</style>
