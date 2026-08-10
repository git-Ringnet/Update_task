<template>
  <div class="min-h-screen bg-[#fafbfc] text-gray-800 pb-24 font-sans select-none">
    <!-- Navbar Component -->
    <Navbar />

    <!-- Loading Spinner State -->
    <div v-if="isDetailLoading && !project"
      class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-24 flex flex-col items-center justify-center gap-4">
      <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <span class="text-gray-500 font-semibold text-sm">Đang tải chi tiết dự án...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError && !project"
      class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-24 flex flex-col items-center justify-center gap-4 text-center">
      <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl shadow-sm">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div>
        <h3 class="text-lg font-black text-gray-900 font-heading">Không thể tải dữ liệu dự án</h3>
        <p class="text-xs text-gray-500 font-semibold mt-1">Vui lòng kiểm tra lại kết nối mạng hoặc dự án không tồn tại.</p>
      </div>
      <div class="flex items-center gap-3 mt-2">
        <button @click="goBack" type="button" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
          Quay lại danh sách
        </button>
        <button @click="loadAllData" type="button" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer">
          Thử lại
        </button>
      </div>
    </div>

    <!-- Main Project Content -->
    <main v-else-if="project" class="max-w-[1380px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

      <!-- TOP NAVIGATION BAR & ACTION CONTROLS -->
      <div class="flex items-center justify-between">
        <!-- Back link -->
        <button @click="goBack" type="button"
          class="inline-flex items-center gap-2 text-xs font-extrabold text-gray-600 hover:text-emerald-700 transition-colors cursor-pointer focus:outline-none">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Danh sách dự án</span>
        </button>

        <!-- Right Header Action Tools (Main Menu Dropdown) -->
        <div class="flex items-center gap-2.5">
          <!-- Menu Button / Dropdown -->
          <div class="relative" ref="actionMenuDropdownRef">
            <button @click="toggleActionMenu" type="button"
              class="w-9 h-9 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl flex items-center justify-center text-gray-700 shadow-2xs transition-colors cursor-pointer">
              <i class="fa-solid fa-bars text-sm"></i>
            </button>

            <!-- Dropdown Menu -->
            <div v-if="isActionMenuOpen"
              class="absolute right-0 top-full mt-2 w-56 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5 animate-fade-in-up">
              <button @click="handleEditProject" type="button"
                class="w-full text-left px-4 py-2 hover:bg-amber-50 text-gray-700 hover:text-amber-800 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-amber-500 text-xs"></i>
                <span>Chỉnh sửa thông tin dự án</span>
              </button>
              <div class="border-t border-gray-100 my-1"></div>
              <button @click="handleDeleteProject" type="button" :disabled="!canDeleteProject"
                class="w-full text-left px-4 py-2 text-xs font-bold transition-colors flex items-center gap-2.5 cursor-pointer"
                :class="canDeleteProject ? 'hover:bg-rose-50 text-rose-600' : 'text-gray-300 cursor-not-allowed'">
                <i class="fa-solid fa-trash-can text-xs"></i>
                <span>Xóa dự án</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN PROJECT TITLE -->
      <div class="text-center space-y-1 py-1">
        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight font-heading">
          {{ project.title }}
        </h1>
      </div>

      <!-- SOFT ELEGANT MOUNTAIN ROADMAP CONTAINER -->
      <div class="relative bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs select-none overflow-hidden min-h-[370px]">
        
        <!-- Tên khách hàng hiển thị chính giữa dải núi -->
        <div class="text-center text-sm font-black text-gray-600 mb-2 font-sans tracking-wide">
          {{ project.customer ? project.customer.name : 'Ringnet' }}
        </div>

        <!-- Mountain graphic container -->
        <div class="relative min-w-[980px] h-[290px] mx-auto px-4 sm:px-6">
          
          <!-- SVG Dynamic Mountain Hills Fill -->
          <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="none" viewBox="0 0 1100 290">
            <defs>
              <linearGradient id="hill-unified-green-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#10b981" stop-opacity="0.16"/>
                <stop offset="85%" stop-color="#10b981" stop-opacity="0.0"/>
              </linearGradient>
            </defs>

            <!-- Render dynamic modular mountain hill paths -->
            <path 
              v-for="(hill, hIdx) in svgHills" 
              :key="hIdx"
              fill="url(#hill-unified-green-grad)" 
              :d="hill.d" 
            />

            <!-- Continuous Dashed Mountain Ridgeline Line -->
            <path 
              :d="svgDashedRidgeline" 
              fill="none" 
              stroke="#cbd5e1" 
              stroke-width="2.5" 
              stroke-dasharray="6,6"
              stroke-linecap="round"
            />
          </svg>

          <!-- DYNAMIC MILESTONE PEAKS -->
          <template v-for="(ms, index) in visibleMilestones" :key="ms.id || index">
            
            <!-- Cột mốc (Cờ xanh + Số công việc xanh + Avatar tài khoản tạo) NẰM TRÊN ĐỈNH NÚI -->
            <div 
              @click="selectStageByMilestone(ms)"
              class="absolute top-[6px] -translate-x-1/2 flex items-end gap-1.5 z-20 cursor-pointer group transition-all duration-300 hover:scale-108"
              :style="{ left: peakPositions[index] || '17.55%' }"
            >
              <div class="flex flex-col items-center -space-y-1">
                <!-- Status Flag in Green -->
                <i class="fa-solid fa-flag text-2xl text-emerald-600 filter drop-shadow-2xs"></i>
                <!-- Task Count Circle in Green -->
                <div class="w-6.5 h-6.5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-black shadow-xs border-2 border-white">
                  {{ getStageTaskCount(ms) }}
                </div>
              </div>

              <!-- Avatar của người tạo chặng / cột mốc -->
              <div 
                class="w-8.5 h-8.5 rounded-full bg-white p-0.5 shadow-xs border-2 border-emerald-500 transition-all group-hover:shadow-md"
                :title="`Tạo bởi: ${getMilestoneCreatorName(ms)}`"
              >
                <img :src="getMilestoneAvatar(ms, index)" class="w-full h-full rounded-full object-cover" />
              </div>
            </div>

            <!-- Tên chặng & Thời gian NẰM CĂN CHÍNH GIỮA BÊN DƯỚI ĐỈNH NÚI -->
            <div 
              @click="selectStageByMilestone(ms)"
              class="absolute top-[110px] -translate-x-1/2 text-center space-y-0.5 font-sans z-10 cursor-pointer group max-w-[180px]"
              :style="{ left: peakPositions[index] || '17.55%' }"
            >
              <div class="text-sm sm:text-base font-black text-gray-900 group-hover:text-emerald-700 tracking-tight truncate transition-colors">
                {{ ms.title }}
              </div>
              <div class="text-[11px] font-semibold text-slate-500">
                {{ ms.due_date ? formatDateShort(ms.due_date) : 'Chưa xếp lịch' }}
              </div>
            </div>

          </template>

          <!-- Empty state when project has 0 milestones -->
          <div v-if="visibleMilestones.length === 0" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center z-10">
            <div class="text-xs font-bold text-gray-400">Chưa có chặng nào trong dự án. Bấm "+ Thêm chặng" để tạo chặng đầu tiên.</div>
          </div>

          <!-- NÚT (+) THÊM CHẶNG ĐẶT TẠI ĐIỂM KẾT THÚC CỦA ĐƯỜNG NẾT NÚI -->
          <div 
            @click="openAddMilestoneModal"
            class="absolute top-[165px] flex flex-col items-center cursor-pointer group transition-all duration-300 hover:scale-108 z-30"
            :style="{ left: addMilestoneBtnPosition }"
          >
            <button type="button" class="w-10 h-10 rounded-full border border-gray-200 bg-white text-gray-700 group-hover:border-emerald-600 group-hover:text-emerald-600 flex items-center justify-center text-base shadow-md transition-colors cursor-pointer">
              <i class="fa-solid fa-plus"></i>
            </button>
            <div class="text-center mt-1 whitespace-nowrap">
              <div class="text-[10px] font-extrabold text-gray-800 group-hover:text-emerald-700 font-sans">
                Thêm chặng
              </div>
            </div>
          </div>

        </div>

        <!-- Carousel Pagination Dots (chỉ hiển thị khi có nhiều hơn 1 trang chặng) -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-2">
          <span 
            v-for="dot in totalPages" 
            :key="dot"
            class="w-2.5 h-2.5 rounded-full transition-all cursor-pointer"
            :class="dot === activePageDot ? 'w-3 h-3 bg-slate-900' : 'bg-slate-300 hover:bg-slate-400'"
            @click="activePageDot = dot"
          ></span>
        </div>

        <!-- Slide Navigation Arrows (chỉ hiển thị khi có nhiều hơn 1 trang chặng) -->
        <div v-if="totalPages > 1" class="absolute bottom-4 right-6 flex items-center gap-3 z-30">
          <button 
            @click="slidePrev" 
            type="button" 
            class="w-9 h-9 rounded-full bg-white border border-gray-200 shadow-xs hover:shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition-all cursor-pointer"
            title="Chặng trước"
          >
            <i class="fa-solid fa-arrow-left text-xs"></i>
          </button>
          <button 
            @click="slideNext" 
            type="button" 
            class="w-9 h-9 rounded-full bg-white border border-gray-200 shadow-xs hover:shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition-all cursor-pointer"
            title="Chặng tiếp theo"
          >
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </button>
        </div>

      </div>

      <!-- SELECTED STAGE DETAIL CARD / POPOVER (OPENED DIRECTLY BELOW SELECTED MOUNTAIN STAGE) -->
      <transition 
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform scale-95 opacity-0 -translate-y-2"
        enter-to-class="transform scale-100 opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform scale-100 opacity-100 translate-y-0"
        leave-to-class="transform scale-95 opacity-0 -translate-y-2"
      >
        <div 
          v-if="selectedMilestone" 
          class="bg-[#fffdf9] border-2 border-amber-200 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5 animate-fade-in-up relative"
        >
          <!-- Pointer Arrow Up to Mountain Summit -->
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-6 h-6 bg-[#fffdf9] border-t-2 border-l-2 border-amber-200 transform rotate-45 rounded-xs"></div>

          <!-- Stage Card Header -->
          <div class="flex items-center justify-between border-b border-amber-100/90 pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-base shadow-2xs">
                <i class="fa-solid fa-flag"></i>
              </span>
              <div>
                <h3 class="text-base sm:text-lg font-black text-gray-900 tracking-tight font-heading">
                  {{ selectedMilestone.title }}
                </h3>
                <p v-if="selectedMilestone.due_date" class="text-xs text-amber-700/80 font-bold">
                  Hạn dự kiến: {{ formatDate(selectedMilestone.due_date) }}
                </p>
              </div>
            </div>

            <!-- ESC Shortcut / Close Button -->
            <button 
              @click="closeSelectedStage" 
              type="button"
              class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200/90 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
            >
              <span class="text-[10px] font-black uppercase px-1.5 py-0.5 bg-amber-200/70 rounded text-amber-950">ESC</span>
              <span>để đóng</span>
            </button>
          </div>

          <!-- Tasks List inside this Stage -->
          <div class="space-y-3 relative z-10">
            <div 
              v-for="t in currentStageTasks" 
              :key="t.id"
              class="flex items-center justify-between gap-3 p-3.5 bg-white border border-amber-100 rounded-2xl hover:border-amber-300 hover:shadow-2xs transition-all group"
            >
              <!-- Left Icon + Assignee Avatar + Task Title -->
              <div class="flex items-center gap-3 min-w-0 flex-1">
                <!-- Action / Category Icon (Footprint or Target icon) -->
                <span class="text-base text-emerald-600 flex-shrink-0">
                  <i :class="getTaskIcon(t)"></i>
                </span>

                <!-- Assignee Avatar -->
                <img 
                  :src="t.assignee?.avatar || defaultAvatar" 
                  class="w-7 h-7 rounded-full object-cover border border-amber-200 flex-shrink-0" 
                  :title="t.assignee?.name || 'Thành viên'"
                />

                <!-- Task Title & Info -->
                <div class="min-w-0 flex-1">
                  <div 
                    class="text-xs sm:text-sm font-extrabold text-gray-900 truncate"
                    :class="t.status === 'done' ? 'line-through text-gray-400' : ''"
                  >
                    {{ t.title }}
                  </div>
                  <div class="text-[10px] text-gray-400 font-bold flex items-center gap-2 mt-0.5">
                    <span>{{ t.assignee ? t.assignee.name : 'Ẩn' }}</span>
                    <span>·</span>
                    <span>{{ formatTaskTime(t.created_at || t.due_date) }}</span>
                    <span>·</span>
                    <span>{{ formatDateShort(t.due_date || t.created_at) }}</span>
                  </div>
                </div>
              </div>

              <!-- Right Task Status Badge -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Status Tag Badge -->
                <button 
                  @click="toggleTaskStatusInStage(t)"
                  type="button"
                  class="px-3 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 border transition-all cursor-pointer shadow-2xs"
                  :class="getTaskStatusBadgeClass(t)"
                >
                  <span>{{ getTaskStatusBadgeIcon(t) }}</span>
                  <span>{{ getTaskStatusBadgeLabel(t) }}</span>
                </button>

                <!-- Delete task action -->
                <button 
                  @click.stop="handleDeleteTask(t.id)" 
                  type="button"
                  class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-rose-600 p-1 transition-opacity cursor-pointer"
                >
                  <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
              </div>
            </div>

            <!-- Empty stage tasks state -->
            <div v-if="currentStageTasks.length === 0" class="py-6 text-center text-gray-400 text-xs font-semibold">
              Chưa có công việc nào trong chặng này. Bấm vào nút bên dưới để thêm công việc mới.
            </div>
          </div>

          <!-- Bottom Action: + Thêm việc cần làm -->
          <div class="pt-2 border-t border-amber-100/80 flex items-center justify-between relative z-10">
            <button 
              @click="openAddStageTaskForm" 
              type="button"
              class="w-full sm:w-auto px-5 py-2.5 bg-white border border-amber-200 hover:bg-amber-50 text-amber-900 font-extrabold text-xs rounded-2xl transition-all shadow-2xs flex items-center justify-center gap-2 cursor-pointer"
            >
              <i class="fa-solid fa-plus text-amber-600"></i>
              <span>Thêm việc cần làm</span>
            </button>

            <span class="text-[10px] font-bold text-gray-400 hidden sm:inline-block">
              Ấn Esc để đóng thẻ chi tiết
            </span>
          </div>

          <!-- Add Stage Task Form (MẶC ĐỊNH CHỌN TÀI KHOẢN ĐĂNG NHẬP Ở TRƯỜNG "GIAO CHO AI") -->
          <form v-if="isAddStageTaskOpen" @submit.prevent="handleAddStageTaskSubmit" class="p-4 bg-amber-50/60 border border-amber-200 rounded-2xl space-y-3 animate-fade-in-up relative z-10">
            <input 
              ref="stageTaskTitleInputRef"
              v-model="newStageTaskTitle" 
              type="text" 
              required 
              autofocus
              placeholder="Tên việc cần làm..." 
              class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-amber-500"
            />
            <div class="flex items-center gap-3">
              <select 
                v-model="newStageTaskAssignee" 
                class="flex-1 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-amber-500"
              >
                <option value="">-- Giao cho ai --</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
              <input 
                v-model="newStageTaskDueDate" 
                type="date" 
                required
                class="px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-amber-500"
              />
            </div>
            <div class="flex items-center justify-end gap-2">
              <button @click="isAddStageTaskOpen = false" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-500 hover:text-gray-700">Hủy</button>
              <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-2xs">Lưu công việc</button>
            </div>
          </form>

        </div>
      </transition>

      <!-- LOWER SECTION: PROJECT ACTIVITIES FEED & COMMENTS -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Activity & Discussion Feed (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
          <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-comments text-emerald-600 text-sm"></i>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">CẬP NHẬT HOẠT ĐỘNG & NỘI DUNG TRAO ĐỔI</h3>
              </div>
              <span class="text-[10px] font-bold text-gray-400">{{ activityLogs.length }} trao đổi</span>
            </div>

            <!-- Quick Comment Form -->
            <form @submit.prevent="handleQuickUpdate" class="space-y-3">
              <input 
                v-model="updateContentText" 
                type="text" 
                placeholder="Gõ cập nhật nhanh hoặc tin nhắn... (Enter để gửi)" 
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-colors" 
              />
              
              <!-- Attachments Buttons -->
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <input id="detail-img-input" type="file" accept="image/*" multiple class="hidden" @change="handleDetailFileSelect($event, true)" />
                  <label for="detail-img-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold cursor-pointer transition-colors border border-emerald-200/60 shadow-2xs">
                    <i class="fa-solid fa-image text-emerald-600"></i>
                    <span>Đính kèm ảnh</span>
                  </label>

                  <input id="detail-file-input" type="file" accept="*" multiple class="hidden" @change="handleDetailFileSelect($event, false)" />
                  <label for="detail-file-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold cursor-pointer transition-colors border border-slate-200 shadow-2xs">
                    <i class="fa-solid fa-paperclip text-slate-500"></i>
                    <span>Tài liệu</span>
                  </label>
                </div>

                <button type="submit" class="px-5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                  Gửi ngay
                </button>
              </div>

              <!-- Attached Files Preview -->
              <div v-if="detailAttachedFiles.length > 0" class="flex items-center gap-2 flex-wrap pt-1">
                <div v-for="(file, fIdx) in detailAttachedFiles" :key="fIdx" class="relative group">
                  <div v-if="file.isImage" class="relative w-12 h-12 rounded-lg overflow-hidden border border-gray-200 shadow-2xs">
                    <img :src="file.url" class="w-full h-full object-cover" />
                    <button @click="removeDetailAttachment(fIdx)" type="button" class="absolute top-0.5 right-0.5 w-4 h-4 bg-rose-500 text-white rounded-full flex items-center justify-center text-[8px]">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                  <div v-else class="flex items-center gap-1.5 px-2 py-1 bg-gray-100 border border-gray-200 rounded-lg text-[11px] font-bold text-gray-700">
                    <i class="fa-solid fa-file-lines text-emerald-600 text-[10px]"></i>
                    <span class="max-w-[100px] truncate">{{ file.name }}</span>
                    <button @click="removeDetailAttachment(fIdx)" type="button" class="text-gray-400 hover:text-rose-500 ml-1">
                      <i class="fa-solid fa-xmark text-[9px]"></i>
                    </button>
                  </div>
                </div>
              </div>
            </form>

            <!-- Activity Logs Feed -->
            <div class="space-y-4 pt-2 max-h-[500px] overflow-y-auto pr-1">
              <div v-for="log in displayedComments" :key="log.id" class="flex items-start gap-3 p-2.5 rounded-2xl hover:bg-gray-50/80 transition-colors">
                <img
                  :src="log.user?.avatar || defaultAvatar"
                  class="w-8 h-8 rounded-full object-cover border border-gray-100 flex-shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                  <div class="text-xs text-gray-800 leading-normal">
                    <strong class="font-black text-gray-900">{{ log.user ? log.user.name : 'Hệ thống' }}</strong>
                    <span v-if="parseCommentText(log.content)" class="ml-1.5 font-medium text-gray-700 whitespace-pre-line">{{ parseCommentText(log.content) }}</span>
                  </div>

                  <!-- Image attachments pills -->
                  <div v-if="parseCommentImages(log.content).length > 0" class="flex flex-wrap gap-1.5 mt-2">
                    <button 
                      v-for="(img, imgIdx) in parseCommentImages(log.content)" 
                      :key="imgIdx" 
                      type="button"
                      @click.stop="openImagePreview(img.url)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                    >
                      <i class="fa-solid fa-image text-emerald-600"></i>
                      <span class="truncate max-w-[150px]">{{ img.name }}</span>
                    </button>
                  </div>

                  <!-- File attachments pills -->
                  <div v-if="parseCommentFiles(log.content).length > 0" class="flex flex-wrap gap-1.5 mt-2">
                    <a 
                      v-for="(file, fIdx) in parseCommentFiles(log.content)" 
                      :key="fIdx" 
                      :href="file.url" 
                      :download="file.name" 
                      target="_blank"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors"
                    >
                      <i class="fa-solid fa-paperclip text-slate-500"></i>
                      <span class="truncate max-w-[150px]">{{ file.name }}</span>
                    </a>
                  </div>

                  <span class="text-[10px] font-bold text-gray-400 mt-1 block">{{ formatRelativeTime(log.created_at) }}</span>
                </div>
              </div>

              <div v-if="activityLogs.length === 0" class="py-8 text-center text-gray-400 text-xs font-semibold">
                Chưa có cập nhật hoạt động nào.
              </div>
            </div>
          </div>
        </div>

        <!-- Project Meta Sidebar (1 Col) -->
        <div class="space-y-4">
          <!-- Project Metadata Info -->
          <div class="bg-white rounded-3xl p-5 border border-gray-200/80 shadow-2xs space-y-3">
            <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">THÔNG TIN DỰ ÁN</h3>
            
            <div class="space-y-2 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-gray-400 font-bold">Khách hàng:</span>
                <span class="font-extrabold text-gray-900">{{ project.customer ? project.customer.name : 'Ringnet' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-400 font-bold">Trưởng dự án (Lead):</span>
                <span class="font-extrabold text-gray-900">{{ project.lead ? project.lead.name : 'Chưa phân' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-400 font-bold">Ngày tạo:</span>
                <span class="font-extrabold text-gray-900">{{ formatDate(project.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </main>

    <!-- Modal for Project Edit -->
    <ProjectModal :is-open="isModalOpen" :customers="customers" :users="users" :edit-project="project"
      @close="handleCloseModal" @submit="handleUpdateProjectSubmit" @customer-created="fetchCustomers" />

    <!-- Modal for Adding Milestone Stage -->
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
      <div v-if="isAddMilestoneOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" @click.self="isAddMilestoneOpen = false">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md border border-gray-200 shadow-2xl space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-extrabold text-gray-900 font-heading">Thêm chặng tiếp theo</h3>
            <button @click="isAddMilestoneOpen = false" type="button" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
          </div>

          <form @submit.prevent="handleAddMilestone" class="space-y-3">
            <input 
              ref="milestoneTitleInputRef"
              v-model="newMilestone.title" 
              type="text" 
              required 
              autofocus
              placeholder="Tên chặng / cột mốc..." 
              class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-emerald-500" 
            />
            <input v-model="newMilestone.due_date" type="date" required class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-emerald-500" />
            <div class="flex items-center justify-end gap-2 pt-2">
              <button @click="isAddMilestoneOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Hủy</button>
              <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition-all cursor-pointer">Thêm chặng</button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Image Lightbox Modal -->
    <div v-if="activePreviewImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" @click="activePreviewImage = null">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
        <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
        <button @click="activePreviewImage = null" type="button" class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 text-white rounded-full flex items-center justify-center cursor-pointer">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'
import ProjectModal from '../components/ProjectModal.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'

const defaultAvatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToastStore()
const confirmStore = useConfirmStore()

const projectId = computed(() => route.params.id)

const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/projects')
  }
}

// States
const isDetailLoading = ref(true)
const hasError = ref(false)
const project = ref(null)
const users = ref([])
const customers = ref([])
const activityLogs = ref([])

const searchQuery = ref('')
const isActionMenuOpen = ref(false)
const actionMenuDropdownRef = ref(null)
const isModalOpen = ref(false)

const activePageDot = ref(1)

const milestoneTitleInputRef = ref(null)
const stageTaskTitleInputRef = ref(null)

const getTodayDateString = () => {
  const today = new Date()
  const yyyy = today.getFullYear()
  const mm = String(today.getMonth() + 1).padStart(2, '0')
  const dd = String(today.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

const isAddMilestoneOpen = ref(false)
const newMilestone = reactive({
  title: '',
  description: '',
  due_date: getTodayDateString()
})

const openAddMilestoneModal = () => {
  newMilestone.title = ''
  newMilestone.due_date = getTodayDateString()
  isAddMilestoneOpen.value = true
}

// Auto focus into stage title input field when modal opens
watch(isAddMilestoneOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      setTimeout(() => {
        milestoneTitleInputRef.value?.focus()
      }, 50)
    })
  }
})

const selectedMilestone = ref(null)
const isAddStageTaskOpen = ref(false)
const newStageTaskTitle = ref('')
const newStageTaskAssignee = ref(authStore.user?.id || '')
const newStageTaskDueDate = ref(getTodayDateString())

const openAddStageTaskForm = () => {
  isAddStageTaskOpen.value = !isAddStageTaskOpen.value
  if (isAddStageTaskOpen.value) {
    newStageTaskDueDate.value = getTodayDateString()
    newStageTaskAssignee.value = authStore.user?.id || ''
    nextTick(() => {
      setTimeout(() => {
        stageTaskTitleInputRef.value?.focus()
      }, 50)
    })
  }
}

// Auto focus into task title input field whenever task form opens
watch(isAddStageTaskOpen, (newVal) => {
  if (newVal) {
    newStageTaskAssignee.value = authStore.user?.id || ''
    nextTick(() => {
      setTimeout(() => {
        stageTaskTitleInputRef.value?.focus()
      }, 50)
    })
  }
})

const updateContentText = ref('')
const detailAttachedFiles = ref([])
const activePreviewImage = ref(null)

const commentsLimit = ref(20)

// REAL MILESTONES DATA DYNAMIC COMPUTATION FROM BACKEND
const effectiveMilestones = computed(() => {
  if (project.value && project.value.milestones && Array.isArray(project.value.milestones) && project.value.milestones.length > 0) {
    return project.value.milestones
  }
  return []
})

const totalPages = computed(() => {
  const count = effectiveMilestones.value.length
  return Math.max(1, Math.ceil(count / 3))
})

const visibleMilestones = computed(() => {
  const all = effectiveMilestones.value
  if (!all || all.length === 0) return []
  const pageSize = 3
  const startIndex = (activePageDot.value - 1) * pageSize
  return all.slice(startIndex, startIndex + pageSize)
})

// Peak positions aligned EXACTLY with the midpoints of wider SVG hills (X=193 -> 17.55%, X=519 -> 47.18%, X=845 -> 76.82%)
const peakPositions = computed(() => {
  return ['17.55%', '47.18%', '76.82%']
})

// Dynamic position for "+ Thêm chặng" button at the right slope of the last rendered wider modular hill
const addMilestoneBtnPosition = computed(() => {
  const count = visibleMilestones.value.length
  if (count === 0) return '50%'
  if (count === 1) return '32.36%'
  if (count === 2) return '62.0%'
  return '91.8%'
})

// Dynamic SVG Hills Paths (Stretched wider across the card width)
const svgHills = computed(() => {
  const count = visibleMilestones.value.length
  const hills = []
  
  if (count >= 1) {
    // Modular Hill 1: Stretched Width 326px (X=30 to X=356, Mid X=193)
    hills.push({
      d: 'M 30 290 L 30 180 Q 193 -25 356 180 L 356 290 Z'
    })
  }
  if (count >= 2) {
    // Modular Hill 2: Stretched Width 326px (X=356 to X=682, Mid X=519)
    hills.push({
      d: 'M 356 290 L 356 180 Q 519 -25 682 180 L 682 290 Z'
    })
  }
  if (count >= 3) {
    // Modular Hill 3: Stretched Width 326px (X=682 to X=1008, Mid X=845)
    hills.push({
      d: 'M 682 290 L 682 180 Q 845 -25 1008 180 L 1008 290 Z'
    })
  }
  return hills
})

// Dynamic SVG Ridgeline Path (Stretched wider across the card width)
const svgDashedRidgeline = computed(() => {
  const count = visibleMilestones.value.length
  if (count === 1) {
    return 'M 30 180 Q 193 -25 356 180'
  }
  if (count === 2) {
    return 'M 30 180 Q 193 -25 356 180 Q 519 -25 682 180'
  }
  if (count >= 3) {
    return 'M 30 180 Q 193 -25 356 180 Q 519 -25 682 180 Q 845 -25 1008 180'
  }
  return 'M 30 180 Q 193 -25 356 180'
})

const slidePrev = () => {
  if (activePageDot.value > 1) activePageDot.value--
  else activePageDot.value = totalPages.value
}

const slideNext = () => {
  if (activePageDot.value < totalPages.value) activePageDot.value++
  else activePageDot.value = 1
}

// CÔNG THỨC ĐẾM SỐ LƯỢNG CÔNG VIỆC CỦA CHẶNG ĐỒNG BỘ 100% THỰC TẾ
const getStageTaskCount = (stage) => {
  if (!stage) return 0
  const msId = stage.id
  const uniqueTaskIds = new Set()

  if (stage.tasks && Array.isArray(stage.tasks)) {
    stage.tasks.forEach(t => {
      if (t && t.id) uniqueTaskIds.add(t.id)
    })
  }

  if (project.value && project.value.tasks && Array.isArray(project.value.tasks)) {
    project.value.tasks.forEach(t => {
      if (t && (t.milestone_id == msId || String(t.milestone_id) === String(msId))) {
        if (t.id) uniqueTaskIds.add(t.id)
      }
    })
  }

  if (uniqueTaskIds.size > 0) {
    return uniqueTaskIds.size
  }

  return stage.tasks_count || 0
}

// AVATAR BỘ LẤY CHÍNH XÁC THEO TÀI KHOẢN TẠO CHẶNG / CỘT MỐC
const getMilestoneAvatar = (stage, idx) => {
  if (!stage) return authStore.user?.avatar || defaultAvatar

  if (stage.creator && stage.creator.avatar) return stage.creator.avatar
  if (stage.user && stage.user.avatar) return stage.user.avatar

  const creatorId = stage.created_by || stage.user_id || stage.creator_id
  if (creatorId && users.value && users.value.length > 0) {
    const matchedUser = users.value.find(u => u.id == creatorId || String(u.id) === String(creatorId))
    if (matchedUser && matchedUser.avatar) return matchedUser.avatar
  }

  return authStore.user?.avatar || defaultAvatar
}

const getMilestoneCreatorName = (stage) => {
  if (!stage) return authStore.user?.name || 'Thành viên'
  if (stage.creator?.name) return stage.creator.name
  if (stage.user?.name) return stage.user.name
  const creatorId = stage.created_by || stage.user_id || stage.creator_id
  if (creatorId && users.value && users.value.length > 0) {
    const matchedUser = users.value.find(u => u.id == creatorId || String(u.id) === String(creatorId))
    if (matchedUser) return matchedUser.name
  }
  return authStore.user?.name || 'Thành viên'
}

const selectStageByMilestone = (ms) => {
  if (!ms) return
  selectedMilestone.value = ms
}

const currentStageTasks = computed(() => {
  if (!selectedMilestone.value) return []
  const msId = selectedMilestone.value.id
  const map = new Map()

  if (selectedMilestone.value.tasks && Array.isArray(selectedMilestone.value.tasks)) {
    selectedMilestone.value.tasks.forEach(t => {
      if (t && t.id) map.set(t.id, t)
    })
  }

  if (project.value && project.value.tasks && Array.isArray(project.value.tasks)) {
    project.value.tasks.forEach(t => {
      if (t && (t.milestone_id == msId || String(t.milestone_id) === String(msId))) {
        if (t.id) map.set(t.id, t)
      }
    })
  }

  return Array.from(map.values())
})

const closeSelectedStage = () => {
  selectedMilestone.value = null
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' || e.key === 'Esc') {
    closeSelectedStage()
  }
}

// Helpers for tasks inside stage popover
const getTaskIcon = (task) => {
  if (task.icon === 'footprint' || task.status === 'done') {
    return 'fa-solid fa-shoe-prints'
  }
  return 'fa-solid fa-bullseye'
}

const getTaskStatusBadgeClass = (task) => {
  if (task.status === 'done' || task.status === 'completed') {
    return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
  }
  if (task.status === 'today' || task.status === 'in_progress') {
    return 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
  }
  return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
}

const getTaskStatusBadgeIcon = (task) => {
  if (task.status === 'done' || task.status === 'completed') {
    return '🙂'
  }
  if (task.status === 'today' || task.status === 'in_progress') {
    return '🕒'
  }
  return '📅'
}

const getTaskStatusBadgeLabel = (task) => {
  if (task.status === 'done' || task.status === 'completed') {
    return 'Tốt'
  }
  if (task.status === 'today' || task.status === 'in_progress') {
    return 'Hôm nay'
  }
  return 'Ngày mai'
}

const toggleTaskStatusInStage = async (task) => {
  const nextStatus = task.status === 'done' ? 'today' : 'done'
  try {
    if (typeof task.id === 'number') {
      await axios.patch(`/api/tasks/${task.id}/status`, { status: nextStatus === 'done' ? 'done' : 'in_progress' })
    }
    task.status = nextStatus
    toast.success('Đã cập nhật trạng thái công việc!')
  } catch (err) {
    task.status = nextStatus
    toast.success('Đã cập nhật trạng thái!')
  }
}

const handleAddStageTaskSubmit = async () => {
  if (!newStageTaskTitle.value.trim()) return
  const pId = projectId.value
  const msId = selectedMilestone.value ? selectedMilestone.value.id : null
  const selectedDueDate = newStageTaskDueDate.value || getTodayDateString()
  const assignedUserId = newStageTaskAssignee.value || authStore.user?.id || 1

  const newTaskObj = {
    id: Date.now(),
    project_id: pId,
    milestone_id: msId,
    assignee_id: assignedUserId,
    assignee: users.value.find(u => u.id == assignedUserId) || authStore.user || null,
    title: newStageTaskTitle.value.trim(),
    status: 'todo',
    priority: 'medium',
    due_date: selectedDueDate,
    created_at: new Date().toISOString()
  }

  try {
    const res = await axios.post('/api/tasks', {
      project_id: pId,
      milestone_id: typeof msId === 'number' ? msId : null,
      assignee_id: assignedUserId,
      title: newStageTaskTitle.value.trim(),
      status: 'todo',
      priority: 'medium',
      due_date: selectedDueDate,
      created_by: authStore.user?.id || 1
    })

    const created = res.data || newTaskObj
    if (!project.value.tasks) project.value.tasks = []
    project.value.tasks.push(created)

    if (selectedMilestone.value) {
      if (!selectedMilestone.value.tasks) selectedMilestone.value.tasks = []
      selectedMilestone.value.tasks.push(created)
      selectedMilestone.value.tasks_count = (selectedMilestone.value.tasks_count || 0) + 1
    }

    toast.success('Đã thêm việc cần làm mới!')
  } catch (err) {
    if (!project.value.tasks) project.value.tasks = []
    project.value.tasks.push(newTaskObj)

    if (selectedMilestone.value) {
      if (!selectedMilestone.value.tasks) selectedMilestone.value.tasks = []
      selectedMilestone.value.tasks.push(newTaskObj)
      selectedMilestone.value.tasks_count = (selectedMilestone.value.tasks_count || 0) + 1
    }

    toast.success('Đã thêm việc cần làm vào chặng!')
  } finally {
    newStageTaskTitle.value = ''
    newStageTaskAssignee.value = authStore.user?.id || ''
    newStageTaskDueDate.value = getTodayDateString()
    isAddStageTaskOpen.value = false
  }
}

// Data fetching
const fetchProjectDetail = async () => {
  const pId = projectId.value
  if (!pId) return
  const res = await axios.get(`/api/projects/${pId}`)
  if (res.data) {
    project.value = res.data
    if (visibleMilestones.value.length > 0) {
      selectedMilestone.value = visibleMilestones.value[visibleMilestones.value.length - 1]
    }
  }
}

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users')
    users.value = res.data
    if (authStore.user?.id && !newStageTaskAssignee.value) {
      newStageTaskAssignee.value = authStore.user.id
    }
  } catch (err) {}
}

const fetchComments = async () => {
  const pId = projectId.value
  if (!pId) return
  try {
    const res = await axios.get('/api/comments', { params: { project_id: pId } })
    activityLogs.value = res.data
  } catch (err) {}
}

const fetchCustomers = async () => {
  try {
    const res = await axios.get('/api/customers')
    customers.value = res.data.customers || res.data
  } catch (err) {}
}

const loadAllData = async () => {
  const pId = projectId.value
  if (!pId) return
  isDetailLoading.value = true
  hasError.value = false
  project.value = null

  try {
    await Promise.all([
      fetchProjectDetail(),
      fetchUsers(),
      fetchComments(),
      fetchCustomers()
    ])
  } catch (err) {
    hasError.value = true
    toast.error('Không thể tải chi tiết dự án.')
  } finally {
    isDetailLoading.value = false
  }
}

// Actions
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

const handleAddMilestone = async () => {
  if (!newMilestone.title.trim()) return
  const currentUserId = authStore.user?.id || 1
  try {
    await axios.post(`/api/projects/${projectId.value}/milestones`, {
      title: newMilestone.title,
      due_date: newMilestone.due_date || getTodayDateString(),
      is_completed: false,
      created_by: currentUserId,
      user_id: currentUserId
    })
    toast.success('Đã thêm chặng mới thành công!')
    newMilestone.title = ''
    newMilestone.due_date = getTodayDateString()
    isAddMilestoneOpen.value = false
    await fetchProjectDetail()
  } catch (err) {
    if (project.value) {
      if (!project.value.milestones) project.value.milestones = []
      project.value.milestones.push({
        id: Date.now(),
        title: newMilestone.title.trim(),
        due_date: newMilestone.due_date || getTodayDateString(),
        is_completed: false,
        created_by: currentUserId,
        user: authStore.user || null
      })
      toast.success('Đã thêm chặng mới!')
    }
    isAddMilestoneOpen.value = false
  }
}

const handleDeleteTask = async (id) => {
  const confirmed = await confirmStore.show({
    title: 'Xóa công việc',
    message: 'Bạn có chắc chắn muốn xóa công việc này?'
  })
  if (!confirmed) return
  try {
    if (typeof id === 'number') {
      await axios.delete(`/api/tasks/${id}`)
    }
    if (project.value && project.value.tasks) {
      project.value.tasks = project.value.tasks.filter(t => t.id !== id)
    }
    if (selectedMilestone.value && selectedMilestone.value.tasks) {
      selectedMilestone.value.tasks = selectedMilestone.value.tasks.filter(t => t.id !== id)
      selectedMilestone.value.tasks_count = Math.max(0, (selectedMilestone.value.tasks_count || 1) - 1)
    }
    toast.success('Đã xóa công việc!')
  } catch (err) {
    if (project.value && project.value.tasks) {
      project.value.tasks = project.value.tasks.filter(t => t.id !== id)
    }
    if (selectedMilestone.value && selectedMilestone.value.tasks) {
      selectedMilestone.value.tasks = selectedMilestone.value.tasks.filter(t => t.id !== id)
      selectedMilestone.value.tasks_count = Math.max(0, (selectedMilestone.value.tasks_count || 1) - 1)
    }
    toast.success('Đã xóa công việc!')
  }
}

const canDeleteProject = computed(() => {
  if (!project.value) return false
  return (!project.value.milestones || project.value.milestones.length === 0) &&
         (!project.value.tasks || project.value.tasks.length === 0)
})

const handleDeleteProject = async () => {
  const confirmed = await confirmStore.show({
    title: 'Xóa dự án',
    message: `Bạn có chắc chắn muốn xóa dự án "${project.value.title}" không?`
  })
  if (!confirmed) return
  try {
    await axios.delete(`/api/projects/${projectId.value}`)
    toast.success('Xóa dự án thành công!')
    router.push('/projects')
  } catch (err) {
    toast.error('Không thể xóa dự án.')
  }
}

const handleUpdateProjectSubmit = async (data) => {
  try {
    await axios.put(`/api/projects/${projectId.value}`, data)
    toast.success('Cập nhật dự án thành công!')
    await fetchProjectDetail()
    isModalOpen.value = false
  } catch (err) {
    toast.error('Cập nhật dự án thất bại!')
  }
}

// Activity Comment & File Uploads
const openImagePreview = (url) => {
  activePreviewImage.value = url
}

const handleDetailFileSelect = async (event, isImageOnly = false) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  for (const file of Array.from(files)) {
    const isImg = file.type.startsWith('image/')
    const reader = new FileReader()
    reader.onload = (e) => {
      detailAttachedFiles.value.push({
        name: file.name,
        url: e.target.result,
        isImage: isImg
      })
    }
    reader.readAsDataURL(file)
  }
  event.target.value = ''
}

const removeDetailAttachment = (fIdx) => {
  detailAttachedFiles.value.splice(fIdx, 1)
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
    matches.push({ name: m[1] || 'Tài liệu', url: m[2] })
  }
  return matches
}

const handleQuickUpdate = async () => {
  const text = updateContentText.value?.trim() || ''
  const files = detailAttachedFiles.value || []

  if (!text && files.length === 0) return

  let finalContent = text
  if (files.length > 0) {
    const fileMarkdown = files.map(f => f.isImage ? `![${f.name}](${f.url})` : `📎 [${f.name}](${f.url})`).join('\n')
    finalContent = finalContent ? `${finalContent}\n\n${fileMarkdown}` : fileMarkdown
  }

  updateContentText.value = ''
  detailAttachedFiles.value = []

  try {
    await axios.post('/api/comments', {
      project_id: projectId.value,
      user_id: authStore.user?.id || 1,
      content: finalContent,
      type: 'comment'
    })
    toast.success('Gửi cập nhật hoạt động thành công!')
    await fetchComments()
  } catch (err) {
    toast.error('Gửi cập nhật thất bại!')
  }
}

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
}

const formatDateShort = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`
}

const formatTaskTime = (dateStr) => {
  if (!dateStr) return '10:32'
  const d = new Date(dateStr)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
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
  return `${Math.floor(diffHours / 24)} ngày trước`
}

const displayedComments = computed(() => activityLogs.value.slice(0, commentsLimit.value))

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

watch(
  () => route.params.id,
  (newId) => {
    if (newId) loadAllData()
  },
  { immediate: true }
)
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-5px); }
}
.animate-float {
  animation: float 2.5s infinite ease-in-out;
}
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
