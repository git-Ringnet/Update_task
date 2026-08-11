<template>
  <div class="min-h-screen bg-[#f4f5f0] text-gray-800 pb-24 font-sans select-none">
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
    <main v-else-if="project" class="max-w-[1380px] mx-auto px-4 sm:px-6 lg:px-8 pt-3 pb-16 space-y-4">

      <!-- TOP NAVIGATION BAR & ACTION CONTROLS -->
      <div class="flex items-center justify-between">
        <!-- Back link -->
        <button @click="goBack" type="button"
          class="inline-flex items-center gap-2.5 text-sm sm:text-base font-black text-gray-800 hover:text-emerald-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-2xl px-4 py-2.5 shadow-2xs transition-all cursor-pointer focus:outline-none">
          <i class="fa-solid fa-arrow-left text-sm"></i>
          <span>Danh sách dự án</span>
        </button>

        <!-- Right Header Action Tools (Main Menu Dropdown) -->
        <div class="flex items-center gap-2.5">
          <!-- Menu Button / Dropdown -->
          <div class="relative" ref="actionMenuDropdownRef">
            <button @click="toggleActionMenu" type="button"
              class="w-11 h-11 bg-white border border-gray-200 hover:bg-gray-50 rounded-2xl flex items-center justify-center text-gray-800 shadow-2xs transition-colors cursor-pointer text-lg"
              title="Menu tùy chọn">
              <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Dropdown Menu -->
            <div v-if="isActionMenuOpen"
              class="absolute right-0 top-full mt-2 w-60 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 py-2 text-left ring-1 ring-black/5 animate-fade-in-up">
              <button @click="handleEditProject" type="button"
                class="w-full text-left px-4.5 py-2.5 hover:bg-amber-50 text-gray-800 hover:text-amber-800 text-xs sm:text-sm font-bold transition-colors flex items-center gap-3 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-amber-500 text-sm"></i>
                <span>Chỉnh sửa thông tin dự án</span>
              </button>
              <div class="border-t border-gray-100 my-1"></div>
              <button @click="handleDeleteProject" type="button" :disabled="!canDeleteProject"
                class="w-full text-left px-4.5 py-2.5 text-xs sm:text-sm font-bold transition-colors flex items-center gap-3 cursor-pointer"
                :class="canDeleteProject ? 'hover:bg-rose-50 text-rose-600' : 'text-gray-300 cursor-not-allowed'">
                <i class="fa-solid fa-trash-can text-sm"></i>
                <span>Xóa dự án</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN PROJECT TITLE & CUSTOMER NAME BELOW -->
      <div class="text-left sm:text-center space-y-1 pt-2 pb-1">
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-gray-900 tracking-tight font-heading uppercase break-words max-w-full leading-tight">
          {{ project.title }}
        </h1>
        <div class="text-base sm:text-lg font-bold text-gray-500 font-sans tracking-wide">
          {{ project.customer ? project.customer.name : 'Hùng Nhơn' }}
        </div>
      </div>

      <!-- SOFT ELEGANT MOUNTAIN ROADMAP CONTAINER -->
      <div class="relative bg-[#f4f5f0] rounded-3xl p-4 sm:p-6 select-none overflow-hidden min-h-[340px]">

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

          <!-- NÚT BẮT ĐẦU DỰ ÁN (HIỂN THỊ CỜ VÀ NGÀY TẠO DỰ ÁN NẰM BÊN DƯỚI) -->
          <div 
            class="absolute top-[160px] -translate-x-1/2 flex flex-col items-center z-30 pointer-events-none"
            style="left: 3.18%"
          >
            <div class="w-11 h-11 rounded-full border-2 border-emerald-300 bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-sm font-black">
              <i class="fa-solid fa-flag text-emerald-600"></i>
            </div>
            <div class="text-center mt-1 whitespace-nowrap font-sans">
              <div class="text-base sm:text-lg font-black tracking-tight text-gray-900">
                Bắt đầu
              </div>
              <div v-if="project.created_at" class="text-xs sm:text-sm font-bold text-slate-500 mt-0.5">
                {{ formatDateShort(project.created_at) }}
              </div>
            </div>
          </div>

          <!-- DYNAMIC MILESTONE PEAKS & SLOPE AVATARS -->
          <template v-for="(item, index) in milestoneLayout.visibleItems" :key="item.ms.id || index">
            
            <!-- COMPACT COMPLETED STAGE NODE (NHÔ CAO GIỐNG NÚI) -->
            <template v-if="item.isDone">
              <!-- Compact Node Flag Icon + Avatars -->
              <div 
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[95px] -translate-x-1/2 flex items-center justify-center gap-1.5 z-20 cursor-pointer group transition-all duration-300 hover:scale-110"
                :style="{ left: item.leftPct }"
              >
                <div 
                  class="w-7.5 h-7.5 rounded-full border-2 border-emerald-600 bg-white text-emerald-600 flex items-center justify-center shadow-xs transition-colors group-hover:bg-emerald-50"
                  :title="`Chặng đã hoàn thành: ${item.ms.title}`"
                >
                  <i class="fa-solid fa-flag text-xs text-emerald-600"></i>
                </div>

                <!-- Task count badge if > 0 -->
                <div v-if="getStageTaskCount(item.ms) > 0" class="w-5.5 h-5.5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black shadow-2xs border border-white">
                  {{ getStageTaskCount(item.ms) }}
                </div>

                <!-- AVATAR NGƯỜI CẬP NHẬT/PHỤ TRÁCH CHẶNG ĐÃ HOÀN THÀNH -->
                <div v-if="getStageActiveUsers(item.ms).length > 0" class="flex items-center -space-x-1.5 ml-0.5">
                  <div 
                    v-for="u in getStageActiveUsers(item.ms)" 
                    :key="u.id"
                    class="w-7 h-7 rounded-full bg-white p-0.5 shadow-sm border-2 border-emerald-500 transition-all hover:scale-115"
                    :title="`Người phụ trách: ${u.name}`"
                  >
                    <img :src="u.avatar || defaultAvatar" class="w-full h-full rounded-full object-cover" />
                  </div>
                </div>
              </div>

              <!-- Compact Stage Label & Date Below -->
              <div 
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[135px] -translate-x-1/2 text-center space-y-0.5 font-sans z-10 cursor-pointer group max-w-[135px]"
                :style="{ left: item.leftPct }"
              >
                <div 
                  class="text-xs sm:text-sm font-black tracking-tight leading-tight uppercase transition-colors truncate max-w-[135px]"
                  :class="selectedMilestone && selectedMilestone.id === item.ms.id ? 'text-emerald-700 underline underline-offset-4 decoration-2' : 'text-gray-900 group-hover:text-emerald-700'"
                  :title="item.ms.title"
                >
                  {{ item.ms.title }}
                </div>

                <div v-if="getStageAssigneeName(item.ms)" class="text-[11px] font-bold text-emerald-700 truncate max-w-[135px]">
                  {{ getStageAssigneeName(item.ms) }}
                </div>

                <div class="text-[10px] font-bold text-slate-500">
                  {{ item.ms.due_date ? formatDateShort(item.ms.due_date) : '' }}
                </div>
              </div>
            </template>

            <!-- ACTIVE / IN-PROGRESS STAGE PEAK NODE (HIGH MOUNTAIN PEAK) -->
            <template v-else>
              <!-- ĐỈNH NÚI: CỜ XANH + SỐ VIỆC -->
              <div 
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[36px] -translate-x-1/2 flex items-end gap-1.5 z-20 cursor-pointer group transition-all duration-300 hover:scale-108"
                :style="{ left: item.leftPct }"
              >
                <div class="flex flex-col items-center -space-y-1">
                  <!-- Status Flag in Green -->
                  <i class="fa-solid fa-flag text-2xl filter drop-shadow-2xs text-emerald-600"></i>
                  
                  <!-- Task Count Circle -->
                  <div class="w-6.5 h-6.5 rounded-full text-white flex items-center justify-center text-xs font-black shadow-xs border-2 border-white transition-colors bg-rose-500">
                    {{ getStageTaskCount(item.ms) }}
                  </div>
                </div>
              </div>

              <!-- AVATAR ĐANG LEO SƯỜN NÚI -->
              <div 
                v-if="getStageActiveUsers(item.ms).length > 0"
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[92px] -translate-x-1/2 flex items-center -space-x-2 z-25 cursor-pointer transition-all duration-300 hover:scale-110"
                :style="{ left: item.slopePct }"
              >
                <div 
                  v-for="u in getStageActiveUsers(item.ms)" 
                  :key="u.id"
                  class="w-8.5 h-8.5 rounded-full bg-white p-0.5 shadow-md border-2 border-emerald-500 transition-all hover:scale-115 hover:z-30 filter drop-shadow-2xs"
                  :title="`Đang thực hiện chặng ${item.ms.title}: ${u.name}`"
                >
                  <img :src="u.avatar || defaultAvatar" class="w-full h-full rounded-full object-cover" />
                </div>
              </div>

              <!-- Tên chặng, Người được giao & Thời gian -->
              <div 
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[112px] -translate-x-1/2 text-center space-y-0.5 font-sans z-10 cursor-pointer group max-w-[210px]"
                :style="{ left: item.leftPct }"
              >
                <div 
                  class="text-base sm:text-lg font-black tracking-tight leading-tight truncate transition-colors"
                  :class="selectedMilestone && selectedMilestone.id === item.ms.id ? 'text-emerald-700 underline underline-offset-4 decoration-2' : 'text-gray-900 group-hover:text-emerald-700'"
                >
                  {{ item.ms.title }}
                </div>

                <div v-if="getStageAssigneeName(item.ms)" class="text-xs sm:text-sm font-black text-emerald-700 truncate mt-0.5">
                  {{ getStageAssigneeName(item.ms) }}
                </div>

                <div class="text-xs sm:text-sm font-bold text-slate-500 mt-0.5">
                  {{ item.ms.due_date ? formatDateShort(item.ms.due_date) : 'Chưa xếp lịch' }}
                </div>
              </div>
            </template>

          </template>

          <!-- Empty state when project has 0 milestones -->
          <div v-if="visibleMilestones.length === 0" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center z-10">
            <div class="text-xs sm:text-sm font-bold text-gray-400">Chưa có chặng nào trong dự án. Bấm "+ Thêm chặng" để tạo chặng đầu tiên.</div>
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
              <div class="text-xs sm:text-sm font-black text-gray-900 group-hover:text-emerald-700 font-sans">
                Thêm chặng
              </div>
            </div>
          </div>

        </div>

        <!-- Carousel Pagination Dots -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-2">
          <span 
            v-for="dot in totalPages" 
            :key="dot"
            class="w-2.5 h-2.5 rounded-full transition-all cursor-pointer"
            :class="dot === activePageDot ? 'w-3 h-3 bg-slate-900' : 'bg-slate-300 hover:bg-slate-400'"
            @click="activePageDot = dot"
          ></span>
        </div>

        <!-- Slide Navigation Arrows -->
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

      <!-- CENTER PROMINENT "CHIM SẺ GỌI ĐẠI BÀNG" BUTTON -->
      <div class="flex justify-center my-6">
        <button 
          @click="toggleInlineForm" 
          type="button"
          class="px-8 py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-black text-base sm:text-xl rounded-full shadow-lg hover:shadow-xl hover:scale-103 active:scale-97 transition-all flex items-center gap-3 cursor-pointer group"
        >
          <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-dove text-lg text-white"></i>
          </div>
          <span>CHIM SẺ GỌI ĐẠI BÀNG</span>
        </button>
      </div>

      <!-- MAIN CARDS / ACTIVITIES CONTAINER ("DẤU CHÂN HOẠT ĐỘNG") -->
      <div class="bg-[#f4f5f0] rounded-3xl p-4 sm:p-6 w-full space-y-5 animate-fade-in-up relative">
        
        <!-- Header Row: Title "DẤU CHÂN HOẠT ĐỘNG" & Double Click Hint -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
          <div class="flex items-center gap-3">
            <!-- Clear Filter / Back Button if Milestone is Selected -->
            <button 
              v-if="selectedMilestone"
              @click="closeSelectedStage" 
              type="button"
              class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 flex items-center justify-center text-sm font-bold shadow-2xs transition-colors cursor-pointer"
              title="Hiện tất cả hoạt động"
            >
              <i class="fa-solid fa-arrow-left text-sm"></i>
            </button>
            
            <h3 class="text-xl sm:text-2xl font-black text-gray-900 uppercase tracking-tight font-heading">
              {{ selectedMilestone ? selectedMilestone.title : 'DẤU CHÂN HOẠT ĐỘNG' }}
            </h3>
            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center shadow-2xs">
              {{ displayedCards.length }}
            </span>

            <!-- BUTTON ĐÁNH DẤU CỘT MỐC ĐÓ HOÀN THÀNH -->
            <button 
              v-if="selectedMilestone"
              @click="toggleMilestoneCompleted(selectedMilestone)"
              type="button"
              class="px-3.5 py-1.5 rounded-xl font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer ml-2"
              :class="selectedMilestone.is_completed ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
            >
              <i class="fa-solid fa-circle-check text-xs"></i>
              <span>{{ selectedMilestone.is_completed ? 'Đã hoàn thành chặng' : 'Đánh dấu hoàn thành chặng' }}</span>
            </button>

            <!-- BUTTON XÓA CHẶNG ĐANG CHỌN -->
            <button 
              v-if="selectedMilestone"
              @click="handleDeleteMilestone(selectedMilestone.id)"
              type="button"
              class="px-3.5 py-1.5 rounded-xl font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-600 hover:text-white"
              title="Xóa chặng này"
            >
              <i class="fa-solid fa-trash-can text-xs"></i>
              <span>Xóa chặng</span>
            </button>
          </div>

          <!-- DOUBLE CLICK HINT BADGE -->
          <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50/90 border border-emerald-200/60 text-emerald-700 text-xs font-bold rounded-full shadow-2xs">
            <i class="fa-solid fa-hand-pointer text-emerald-600 text-xs animate-bounce"></i>
            <span>Nhấn đúp (Double-click) vào thẻ để chỉnh sửa</span>
          </div>
        </div>



        <!-- ACTIVITIES LIST: CLEAN WHITE CARDS WITH LARGE, BOLD FONT SIZES -->
        <div class="space-y-3.5 relative z-10">
          <div 
            v-for="t in displayedCards" 
            :key="t.id"
            @dblclick="openEditStageTaskForm(t)"
            class="bg-white border border-gray-100 hover:border-gray-200 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all flex items-center justify-between gap-4 group cursor-pointer"
            title="Nhấn đúp (Double-click) để chỉnh sửa hoạt động này"
          >
            <!-- LEFT GROUP: ICON + AVATAR + USER & TITLE -->
            <div class="flex items-center gap-4 min-w-0 flex-1">
              <!-- ICON BADGE (GREEN FOOTPRINTS IF UNASSIGNED, YELLOW BELL IF ASSIGNED) -->
              <div 
                class="w-11 h-11 rounded-full flex items-center justify-center text-lg shadow-2xs flex-shrink-0 border"
                :class="isNewUnassignedTask(t) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-500 border-amber-100'"
                :title="isNewUnassignedTask(t) ? 'Hoạt động chưa phân công (Dấu chân xanh)' : 'Hoạt động đã có người phụ trách (Chuông vàng)'"
              >
                <i :class="isNewUnassignedTask(t) ? 'fa-solid fa-shoe-prints text-emerald-600' : 'fa-solid fa-bell text-amber-500'"></i>
              </div>

              <!-- CREATOR AVATAR -->
              <img 
                v-if="getCreatorAvatar(t)"
                :src="getCreatorAvatar(t)" 
                class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm flex-shrink-0" 
                :title="`Người tạo: ${getCreatorDisplayName(t)}`"
              />
              <div v-else class="w-10 h-10 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-gray-500 shadow-sm flex-shrink-0">
                <i class="fa-solid fa-user text-sm"></i>
              </div>

              <!-- CONTENT BLOCK: USER NAME & LARGE BOLD TITLE -->
              <div class="min-w-0 flex-1">
                <!-- User Name -->
                <div class="text-xs sm:text-sm font-bold text-gray-500 tracking-tight">
                  {{ getCreatorDisplayName(t) }}
                </div>
                <!-- Large Activity Title -->
                <div class="text-base sm:text-xl font-black text-gray-900 leading-snug tracking-tight mt-0.5" v-html="formatTitleWithMentions(t.title)"></div>
                <!-- Assignee Dropdown Selector -->
                <div class="text-xs font-bold text-gray-500 mt-1.5 flex items-center gap-1.5 relative">
                  <span class="opacity-80">Giao cho:</span>
                  <div class="relative">
                    <button
                      type="button"
                      @click.stop="toggleAssigneeDropdown(t.id)"
                      class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs border border-gray-200 rounded-full px-3 py-1 transition-all shadow-2xs cursor-pointer group/btn"
                      title="Đổi người được giao"
                    >
                      <img 
                        v-if="t.assignee_id && getAssigneeAvatar(t)"
                        :src="getAssigneeAvatar(t)" 
                        class="w-4 h-4 rounded-full object-cover border border-white flex-shrink-0" 
                      />
                      <i v-else class="fa-solid fa-user-circle text-xs text-gray-500 flex-shrink-0"></i>
                      
                      <span class="truncate max-w-[120px]">
                        {{ t.assignee_id ? getAssigneeDisplayName(t) : 'Chưa phân công' }}
                      </span>

                      <i class="fa-solid fa-chevron-down text-[9px] text-gray-500 group-hover/btn:text-gray-800 transition-transform duration-200" :class="{ 'rotate-180': activeAssigneeDropdownTaskId === t.id }"></i>
                    </button>

                    <!-- CUSTOM DROPDOWN POPOVER MENU -->
                    <div 
                      v-if="activeAssigneeDropdownTaskId === t.id"
                      @click.stop
                      class="absolute left-0 top-full mt-1.5 z-50 w-56 bg-white border border-gray-200 rounded-xl shadow-2xl py-1.5 text-gray-800 animate-in fade-in zoom-in-95 duration-150 max-h-60 overflow-y-auto custom-scrollbar ring-1 ring-black/5"
                    >
                      <div class="px-3 py-1 text-[10px] uppercase tracking-wider font-extrabold text-emerald-600 border-b border-gray-100 mb-1 flex items-center justify-between">
                        <span>Chọn người phụ trách</span>
                        <i class="fa-solid fa-user-gear text-xs text-emerald-600"></i>
                      </div>

                      <!-- OPTION: CHƯA PHÂN CÔNG -->
                      <button
                        type="button"
                        @click.stop="selectAssigneeForTask(t, null)"
                        class="w-full px-3 py-1.5 flex items-center gap-2.5 text-xs font-medium hover:bg-gray-100 transition-colors rounded-lg text-left"
                        :class="{ 'bg-emerald-50 text-emerald-700 font-bold': !t.assignee_id }"
                      >
                        <div class="w-5.5 h-5.5 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                          <i class="fa-solid fa-user-slash text-[10px]"></i>
                        </div>
                        <span class="flex-1 truncate">-- Chưa phân công --</span>
                        <i v-if="!t.assignee_id" class="fa-solid fa-check text-emerald-600 text-xs"></i>
                      </button>

                      <!-- USERS OPTIONS -->
                      <button
                        v-for="u in users"
                        :key="u.id"
                        type="button"
                        @click.stop="selectAssigneeForTask(t, u.id)"
                        class="w-full px-3 py-1.5 flex items-center gap-2.5 text-xs font-medium hover:bg-gray-100 transition-colors rounded-lg text-left"
                        :class="{ 'bg-emerald-50 text-emerald-700 font-bold': t.assignee_id == u.id }"
                      >
                        <img 
                          :src="u.avatar || defaultAvatar" 
                          class="w-5.5 h-5.5 rounded-full object-cover border border-gray-200 flex-shrink-0" 
                        />
                        <span class="flex-1 truncate">{{ u.name }}</span>
                        <i v-if="t.assignee_id == u.id" class="fa-solid fa-check text-emerald-600 text-xs"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RIGHT GROUP: DATE & TIME STACK + DELETE BUTTON -->
            <div class="flex items-center gap-3.5 flex-shrink-0">
              <div class="text-right whitespace-nowrap">
                <div class="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                  {{ formatDateShort(t.due_date || t.created_at) }}
                </div>
                <div class="text-xs sm:text-sm font-extrabold text-slate-500 mt-0.5">
                  {{ formatTimeOnly(t.due_date || t.created_at) || '10:30' }}
                </div>
              </div>

              <!-- Edit & Delete actions on hover -->
              <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                <button 
                  @click.stop="openEditStageTaskForm(t)" 
                  type="button"
                  class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center text-xs transition-colors cursor-pointer"
                  title="Nhấn đúp hoặc bấm vào đây để chỉnh sửa hoạt động"
                >
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button 
                  @click.stop="handleDeleteTask(t.id)" 
                  type="button"
                  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-rose-600 hover:text-white text-gray-400 flex items-center justify-center text-xs transition-colors cursor-pointer"
                  title="Xóa hoạt động"
                >
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty stage tasks state: Click or Double-click to open create modal -->
          <div 
            v-if="displayedCards.length === 0" 
            @click="openAddStageTaskForm"
            @dblclick="openAddStageTaskForm"
            class="py-12 text-center text-gray-400 text-sm font-semibold bg-white rounded-2xl border border-dashed border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all cursor-pointer group"
            title="Bấm hoặc Nhấn đúp (Double-click) vào đây để tạo cập nhật mới"
          >
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mx-auto mb-2 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div class="font-extrabold text-gray-700 group-hover:text-emerald-700">Chưa có cập nhật hoạt động nào.</div>
            <div class="text-xs text-gray-400 mt-1">Bấm hoặc Nhấn đúp (Double-click) vào đây để mở bảng tạo cập nhật mới.</div>
          </div>
        </div>
      </div>

    </main>

    <!-- Modal for Project Edit -->
    <ProjectModal :is-open="isModalOpen" :customers="customers" :users="users" :edit-project="project"
      @close="handleCloseModal" @submit="handleUpdateProjectSubmit" @customer-created="fetchCustomers" />

    <!-- FIXED BOTTOM UPDATE CREATION CARD (COLLAPSED BY DEFAULT, FIXED AT BOTTOM OF SCREEN WHEN OPEN) -->
    <transition 
      enter-active-class="transition duration-300 ease-out transform" 
      enter-from-class="opacity-0 translate-y-full" 
      enter-to-class="opacity-100 translate-y-0" 
      leave-active-class="transition duration-200 ease-in transform" 
      leave-from-class="opacity-100 translate-y-0" 
      leave-to-class="opacity-0 translate-y-full"
    >
      <div 
        v-if="isInlineFormOpen"
        ref="inlineFormRef"
        class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none pb-3 sm:pb-5 px-4 sm:px-6 lg:px-8 transition-all"
      >
        <div class="max-w-[1380px] mx-auto pointer-events-auto bg-white border border-gray-200 shadow-2xl rounded-2xl p-4 sm:p-5 relative ring-1 ring-black/5">
          <form @submit.prevent="handleAddStageTaskSubmit" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4 lg:gap-6">
            
            <!-- LEFT SECTION: MỤC TIÊU HƯỚNG ĐẾN (CHẶNG / MILESTONE SELECTOR) -->
            <div class="flex flex-col gap-2 lg:pr-5 lg:border-r lg:border-gray-200 flex-shrink-0">
              <div class="flex items-center gap-1 text-[11px] font-extrabold text-gray-500 tracking-wider uppercase font-sans">
                <span>MỤC TIÊU HƯỚNG ĐẾN</span>
                <i class="fa-regular fa-circle-question text-gray-400 text-xs" title="Chọn chặng mục tiêu hướng đến cho cập nhật"></i>
              </div>

              <!-- IF <= 3 STAGES: DISPLAY STAGE BUTTONS SIDE BY SIDE LIKE IN THE IMAGE -->
              <div v-if="effectiveMilestones.length <= 3 && effectiveMilestones.length > 0" class="flex items-center gap-2">
                <div
                  v-for="ms in effectiveMilestones"
                  :key="ms.id"
                  @click="newStageTaskMilestoneId = ms.id"
                  class="flex flex-col items-center justify-between p-2 rounded-xl border transition-all cursor-pointer select-none min-w-[72px] sm:min-w-[84px] h-[78px]"
                  :class="newStageTaskMilestoneId === ms.id 
                    ? 'bg-rose-50/70 border-rose-200 text-rose-600 shadow-2xs' 
                    : 'bg-white border-gray-200 hover:border-gray-300 text-gray-600 hover:bg-gray-50'"
                >
                  <!-- Circle with task count -->
                  <div 
                    class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors"
                    :class="newStageTaskMilestoneId === ms.id 
                      ? 'border-2 border-rose-500 bg-white text-rose-600' 
                      : 'border border-gray-300 bg-white text-gray-700'"
                  >
                    {{ getStageTaskCount(ms) }}
                  </div>

                  <!-- Stage Name -->
                  <span 
                    class="text-[10px] font-black tracking-tight text-center uppercase leading-tight line-clamp-2 mt-0.5"
                    :class="newStageTaskMilestoneId === ms.id ? 'text-gray-900 font-extrabold' : 'text-gray-600'"
                  >
                    {{ ms.title }}
                  </span>
                </div>
              </div>

              <!-- IF > 3 STAGES: CONVERT TO DROPDOWN SELECTOR AS REQUESTED -->
              <div v-else-if="effectiveMilestones.length > 3" class="w-full sm:w-56">
                <select 
                  v-model="newStageTaskMilestoneId" 
                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors cursor-pointer"
                >
                  <option v-for="ms in effectiveMilestones" :key="ms.id" :value="ms.id">
                    🚩 {{ ms.title }} ({{ getStageTaskCount(ms) }})
                  </option>
                </select>
              </div>

              <!-- IF 0 STAGES -->
              <div v-else class="text-xs text-gray-400 font-medium italic">
                Chưa có chặng mục tiêu
              </div>
            </div>

            <!-- RIGHT SECTION: USER AVATAR + TEXTAREA INPUT + TAGS & SUBMIT BUTTON -->
            <div class="flex-1 flex flex-col justify-between gap-2.5 min-w-0 relative">
              
              <!-- TOP ROW: USER AVATAR + TEXTAREA INPUT -->
              <div class="flex items-start gap-3 min-w-0">
                <!-- User avatar -->
                <img 
                  :src="authStore.user?.avatar || defaultAvatar" 
                  class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 shadow-2xs flex-shrink-0 mt-0.5" 
                  :title="authStore.user?.name || 'Tôi'"
                />

                <!-- Input Area Container with Mention Dropdown -->
                <div class="flex-1 min-w-0 relative">
                  <!-- HIGHLIGHT OVERLAY BEHIND TEXTAREA (ONLY WHEN MENTIONS EXIST) -->
                  <div 
                    v-if="hasMentionsInTitle"
                    class="w-full bg-transparent text-sm sm:text-base font-bold text-gray-900 leading-relaxed py-1 whitespace-pre-wrap break-words pointer-events-none absolute inset-0 select-none overflow-hidden m-0 border-0"
                    v-html="formattedInputTitle + (newStageTaskTitle.endsWith('\n') ? '<br/>' : '')"
                  ></div>

                  <textarea 
                    ref="stageTaskTitleInputRef"
                    v-model="newStageTaskTitle" 
                    rows="2"
                    required 
                    @input="onTitleInput"
                    @keydown="onTitleKeydown"
                    placeholder="9g 15/8 @Khánh đi khảo sát Outspan" 
                    class="w-full bg-transparent text-sm sm:text-base font-bold leading-relaxed py-1 focus:outline-none placeholder-gray-400 resize-none relative z-10 m-0 border-0"
                    :class="hasMentionsInTitle ? 'text-transparent caret-gray-900' : 'text-gray-900'"
                  ></textarea>

                  <!-- AUTOCOMPLETE @MENTION DROPDOWN POPOVER (POPS UP ABOVE INPUT) -->
                  <div 
                    v-if="showMentionDropdown && filteredUsersForMention.length > 0"
                    class="absolute left-0 bottom-full mb-2 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-xl py-1 text-gray-800 max-h-52 overflow-y-auto ring-1 ring-black/5"
                  >
                    <div class="px-3 py-1 text-[10px] uppercase font-bold text-emerald-600 border-b border-gray-100 mb-1 flex items-center justify-between">
                      <span>Chọn người phụ trách (@)</span>
                      <i class="fa-solid fa-at text-xs"></i>
                    </div>
                    <button
                      v-for="(u, idx) in filteredUsersForMention"
                      :key="u.id"
                      type="button"
                      @mousedown.prevent
                      @click="selectMentionUser(u)"
                      class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left"
                      :class="{ 'bg-emerald-50 text-emerald-800 font-bold': idx === mentionIndex }"
                    >
                      <img :src="u.avatar || defaultAvatar" class="w-5 h-5 rounded-full object-cover border border-gray-200" />
                      <span class="truncate flex-1">{{ u.name }}</span>
                    </button>
                  </div>
                </div>
              </div>

                <!-- BOTTOM ROW: ACTION TAGS & SUBMIT BUTTON (RIGHT ALIGNED) -->
              <div class="flex items-center justify-between gap-2 flex-wrap pt-1">
                
                <!-- LEFT: Quick picker buttons for person & date/time -->
                <div class="flex items-center gap-2">
                  <!-- Person picker toggle button -->
                  <div class="relative" ref="personPickerRef">
                    <button
                      type="button"
                      @click="showPersonPicker = !showPersonPicker"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs"
                      :class="newStageTaskAssignee ? 'bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 text-emerald-700' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600'"
                    >
                      <i class="fa-regular fa-user text-xs"></i>
                      <span v-if="newStageTaskAssignee">@{{ selectedAssigneeName }}</span>
                      <span v-else>Chọn người</span>
                    </button>
                    <!-- Person picker dropdown -->
                    <div 
                      v-if="showPersonPicker" 
                      class="absolute left-0 bottom-full mb-2 z-50 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-1 max-h-52 overflow-y-auto ring-1 ring-black/5"
                    >
                      <div class="px-3 py-1 text-[10px] uppercase font-bold text-emerald-600 border-b border-gray-100 mb-1">Chọn người phụ trách</div>
                      <button
                        v-if="newStageTaskAssignee"
                        type="button"
                        @click="clearAssignee(); showPersonPicker = false"
                        class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-rose-50 text-rose-500 transition-colors text-left"
                      >
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Bỏ chọn</span>
                      </button>
                      <button
                        v-for="u in users"
                        :key="u.id"
                        type="button"
                        @click="newStageTaskAssignee = String(u.id); showPersonPicker = false"
                        class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left"
                        :class="{ 'bg-emerald-50 text-emerald-800 font-bold': String(u.id) === String(newStageTaskAssignee) }"
                      >
                        <img :src="u.avatar || defaultAvatar" class="w-5 h-5 rounded-full object-cover border border-gray-200" />
                        <span class="truncate flex-1">{{ u.name }}</span>
                      </button>
                    </div>
                  </div>

                  <!-- Date picker button -->
                  <div class="relative inline-flex items-center gap-1">
                    <button
                      type="button"
                      @click="showDateTimePicker = !showDateTimePicker"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs"
                      :class="(newStageTaskDueDate || newStageTaskDueTime) ? 'bg-blue-50 hover:bg-blue-100/80 border border-blue-200 text-blue-700' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600'"
                    >
                      <i class="fa-regular fa-calendar-days text-xs"></i>
                      <span v-if="newStageTaskDueDate || newStageTaskDueTime">{{ tagFormattedDateTime }}</span>
                      <span v-else>Ngày / giờ</span>
                    </button>
                    <button 
                      v-if="newStageTaskDueDate || newStageTaskDueTime"
                      type="button" 
                      @click="clearDateTime" 
                      class="text-gray-400 hover:text-rose-500 transition-colors cursor-pointer p-0.5 rounded-full"
                      title="Bỏ chọn ngày giờ"
                    >
                      <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                    <!-- Date/time picker popup -->
                    <div 
                      v-if="showDateTimePicker" 
                      class="absolute left-0 bottom-full mb-2 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-xl p-3 ring-1 ring-black/5"
                    >
                      <div class="text-[10px] uppercase font-bold text-gray-500 mb-2">Chọn ngày & giờ</div>
                      <div class="space-y-2">
                        <div>
                          <label class="text-[10px] font-bold text-gray-500 mb-0.5 block">Giờ</label>
                          <input 
                            type="time" 
                            v-model="newStageTaskDueTime" 
                            class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500"
                          />
                        </div>
                        <div>
                          <label class="text-[10px] font-bold text-gray-500 mb-0.5 block">Ngày</label>
                          <input 
                            type="date" 
                            v-model="newStageTaskDueDate" 
                            class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500"
                          />
                        </div>
                      </div>
                      <button 
                        type="button" 
                        @click="showDateTimePicker = false" 
                        class="mt-2 w-full px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer"
                      >Xong</button>
                    </div>
                  </div>
                </div>

                <!-- RIGHT: Submit buttons -->
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="cancelEditTask"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                  >
                    {{ editingTaskId ? 'Hủy' : 'Đóng' }}
                  </button>
                  
                  <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#2e7d32] hover:bg-[#1b5e20] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-sm transition-all cursor-pointer"
                  >
                    <i class="fa-brands fa-twitter text-sm"></i>
                    <span>{{ editingTaskId ? 'Lưu' : 'Hú Hú' }}</span>
                    <i class="fa-solid fa-chevron-down text-[10px] opacity-80"></i>
                  </button>
                </div>

              </div>

            </div>

          </form>
        </div>
      </div>
    </transition>
          


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

watch(isAddMilestoneOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      setTimeout(() => {
        milestoneTitleInputRef.value?.focus()
      }, 50)
    })
  }
})

// Selected Milestone for Card View
const selectedMilestone = ref(null)
const selectedTargetMilestoneId = ref(null)

const isInlineFormOpen = ref(false)
const inlineFormRef = ref(null)
const editingTaskId = ref(null)
const newStageTaskTitle = ref('')
const newStageTaskAssignee = ref('')
const newStageTaskDueDate = ref('')
const newStageTaskDueTime = ref('')
const newStageTaskMilestoneId = ref(null)
const showPersonPicker = ref(false)
const showDateTimePicker = ref(false)
const personPickerRef = ref(null)

const toggleInlineForm = () => {
  isInlineFormOpen.value = !isInlineFormOpen.value
  if (isInlineFormOpen.value) {
    editingTaskId.value = null
    newStageTaskTitle.value = ''
    newStageTaskDueDate.value = ''
    newStageTaskDueTime.value = ''
    newStageTaskAssignee.value = ''
    showPersonPicker.value = false
    showDateTimePicker.value = false
    if (!newStageTaskMilestoneId.value) {
      newStageTaskMilestoneId.value = selectedMilestone.value ? selectedMilestone.value.id : (effectiveMilestones.value[0]?.id || null)
    }
    nextTick(() => {
      stageTaskTitleInputRef.value?.focus()
    })
  }
}

const removeVietnameseAccents = (str) => {
  if (!str) return ''
  return str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
}

const formatTitleWithMentions = (titleText) => {
  if (!titleText) return ''
  let escaped = String(titleText)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')

  const userList = (users && users.value) ? users.value : []
  if (userList && userList.length > 0) {
    const sortedUsers = [...userList].sort((a, b) => (b.name ? b.name.length : 0) - (a.name ? a.name.length : 0))
    sortedUsers.forEach(u => {
      if (u && u.name) {
        const escapedName = u.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
        const regex = new RegExp(`@${escapedName}`, 'gi')
        escaped = escaped.replace(regex, `<span class="text-emerald-600 font-extrabold">@${u.name}</span>`)
      }
    })
  }

  // Fallback for single-word @mentions not already matched inside HTML tags
  escaped = escaped.replace(/@([^\s@,.:;!?()\n]+)(?![^<]*>|[^<>]*<\/span>)/g, '<span class="text-emerald-600 font-extrabold">@$1</span>')

  return escaped
}

const selectedAssigneeName = computed(() => {
  if (!newStageTaskAssignee.value) return ''
  const u = users.value.find(user => String(user.id) === String(newStageTaskAssignee.value))
  return u ? u.name : ''
})

const tagFormattedDateTime = computed(() => {
  let timeStr = newStageTaskDueTime.value || ''
  let dateStr = ''
  if (newStageTaskDueDate.value) {
    const parts = newStageTaskDueDate.value.split('-')
    if (parts.length === 3) {
      dateStr = `${parts[2]}/${parts[1]}`
    }
  }
  if (timeStr && dateStr) {
    return `${timeStr} ${dateStr}`
  }
  return timeStr || dateStr || ''
})

// @Mention State & Auto-Assign Logic
const showMentionDropdown = ref(false)
const mentionQuery = ref('')
const mentionIndex = ref(0)

const formattedInputTitle = computed(() => {
  if (!newStageTaskTitle.value) return ''
  let text = newStageTaskTitle.value
  
  // Escape HTML special characters
  text = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

  // Sort users by name length descending so multi-word names take priority
  const sortedUsers = [...users.value].sort((a, b) => (b.name || '').length - (a.name || '').length)
  
  // Replace matching @User Full Name with green text span matching exact textarea font-bold
  sortedUsers.forEach(u => {
    if (u && u.name) {
      const escapedName = u.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      const reg = new RegExp(`@${escapedName}`, 'gi')
      text = text.replace(reg, `<span class="text-emerald-600 font-bold">@${u.name}</span>`)
    }
  })

  // Match any generic @mention if user not in list yet
  text = text.replace(/(@[^\s<]+)(?![^<]*>)/g, '<span class="text-emerald-600 font-bold">$1</span>')

  return text
})

const hasMentionsInTitle = computed(() => {
  return /@[^\s]+/.test(newStageTaskTitle.value)
})

const filteredUsersForMention = computed(() => {
  if (!mentionQuery.value) return users.value
  const q = removeVietnameseAccents(mentionQuery.value)
  return users.value.filter(u => {
    const nameAcc = removeVietnameseAccents(u.name)
    return nameAcc.includes(q) || u.name.toLowerCase().includes(mentionQuery.value.toLowerCase())
  })
})

const autoDetectAssigneeFromText = (text) => {
  if (!text) return
  const matches = [...text.matchAll(/@([^\s@,.:;!?()\n]+)/g)]
  if (matches && matches.length > 0) {
    const lastTerm = matches[matches.length - 1][1]
    if (lastTerm) {
      const qAcc = removeVietnameseAccents(lastTerm)
      const matched = users.value.find(u => {
        const nameAcc = removeVietnameseAccents(u.name)
        const parts = nameAcc.split(' ')
        return parts.some(p => p === qAcc) || nameAcc === qAcc
      })
      if (matched) {
        newStageTaskAssignee.value = String(matched.id)
      }
    }
  }
}

const onTitleInput = (e) => {
  const text = newStageTaskTitle.value || ''
  
  // 1. Parse Vietnamese date & time
  const parsedDate = parseVietnameseDateFromText(text)
  if (parsedDate) {
    newStageTaskDueDate.value = parsedDate
  }
  const parsedTime = parseTimeFromText(text)
  if (parsedTime) {
    newStageTaskDueTime.value = parsedTime
  }

  // 2. Mention dropdown trigger
  const cursorPos = e.target.selectionStart || text.length
  const textBeforeCursor = text.substring(0, cursorPos)
  const match = textBeforeCursor.match(/@([^\s@]*)$/)

  if (match) {
    mentionQuery.value = match[1]
    showMentionDropdown.value = true
    mentionIndex.value = 0
  } else {
    showMentionDropdown.value = false
  }

  // 3. Auto-detect assignee if exact user name typed (e.g. @hiếu)
  autoDetectAssigneeFromText(text)
}

const onTitleKeydown = (e) => {
  if (showMentionDropdown.value && filteredUsersForMention.value.length > 0) {
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      mentionIndex.value = (mentionIndex.value + 1) % filteredUsersForMention.value.length
      return
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      mentionIndex.value = (mentionIndex.value - 1 + filteredUsersForMention.value.length) % filteredUsersForMention.value.length
      return
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      e.preventDefault()
      const selectedUser = filteredUsersForMention.value[mentionIndex.value]
      if (selectedUser) {
        selectMentionUser(selectedUser)
      }
      return
    } else if (e.key === 'Escape') {
      showMentionDropdown.value = false
      return
    }
  }

  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    handleAddStageTaskSubmit()
  }
}

const selectMentionUser = (user) => {
  if (!user) return
  newStageTaskAssignee.value = String(user.id)
  
  const text = newStageTaskTitle.value
  const cursorPos = stageTaskTitleInputRef.value?.selectionStart || text.length
  const textBeforeCursor = text.substring(0, cursorPos)
  const textAfterCursor = text.substring(cursorPos)

  let newCursorPos = cursorPos
  const match = textBeforeCursor.match(/@([^\s@]*)$/)
  if (match) {
    const startIndex = match.index
    const newBefore = textBeforeCursor.substring(0, startIndex) + `@${user.name} `
    newStageTaskTitle.value = newBefore + textAfterCursor
    newCursorPos = newBefore.length
  } else {
    newStageTaskTitle.value = `${text} @${user.name} `
    newCursorPos = newStageTaskTitle.value.length
  }
  showMentionDropdown.value = false

  nextTick(() => {
    if (stageTaskTitleInputRef.value) {
      stageTaskTitleInputRef.value.focus()
      stageTaskTitleInputRef.value.setSelectionRange(newCursorPos, newCursorPos)
    }
  })
}

const clearAssignee = () => {
  newStageTaskAssignee.value = ''
}

const clearDateTime = () => {
  newStageTaskDueDate.value = ''
  newStageTaskDueTime.value = ''
}

const cancelEditTask = () => {
  isInlineFormOpen.value = false
  editingTaskId.value = null
  newStageTaskTitle.value = ''
  newStageTaskAssignee.value = ''
  newStageTaskDueDate.value = ''
  newStageTaskDueTime.value = ''
  showPersonPicker.value = false
  showDateTimePicker.value = false
}

const openAddStageTaskForm = () => {
  editingTaskId.value = null
  newStageTaskTitle.value = ''
  newStageTaskDueDate.value = ''
  newStageTaskDueTime.value = ''
  newStageTaskAssignee.value = ''
  if (!newStageTaskMilestoneId.value) {
    newStageTaskMilestoneId.value = selectedMilestone.value ? selectedMilestone.value.id : (effectiveMilestones.value[0]?.id || null)
  }
  isInlineFormOpen.value = true
  nextTick(() => {
    stageTaskTitleInputRef.value?.focus()
  })
}

const openEditStageTaskForm = (task) => {
  if (!task) return
  editingTaskId.value = task.id
  newStageTaskTitle.value = task.title || ''
  newStageTaskAssignee.value = task.assignee_id ? String(task.assignee_id) : ''
  newStageTaskMilestoneId.value = task.milestone_id || (selectedMilestone.value ? selectedMilestone.value.id : (effectiveMilestones.value[0]?.id || null))
  
  if (task.due_date) {
    if (typeof task.due_date === 'string' && task.due_date.includes(' ')) {
      const parts = task.due_date.split(' ')
      newStageTaskDueDate.value = parts[0]
      if (parts[1]) {
        const timeParts = parts[1].split(':')
        newStageTaskDueTime.value = `${timeParts[0]}:${timeParts[1]}`
      }
    } else {
      const d = new Date(task.due_date)
      if (!isNaN(d.getTime())) {
        const yyyy = d.getFullYear()
        const mm = String(d.getMonth() + 1).padStart(2, '0')
        const dd = String(d.getDate()).padStart(2, '0')
        newStageTaskDueDate.value = `${yyyy}-${mm}-${dd}`
        const hh = String(d.getHours()).padStart(2, '0')
        const min = String(d.getMinutes()).padStart(2, '0')
        newStageTaskDueTime.value = `${hh}:${min}`
      }
    }
  } else {
    newStageTaskDueDate.value = ''
    newStageTaskDueTime.value = ''
  }

  isInlineFormOpen.value = true
  nextTick(() => {
    inlineFormRef.value?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    stageTaskTitleInputRef.value?.focus()
  })
}

const activePreviewImage = ref(null)

// ALL PROJECT CARDS SORTED NEWEST FIRST
const allProjectCards = computed(() => {
  if (!project.value || !project.value.tasks) return []
  return [...project.value.tasks].sort((a, b) => {
    const timeA = a.created_at ? new Date(a.created_at).getTime() : (typeof a.id === 'number' ? a.id : 0)
    const timeB = b.created_at ? new Date(b.created_at).getTime() : (typeof b.id === 'number' ? b.id : 0)
    if (timeA !== timeB) return timeB - timeA
    return (b.id || 0) - (a.id || 0)
  })
})

// DISPLAYED CARDS: ALL CARDS BY DEFAULT, OR FILTERED BY MILESTONE WHEN A MILESTONE IS CLICKED!
const displayedCards = computed(() => {
  if (selectedMilestone.value) {
    return currentStageTasks.value
  }
  return allProjectCards.value
})

// TASK CARD COLOR DEFINITIONS MATCHING IMAGE 2 EXACTLY
const getTaskCardBgColor = (task) => {
  if (!task) return 'bg-emerald-700 hover:bg-emerald-800'
  // Yellow/Orange card if task has an Assignee + Due Date
  if ((task.assignee_id || task.assignee?.id) && task.due_date) {
    return 'bg-amber-500 hover:bg-amber-600'
  }
  // Purple card if task is a comment / quick note
  if (task.type === 'comment' || task.is_comment) {
    return 'bg-purple-600 hover:bg-purple-700'
  }
  // Green card default for footprint / task
  return 'bg-emerald-700 hover:bg-emerald-800'
}

const getTaskCardIcon = (task) => {
  if (!task) return 'fa-solid fa-shoe-prints'
  if ((task.assignee_id || task.assignee?.id) && task.due_date) {
    return 'fa-solid fa-bell'
  }
  if (task.type === 'comment' || task.is_comment) {
    return 'fa-solid fa-comment-dots'
  }
  return 'fa-solid fa-shoe-prints'
}

const getAssigneeAvatar = (task) => {
  if (!task || !task.assignee_id) return null
  if (task.assignee?.avatar) return task.assignee.avatar
  if (users.value) {
    const u = users.value.find(user => user.id == task.assignee_id || String(user.id) === String(task.assignee_id))
    if (u?.avatar) return u.avatar
  }
  return defaultAvatar
}

const getAssigneeDisplayName = (task) => {
  if (!task || !task.assignee_id) return ''
  if (task.assignee?.name) return task.assignee.name
  if (users.value) {
    const u = users.value.find(user => user.id == task.assignee_id || String(user.id) === String(task.assignee_id))
    if (u) return u.name
  }
  return ''
}

const getCreatorAvatar = (task) => {
  if (!task) return defaultAvatar
  if (task.creator?.avatar) return task.creator.avatar
  const creatorId = task.created_by || task.creator_id || task.creator?.id
  if (creatorId && users.value) {
    const u = users.value.find(user => user.id == creatorId || String(user.id) === String(creatorId))
    if (u?.avatar) return u.avatar
  }
  return defaultAvatar
}

const getCreatorDisplayName = (task) => {
  if (!task) return 'Người tạo'
  if (task.creator?.name) return task.creator.name
  const creatorId = task.created_by || task.creator_id || task.creator?.id
  if (creatorId && users.value) {
    const u = users.value.find(user => user.id == creatorId || String(user.id) === String(creatorId))
    if (u) return u.name
  }
  return 'Người tạo'
}

// REAL MILESTONES DATA DYNAMIC COMPUTATION FROM BACKEND
const effectiveMilestones = computed(() => {
  if (project.value && project.value.milestones && Array.isArray(project.value.milestones) && project.value.milestones.length > 0) {
    return project.value.milestones
  }
  return []
})

const milestoneLayout = computed(() => {
  const all = effectiveMilestones.value
  if (!all || all.length === 0) {
    return { visibleItems: [], pageCount: 1, hills: [], ridgeline: 'M 35 180 Q 540 -25 1040 180', btnPos: '94.55%' }
  }

  const startX = 100
  const maxContainerW = 940 // Available width before "+ Thêm chặng"
  const fixedDoneW = 155 // Spacing for completed stage to prevent text overlap

  // 1. Dynamic Paginate: Group milestones into pages where completed take 155px and active take 240px
  const pages = []
  let currentPage = []
  let currentW = 0

  all.forEach((ms) => {
    const isDone = isStageCompleted(ms)
    const msW = isDone ? fixedDoneW : 240
    if (currentW + msW > maxContainerW && currentPage.length > 0) {
      pages.push(currentPage)
      currentPage = [ms]
      currentW = msW
    } else {
      currentPage.push(ms)
      currentW += msW
    }
  })
  if (currentPage.length > 0) pages.push(currentPage)

  const pageIdx = Math.min(Math.max(0, activePageDot.value - 1), pages.length - 1)
  const currentVisible = pages[pageIdx] || []

  // 2. Position items in currentVisible:
  // Done items take fixed 155px (compacted on left), active items take remaining space evenly!
  const doneCount = currentVisible.filter(ms => isStageCompleted(ms)).length
  const activeCount = currentVisible.length - doneCount

  const reservedDoneW = doneCount * fixedDoneW
  const remainingW = maxContainerW - reservedDoneW
  const activeItemW = activeCount > 0 ? (remainingW / activeCount) : fixedDoneW

  let curX = startX
  const items = currentVisible.map((ms) => {
    const isDone = isStageCompleted(ms)
    const spanW = isDone ? fixedDoneW : activeItemW
    const peakX = curX + spanW / 2
    const leftPct = `${((peakX / 1100) * 100).toFixed(2)}%`
    const slopeX = curX + spanW * 0.25
    const slopePct = `${((slopeX / 1100) * 100).toFixed(2)}%`
    
    const itemData = {
      ms,
      isDone,
      spanW,
      startX: curX,
      endX: curX + spanW,
      peakX,
      leftPct,
      slopePct
    }
    curX += spanW
    return itemData
  })

  // 3. SVG hill paths and ridgeline
  let dPath = `M 35 180 L ${startX} 180`
  const hillPaths = []
  
  items.forEach((item) => {
    const midX = item.startX + item.spanW / 2
    if (item.isDone) {
      dPath += ` Q ${midX} 85 ${item.endX} 180`
      hillPaths.push({ d: `M ${item.startX} 290 L ${item.startX} 180 Q ${midX} 85 ${item.endX} 180 L ${item.endX} 290 Z` })
    } else {
      dPath += ` Q ${midX} -25 ${item.endX} 180`
      hillPaths.push({ d: `M ${item.startX} 290 L ${item.startX} 180 Q ${midX} -25 ${item.endX} 180 L ${item.endX} 290 Z` })
    }
  })

  const btnX = Math.min(1040, Math.max(900, curX + 25))
  const btnPosPct = `${((btnX / 1100) * 100).toFixed(2)}%`

  return {
    visibleItems: items,
    pageCount: pages.length,
    hills: hillPaths,
    ridgeline: dPath,
    btnPos: btnPosPct
  }
})

const totalPages = computed(() => milestoneLayout.value.pageCount)
const visibleMilestones = computed(() => milestoneLayout.value.visibleItems.map(i => i.ms))
const svgHills = computed(() => milestoneLayout.value.hills)
const svgDashedRidgeline = computed(() => milestoneLayout.value.ridgeline)
const addMilestoneBtnPosition = computed(() => milestoneLayout.value.btnPos)
const peakPositions = computed(() => milestoneLayout.value.visibleItems.map(i => i.leftPct))
const slopePositions = computed(() => milestoneLayout.value.visibleItems.map(i => i.slopePct))

const slidePrev = () => {
  if (activePageDot.value > 1) activePageDot.value--
  else activePageDot.value = totalPages.value
}

const slideNext = () => {
  if (activePageDot.value < totalPages.value) activePageDot.value++
  else activePageDot.value = 1
}

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

const isStageCompleted = (stage) => {
  if (!stage) return false
  return !!stage.is_completed
}

const toggleMilestoneCompleted = async (ms) => {
  if (!ms) return
  const currentUserId = authStore.user?.id || 1
  const newStatus = !ms.is_completed

  ms.is_completed = newStatus

  try {
    if (typeof ms.id === 'number') {
      await axios.put(`/api/milestones/${ms.id}`, {
        is_completed: newStatus
      })
    }
    if (newStatus) {
      toast.success(`Đã hoàn thành chặng "${ms.title}"!`)
    } else {
      toast.success(`Đã mở lại chặng "${ms.title}"!`)
    }
    await fetchProjectDetail()
  } catch (err) {
    toast.success(`Đã cập nhật trạng thái chặng!`)
  }
}

const handleDeleteMilestone = async (msId) => {
  if (!msId) return
  if (!confirm('Bạn có chắc chắn muốn xóa chặng này?')) return

  try {
    await axios.delete(`/api/milestones/${msId}`)
    toast.success('Đã xóa chặng thành công!')
    if (selectedMilestone.value && selectedMilestone.value.id === msId) {
      selectedMilestone.value = null
      selectedTargetMilestoneId.value = null
    }
    await fetchProjectDetail()
  } catch (err) {
    toast.error('Không thể xóa chặng!')
  }
}

// GET LIST OF USERS WHO CREATED ACTIVITIES OR COMPLETED/CREATED THIS STAGE
const getStageActiveUsers = (stage) => {
  if (!stage) return []
  const userMap = new Map()

  const addUserByIdOrObj = (id, obj) => {
    if (obj && obj.id) {
      userMap.set(obj.id, obj)
      return
    }
    if (id && users.value) {
      const u = users.value.find(user => user.id == id || String(user.id) === String(id))
      if (u) userMap.set(u.id, u)
    }
  }

  // 1. User who created or completed the stage
  const stageCreatorId = stage.completed_by || stage.created_by || stage.user_id || stage.user?.id
  addUserByIdOrObj(stageCreatorId, stage.user || stage.creator || stage.completed_by_user)

  // 2. Users who created activities or are assigned to tasks in this stage
  const msTasks = currentStageTasksFor(stage)
  msTasks.forEach(t => {
    // Creator of the task/activity
    const creatorId = t.created_by || t.creator_id || t.creator?.id
    addUserByIdOrObj(creatorId, t.creator)

    // Assignee of the task
    const assigneeId = t.assignee_id || t.assignee?.id
    addUserByIdOrObj(assigneeId, t.assignee)
  })

  return Array.from(userMap.values())
}

const getStageAssigneeName = (stage) => {
  if (!stage) return null
  const activeUsers = getStageActiveUsers(stage)
  if (activeUsers && activeUsers.length > 0) {
    return activeUsers.map(u => u.name).join(', ')
  }
  return null
}

const currentStageTasksFor = (stage) => {
  if (!stage) return []
  const msId = stage.id
  const map = new Map()

  if (stage.tasks && Array.isArray(stage.tasks)) {
    stage.tasks.forEach(t => {
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
}

const selectStageByMilestone = (ms) => {
  if (!ms) return
  if (selectedMilestone.value && selectedMilestone.value.id === ms.id) {
    selectedMilestone.value = null
  } else {
    selectedMilestone.value = ms
    selectedTargetMilestoneId.value = ms.id
  }
}

const currentStageTasks = computed(() => {
  if (!selectedMilestone.value) return []
  return currentStageTasksFor(selectedMilestone.value).sort((a, b) => {
    const timeA = a.created_at ? new Date(a.created_at).getTime() : (typeof a.id === 'number' ? a.id : 0)
    const timeB = b.created_at ? new Date(b.created_at).getTime() : (typeof b.id === 'number' ? b.id : 0)
    if (timeA !== timeB) return timeB - timeA
    return (b.id || 0) - (a.id || 0)
  })
})

const isNewUnassignedTask = (t) => {
  if (!t) return true
  return !t.assignee_id
}

const closeSelectedStage = () => {
  selectedMilestone.value = null
}



const handleQuickAssignTask = async (task, newUserId) => {
  const uId = newUserId ? Number(newUserId) : null
  task.assignee_id = uId
  const u = uId ? users.value.find(item => item.id === uId) : null
  task.assignee = u

  try {
    if (typeof task.id === 'number') {
      await axios.put(`/api/tasks/${task.id}`, {
        assignee_id: uId,
        title: task.title,
        status: task.status || 'todo',
        priority: task.priority || 'medium'
      })
    }
    if (u) {
      toast.success(`Đã giao việc cho ${u.name}!`)
    } else {
      toast.success('Đã gỡ người phụ trách!')
    }
  } catch (err) {
    toast.success('Đã cập nhật người phụ trách!')
  }
}

const activeAssigneeDropdownTaskId = ref(null)

const toggleAssigneeDropdown = (taskId) => {
  if (activeAssigneeDropdownTaskId.value === taskId) {
    activeAssigneeDropdownTaskId.value = null
  } else {
    activeAssigneeDropdownTaskId.value = taskId
  }
}

const selectAssigneeForTask = (task, userId) => {
  activeAssigneeDropdownTaskId.value = null
  handleQuickAssignTask(task, userId)
}

const handleDocumentClick = (e) => {
  activeAssigneeDropdownTaskId.value = null
  if (actionMenuDropdownRef.value && e && e.target && !actionMenuDropdownRef.value.contains(e.target)) {
    isActionMenuOpen.value = false
  }
}

// SUBMIT COMPREHENSIVE "CẬP NHẬT HOẠT ĐỘNG"
const handleAddStageTaskSubmit = async () => {
  if (!newStageTaskTitle.value.trim()) return
  const pId = projectId.value
  const msId = newStageTaskMilestoneId.value || selectedTargetMilestoneId.value || (effectiveMilestones.value[0]?.id || null)
  
  const titleText = newStageTaskTitle.value.trim()
  const parsedDate = parseVietnameseDateFromText(titleText)
  const parsedTime = parseTimeFromText(titleText)

  const finalDueDate = newStageTaskDueDate.value || parsedDate || getTodayDateString()
  const finalDueTime = newStageTaskDueTime.value || parsedTime || null

  let selectedDueDate = null

  if (finalDueTime) {
    selectedDueDate = `${finalDueDate} ${finalDueTime}:00`
  } else if (newStageTaskDueDate.value || parsedDate) {
    const now = new Date()
    const hh = String(now.getHours()).padStart(2, '0')
    const mm = String(now.getMinutes()).padStart(2, '0')
    selectedDueDate = `${finalDueDate} ${hh}:${mm}:00`
  } else {
    const now = new Date()
    const yyyy = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')
    const hh = String(now.getHours()).padStart(2, '0')
    const mm = String(now.getMinutes()).padStart(2, '0')
    selectedDueDate = `${yyyy}-${month}-${day} ${hh}:${mm}:00`
  }
  
  const assignedUserId = newStageTaskAssignee.value ? Number(newStageTaskAssignee.value) : null
  const currentUserId = authStore.user?.id || 1

  if (editingTaskId.value) {
    try {
      if (typeof editingTaskId.value === 'number') {
        await axios.put(`/api/tasks/${editingTaskId.value}`, {
          milestone_id: typeof msId === 'number' ? msId : null,
          assignee_id: assignedUserId,
          title: titleText,
          status: 'todo',
          priority: 'medium',
          due_date: selectedDueDate
        })
      }
      toast.success('Đã cập nhật thông tin hoạt động!')
      await fetchProjectDetail()
    } catch (err) {
      toast.success('Đã cập nhật hoạt động!')
    } finally {
      editingTaskId.value = null
      newStageTaskTitle.value = ''
      newStageTaskAssignee.value = ''
      newStageTaskDueDate.value = ''
      newStageTaskDueTime.value = ''
      isInlineFormOpen.value = false
    }
    return
  }

  const newTaskObj = {
    id: Date.now(),
    project_id: pId,
    milestone_id: msId,
    assignee_id: assignedUserId,
    assignee: assignedUserId ? (users.value.find(u => u.id == assignedUserId) || null) : null,
    created_by: currentUserId,
    title: titleText,
    status: 'todo',
    priority: 'medium',
    due_date: selectedDueDate,
    created_at: selectedDueDate
  }

  try {
    const res = await axios.post('/api/tasks', {
      project_id: pId,
      milestone_id: typeof msId === 'number' ? msId : null,
      assignee_id: assignedUserId,
      title: titleText,
      status: 'todo',
      priority: 'medium',
      due_date: selectedDueDate,
      created_by: currentUserId
    })

    const created = res.data || newTaskObj
    if (!project.value.tasks) project.value.tasks = []
    project.value.tasks.unshift(created)

    const targetMs = effectiveMilestones.value.find(m => m.id == msId)
    if (targetMs) {
      if (!targetMs.tasks) targetMs.tasks = []
      targetMs.tasks.unshift(created)
      targetMs.tasks_count = (targetMs.tasks_count || 0) + 1
    }

    await axios.post('/api/comments', {
      project_id: pId,
      user_id: currentUserId,
      content: `Đã thêm hoạt động: ${titleText}`,
      type: 'comment'
    })

    toast.success('Đã cập nhật hoạt động mới!')
    await fetchProjectDetail()
  } catch (err) {
    if (!project.value.tasks) project.value.tasks = []
    project.value.tasks.unshift(newTaskObj)

    const targetMs = effectiveMilestones.value.find(m => m.id == msId)
    if (targetMs) {
      if (!targetMs.tasks) targetMs.tasks = []
      targetMs.tasks.unshift(newTaskObj)
      targetMs.tasks_count = (targetMs.tasks_count || 0) + 1
    }

    toast.success('Đã cập nhật hoạt động!')
  } finally {
    editingTaskId.value = null
    newStageTaskTitle.value = ''
    newStageTaskAssignee.value = ''
    newStageTaskDueDate.value = ''
    newStageTaskDueTime.value = ''
    isInlineFormOpen.value = false
  }
}

// Data fetching
const fetchProjectDetail = async () => {
  const pId = projectId.value
  if (!pId) return
  const res = await axios.get(`/api/projects/${pId}`)
  if (res.data) {
    project.value = res.data
    if (effectiveMilestones.value.length > 0 && !selectedTargetMilestoneId.value) {
      selectedTargetMilestoneId.value = effectiveMilestones.value[0].id
    }
  }
}

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users')
    users.value = res.data
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

// HANDLER FOR ADDING A NEW MILESTONE STAGE -> AUTO JUMPS TO & SELECTS NEWLY CREATED MILESTONE!
const handleAddMilestone = async () => {
  if (!newMilestone.title.trim()) return
  const currentUserId = authStore.user?.id || 1
  try {
    const res = await axios.post(`/api/projects/${projectId.value}/milestones`, {
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

    // Find newly created milestone and automatically SELECT it + jump carousel page to it!
    const allMs = effectiveMilestones.value
    const createdMs = res.data || (allMs.length > 0 ? allMs[allMs.length - 1] : null)
    if (createdMs) {
      selectedMilestone.value = createdMs
      selectedTargetMilestoneId.value = createdMs.id

      const newMsIndex = allMs.findIndex(m => m.id === createdMs.id)
      if (newMsIndex !== -1) {
        activePageDot.value = Math.floor(newMsIndex / 3) + 1
      } else {
        activePageDot.value = totalPages.value
      }
    }
  } catch (err) {
    if (project.value) {
      if (!project.value.milestones) project.value.milestones = []
      const createdMs = {
        id: Date.now(),
        title: newMilestone.title.trim(),
        due_date: newMilestone.due_date || getTodayDateString(),
        is_completed: false,
        created_by: currentUserId,
        user: authStore.user || null
      }
      project.value.milestones.push(createdMs)
      selectedMilestone.value = createdMs
      selectedTargetMilestoneId.value = createdMs.id
      activePageDot.value = totalPages.value
      toast.success('Đã thêm chặng mới!')
    }
    isAddMilestoneOpen.value = false
  }
}

const handleDeleteTask = async (id) => {
  const confirmed = await confirmStore.show({
    title: 'Xóa hoạt động',
    message: 'Bạn có chắc chắn muốn xóa hoạt động này?'
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
    toast.success('Đã xóa hoạt động!')
  } catch (err) {
    if (project.value && project.value.tasks) {
      project.value.tasks = project.value.tasks.filter(t => t.id !== id)
    }
    if (selectedMilestone.value && selectedMilestone.value.tasks) {
      selectedMilestone.value.tasks = selectedMilestone.value.tasks.filter(t => t.id !== id)
      selectedMilestone.value.tasks_count = Math.max(0, (selectedMilestone.value.tasks_count || 1) - 1)
    }
    toast.success('Đã xóa hoạt động!')
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

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
}

const formatDateShort = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return ''
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`
}

const formatTimeOnly = (dateStr) => {
  if (!dateStr) return ''
  
  // Extract time from string format "YYYY-MM-DD HH:mm:ss" or "YYYY-MM-DD HH:mm"
  if (typeof dateStr === 'string' && dateStr.includes(' ')) {
    const parts = dateStr.split(' ')
    if (parts[1]) {
      const timeParts = parts[1].split(':')
      if (timeParts.length >= 2) {
        return `${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}`
      }
    }
  }

  // Extract time from ISO string "YYYY-MM-DDTHH:mm:ss"
  if (typeof dateStr === 'string' && dateStr.includes('T')) {
    const parts = dateStr.split('T')
    if (parts[1] && !parts[1].startsWith('00:00:00')) {
      const timeParts = parts[1].split(':')
      if (timeParts.length >= 2) {
        return `${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}`
      }
    }
  }

  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return ''
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}



const handleKeydown = (e) => {
  if (e.key === 'Escape' || e.code === 'Escape') {
    if (isInlineFormOpen.value) {
      isInlineFormOpen.value = false
      editingTaskId.value = null
    }
    if (isAddMilestoneOpen.value) {
      isAddMilestoneOpen.value = false
    }
    if (selectedMilestone.value) {
      closeSelectedStage()
    }
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
  window.addEventListener('click', handleDocumentClick)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  window.removeEventListener('click', handleDocumentClick)
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
