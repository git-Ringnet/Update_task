<template>
  <div class="min-h-screen bg-[#F9F4EE] flex flex-col justify-between pb-24 font-sans">
    <div>
      <!-- Navbar Component matching standard app header -->
      <Navbar>
        <template #left>
          <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <button v-if="showStickyBar" @click="goBack" type="button"
              class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium transition-colors cursor-pointer focus:outline-none">
              <i class="fa-solid fa-arrow-left text-xs"></i>
              <span>Quay lại</span>
            </button>
          </transition>
        </template>
      </Navbar>

      <!-- Main Container -->
      <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <button @click="goBack" type="button"
          class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700 font-medium mb-4 transition-colors cursor-pointer focus:outline-none">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại</span>
        </button>

        <!-- Header Info (Progress & Keyboard hints) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-6">
          <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight font-heading">Chào buổi chiều, {{
              currentUser.name }}! 👋</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Hôm nay bạn đã làm những gì?</p>
          </div>

          <!-- Progress stats and Finish all button -->
          <div class="flex flex-col items-end gap-2 bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-emerald-700 font-heading">
                Đã cập nhật {{ updatedCount }} / {{ totalCount }}
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-48 h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-600 rounded-full transition-all duration-300"
                :style="{ width: `${progressPercentage}%` }"></div>
            </div>

            <!-- Finish All Action Button -->
            <button @click="handleFinishAll" type="button"
              class="mt-1 text-emerald-700 hover:text-emerald-950 font-bold text-xs flex items-center gap-1.5 transition-colors focus:outline-none cursor-pointer">
              <span>Hoàn thành tất cả</span>
              <kbd
                class="px-1.5 py-0.5 text-[9px] font-bold bg-gray-50 border border-emerald-200 text-emerald-800 rounded shadow-3xs select-none">F</kbd>
              <span class="text-[10px] text-gray-400 font-normal ml-0.5">(Ctrl + Enter)</span>
            </button>
          </div>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="isLoading" class="space-y-4">
          <div v-for="i in 3" :key="'skel-' + i"
            class="bg-white rounded-2xl p-6 border border-gray-100 flex items-center gap-4 animate-pulse">
            <div class="w-12 h-12 rounded-full bg-gray-200"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 w-1/4 rounded-md"></div>
              <div class="h-10 bg-gray-100 w-full rounded-xl"></div>
            </div>
          </div>
        </div>

        <!-- Error state -->
        <div v-else-if="loadError" class="bg-white rounded-2xl p-12 text-center border border-rose-100 shadow-2xs">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-50 flex items-center justify-center">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
          </div>
          <p class="text-gray-700 font-bold text-base mb-1">Không thể tải danh sách dự án</p>
          <p class="text-gray-400 text-sm font-medium mb-5">{{ loadError }}</p>
          <button @click="loadProjects" type="button"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition-colors cursor-pointer focus:outline-none">
            <i class="fa-solid fa-rotate-right text-xs"></i>
            <span>Thử lại</span>
          </button>
        </div>

        <!-- Empty state -->
        <div v-else-if="projects.length === 0"
          class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-2xs">
          <p class="text-gray-400 font-medium">Không có dự án nào được chọn để cập nhật.</p>
          <router-link to="/projects" class="mt-4 inline-block text-sm font-semibold text-emerald-700 hover:underline">
            Quay lại danh sách dự án
          </router-link>
        </div>

        <!-- SELECTED PROJECTS LIST: EACH CARD USES HÚ HÚ FORM LAYOUT (MAX-W 720PX CENTERED MATCHING DETAIL PAGE) -->
        <div v-else class="space-y-4 max-w-[720px] mx-auto w-full">
          <div v-for="project in projects" :key="project.id"
            class="bg-white border border-gray-200 shadow-xl rounded-2xl p-4 sm:p-5 relative ring-1 ring-black/5">
            <form @submit.prevent="saveUpdate(project.id)"
              class="flex flex-col lg:flex-row items-stretch lg:items-start gap-4 lg:gap-5">

              <!-- LEFT SECTION: MỤC TIÊU HƯỚNG ĐẾN (CHẶNG CHƯA HOÀN THÀNH CỦA DỰ ÁN) -->
              <div class="flex flex-col gap-2 lg:pr-5 lg:border-r lg:border-gray-200 flex-shrink-0 min-w-[240px]">
                <!-- Project Title -->
                <div class="font-extrabold text-gray-900 text-sm sm:text-base font-heading truncate max-w-[220px]">
                  {{ project.title }}
                </div>

                <div
                  class="flex items-center gap-1 text-[11px] font-bold text-gray-500 tracking-wider uppercase font-sans">
                  <span>MỤC TIÊU HƯỚNG ĐẾN</span>
                </div>

                <!-- IF <= 3 ACTIVE STAGES: DISPLAY STAGE BUTTONS SIDE BY SIDE -->
                <div
                  v-if="getActiveMilestonesForProject(project).length <= 3 && getActiveMilestonesForProject(project).length > 0"
                  class="flex items-center gap-2">
                  <div v-for="ms in getActiveMilestonesForProject(project)" :key="ms.id"
                    @click="selectedMilestoneMap[project.id] = ms.id"
                    class="flex flex-col items-center justify-between p-2 rounded-xl border transition-all cursor-pointer select-none min-w-[72px] sm:min-w-[84px] h-[78px]"
                    :class="selectedMilestoneMap[project.id] === ms.id
                      ? 'bg-[#45A246]/10 border-[#45A246]/35 text-[#45A246] shadow-2xs'
                      : 'bg-white border-gray-200 hover:border-gray-300 text-gray-600 hover:bg-gray-50'">
                    <!-- Circle with SVG flag -->
                    <div
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors"
                      :class="selectedMilestoneMap[project.id] === ms.id
                        ? 'border-2 border-[#45A246] bg-white text-[#45A246]'
                        : 'border border-gray-300 bg-white text-gray-400'">
                      <svg class="w-3.5 h-3.5" :class="selectedMilestoneMap[project.id] === ms.id ? 'fill-[#45A246]' : 'fill-gray-400'" viewBox="0 0 512 512">
                        <g transform="translate(0,512) scale(0.1,-0.1)">
                          <path d="M560 4828 c-18 -13 -43 -36 -54 -51 l-21 -28 0 -2189 0 -2189 21 -28 c73 -98 195 -98 268 0 21 28 21 34 24 850 l3 822 1874 562 c1031 309 1886 570 1900 580 31 22 65 90 65 128 0 39 -36 110 -66 130 -19 12 -2490 923 -3748 1381 -168 61 -215 66 -266 32z m1834 -948 c861 -316 1566 -577 1566 -580 0 -3 -706 -216 -1568 -474 -862 -258 -1573 -471 -1579 -474 -10 -3 -13 212 -13 1053 0 885 2 1056 14 1053 7 -2 718 -262 1580 -578z"/>
                        </g>
                      </svg>
                    </div>

                    <!-- Stage Name -->
                    <span
                      class="text-[10px] font-black tracking-tight text-center uppercase leading-tight line-clamp-2 mt-0.5"
                      :class="selectedMilestoneMap[project.id] === ms.id ? 'text-gray-900 font-bold' : 'text-gray-600'"
                      :title="ms.title">
                      {{ truncateMilestoneTitle(ms.title) }}
                    </span>
                  </div>
                </div>

                <!-- IF > 3 ACTIVE STAGES: DROPDOWN SELECTOR -->
                <div v-else-if="getActiveMilestonesForProject(project).length > 3" class="w-full">
                  <select v-model="selectedMilestoneMap[project.id]"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors cursor-pointer">
                    <option v-for="ms in getActiveMilestonesForProject(project)" :key="ms.id" :value="ms.id">
                      🚩 {{ truncateMilestoneTitle(ms.title) }} ({{ getMilestoneTaskCount(ms) }})
                    </option>
                  </select>
                </div>

                <!-- IF 0 ACTIVE STAGES: Tự động dùng chặng Bắt đầu -->
                <!-- IF 0 ACTIVE STAGES: Tự động dùng chặng Bắt đầu -->
                <div v-else class="flex flex-col gap-2">
                  <div class="flex items-center gap-2">
                    <div
                      class="flex flex-col items-center justify-between p-2 rounded-xl border bg-[#45A246]/10 border-[#45A246]/35 text-[#45A246] shadow-2xs select-none min-w-[72px] sm:min-w-[84px] h-[78px]">
                      <div
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-[#45A246] bg-white text-[#45A246]">
                        <svg class="w-3.5 h-3.5 fill-[#45A246]" viewBox="0 0 512 512">
                          <g transform="translate(0,512) scale(0.1,-0.1)">
                            <path d="M560 4828 c-18 -13 -43 -36 -54 -51 l-21 -28 0 -2189 0 -2189 21 -28 c73 -98 195 -98 268 0 21 28 21 34 24 850 l3 822 1874 562 c1031 309 1886 570 1900 580 31 22 65 90 65 128 0 39 -36 110 -66 130 -19 12 -2490 923 -3748 1381 -168 61 -215 66 -266 32z m1834 -948 c861 -316 1566 -577 1566 -580 0 -3 -706 -216 -1568 -474 -862 -258 -1573 -471 -1579 -474 -10 -3 -13 212 -13 1053 0 885 2 1056 14 1053 7 -2 718 -262 1580 -578z"/>
                          </g>
                        </svg>
                      </div>
                      <span
                        class="text-[10px] font-black tracking-tight text-center uppercase leading-tight text-gray-900 font-bold mt-0.5">
                        Bắt đầu
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- RIGHT SECTION: USER AVATAR + TEXTAREA + ATTACHMENTS & SUBMIT BUTTON -->
              <div class="flex-1 flex flex-col justify-between gap-2 min-w-0 relative">

                <!-- TOP ROW: USER AVATAR + TEXTAREA INPUT -->
                <div class="flex items-start gap-3 min-w-0">
                  <img :src="currentUser.avatar || defaultAvatar"
                    class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 shadow-2xs flex-shrink-0 mt-0.5"
                    :title="currentUser.name" />

                  <!-- Textarea input -->
                  <div class="project-mention-picker flex-1 min-w-0 relative">
                    <textarea :ref="(el) => setTextareaRef(el, project.id)" v-model="updateTexts[project.id]"
                      @input="onInputText(project.id, $event)" @keydown="onTextareaKeydown(project.id, $event)"
                      @paste="handlePaste(project.id, $event)" rows="1" maxlength="1000"
                      placeholder="Chia sẻ cập nhật với team... (Gõ @ để nhắc tên, 'ngày mai', 'hôm nay' để đặt ngày)"
                      class="w-full h-32 overflow-y-auto bg-transparent text-sm sm:text-base font-bold text-gray-900 leading-relaxed py-1 focus:outline-none placeholder-gray-400 resize-none m-0 border-0"></textarea>

                    <!-- MENTION DROPDOWN POPUP -->
                    <div
                      v-if="activeMentionProjectId === project.id && showMentionDropdown && filteredUsersForMention.length > 0"
                      class="absolute left-0 top-full mt-1 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-2xl py-1 text-gray-800 ring-1 ring-black/5 animate-fade-in-up">
                      <div
                        class="px-3 py-1 text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100 mb-1 flex items-center justify-between">
                        <span>Gắn thẻ thành viên</span>
                        <span class="text-[9px] text-emerald-600 font-extrabold">↑↓ Enter</span>
                      </div>
                      <button v-for="(u, idx) in filteredUsersForMention" :key="u.id" type="button" @mousedown.prevent
                        @click="selectMentionUser(project.id, u)"
                        class="w-full px-3 py-2 flex items-center gap-2.5 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left cursor-pointer"
                        :class="{ 'bg-emerald-50 text-emerald-800 font-bold': idx === mentionIndex }">
                        <img :src="u.avatar || defaultAvatar"
                          class="w-6 h-6 rounded-full object-cover border border-gray-200" />
                        <div class="flex flex-col min-w-0 flex-1">
                          <span class="truncate font-bold">{{ u.name }}</span>
                          <span class="text-[10px] text-gray-400 truncate">@{{ u.name }}</span>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- ATTACHED FILES & PASTED IMAGES PREVIEW BAR -->
                <div v-if="attachedFiles[project.id]?.length > 0"
                  class="w-full flex items-center gap-2 overflow-x-auto py-1 border-t border-gray-100 custom-scrollbar">
                  <div v-for="(file, fIdx) in attachedFiles[project.id]" :key="fIdx"
                    class="relative group flex-shrink-0">
                    <!-- Image Thumbnail -->
                    <div v-if="file.isImage" @click="openImageModal(file.url)"
                      class="w-14 h-14 rounded-xl border border-gray-200 overflow-hidden bg-gray-100 shadow-2xs relative cursor-pointer hover:opacity-90 group transition-all"
                      title="Nhấn để xem ảnh phóng to">
                      <img :src="file.url" class="w-full h-full object-cover" />
                      <div
                        class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs">
                        <i class="fa-solid fa-eye"></i>
                      </div>
                      <button type="button" @click.stop="removeAttachment(project.id, fIdx)"
                        class="absolute top-0.5 right-0.5 w-4.5 h-4.5 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] transition-colors z-10"
                        title="Xóa hình ảnh">
                        <i class="fa-solid fa-xmark"></i>
                      </button>
                    </div>

                    <!-- File Pill -->
                    <div v-else
                      class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 max-w-[200px]">
                      <i class="fa-solid fa-file text-gray-500"></i>
                      <span class="truncate">{{ file.name }}</span>
                      <button type="button" @click="removeAttachment(project.id, fIdx)"
                        class="text-gray-400 hover:text-rose-600 transition-colors ml-1" title="Xóa file">
                        <i class="fa-solid fa-xmark text-xs"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- BOTTOM ROW: Attachment left, Person + Date + Submit right -->
                <div class="flex items-center justify-between gap-2 pt-0.5 flex-wrap">

                  <!-- LEFT: Attachment File / Image Button -->
                  <div class="flex items-center gap-2">
                    <input :id="'file-input-' + project.id" type="file" multiple
                      accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" class="hidden"
                      @change="handleFileSelect(project.id, $event)" />
                    <label :for="'file-input-' + project.id"
                      class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-500 w-9 h-9"
                      title="Đính kèm tệp / ảnh (hoặc Ctrl+V dán ảnh từ bộ nhớ tạm)">
                      <i class="fa-solid fa-paperclip text-sm"></i>
                    </label>
                    
                    <HealthStatusSelector
                      v-model="healthMap[project.id]"
                      :show-label="false"
                      is-toggle
                    />
                  </div>

                  <!-- RIGHT: Status + Person Picker + Date Picker + Submit Button -->
                  <div class="flex items-center gap-2">
                    <!-- Status indicator -->
                    <template v-if="isSaved[project.id]">
                      <span class="text-xs text-gray-400 font-semibold whitespace-nowrap">{{ savedTimes[project.id]
                      }}</span>
                      <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    </template>

                    <!-- Person picker button -->
                    <div class="project-person-picker relative">
                      <button type="button" @click="toggleProjectPersonPicker(project.id)"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs px-3 py-1.5"
                        :class="(taggedUsersMap[project.id] && taggedUsersMap[project.id].length > 0) ? 'bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 text-emerald-700' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600'"
                        title="Chọn người phụ trách / Tag tên">
                        <i class="fa-regular fa-user text-sm"></i>
                        <span v-if="taggedUsersMap[project.id] && taggedUsersMap[project.id].length > 0">
                          {{ (() => {
                            const names = taggedUsersMap[project.id].map(id => {
                              const u = usersList.find(user => String(user.id) === String(id));
                              return u ? '@' + u.name : '';
                            }).filter(Boolean);
                            if (names.length === 0) return '';
                            return names[0] + (names.length > 1 ? ' ...' : '');
                          })() }}
                        </span>
                        <span v-else>Tag tên</span>
                      </button>

                      <div v-if="activePersonPickerProjectId === project.id"
                        class="absolute right-0 bottom-full mb-2 z-50 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-1 max-h-52 overflow-y-auto ring-1 ring-black/5">
                        <div
                          class="px-3 py-1 text-[10px] uppercase font-bold text-emerald-600 border-b border-gray-100 mb-1">
                          Chọn người phụ trách / Tag tên
                        </div>
                        <button v-if="taggedUsersMap[project.id] && taggedUsersMap[project.id].length > 0" type="button"
                          @click="clearTaggedUsers(project.id)"
                          class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-rose-50 text-rose-500 transition-colors text-left border-b border-gray-100">
                          <i class="fa-solid fa-xmark text-xs"></i><span>Bỏ chọn tất cả</span>
                        </button>
                        <button v-for="u in taggableUsers" :key="u.id" type="button"
                          @click="toggleTaggedUser(project.id, u)"
                          class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left"
                          :class="{ 'bg-emerald-50 text-emerald-800 font-bold': taggedUsersMap[project.id] && taggedUsersMap[project.id].includes(String(u.id)) }">
                          <input type="checkbox" :checked="taggedUsersMap[project.id] && taggedUsersMap[project.id].includes(String(u.id))" class="rounded text-emerald-600 accent-emerald-600 cursor-pointer w-3.5 h-3.5" @click.stop="toggleTaggedUser(project.id, u)" />
                          <img :src="u.avatar || defaultAvatar"
                            class="w-5 h-5 rounded-full object-cover border border-gray-200" />
                          <span class="truncate flex-1">{{ u.name }}</span>
                        </button>
                      </div>
                    </div>

                    <!-- Date & Time picker button -->
                    <div class="relative inline-flex items-center gap-1">
                      <button type="button" @click="toggleProjectDatePicker(project.id)"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs"
                        :class="(dueDateMap[project.id] || dueTimeMap[project.id]) ? 'bg-blue-50 hover:bg-blue-100/80 border border-blue-200 text-blue-700 px-3 py-1.5' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 w-9 h-9'"
                        title="Chọn ngày & giờ">
                        <i class="fa-regular fa-calendar-days text-sm"></i>
                        <span v-if="dueDateMap[project.id] || dueTimeMap[project.id]">{{
                          formatDueDateTag(dueDateMap[project.id],
                            dueTimeMap[project.id]) }}</span>
                      </button>
                      <div v-if="activeDatePickerProjectId === project.id"
                        class="absolute right-0 bottom-full mb-2 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-xl p-3 ring-1 ring-black/5">
                        <div class="text-[10px] uppercase font-bold text-gray-500 mb-2">Chọn ngày & giờ</div>
                        <div class="space-y-2">
                          <div>
                            <label class="text-[10px] font-bold text-gray-400 block mb-1">Ngày hết hạn</label>
                            <input type="date" v-model="dueDateMap[project.id]"
                              class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500" />
                          </div>
                          <div>
                            <label class="text-[10px] font-bold text-gray-400 block mb-1">Giờ (hh:mm)</label>
                            <input type="time" v-model="dueTimeMap[project.id]"
                              class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500" />
                          </div>
                        </div>
                        <button type="button" @click="activeDatePickerProjectId = null"
                          class="mt-3 w-full px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg cursor-pointer hover:bg-emerald-700 transition-colors">Xong</button>
                      </div>
                    </div>

                    <!-- Submit "Hú hú!" Button (Vô hiệu hóa khi có chặng nhưng chưa chọn chặng) -->
                    <div v-if="isSaving[project.id] && uploadProgress[project.id] !== null" class="w-20 self-center">
                      <div class="h-1.5 overflow-hidden rounded-full bg-emerald-100">
                        <div class="h-full rounded-full bg-emerald-600 transition-all duration-200" :style="{ width: `${uploadProgress[project.id]}%` }"></div>
                      </div>
                      <p class="mt-1 text-center text-[9px] font-bold text-emerald-700">{{ uploadProgress[project.id] }}%</p>
                    </div>
                    <button type="submit"
                      :disabled="(getActiveMilestonesForProject(project).length > 0 && !selectedMilestoneMap[project.id]) || isSaving[project.id]"
                      class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#45A246] hover:bg-[#3a903b] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                      <i v-if="!isSaving[project.id]" class="fa-solid fa-dove text-sm"></i>
                      <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                      <span>{{ isSaving[project.id] ? 'Đang tải lên...' : 'Hú hú!' }}</span>
                      <i v-if="!isSaving[project.id]" class="fa-solid fa-chevron-down text-[10px] opacity-80"></i>
                    </button>
                  </div>

                </div>

              </div>

            </form>
          </div>
        </div>

        <!-- IMAGE LIGHTBOX PREVIEW MODAL -->
        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div v-if="previewModalImageUrl" @click="closeImageModal"
            class="fixed inset-0 z-[100] bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8 cursor-zoom-out">
            <div class="relative max-w-5xl max-h-[90vh] flex items-center justify-center" @click.stop>
              <img :src="previewModalImageUrl"
                class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border border-white/20" />
              <button type="button" @click="closeImageModal"
                class="absolute -top-4 -right-4 w-9 h-9 rounded-full bg-white text-gray-800 hover:bg-rose-600 hover:text-white flex items-center justify-center font-bold text-base shadow-xl transition-all cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </transition>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import HealthStatusSelector from '../components/HealthStatusSelector.vue'

import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToastStore()

const defaultAvatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=120'

// Sticky floating bar state
const showStickyBar = ref(false)
const handleScroll = () => {
  showStickyBar.value = window.scrollY > 120
}

const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}

const currentUser = computed(() => authStore.user || {
  name: 'Minh',
  avatar: defaultAvatar
})

// Parse selected project IDs from url query
const selectedIds = route.query.ids ? route.query.ids.split(',') : []

const projects = ref([])
const usersList = ref([])
const isLoading = ref(true)
const loadError = ref(null)

// IMAGE LIGHTBOX PREVIEW MODAL STATE & HANDLERS
const previewModalImageUrl = ref(null)

const openImageModal = (url) => {
  if (url) previewModalImageUrl.value = url
}

const closeImageModal = () => {
  previewModalImageUrl.value = null
}

// Reactive structures to store user updates
const updateTexts = reactive({})
const attachedFiles = reactive({})
const savedTimes = reactive({})
const isSaved = reactive({})
const isSaving = reactive({})
const uploadProgress = reactive({})

// Per-project milestone, assignee, date and time maps
const selectedMilestoneMap = reactive({})
const assigneeMap = reactive({})
const taggedUsersMap = reactive({})
const dueDateMap = reactive({})
const dueTimeMap = reactive({})
const healthMap = reactive({})

const activePersonPickerProjectId = ref(null)
const activeDatePickerProjectId = ref(null)
const taggableUsers = computed(() => {
  const currentUserId = String(authStore.user?.id || '')
  return usersList.value.filter(user => String(user.id) !== currentUserId)
})

// Quick Add Stage State & Methods
const activeAddStageProjectId = ref(null)
const newStageTitleMap = reactive({})
const newStageDueDateMap = reactive({})
const isCreatingStageMap = reactive({})
const stageTitleInputRefs = reactive({})

const setStageTitleInputRef = (el, projectId) => {
  if (el) stageTitleInputRefs[projectId] = el
}

const openQuickAddStage = (projectId) => {
  activeAddStageProjectId.value = projectId
  if (newStageTitleMap[projectId] === undefined) newStageTitleMap[projectId] = ''
  if (!newStageDueDateMap[projectId]) {
    const today = new Date()
    const yyyy = today.getFullYear()
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const dd = String(today.getDate()).padStart(2, '0')
    newStageDueDateMap[projectId] = `${yyyy}-${mm}-${dd}`
  }

  nextTick(() => {
    if (stageTitleInputRefs[projectId]) {
      stageTitleInputRefs[projectId].focus()
    }
  })
}

const quickCreateStage = async (projectId) => {
  const title = newStageTitleMap[projectId]?.trim()
  if (!title) {
    toast.warning('Vui lòng nhập tên chặng!')
    return
  }

  isCreatingStageMap[projectId] = true
  try {
    const res = await axios.post(`/api/projects/${projectId}/milestones`, {
      title,
      due_date: newStageDueDateMap[projectId] || null,
      is_completed: false
    })

    const newMs = res.data
    const targetProject = projects.value.find(p => p.id === projectId)
    if (targetProject) {
      if (!targetProject.milestones) targetProject.milestones = []
      targetProject.milestones.push(newMs)
    }

    // Automatically select the newly created milestone
    selectedMilestoneMap[projectId] = newMs.id
    activeAddStageProjectId.value = null
    newStageTitleMap[projectId] = ''

    toast.success(`Đã tạo chặng "${newMs.title}" thành công!`)

    nextTick(() => {
      focusTextarea(projectId)
    })
  } catch (err) {
    console.error('Lỗi khi tạo chặng mới:', err)
    toast.error('Tạo chặng mới thất bại!')
  } finally {
    isCreatingStageMap[projectId] = false
  }
}

// @Mention State & Auto-Assign Logic
const activeMentionProjectId = ref(null)
const showMentionDropdown = ref(false)
const mentionQuery = ref('')
const mentionIndex = ref(0)

const removeVietnameseAccents = (str) => {
  if (!str) return ''
  return str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
}

const filteredUsersForMention = computed(() => {
  const projectId = activeMentionProjectId.value
  const currentText = updateTexts[projectId] || ''
  
  // Find which users are already tagged in the text
  const taggedUserIds = new Set()
  let tempText = currentText
  
  // Sort users by name length descending to avoid partial matches
  const sortedUsers = taggableUsers.value.slice().sort((a, b) => b.name.length - a.name.length)
  
  sortedUsers.forEach(u => {
    if (u && u.name) {
      const escapedName = u.name.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
      const regex = new RegExp('@' + escapedName + '(?=\\s|$|[,.;:!?()])', 'i')
      if (regex.test(tempText)) {
        taggedUserIds.add(String(u.id))
        tempText = tempText.replace(regex, '[TAGGED]')
      }
    }
  })
  
  // Filter out users that are already tagged
  const availableUsers = taggableUsers.value.filter(u => !taggedUserIds.has(String(u.id)))

  if (!mentionQuery.value) return availableUsers
  
  const q = removeVietnameseAccents(mentionQuery.value).toLowerCase()
  const firstWordMatches = availableUsers.filter(u => {
    const nameAcc = removeVietnameseAccents(u.name).toLowerCase()
    return nameAcc.split(/\s+/)[0]?.startsWith(q)
  })

  if (firstWordMatches.length > 0) return firstWordMatches

  return availableUsers.filter(u => {
    const words = removeVietnameseAccents(u.name).toLowerCase().split(/\s+/)
    return words.slice(1).some(word => word.startsWith(q))
  })
})

// SMART VIETNAMESE NATURAL LANGUAGE DATE PARSER
const parseVietnameseDateFromText = (text) => {
  if (!text) return null
  const currentYear = new Date().getFullYear()
  const now = new Date()

  // 1. Check DD/MM or DD/MM/YYYY (e.g. 18/7, 19/5, 18/07/2026, 18-7)
  const slashMatch = text.match(/(?:ngày\s+)?(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?/i)
  if (slashMatch) {
    const day = parseInt(slashMatch[1], 10)
    const month = parseInt(slashMatch[2], 10)
    let year = slashMatch[3] ? parseInt(slashMatch[3], 10) : currentYear
    if (year < 100) year += 2000

    if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
      const mm = String(month).padStart(2, '0')
      const dd = String(day).padStart(2, '0')
      return `${year}-${mm}-${dd}`
    }
  }

  // 2. Check "ngày X tháng Y" (e.g. 17 tháng 9, ngày 17 tháng 9)
  const textMatch = text.match(/(?:ngày\s+)?(\d{1,2})\s+tháng\s+(\d{1,2})(?:\s+năm\s+(\d{2,4}))?/i)
  if (textMatch) {
    const day = parseInt(textMatch[1], 10)
    const month = parseInt(textMatch[2], 10)
    let year = textMatch[3] ? parseInt(textMatch[3], 10) : currentYear
    if (year < 100) year += 2000

    if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
      const mm = String(month).padStart(2, '0')
      const dd = String(day).padStart(2, '0')
      return `${year}-${mm}-${dd}`
    }
  }

  // 3. Relative keywords (hôm nay, ngày mai, ngày kia)
  const lower = text.toLowerCase()
  if (lower.includes('hôm nay') || lower.includes('hom nay')) {
    const yyyy = now.getFullYear()
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }
  if (lower.includes('ngày mai') || lower.includes('ngay mai')) {
    const tomorrow = new Date(now)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const yyyy = tomorrow.getFullYear()
    const mm = String(tomorrow.getMonth() + 1).padStart(2, '0')
    const dd = String(tomorrow.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }
  if (lower.includes('ngày kia') || lower.includes('ngay kia')) {
    const afterTomorrow = new Date(now)
    afterTomorrow.setDate(afterTomorrow.getDate() + 2)
    const yyyy = afterTomorrow.getFullYear()
    const mm = String(afterTomorrow.getMonth() + 1).padStart(2, '0')
    const dd = String(afterTomorrow.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }

  return null
}

// SMART VIETNAMESE NATURAL LANGUAGE TIME PARSER
const parseTimeFromText = (text) => {
  if (!text) return ''
  const lower = text.toLowerCase()

  const isPM = /\b(chiều|tối|đêm)\b/i.test(lower)
  const isAM = /\b(sáng)\b/i.test(lower)
  const isNoon = /\b(trưa)\b/i.test(lower)

  const minuteMatch = lower.match(/(\d{1,2})\s*(?:h|g|:|giờ)\s*(\d{2})/)
  if (minuteMatch) {
    let hour = parseInt(minuteMatch[1], 10)
    const minute = parseInt(minuteMatch[2], 10)

    if ((isPM || (isNoon && hour < 12)) && hour < 12) {
      hour += 12
    } else if (isAM && hour === 12) {
      hour = 0
    }

    if (hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
      const hh = String(hour).padStart(2, '0')
      const mm = String(minute).padStart(2, '0')
      return `${hh}:${mm}`
    }
  }

  const hourMatch = lower.match(/(\d{1,2})\s*(?:h|g|:|giờ)/)
  if (hourMatch) {
    let hour = parseInt(hourMatch[1], 10)

    if ((isPM || (isNoon && hour < 12)) && hour < 12) {
      hour += 12
    } else if (isAM && hour === 12) {
      hour = 0
    }

    if (hour >= 0 && hour <= 23) {
      const hh = String(hour).padStart(2, '0')
      return `${hh}:00`
    }
  }

  return ''
}

const autoDetectAssigneeFromText = (projectId, text) => {
  if (!text || !usersList.value || usersList.value.length === 0) return
  const matches = [...text.matchAll(/@([^\s@,.:;!?()\n]+)/g)]
  if (matches && matches.length > 0) {
    const lastTerm = matches[matches.length - 1][1]
    if (lastTerm) {
      const qAcc = removeVietnameseAccents(lastTerm)
      const matched = usersList.value.find(u => {
        const nameAcc = removeVietnameseAccents(u.name)
        const parts = nameAcc.split(' ')
        return parts.some(p => p === qAcc) || nameAcc === qAcc
      })
      if (matched) {
        assigneeMap[projectId] = String(matched.id)
      }
    }
  }
}

const textareaRefs = reactive({})

const setTextareaRef = (el, projectId) => {
  if (el) {
    textareaRefs[projectId] = el
  }
}

const focusTextarea = (projectId) => {
  if (textareaRefs[projectId]) {
    textareaRefs[projectId].focus()
  }
}

const onInputText = (projectId, event) => {
  isSaved[projectId] = false
  const text = updateTexts[projectId] || ''
  const el = event.target
  if (el) {
    el.style.height = '128px'
  }

  // 1. Parse Vietnamese natural language date & time
  const parsedDate = parseVietnameseDateFromText(text)
  if (parsedDate) {
    dueDateMap[projectId] = parsedDate
  }
  const parsedTime = parseTimeFromText(text)
  if (parsedTime) {
    dueTimeMap[projectId] = parsedTime
  }

  // 2. Mention dropdown trigger
  const cursorPos = el?.selectionStart || text.length
  const textBeforeCursor = text.substring(0, cursorPos)
  const match = textBeforeCursor.match(/@([^\s@]*)$/)

  if (match) {
    activeMentionProjectId.value = projectId
    mentionQuery.value = match[1]
    showMentionDropdown.value = true
    mentionIndex.value = 0
  } else {
    showMentionDropdown.value = false
  }

  // 3. Auto-detect assignee if user name typed
  autoDetectAssigneeFromText(projectId, text)
}

const onTextareaKeydown = (projectId, event) => {
  if (activeMentionProjectId.value === projectId && showMentionDropdown.value && filteredUsersForMention.value.length > 0) {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      mentionIndex.value = (mentionIndex.value + 1) % filteredUsersForMention.value.length
      return
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      mentionIndex.value = (mentionIndex.value - 1 + filteredUsersForMention.value.length) % filteredUsersForMention.value.length
      return
    } else if (event.key === 'Enter' || event.key === 'Tab') {
      event.preventDefault()
      const selectedUser = filteredUsersForMention.value[mentionIndex.value]
      if (selectedUser) {
        selectMentionUser(projectId, selectedUser)
      }
      return
    } else if (event.key === 'Escape') {
      showMentionDropdown.value = false
      return
    }
  }

  if (event.key === 'Enter' && !event.ctrlKey && !event.metaKey && !event.shiftKey) {
    event.preventDefault()
    saveUpdate(projectId)
  }
}

const selectMentionUser = (projectId, user) => {
  if (!user) return
  const idStr = String(user.id)
  if (!taggedUsersMap[projectId]) {
    taggedUsersMap[projectId] = []
  }
  if (!taggedUsersMap[projectId].includes(idStr)) {
    taggedUsersMap[projectId].push(idStr)
  }
  assigneeMap[projectId] = taggedUsersMap[projectId][0]

  const text = updateTexts[projectId] || ''
  const el = textareaRefs[projectId]
  const cursorPos = el?.selectionStart || text.length
  const textBeforeCursor = text.substring(0, cursorPos)
  const textAfterCursor = text.substring(cursorPos)

  let newPos = cursorPos
  const match = textBeforeCursor.match(/@([^\s@]*)$/)
  if (match) {
    const startIndex = match.index
    const newBefore = textBeforeCursor.substring(0, startIndex) + `@${user.name} `
    updateTexts[projectId] = newBefore + textAfterCursor
    newPos = newBefore.length
  } else {
    updateTexts[projectId] = `${text} @${user.name} `
    newPos = updateTexts[projectId].length
  }
  showMentionDropdown.value = false

  nextTick(() => {
    if (el) {
      el.style.height = '128px'
      el.focus()
      if (typeof el.setSelectionRange === 'function') {
        el.setSelectionRange(newPos, newPos)
      }
      el.scrollTop = el.scrollHeight
    }
  })
}

const toggleTaggedUser = (projectId, user) => {
  if (!user) return
  if (!taggedUsersMap[projectId]) {
    taggedUsersMap[projectId] = []
  }
  const idStr = String(user.id)
  const idx = taggedUsersMap[projectId].indexOf(idStr)
  let text = updateTexts[projectId] || ''

  if (idx > -1) {
    // Uncheck
    taggedUsersMap[projectId].splice(idx, 1)
    const escapedName = user.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    const regex = new RegExp(`@${escapedName}\\s*`, 'gi')
    text = text.replace(regex, '').trim()
  } else {
    // Check
    taggedUsersMap[projectId].push(idStr)
    if (!text.includes(`@${user.name}`)) {
      text = (text + ` @${user.name}`).trim() + ' '
    }
  }
  updateTexts[projectId] = text
  assigneeMap[projectId] = taggedUsersMap[projectId].length > 0 ? taggedUsersMap[projectId][0] : null

  // Resize textarea if element exists
  const el = textareaRefs[projectId]
  if (el) {
    el.style.height = '128px'
  }
}

const clearTaggedUsers = (projectId) => {
  taggedUsersMap[projectId] = []
  assigneeMap[projectId] = null
}

const compressImage = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        let width = img.width
        let height = img.height
        const maxDim = 1200
        if (width > maxDim || height > maxDim) {
          if (width > height) {
            height = Math.round((height * maxDim) / width)
            width = maxDim
          } else {
            width = Math.round((width * maxDim) / height)
            height = maxDim
          }
        }
        canvas.width = width
        canvas.height = height
        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)
        const dataUrl = canvas.toDataURL(file.type.includes('png') ? 'image/png' : 'image/jpeg', 0.75)
        resolve(dataUrl)
      }
      img.onerror = () => resolve(e.target.result)
      img.src = e.target.result
    }
    reader.readAsDataURL(file)
  })
}

const handleFileSelect = async (projectId, event) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  if (!attachedFiles[projectId]) {
    attachedFiles[projectId] = []
  }

  for (const file of Array.from(files)) {
    const isImg = file.type.startsWith('image/')
    if (isImg) {
      const fileUrl = await compressImage(file)
      attachedFiles[projectId].push({
        name: file.name,
        size: file.size,
        type: file.type,
        url: fileUrl,
        isImage: true
      })
    } else {
      // Keep original File object for server upload later (avoid base64 bloat)
      attachedFiles[projectId].push({
        name: file.name,
        size: file.size,
        type: file.type,
        url: null,
        isImage: false,
        file: file
      })
    }
    isSaved[projectId] = false
  }

  event.target.value = ''
}

const handlePaste = async (projectId, event) => {
  const clipboardData = event.clipboardData
  if (!clipboardData || !clipboardData.items) return

  const imageItems = Array.from(clipboardData.items).filter(item => item.type.startsWith('image/'))
  if (imageItems.length === 0) return

  event.preventDefault()

  if (!attachedFiles[projectId]) {
    attachedFiles[projectId] = []
  }

  for (const item of imageItems) {
    const file = item.getAsFile()
    if (!file) continue

    const fileUrl = await compressImage(file)
    const timestamp = new Date().toISOString().slice(11, 19).replace(/:/g, '')
    const ext = file.type.includes('png') ? 'png' : 'jpg'

    attachedFiles[projectId].push({
      name: `pasted_${timestamp}.${ext}`,
      size: file.size,
      type: file.type,
      url: fileUrl,
      isImage: true
    })
    isSaved[projectId] = false
  }
}

const removeAttachment = (projectId, fileIndex) => {
  if (attachedFiles[projectId]) {
    attachedFiles[projectId].splice(fileIndex, 1)
    isSaved[projectId] = false
  }
}

// Milestone & Person Picker Helpers
const getActiveMilestonesForProject = (p) => {
  if (!p || !p.milestones || !Array.isArray(p.milestones)) return []
  return p.milestones.filter(ms => !ms.is_completed)
}

const getMilestoneTaskCount = (ms) => {
  if (!ms) return 0
  if (typeof ms.tasks_count === 'number') return ms.tasks_count
  if (Array.isArray(ms.tasks)) return ms.tasks.length
  return 0
}

const getStartStageTaskCount = (p) => {
  if (!p || !p.tasks || !Array.isArray(p.tasks)) return 0
  return p.tasks.filter(t => !t.milestone_id).length
}

const truncateMilestoneTitle = (title) => {
  if (!title) return ''
  const trimmed = title.trim()
  const words = trimmed.split(/\s+/)
  let result = trimmed
  if (words.length > 3) {
    result = words.slice(0, 3).join(' ') + '...'
  }
  if (result.length > 15) {
    return result.slice(0, 12) + '...'
  }
  return result
}

const toggleProjectPersonPicker = (projectId) => {
  if (activePersonPickerProjectId.value === projectId) {
    activePersonPickerProjectId.value = null
  } else {
    activePersonPickerProjectId.value = projectId
    activeDatePickerProjectId.value = null
  }
}

const toggleProjectDatePicker = (projectId) => {
  if (activeDatePickerProjectId.value === projectId) {
    activeDatePickerProjectId.value = null
  } else {
    activeDatePickerProjectId.value = projectId
    activePersonPickerProjectId.value = null
  }
}

const getAssigneeName = (userId) => {
  if (!userId) return ''
  const u = usersList.value.find(item => String(item.id) === String(userId))
  return u ? u.name : ''
}

const formatDueDateTag = (dateStr, timeStr) => {
  if (!dateStr && !timeStr) return ''
  let result = ''
  if (dateStr) {
    const parts = dateStr.split('-')
    if (parts.length === 3) result = `${parts[2]}/${parts[1]}`
    else result = dateStr
  }
  if (timeStr) {
    result = result ? `${result} ${timeStr}` : timeStr
  }
  return result
}

// Compute progress indicators
const updatedCount = computed(() => {
  return Object.values(isSaved).filter(Boolean).length
})

const totalCount = computed(() => {
  return projects.value.length
})

const progressPercentage = computed(() => {
  if (totalCount.value === 0) return 0
  return (updatedCount.value / totalCount.value) * 100
})

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users')
    usersList.value = res.data || []
  } catch (err) { }
}

const loadProjects = async () => {
  isLoading.value = true
  loadError.value = null
  try {
    await fetchUsers()
    const res = await axios.get('/api/projects')
    const allProjects = res.data?.projects || res.data || []

    if (!Array.isArray(allProjects)) {
      throw new Error('Dữ liệu trả về không hợp lệ')
    }

    if (selectedIds.length > 0) {
      projects.value = allProjects.filter(p => selectedIds.some(id => String(id) === String(p.id)))
    } else {
      projects.value = allProjects
    }

    // Initialize state mapping & default selected milestone per project
    projects.value.forEach(p => {
      if (updateTexts[p.id] === undefined) updateTexts[p.id] = ''
      if (attachedFiles[p.id] === undefined) attachedFiles[p.id] = []
      if (savedTimes[p.id] === undefined) savedTimes[p.id] = null
      if (isSaved[p.id] === undefined) isSaved[p.id] = false
      if (isSaving[p.id] === undefined) isSaving[p.id] = false
      if (healthMap[p.id] === undefined) healthMap[p.id] = p.health

      const activeMs = getActiveMilestonesForProject(p)
      if (!selectedMilestoneMap[p.id] && activeMs.length > 0) {
        selectedMilestoneMap[p.id] = activeMs[0].id
      }
    })

    // Auto-focus the first project's input field after DOM render
    nextTick(() => {
      if (projects.value.length > 0 && projects.value[0]?.id) {
        focusTextarea(projects.value[0].id)
      }
    })
  } catch (err) {
    console.error('Failed to load projects for bulk update:', err)
    if (err.response?.status === 401) {
      loadError.value = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
    } else if (err.response?.status >= 500) {
      loadError.value = 'Lỗi máy chủ. Vui lòng thử lại sau.'
    } else if (!err.response) {
      loadError.value = 'Không thể kết nối đến máy chủ. Kiểm tra kết nối mạng.'
    } else {
      loadError.value = err.message || 'Đã xảy ra lỗi không xác định.'
    }
  } finally {
    isLoading.value = false
  }
}

const saveUpdate = async (projectId) => {
  if (isSaving[projectId] || isSaved[projectId]) return
  isSaving[projectId] = true
  uploadProgress[projectId] = null

  let text = updateTexts[projectId]?.trim() || ''
  const files = attachedFiles[projectId] || []

  if (!text && files.length === 0) {
    isSaving[projectId] = false
    return
  }

  const project = projects.value.find(p => p.id === projectId)
  const activeMs = project ? getActiveMilestonesForProject(project) : []
  const selectedMsId = selectedMilestoneMap[projectId] || null
  if (activeMs.length > 0 && !selectedMsId) {
    toast.warning('Vui lòng chọn chặng mục tiêu trước khi cập nhật.')
    isSaving[projectId] = false
    return
  }

  let titleText = text
  const uploadedAttachmentIds = []
  if (files.length > 0) {
    // Upload non-image files to server; images stay inline (small after compression)
    for (const f of files) {
      if (f.isImage) {
        titleText += `<br/><img src="${f.url}" class="max-h-56 rounded-xl my-2 border border-gray-200 shadow-2xs block" />`
      } else if (f.file) {
        // Upload to server
        try {
          const formData = new FormData()
          formData.append('files[]', f.file)
          const uploadRes = await axios.post('/api/attachments', formData, {
            onUploadProgress: (event) => {
              if (event.total) uploadProgress[projectId] = Math.round((event.loaded * 100) / event.total)
            }
          })
          const uploaded = uploadRes.data?.[0]
          if (!uploaded) throw new Error('Máy chủ không trả về thông tin tệp đã tải lên.')
          uploadedAttachmentIds.push(uploaded.id)
          const serverUrl = uploaded.url
          const safeName = (uploaded.original_name || f.name).replace(/</g, '&lt;').replace(/"/g, '&quot;')
          titleText += `<br/><a href="${serverUrl}" download="${safeName}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 my-1">📎 Tệp đính kèm: ${safeName}</a>`
        } catch (uploadErr) {
          console.error('Failed to upload file:', f.name, uploadErr)
          toast.error(`Không thể tải lên tệp ${f.name}. Vui lòng thử lại.`)
          isSaving[projectId] = false
          uploadProgress[projectId] = null
          return
        }
      } else {
        // Fallback for files without File object (e.g. already embedded)
        titleText += `<br/><span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 my-1">📎 Tệp đính kèm: ${f.name}</span>`
      }
    }
  }

  const selectedAssigneeId = assigneeMap[projectId] ? Number(assigneeMap[projectId]) : null

  let selectedDueDate = null
  const dateVal = dueDateMap[projectId]
  const timeVal = dueTimeMap[projectId]

  if (dateVal && timeVal) {
    selectedDueDate = `${dateVal} ${timeVal}:00`
  } else if (dateVal) {
    selectedDueDate = `${dateVal} 00:00:00`
  } else if (timeVal) {
    const now = new Date()
    const yyyy = now.getFullYear()
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')
    selectedDueDate = `${yyyy}-${mm}-${dd} ${timeVal}:00`
  }

  if (selectedDueDate) {
    const parts = selectedDueDate.split(' ')
    if (parts.length === 2) {
      const dateParts = parts[0].split('-')
      const timeParts = parts[1].split(':')
      if (dateParts.length === 3 && timeParts.length === 3) {
        const yyyy = parseInt(dateParts[0], 10)
        const mm = parseInt(dateParts[1], 10) - 1
        const dd = parseInt(dateParts[2], 10)
        const hh = parseInt(timeParts[0], 10)
        const min = parseInt(timeParts[1], 10)
        const ss = parseInt(timeParts[2], 10)
        const localDate = new Date(yyyy, mm, dd, hh, min, ss)
        if (!isNaN(localDate.getTime())) {
          selectedDueDate = localDate.toISOString()
        }
      }
    }
  }
  const currentUserId = authStore.user?.id || 1

  try {
    // 0. Update Health of the Project if it has changed
    const newHealth = healthMap[projectId]
    if (newHealth && newHealth !== project.health) {
      await axios.patch(`/api/projects/${projectId}/health`, { health: newHealth })
      project.health = newHealth
    }

    // Auto-add tagged users to project members
    const taggedIds = taggedUsersMap[projectId] || []
    if (taggedIds.length > 0) {
      const currentMemberIds = project.members ? project.members.map(m => m.id) : []
      const nextMemberIds = [...currentMemberIds]
      let updated = false
      taggedIds.forEach(id => {
        const idNum = Number(id)
        if (idNum && !nextMemberIds.includes(idNum)) {
          nextMemberIds.push(idNum)
          updated = true
        }
      })
      if (updated) {
        try {
          const res = await axios.put(`/api/projects/${projectId}`, {
            member_ids: nextMemberIds
          })
          project.members = res.data.members || []
        } catch (err) {
          console.error('Failed to auto-add tagged users to project members in bulk page:', err)
        }
      }
    }

    // 1. Post to /api/tasks (creates task under selected project & milestone)
    await axios.post('/api/tasks', {
      project_id: projectId,
      milestone_id: (selectedMsId !== null && selectedMsId !== undefined && !isNaN(Number(selectedMsId))) ? Number(selectedMsId) : null,
      assignee_id: selectedAssigneeId,
      title: titleText,
      status: 'todo',
      priority: 'medium',
      due_date: selectedDueDate,
      created_by: currentUserId,
      tagged_user_ids: taggedIds.map(Number),
      attachment_ids: uploadedAttachmentIds
    })



    // Set saved state
    isSaved[projectId] = true
    const now = new Date()
    const pad = (n) => String(n).padStart(2, '0')
    savedTimes[projectId] = `${pad(now.getHours())}:${pad(now.getMinutes())}`

    toast.success('Đã cập nhật hoạt động!')

    // Automatically navigate back to home screen when all projects are updated
    const allSaved = projects.value.every(p => isSaved[p.id])
    if (allSaved) {
      setTimeout(() => {
        router.push('/views')
      }, 500)
    }
  } catch (err) {
    console.error('Failed to save project update:', err)
    toast.error('Lưu cập nhật thất bại!')
  } finally {
    isSaving[projectId] = false
    uploadProgress[projectId] = null
  }
}

const handleFinishAll = async () => {
  const savePromises = []
  projects.value.forEach(p => {
    const text = updateTexts[p.id]?.trim() || ''
    const files = attachedFiles[p.id] || []
    if ((text || files.length > 0) && !isSaved[p.id]) {
      savePromises.push(saveUpdate(p.id))
    }
  })

  if (savePromises.length > 0) {
    await Promise.all(savePromises)
  }

  setTimeout(() => {
    router.push('/views')
  }, 400)
}

const handleGlobalKeyDown = (e) => {
  if (e.key === 'Escape') {
    if (previewModalImageUrl.value) {
      e.preventDefault()
      closeImageModal()
      return
    }
  }

  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault()
    handleFinishAll()
  }

  if (e.key === 'f' || e.key === 'F') {
    const activeEl = document.activeElement
    const isTyping = activeEl && (
      activeEl.tagName === 'INPUT' ||
      activeEl.tagName === 'TEXTAREA' ||
      activeEl.isContentEditable
    )
    if (!isTyping) {
      e.preventDefault()
      handleFinishAll()
    }
  }
}

const closeTagPickersOnOutsideClick = (event) => {
  const target = event.target
  if (!target?.closest?.('.project-person-picker')) {
    activePersonPickerProjectId.value = null
  }
  if (!target?.closest?.('.project-mention-picker')) {
    showMentionDropdown.value = false
    activeMentionProjectId.value = null
  }
}

onMounted(() => {
  loadProjects()
  window.addEventListener('keydown', handleGlobalKeyDown)
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('click', closeTagPickersOnOutsideClick)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('click', closeTagPickersOnOutsideClick)
})
</script>
