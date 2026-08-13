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
        <p class="text-xs text-gray-500 font-semibold mt-1">Vui lòng kiểm tra lại kết nối mạng hoặc dự án không tồn tại.
        </p>
      </div>
      <div class="flex items-center gap-3 mt-2">
        <button @click="goBack" type="button"
          class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
          Quay lại danh sách
        </button>
        <button @click="loadAllData" type="button"
          class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors cursor-pointer">
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
                <span>Chỉnh sửa dự án</span>
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

      <!-- MAIN PROJECT TITLE & CUSTOMER NAME BELOW (CĂN GIỮA) -->
      <div class="text-center space-y-0.5 pt-1 pb-2">
        <h1
          class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 tracking-tight font-heading uppercase break-words max-w-full leading-tight mx-auto">
          {{ project.title }}
        </h1>
        <div
          class="flex items-center justify-center gap-1.5 text-sm sm:text-base font-bold text-gray-500 font-sans">
          <span>{{ project.customer ? project.customer.name : '' }}</span>
        </div>
      </div>

      <!-- SOFT ELEGANT MOUNTAIN ROADMAP CONTAINER -->
      <div class="relative bg-[#f4f5f0] rounded-3xl p-4 sm:p-6 select-none overflow-hidden min-h-[340px]">

        <!-- Mountain graphic container -->
        <div class="relative min-w-[980px] h-[290px] mx-auto px-4 sm:px-6">

          <!-- SVG Dynamic Mountain Hills Fill -->
          <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="none"
            viewBox="0 0 1100 290">
            <defs>
              <linearGradient id="hill-unified-green-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#94a3b8" stop-opacity="0.25" />
                <stop offset="100%" stop-color="#cbd5e1" stop-opacity="0.05" />
              </linearGradient>
              <linearGradient id="hill-active-red-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#64748b" stop-opacity="0.30" />
                <stop offset="100%" stop-color="#cbd5e1" stop-opacity="0.08" />
              </linearGradient>
            </defs>

            <!-- Render dynamic modular mountain hill paths -->
            <path v-for="(hill, hIdx) in svgHills" :key="hIdx"
              :fill="hill.isActive ? 'url(#hill-active-red-grad)' : 'url(#hill-unified-green-grad)'" :d="hill.d" />

            <!-- Continuous Dashed Mountain Ridgeline Line -->
            <path :d="svgDashedRidgeline" fill="none" stroke="#cbd5e1" stroke-width="2.5" stroke-dasharray="6,6"
              stroke-linecap="round" />
          </svg>

          <!-- NÚT BẮT ĐẦU DỰ ÁN (CỜ SVG + BADGE TICK DONE — CLICKABLE NHƯ 1 CHẶNG) -->
          <div @click="selectStartStage"
            class="absolute top-[150px] -translate-x-1/2 flex flex-col items-center z-30 cursor-pointer group transition-all duration-300 hover:scale-108"
            style="left: 3.18%">
            <div class="relative w-10 h-10 rounded-full border-2 border-emerald-600 bg-emerald-50 flex items-center justify-center shadow-2xs transition-colors group-hover:bg-emerald-100">
              <svg class="w-5 h-5 fill-emerald-600" viewBox="0 0 512 512">
                <g transform="translate(0,512) scale(0.1,-0.1)">
                  <path d="M560 4828 c-18 -13 -43 -36 -54 -51 l-21 -28 0 -2189 0 -2189 21 -28 c73 -98 195 -98 268 0 21 28 21 34 24 850 l3 822 1874 562 c1031 309 1886 570 1900 580 31 22 65 90 65 128 0 39 -36 110 -66 130 -19 12 -2490 923 -3748 1381 -168 61 -215 66 -266 32z m1834 -948 c861 -316 1566 -577 1566 -580 0 -3 -706 -216 -1568 -474 -862 -258 -1573 -471 -1579 -474 -10 -3 -13 212 -13 1053 0 885 2 1056 14 1053 7 -2 718 -262 1580 -578z"/>
                </g>
              </svg>
              <!-- Tick Done Badge hoặc số task -->
              <div v-if="startStageTasks.length === 0" class="w-4.5 h-4.5 rounded-full bg-emerald-600 border-2 border-white text-white flex items-center justify-center absolute -bottom-1 left-1/2 -translate-x-1/2 shadow-2xs">
                <i class="fa-solid fa-check text-[8px]"></i>
              </div>
              <div v-else class="w-4.5 h-4.5 rounded-full bg-emerald-600 border-2 border-white text-white flex items-center justify-center absolute -bottom-1 left-1/2 -translate-x-1/2 shadow-2xs text-[9px] font-black leading-none">
                {{ startStageTasks.length }}
              </div>
            </div>
            <div class="text-center mt-1.5 whitespace-nowrap font-sans">
              <div class="text-xs font-black tracking-tight uppercase transition-colors"
                :class="isStartStageSelected ? 'text-emerald-700 underline underline-offset-4 decoration-2' : 'text-gray-900 group-hover:text-emerald-700'">
                Bắt đầu
              </div>
              <div v-if="project.created_at" class="text-[11px] font-bold text-gray-400 mt-0.5">
                {{ formatDateShort(project.created_at) }}
              </div>
            </div>
          </div>

          <!-- DYNAMIC MILESTONE PEAKS & SLOPE AVATARS -->
          <template v-for="(item, index) in milestoneLayout.visibleItems" :key="item.ms.id || index">

            <!-- COMPACT COMPLETED STAGE NODE (CỜ SVG, BADGE TICK DONE CENTERED BÊN DƯỚI CỜ, KHÔNG CÓ NỀN MẢNG XÁM BÊN DƯỚI) -->
            <template v-if="item.isDone">
              <div @click="selectStageByMilestone(item.ms)"
                class="absolute top-[150px] -translate-x-1/2 flex flex-col items-center z-20 cursor-pointer group transition-all duration-300 hover:scale-108 max-w-[100px]"
                :style="{ left: item.leftPct }">

                <!-- Cờ xanh SVG hoàn thành với badge tick checkmark NẰM CHÍNH GIỮA PHÍA DƯỚI CỜ -->
                <div class="relative w-10 h-10 rounded-full border-2 border-emerald-600 bg-emerald-50 flex items-center justify-center shadow-2xs transition-colors group-hover:bg-emerald-100"
                  :title="`Chặng đã hoàn thành: ${item.ms.title}`">

                  <!-- AVATARS CỦA RIÊNG CHẶNG ĐÃ HOÀN THÀNH (Định vị absolute phía trên cờ để không đẩy cờ đi xuống) -->
                  <div v-if="(milestoneAvatarsMap[item.ms.id] || []).length > 0"
                    class="absolute -top-8.5 left-1/2 -translate-x-1/2 flex items-center -space-x-1.5 mb-1 pointer-events-none whitespace-nowrap">
                    <div v-for="u in (milestoneAvatarsMap[item.ms.id] || [])" :key="u.id"
                      class="w-7.5 h-7.5 rounded-full bg-white p-0.5 shadow-sm border-2 border-emerald-500 transition-all hover:scale-115">
                      <img :src="u.avatar || defaultAvatar" class="w-full h-full rounded-full object-cover" />
                    </div>
                  </div>

                  <svg class="w-5 h-5 fill-emerald-600" viewBox="0 0 512 512">
                    <g transform="translate(0,512) scale(0.1,-0.1)">
                      <path d="M560 4828 c-18 -13 -43 -36 -54 -51 l-21 -28 0 -2189 0 -2189 21 -28 c73 -98 195 -98 268 0 21 28 21 34 24 850 l3 822 1874 562 c1031 309 1886 570 1900 580 31 22 65 90 65 128 0 39 -36 110 -66 130 -19 12 -2490 923 -3748 1381 -168 61 -215 66 -266 32z m1834 -948 c861 -316 1566 -577 1566 -580 0 -3 -706 -216 -1568 -474 -862 -258 -1573 -471 -1579 -474 -10 -3 -13 212 -13 1053 0 885 2 1056 14 1053 7 -2 718 -262 1580 -578z"/>
                    </g>
                  </svg>
                  <div class="w-4.5 h-4.5 rounded-full bg-emerald-600 border-2 border-white text-white flex items-center justify-center absolute -bottom-1 left-1/2 -translate-x-1/2 shadow-2xs">
                    <i class="fa-solid fa-check text-[8px]"></i>
                  </div>
                </div>

                <!-- Tên chặng & Ngày hoàn thành (e.g. 01/05, 02/05) -->
                <div class="text-center mt-1.5 whitespace-nowrap font-sans max-w-[100px]">
                  <div class="text-xs font-black tracking-tight leading-tight uppercase transition-colors truncate"
                    :class="selectedMilestone && selectedMilestone.id === item.ms.id ? 'text-emerald-700 underline underline-offset-4 decoration-2' : 'text-gray-900 group-hover:text-emerald-700'"
                    :title="item.ms.title">
                    {{ item.ms.title }}
                  </div>
                  <div v-if="item.ms.updated_at" class="text-[11px] font-bold text-gray-400 mt-0.5">
                    {{ formatDateShort(item.ms.updated_at) }}
                  </div>
                </div>
              </div>
            </template>

            <!-- ACTIVE / IN-PROGRESS STAGE PEAK NODE (CỜ XÁM VÀ SỐ) -->
            <template v-else>
              <!-- ĐỈNH NÚI: CỜ SLATE/XÁM TRÒN Y CHANG DONE (BỎ MÀU ĐỎ CỦA CỜ VÀ NỀN) -->
              <div @click="selectStageByMilestone(item.ms)"
                class="absolute top-[36px] -translate-x-1/2 flex flex-col items-center gap-1 z-20 cursor-pointer group transition-all duration-300 hover:scale-108"
                :style="{ left: item.leftPct }">
                
                <!-- Cờ xám SVG tròn với badge số công việc NẰM CHÍNH GIỮA PHÍA DƯỚI CỜ -->
                <div class="relative w-10 h-10 rounded-full border-2 border-slate-400 bg-slate-100 flex items-center justify-center shadow-2xs transition-colors group-hover:bg-slate-200"
                  :title="`Chặng chưa hoàn thành: ${item.ms.title}`">
                  <svg class="w-5 h-5 fill-slate-500" viewBox="0 0 512 512">
                    <g transform="translate(0,512) scale(0.1,-0.1)">
                      <path d="M560 4828 c-18 -13 -43 -36 -54 -51 l-21 -28 0 -2189 0 -2189 21 -28 c73 -98 195 -98 268 0 21 28 21 34 24 850 l3 822 1874 562 c1031 309 1886 570 1900 580 31 22 65 90 65 128 0 39 -36 110 -66 130 -19 12 -2490 923 -3748 1381 -168 61 -215 66 -266 32z m1834 -948 c861 -316 1566 -577 1566 -580 0 -3 -706 -216 -1568 -474 -862 -258 -1573 -471 -1579 -474 -10 -3 -13 212 -13 1053 0 885 2 1056 14 1053 7 -2 718 -262 1580 -578z"/>
                    </g>
                  </svg>
                  <div class="w-4.5 h-4.5 rounded-full bg-slate-500 border-2 border-white text-white flex items-center justify-center absolute -bottom-1 left-1/2 -translate-x-1/2 shadow-2xs text-[9px] font-black leading-none">
                    {{ getStageTaskCount(item.ms) }}
                  </div>
                </div>
              </div>

              <!-- AVATAR ĐANG LEO SƯỜN NÚI CỦA RIÊNG CHẶNG NÀY (Phân bố theo milestoneAvatarsMap) -->
              <div v-if="(milestoneAvatarsMap[item.ms.id] || []).length > 0"
                @click="selectStageByMilestone(item.ms)"
                class="absolute top-[105px] -translate-x-1/2 z-25 cursor-pointer transition-all duration-300 hover:scale-105"
                :style="{ left: item.slopePct }">
                <div class="relative flex items-center justify-center">
                  <div v-for="(u, uIdx) in (milestoneAvatarsMap[item.ms.id] || []).slice(0, 7)" :key="u.id || uIdx"
                    class="absolute w-8 h-8 rounded-full bg-white p-0.5 shadow-md border-2 border-emerald-500 transition-all hover:scale-125 filter drop-shadow-2xs"
                    :style="{
                      transform: `translate(${(uIdx - (Math.min((milestoneAvatarsMap[item.ms.id] || []).length, 7) - 1) / 2) * 20}px, ${-(uIdx - (Math.min((milestoneAvatarsMap[item.ms.id] || []).length, 7) - 1) / 2) * 16}px)`,
                      zIndex: uIdx + 1
                    }" :title="`Thành viên thực hiện ${item.ms.title}: ${u.name}`">
                    <img :src="u.avatar || defaultAvatar" class="w-full h-full rounded-full object-cover" />
                  </div>
                </div>
              </div>

              <!-- Tên chặng & Thời gian -->
              <div @click="selectStageByMilestone(item.ms)"
                class="absolute top-[112px] -translate-x-1/2 text-center space-y-0.5 font-sans z-10 cursor-pointer group max-w-[210px]"
                :style="{ left: item.leftPct }">
                <div
                  class="text-sm sm:text-base font-black tracking-tight leading-tight uppercase truncate transition-colors"
                  :class="selectedMilestone && selectedMilestone.id === item.ms.id ? 'text-emerald-700 underline underline-offset-4 decoration-2' : 'text-gray-900 group-hover:text-emerald-700'">
                  {{ item.ms.title }}
                </div>

                <div v-if="item.ms.is_completed && item.ms.updated_at" class="text-[11px] font-bold text-gray-400 mt-0.5">
                  {{ formatDateShort(item.ms.updated_at) }}
                </div>
              </div>
            </template>

          </template>

          <!-- Empty state when project has 0 milestones -->
          <div v-if="visibleMilestones.length === 0"
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center z-10">
            <div class="text-xs sm:text-sm font-bold text-gray-400">Chưa có chặng nào trong dự án. Bấm "+ THÊM CHẶNG" để
              tạo chặng
              đầu tiên.</div>
          </div>

          <!-- NÚT (+) THÊM CHẶNG ĐẶT TẠI ĐIỂM KẾT THÚC CỦA ĐƯỜNG NẾT NÚI -->
          <div @click="openAddMilestoneModal"
            class="absolute top-[165px] flex flex-col items-center cursor-pointer group transition-all duration-300 hover:scale-108 z-30"
            :style="{ left: addMilestoneBtnPosition }">
            <button type="button"
              class="w-8 h-8 rounded-full border border-dashed border-gray-400 bg-white text-gray-500 group-hover:border-emerald-600 group-hover:text-emerald-600 flex items-center justify-center text-sm shadow-2xs transition-colors cursor-pointer">
              <i class="fa-solid fa-plus"></i>
            </button>
            <div class="text-center mt-1.5 whitespace-nowrap">
              <div class="text-xs font-black text-gray-900 group-hover:text-emerald-700 font-sans uppercase">
                Thêm chặng
              </div>
            </div>
          </div>

        </div>

        <!-- Carousel Pagination Dots -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-2">
          <span v-for="dot in totalPages" :key="dot" class="w-2.5 h-2.5 rounded-full transition-all cursor-pointer"
            :class="dot === activePageDot ? 'w-3 h-3 bg-slate-900' : 'bg-slate-300 hover:bg-slate-400'"
            @click="activePageDot = dot"></span>
        </div>

        <!-- Slide Navigation Arrows -->
        <div v-if="totalPages > 1" class="absolute bottom-4 right-6 flex items-center gap-3 z-30">
          <button @click="slidePrev" type="button"
            class="w-9 h-9 rounded-full bg-white border border-gray-200 shadow-xs hover:shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition-all cursor-pointer"
            title="Chặng trước">
            <i class="fa-solid fa-arrow-left text-xs"></i>
          </button>
          <button @click="slideNext" type="button"
            class="w-9 h-9 rounded-full bg-white border border-gray-200 shadow-xs hover:shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition-all cursor-pointer"
            title="Chặng tiếp theo">
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </button>
        </div>

      </div>

      <!-- CENTER PROMINENT "HÚ HÚ" BUTTON (Luôn hiện trừ khi đang chọn chặng đã hoàn thành) -->
      <div v-if="!selectedMilestone || !selectedMilestone.is_completed" class="flex flex-col items-center justify-center my-6 gap-2">
        <button @click="toggleInlineForm" type="button"
          class="px-8 py-3 bg-[#10b981] hover:bg-emerald-600 text-white font-extrabold text-base sm:text-xl rounded-full shadow-md hover:shadow-lg hover:scale-103 active:scale-97 transition-all flex items-center gap-3 cursor-pointer group">
          <div
            class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-dove text-lg text-white"></i>
          </div>
          <span>HÚ HÚ</span>
        </button>
        <span class="text-xs sm:text-sm font-semibold text-gray-500 font-sans tracking-wide">Chim sẻ gọi đại bàng</span>
      </div>

      <!-- MAIN CARDS / ACTIVITIES CONTAINER ("DẤU CHÂN HOẠT ĐỘNG") -->
      <div @click="handleActivityContainerClick"
        class="bg-white rounded-3xl p-5 sm:p-6 shadow-xl border border-gray-100 max-w-[720px] mx-auto w-full space-y-4 animate-fade-in-up relative">

        <!-- Header Row: Title "DẤU CHÂN HOẠT ĐỘNG" & Xem tất cả -->
        <div class="border-b border-gray-100 pb-3.5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <!-- Clear Filter / Back Button if Milestone is Selected -->
              <button v-if="selectedMilestone || isStartStageSelected" @click="closeSelectedStage" type="button"
                class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 flex items-center justify-center text-xs font-bold shadow-2xs transition-colors cursor-pointer flex-shrink-0"
                title="Hiện tất cả hoạt động">
                <i class="fa-solid fa-arrow-left"></i>
              </button>

              <h3 v-if="!selectedMilestone" class="text-base sm:text-lg font-black text-gray-900 uppercase tracking-tight font-heading break-words min-w-0">
                {{ isStartStageSelected ? 'BẮT ĐẦU' : 'DẤU CHÂN HOẠT ĐỘNG' }}
              </h3>
              <h3 v-else class="text-base sm:text-lg font-black text-gray-500 uppercase tracking-tight font-heading break-words min-w-0">
                DẤU CHÂN HOẠT ĐỘNG
              </h3>
            </div>

            <!-- Xem tất cả link với hiệu ứng loading -->
            <button @click="handleViewAll" type="button" :disabled="isViewingAllLoading"
              class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 cursor-pointer disabled:opacity-50 transition-all select-none flex-shrink-0"
              title="Xem tất cả hoạt động của dự án">
              <span v-if="isViewingAllLoading" class="w-3.5 h-3.5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
              <i v-else class="fa-solid fa-layer-group text-[11px]"></i>
              <span>{{ isViewingAllLoading ? 'Đang tải...' : 'Xem tất cả' }}</span>
              <i v-if="!isViewingAllLoading" class="fa-solid fa-arrow-right text-[10px]"></i>
            </button>
          </div>

          <!-- Separate section for selected Milestone Details (prevent crowding) -->
          <div v-if="selectedMilestone" class="mt-3">
            <h4 class="text-lg sm:text-xl font-black text-gray-900 uppercase tracking-tight font-heading break-words leading-tight">
              {{ selectedMilestone.title }}
            </h4>
            
            <!-- BUTTONS ĐÁNH DẤU HOÀN THÀNH & XÓA CHẶNG — ĐẶT DƯỚI TÊN CHẶNG -->
            <div class="flex items-center gap-2 mt-2 flex-wrap">
              <button @click="toggleMilestoneCompleted(selectedMilestone)" type="button"
                class="px-3 py-1 rounded-xl font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer"
                :class="selectedMilestone.is_completed ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white'">
                <i class="fa-solid fa-circle-check text-xs"></i>
                <span>{{ selectedMilestone.is_completed ? 'Đã hoàn thành chặng' : 'Đánh dấu hoàn thành chặng' }}</span>
              </button>

              <button v-if="!selectedMilestone.is_completed"
                @click="handleDeleteMilestone(selectedMilestone.id)" type="button"
                class="px-3 py-1 rounded-xl font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-600 hover:text-white"
                title="Xóa chặng này">
                <i class="fa-solid fa-trash-can text-xs"></i>
                <span>Xóa chặng</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ACTIVITIES LIST CONTAINER -->
        <div class="relative z-10">
          <!-- Loading overlay mượt mà khi nhấn Xem tất cả -->
          <div v-if="isViewingAllLoading" class="py-12 flex flex-col items-center justify-center gap-3">
            <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs font-extrabold text-emerald-600 animate-pulse">Đang tải tất cả hoạt động...</span>
          </div>

          <div v-else class="space-y-3">
            <!-- CARDS LIST -->
            <div v-for="t in displayedCards" :key="t.id" @dblclick="!isTaskInDoneStage(t) && openEditStageTaskForm(t)"
              class="bg-white border border-gray-100 hover:border-gray-200 rounded-2xl p-3 sm:p-3.5 shadow-2xs hover:shadow-xs transition-all group cursor-pointer flex gap-3"
              :title="isTaskInDoneStage(t) ? 'Chặng đã hoàn thành (Không thể chỉnh sửa)' : 'Nhấn đúp (Double-click) để chỉnh sửa hoạt động này'">

              <!-- LEFT: Icon block (green footprints / orange bell) -->
              <div class="w-[44px] h-[44px] rounded-xl flex items-center justify-center flex-shrink-0 self-start"
                :class="isAssignedHuhuTask(t) ? 'bg-amber-500' : 'bg-emerald-600'">
                <i :class="[getTaskCardIcon(t), 'text-white text-base']"></i>
              </div>

              <!-- RIGHT: Content area -->
              <div class="flex-1 min-w-0">
                <!-- Header: name + tags + date -->
                <div class="flex items-start justify-between gap-2">
                  <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                    <span class="text-sm font-bold text-gray-800">{{ getCreatorDisplayName(t) }}</span>

                    <span v-if="getAssigneeDisplayName(t)" class="text-sm font-extrabold text-amber-500 leading-none">
                      Hú
                    </span>

                    <span v-if="getAssigneeDisplayName(t)"
                      class="inline-flex items-center px-2 py-0.5 bg-amber-500 rounded-full text-[11px] font-bold text-white leading-none">
                      {{ getAssigneeDisplayName(t) }}
                    </span>

                    <span v-if="t.due_date && getAssigneeDisplayName(t)"
                      class="inline-flex items-center px-2 py-0.5 bg-amber-500 rounded-full text-[11px] font-bold text-white leading-none">
                      {{ formatDueDateTagForCard(t.due_date) }}
                    </span>
                  </div>

                  <span class="text-xs font-medium text-gray-400 flex-shrink-0 pt-0.5">
                    {{ formatDateShort(t.created_at || t.due_date) }}
                  </span>
                </div>

                <!-- Body: message content -->
                <div class="mt-1.5 text-sm sm:text-base font-extrabold text-gray-900 leading-snug tracking-tight"
                  v-html="formatTitleText(t.title)"></div>

                <!-- Attachments: image thumbnails + file doc icons -->
                <div v-if="getTaskAttachments(t).length > 0" class="flex flex-wrap items-end gap-2 mt-2.5">
                  <template v-for="(att, attIdx) in getTaskAttachments(t)" :key="attIdx">
                    <button v-if="att.type === 'image'" type="button" @click.stop="openImageModal(att.src)"
                      class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 cursor-pointer hover:ring-2 hover:ring-emerald-300 transition-all flex-shrink-0"
                      :title="'Xem ảnh: ' + att.name">
                      <img :src="att.src" class="w-full h-full object-cover" alt="" />
                    </button>
                    <a v-else :href="att.src" :download="att.fullName || att.name" :title="'Tải xuống: ' + (att.fullName || att.name)" @click.stop
                      class="w-8 h-10 rounded-md border border-amber-200 bg-amber-50 hover:bg-amber-100 flex flex-col items-center justify-between overflow-hidden cursor-pointer transition-colors flex-shrink-0">
                      <i :class="[isPdfAttachment(att.name) ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file', 'text-amber-600 text-sm mt-1']"></i>
                      <span class="text-[8px] font-extrabold text-amber-800 bg-amber-200/80 w-full text-center py-0.5 leading-none">
                        {{ isPdfAttachment(att.name) ? 'PDF' : getFileExtLabel(att.name) }}
                      </span>
                    </a>
                  </template>
                </div>
              </div>
            </div>

            <!-- EMPTY STATE IF NO CARDS -->
            <div v-if="totalCardsForCurrentView.length === 0" class="py-10 text-center text-gray-400 font-medium text-xs">
              Chưa có hoạt động nào trong chặng này.
            </div>

            <!-- LOAD MORE BUTTON (HIỂN THỊ KHI CÒN CARD CHƯA XEM) -->
            <div v-if="hasMoreCards" class="pt-3 text-center">
              <button @click="handleLoadMore" type="button" :disabled="isLoadingMore"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 hover:border-emerald-200 rounded-2xl text-xs sm:text-sm font-extrabold shadow-2xs hover:shadow-xs transition-all cursor-pointer disabled:opacity-50">
                <div v-if="isLoadingMore" class="w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                <i v-else class="fa-solid fa-angles-down text-xs text-emerald-600"></i>
                <span>{{ isLoadingMore ? 'Đang tải thêm...' : `Xem thêm (${totalCardsForCurrentView.length - displayedCards.length} hoạt động nữa)` }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>

  <!-- Modal for Project Edit -->
  <ProjectModal :is-open="isModalOpen" :customers="customers" :users="users" :edit-project="project"
    @close="handleCloseModal" @submit="handleUpdateProjectSubmit" @customer-created="fetchCustomers" />

  <!-- FIXED BOTTOM UPDATE CREATION CARD (COLLAPSED BY DEFAULT, FIXED AT BOTTOM OF SCREEN WHEN OPEN) -->
  <transition enter-active-class="transition duration-300 ease-out transform"
    enter-from-class="opacity-0 translate-y-full" enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in transform" leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-full">
    <div v-if="isInlineFormOpen" ref="inlineFormRef"
      class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none pb-3 sm:pb-5 px-4 sm:px-6 lg:px-8 transition-all">
      <div
        class="max-w-[720px] mx-auto pointer-events-auto bg-white border border-gray-200 shadow-2xl rounded-2xl p-4 sm:p-5 relative ring-1 ring-black/5">

        <!-- X CLOSE BUTTON (TOP RIGHT) -->
        <button type="button" @click="cancelEditTask"
          class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 flex items-center justify-center text-xs transition-colors cursor-pointer z-10"
          title="Đóng (Esc)">
          <i class="fa-solid fa-xmark"></i>
        </button>

        <form @submit.prevent="handleAddStageTaskSubmit"
          class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4 lg:gap-5">

          <!-- LEFT SECTION: MỤC TIÊU HƯỚNG ĐẾN (CHẶNG / MILESTONE SELECTOR) -->
          <div class="flex flex-col gap-2 lg:pr-5 lg:border-r lg:border-gray-200 flex-shrink-0">
            <div
              class="flex items-center gap-1 text-[11px] font-extrabold text-gray-500 tracking-wider uppercase font-sans">
              <span>MỤC TIÊU HƯỚNG ĐẾN</span>
              <i class="fa-regular fa-circle-question text-gray-400 text-xs"
                title="Chọn chặng mục tiêu hướng đến cho cập nhật"></i>
            </div>

            <!-- IF <= 3 STAGES: DISPLAY STAGE BUTTONS SIDE BY SIDE LIKE IN THE IMAGE -->
            <div v-if="activeTargetMilestones.length <= 3 && activeTargetMilestones.length > 0"
              class="flex items-center gap-2">
              <div v-for="ms in activeTargetMilestones" :key="ms.id" @click="newStageTaskMilestoneId = ms.id"
                class="flex flex-col items-center justify-between p-2 rounded-xl border transition-all cursor-pointer select-none min-w-[72px] sm:min-w-[84px] h-[78px]"
                :class="newStageTaskMilestoneId === ms.id
                  ? 'bg-rose-50/70 border-rose-200 text-rose-600 shadow-2xs'
                  : 'bg-white border-gray-200 hover:border-gray-300 text-gray-600 hover:bg-gray-50'">
                <!-- Circle with task count -->
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors"
                  :class="newStageTaskMilestoneId === ms.id
                    ? 'border-2 border-rose-500 bg-white text-rose-600'
                    : 'border border-gray-300 bg-white text-gray-700'">
                  {{ getStageTaskCount(ms) }}
                </div>

                <!-- Stage Name -->
                <span
                  class="text-[10px] font-black tracking-tight text-center uppercase leading-tight line-clamp-2 mt-0.5"
                  :class="newStageTaskMilestoneId === ms.id ? 'text-gray-900 font-extrabold' : 'text-gray-600'">
                  {{ ms.title }}
                </span>
              </div>
            </div>

            <!-- IF > 3 STAGES: CONVERT TO DROPDOWN SELECTOR AS REQUESTED -->
            <div v-else-if="activeTargetMilestones.length > 3" class="w-full sm:w-56">
              <select v-model="newStageTaskMilestoneId"
                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors cursor-pointer">
                <option v-for="ms in activeTargetMilestones" :key="ms.id" :value="ms.id">
                  🚩 {{ ms.title }} ({{ getStageTaskCount(ms) }})
                </option>
              </select>
            </div>

            <!-- IF 0 STAGES: Tự động dùng chặng Bắt đầu -->
            <div v-else class="flex items-center gap-2">
              <div class="flex flex-col items-center justify-between p-2 rounded-xl border bg-emerald-50/70 border-emerald-200 text-emerald-600 shadow-2xs select-none min-w-[72px] sm:min-w-[84px] h-[78px]">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black border-2 border-emerald-500 bg-white text-emerald-600">
                  {{ startStageTasks.length }}
                </div>
                <span class="text-[10px] font-black tracking-tight text-center uppercase leading-tight text-gray-900 font-extrabold mt-0.5">Bắt đầu</span>
              </div>
            </div>
          </div>

          <!-- RIGHT SECTION: AVATAR + TEXTAREA + BOTTOM ACTION ROW -->
          <div class="flex-1 flex flex-col justify-between gap-2 min-w-0 relative pr-5">

            <!-- TOP ROW: USER AVATAR + TEXTAREA INPUT -->
            <div class="flex items-start gap-3 min-w-0">
              <!-- User avatar -->
              <img :src="authStore.user?.avatar || defaultAvatar"
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 shadow-2xs flex-shrink-0 mt-0.5"
                :title="authStore.user?.name || 'Tôi'" />

              <!-- Input Area Container with Mention Dropdown & Paste Support -->
              <div class="flex-1 min-w-0 relative">
                <textarea ref="stageTaskTitleInputRef" v-model="newStageTaskTitle" rows="1" required
                  @input="onTitleInput" @keydown="onTitleKeydown" @paste="onTextareaPaste" placeholder="Chia sẻ cập nhật với team..."
                  class="w-full bg-transparent text-sm sm:text-base font-bold text-gray-900 leading-relaxed py-1 focus:outline-none placeholder-gray-400 resize-none m-0 border-0"></textarea>

                <!-- AUTOCOMPLETE @MENTION DROPDOWN POPOVER (POPS UP ABOVE INPUT) -->
                <div v-if="showMentionDropdown && filteredUsersForMention.length > 0"
                  class="absolute left-0 bottom-full mb-2 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-xl py-1 text-gray-800 max-h-52 overflow-y-auto ring-1 ring-black/5">
                  <div
                    class="px-3 py-1 text-[10px] uppercase font-bold text-emerald-600 border-b border-gray-100 mb-1 flex items-center justify-between">
                    <span>Chọn người phụ trách (@)</span>
                    <i class="fa-solid fa-at text-xs"></i>
                  </div>
                  <button v-for="(u, idx) in filteredUsersForMention" :key="u.id" type="button" @mousedown.prevent
                    @click="selectMentionUser(u)"
                    class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left"
                    :class="{ 'bg-emerald-50 text-emerald-800 font-bold': idx === mentionIndex }">
                    <img :src="u.avatar || defaultAvatar"
                      class="w-5 h-5 rounded-full object-cover border border-gray-200" />
                    <span class="truncate flex-1">{{ u.name }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- ATTACHED FILES & PASTED IMAGES PREVIEW BAR (FULL WIDTH) -->
            <div v-if="attachedFiles && attachedFiles.length > 0" class="w-full flex items-center gap-2 overflow-x-auto py-1 border-t border-gray-100 custom-scrollbar">
              <div 
                v-for="(att, aIdx) in attachedFiles" 
                :key="aIdx"
                class="relative group flex-shrink-0"
              >
                <!-- Image Thumbnail (Click to view full-size modal) -->
                <div v-if="att.isImage" @click="openImageModal(att.preview)" class="w-14 h-14 rounded-xl border border-gray-200 overflow-hidden bg-gray-100 shadow-2xs relative cursor-pointer group/img" title="Nhấn để xem ảnh phóng to">
                  <img :src="att.preview" class="w-full h-full object-cover group-hover/img:scale-105 transition-transform" />
                  <button 
                    type="button" 
                    @click.stop="removeAttachment(aIdx)" 
                    class="absolute top-0.5 right-0.5 w-4.5 h-4.5 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] transition-colors z-10"
                    title="Xóa tệp này"
                  >
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>

                <!-- Document File Badge -->
                <div v-else class="relative flex-shrink-0">
                  <div class="w-8 h-10 rounded-md border border-amber-200 bg-amber-50 flex flex-col items-center justify-between overflow-hidden">
                    <i :class="[isPdfAttachment(att.name) ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file', 'text-amber-600 text-sm mt-1']"></i>
                    <span class="text-[8px] font-extrabold text-amber-800 bg-amber-200/80 w-full text-center py-0.5 leading-none truncate px-0.5">
                      {{ isPdfAttachment(att.name) ? 'PDF' : getFileExtLabel(att.name) }}
                    </span>
                  </div>
                  <button 
                    type="button" 
                    @click="removeAttachment(aIdx)" 
                    class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-[9px] transition-colors z-10"
                    title="Xóa tệp này"
                  >
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- BOTTOM ROW: Attachment left, Person + Date + Submit right -->
            <div class="flex items-center justify-between gap-2 pt-0.5">

              <!-- LEFT: Attachment button only -->
              <div class="flex items-center gap-1.5">
                <div class="relative inline-flex items-center gap-1">
                  <button type="button" @click="triggerFileInput"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-500 w-9 h-9"
                    title="Đính kèm tệp / ảnh (hoặc Ctrl+V dán ảnh từ bộ nhớ tạm)">
                    <i class="fa-solid fa-paperclip text-sm"></i>
                  </button>
                  <input 
                    type="file" 
                    ref="fileInputRef" 
                    multiple 
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" 
                    class="hidden" 
                    @change="onFileSelected" 
                  />
                </div>
              </div>

              <!-- RIGHT: Person + Date + Submit -->
              <div class="flex items-center gap-2">
                <!-- Person picker toggle button -->
                <div class="relative" ref="personPickerRef">
                  <button type="button" @click="showPersonPicker = !showPersonPicker"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs"
                    :class="newStageTaskAssignee ? 'bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 text-emerald-700 px-3 py-1.5' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 w-9 h-9'"
                    title="Chọn người phụ trách">
                    <i class="fa-regular fa-user text-sm"></i>
                    <span v-if="newStageTaskAssignee">@{{ selectedAssigneeName }}</span>
                  </button>
                  <!-- Person picker dropdown -->
                  <div v-if="showPersonPicker"
                    class="absolute left-0 bottom-full mb-2 z-50 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-1 max-h-52 overflow-y-auto ring-1 ring-black/5">
                    <div
                      class="px-3 py-1 text-[10px] uppercase font-bold text-emerald-600 border-b border-gray-100 mb-1">
                      Chọn người phụ trách</div>
                    <button v-if="newStageTaskAssignee" type="button" @click="clearAssignee(); showPersonPicker = false"
                      class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-rose-50 text-rose-500 transition-colors text-left">
                      <i class="fa-solid fa-xmark text-xs"></i>
                      <span>Bỏ chọn</span>
                    </button>
                    <button v-for="u in users" :key="u.id" type="button"
                      @click="newStageTaskAssignee = String(u.id); showPersonPicker = false"
                      class="w-full px-3 py-1.5 flex items-center gap-2 text-xs font-semibold hover:bg-emerald-50 transition-colors text-left"
                      :class="{ 'bg-emerald-50 text-emerald-800 font-bold': String(u.id) === String(newStageTaskAssignee) }">
                      <img :src="u.avatar || defaultAvatar"
                        class="w-5 h-5 rounded-full object-cover border border-gray-200" />
                      <span class="truncate flex-1">{{ u.name }}</span>
                    </button>
                  </div>
                </div>

                <!-- Date picker button -->
                <div class="relative inline-flex items-center gap-1">
                  <button type="button" @click="showDateTimePicker = !showDateTimePicker"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl text-xs font-bold cursor-pointer transition-colors select-none shadow-3xs"
                    :class="(newStageTaskDueDate || newStageTaskDueTime) ? 'bg-blue-50 hover:bg-blue-100/80 border border-blue-200 text-blue-700 px-3 py-1.5' : 'bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 w-9 h-9'"
                    title="Chọn ngày & giờ">
                    <i class="fa-regular fa-calendar-days text-sm"></i>
                    <span v-if="newStageTaskDueDate || newStageTaskDueTime">{{ tagFormattedDateTime }}</span>
                  </button>
                  <button v-if="newStageTaskDueDate || newStageTaskDueTime" type="button" @click="clearDateTime"
                    class="text-gray-400 hover:text-rose-500 transition-colors cursor-pointer p-0.5 rounded-full"
                    title="Bỏ chọn ngày giờ">
                    <i class="fa-solid fa-xmark text-xs"></i>
                  </button>
                  <!-- Date/time picker popup -->
                  <div v-if="showDateTimePicker"
                    class="absolute left-0 bottom-full mb-2 z-50 w-64 bg-white border border-gray-200 rounded-xl shadow-xl p-3 ring-1 ring-black/5">
                    <div class="text-[10px] uppercase font-bold text-gray-500 mb-2">Chọn ngày & giờ</div>
                    <div class="space-y-2">
                      <div>
                        <label class="text-[10px] font-bold text-gray-500 mb-0.5 block">Giờ</label>
                        <input type="time" v-model="newStageTaskDueTime"
                          class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500" />
                      </div>
                      <div>
                        <label class="text-[10px] font-bold text-gray-500 mb-0.5 block">Ngày</label>
                        <input type="date" v-model="newStageTaskDueDate"
                          class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-800 focus:outline-none focus:border-emerald-500" />
                      </div>
                    </div>
                    <button type="button" @click="showDateTimePicker = false"
                      class="mt-2 w-full px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer">Xong</button>
                  </div>
                </div>

                <!-- Submit button -->
                <button type="submit"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#10b981] hover:bg-emerald-600 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-xs transition-all cursor-pointer">
                  <i class="fa-solid fa-dove text-sm"></i>
                  <span>{{ editingTaskId ? 'Lưu' : 'Hú hú!' }}</span>
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
  <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
    <div v-if="isAddMilestoneOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
      @click.self="isAddMilestoneOpen = false">
      <div class="bg-white rounded-3xl p-6 w-full max-w-md border border-gray-200 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="text-base font-extrabold text-gray-900 font-heading">Thêm chặng tiếp theo</h3>
          <button @click="isAddMilestoneOpen = false" type="button" class="text-gray-400 hover:text-gray-600"><i
              class="fa-solid fa-xmark"></i></button>
        </div>

        <form @submit.prevent="handleAddMilestone" class="space-y-3">
          <input ref="milestoneTitleInputRef" v-model="newMilestone.title" type="text" required autofocus
            placeholder="Tên chặng / cột mốc..."
            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-emerald-500" />
          <div class="flex items-center justify-end gap-2 pt-2">
            <button @click="isAddMilestoneOpen = false" type="button"
              class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Hủy</button>
            <button type="submit"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition-all cursor-pointer">Thêm
              chặng</button>
          </div>
        </form>
      </div>
    </div>
  </transition>

  <!-- Image Lightbox Modal -->
  <div v-if="activePreviewImage"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
    @click="activePreviewImage = null">
    <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl" @click.stop>
      <img :src="activePreviewImage" class="max-w-full max-h-[85vh] object-contain rounded-2xl" />
      <button @click="activePreviewImage = null" type="button"
        class="absolute top-3 right-3 w-9 h-9 bg-slate-900/80 text-white rounded-full flex items-center justify-center cursor-pointer">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
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

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick, watchEffect } from 'vue'
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
  description: ''
})

const openAddMilestoneModal = () => {
  newMilestone.title = ''
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

// "Bắt đầu" virtual start stage
const isStartStageSelected = ref(false)

// Tasks thuộc chặng "Bắt đầu" (không có milestone_id)
const startStageTasks = computed(() => {
  if (!project.value || !project.value.tasks) return []
  return project.value.tasks.filter(t => !t.milestone_id).sort((a, b) => {
    const timeA = a.created_at ? new Date(a.created_at).getTime() : (typeof a.id === 'number' ? a.id : 0)
    const timeB = b.created_at ? new Date(b.created_at).getTime() : (typeof b.id === 'number' ? b.id : 0)
    if (timeA !== timeB) return timeB - timeA
    return (b.id || 0) - (a.id || 0)
  })
})

const selectStartStage = () => {
  if (isStartStageSelected.value) {
    // Deselect
    isStartStageSelected.value = false
    selectedMilestone.value = null
  } else {
    isStartStageSelected.value = true
    selectedMilestone.value = null
    visibleCardCount.value = 15
  }
}

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

// ATTACHMENTS & CLIPBOARD PASTED IMAGES STATE
const attachedFiles = ref([])
const fileInputRef = ref(null)

const triggerFileInput = () => {
  if (fileInputRef.value) fileInputRef.value.click()
}

const addFileToAttachments = (file) => {
  if (!file) return
  const isImage = file.type.startsWith('image/')
  const preview = isImage ? URL.createObjectURL(file) : null
  attachedFiles.value.push({
    file,
    name: file.name,
    type: file.type,
    isImage,
    preview
  })
}

const onFileSelected = (e) => {
  const files = e.target.files
  if (!files || files.length === 0) return
  Array.from(files).forEach(f => addFileToAttachments(f))
  e.target.value = ''
}

const removeAttachment = (index) => {
  const item = attachedFiles.value[index]
  if (item && item.preview && !item.isExisting) {
    URL.revokeObjectURL(item.preview)
  }
  attachedFiles.value.splice(index, 1)
}

const clearAttachedFiles = () => {
  attachedFiles.value.forEach(item => {
    if (item.preview && !item.isExisting) {
      URL.revokeObjectURL(item.preview)
    }
  })
  attachedFiles.value = []
}

const readFileAsDataUrl = (file) => new Promise((resolve) => {
  const reader = new FileReader()
  reader.onload = (e) => resolve(e.target.result)
  reader.readAsDataURL(file)
})

const parseAttachmentsFromTitle = (titleText) => {
  if (!titleText) return []
  const text = String(titleText)
  const items = []
  let match

  const imgRegex = /<img[^>]+src="([^"]*)"[^>]*>/gi
  while ((match = imgRegex.exec(text)) !== null) {
    items.push({
      isExisting: true,
      isImage: true,
      name: 'Ảnh đính kèm',
      preview: match[1],
      dataUrl: match[1],
      file: null
    })
  }

  const fileLinkRegex = /<a[^>]+href="([^"]*)"[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/a>/gi
  while ((match = fileLinkRegex.exec(text)) !== null) {
    const fileName = match[2].trim()
    items.push({
      isExisting: true,
      isImage: false,
      name: fileName,
      preview: null,
      dataUrl: match[1],
      file: null
    })
  }

  const fileSpanRegex = /<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi
  while ((match = fileSpanRegex.exec(text)) !== null) {
    const fileName = match[1].trim()
    if (!items.some(a => !a.isImage && a.name === fileName)) {
      items.push({
        isExisting: true,
        isImage: false,
        name: fileName,
        preview: null,
        dataUrl: null,
        file: null
      })
    }
  }

  return items
}

const buildAttachmentHtml = async (attachments) => {
  let html = ''
  for (const att of attachments) {
    let dataUrl = att.dataUrl
    if (!dataUrl && att.file) {
      dataUrl = await readFileAsDataUrl(att.file)
    }
    if (!dataUrl) continue
    if (att.isImage) {
      html += `<br/><img src="${dataUrl}" class="max-h-56 rounded-xl my-2 border border-gray-200 shadow-2xs block" />`
    } else {
      html += `<br/><a href="${dataUrl}" download="${att.name}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 my-1">📎 Tệp đính kèm: ${att.name}</a>`
    }
  }
  return html
}

const stripAttachmentsFromTitle = (titleText) => {
  if (!titleText) return ''
  return String(titleText)
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<img[^>]*>/gi, '')
    .replace(/<a[^>]*>📎\s*Tệp đính kèm:\s*[^<]*<\/a>/gi, '')
    .replace(/<span[^>]*>📎\s*Tệp đính kèm:\s*[^<]*<\/span>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
}

const onTextareaPaste = (e) => {
  const clipboardData = e.clipboardData || window.clipboardData
  if (!clipboardData || !clipboardData.items) return

  const items = clipboardData.items
  for (let i = 0; i < items.length; i++) {
    if (items[i].type.indexOf('image') !== -1) {
      const file = items[i].getAsFile()
      if (file) {
        const pasteName = `pasted_image_${Date.now()}.png`
        const renamedFile = new File([file], pasteName, { type: file.type })
        addFileToAttachments(renamedFile)
      }
    }
  }
}

// IMAGE LIGHTBOX PREVIEW MODAL STATE & HANDLERS
const previewModalImageUrl = ref(null)

const openImageModal = (url) => {
  if (url) previewModalImageUrl.value = url
}

const closeImageModal = () => {
  previewModalImageUrl.value = null
}

const handleActivityContainerClick = (e) => {
  if (!e || !e.target) return
  const el = e.target

  // Check if clicked on an image preview badge or its child (icon inside the badge)
  const badgeEl = el.closest('[data-img-src]')
  if (badgeEl) {
    e.stopPropagation()
    e.preventDefault()
    try {
      const encodedSrc = badgeEl.getAttribute('data-img-src')
      const src = decodeURIComponent(atob(encodedSrc))
      if (src) openImageModal(src)
    } catch (err) {
      console.warn('Could not decode image src:', err)
    }
    return
  }

  // Fallback: if an actual <img> tag is clicked (e.g. in Hú Hú preview)
  if (el.tagName === 'IMG') {
    const src = el.getAttribute('src')
    if (src && !el.classList.contains('rounded-full')) {
      e.stopPropagation()
      openImageModal(src)
    }
  }
}

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
    clearAttachedFiles()
    // Nếu đang ở chặng Bắt đầu hoặc chưa có chặng nào -> milestone_id = null (Bắt đầu)
    if (isStartStageSelected.value || effectiveMilestones.length === 0) {
      newStageTaskMilestoneId.value = null
    } else if (!newStageTaskMilestoneId.value || isStageCompleted(effectiveMilestones.value.find(m => m.id === newStageTaskMilestoneId.value))) {
      newStageTaskMilestoneId.value = (selectedMilestone.value && !selectedMilestone.value.is_completed)
        ? selectedMilestone.value.id
        : (activeTargetMilestones.value[0]?.id || null)
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
  let text = String(titleText)

  // Collect all raw HTML fragments to protect from escaping
  const protectedBlocks = []
  const protect = (html) => {
    const idx = protectedBlocks.length
    protectedBlocks.push(html)
    return `__BADGE_${idx}__`
  }

  // 1) Replace <img> tags with clickable green image badge
  text = text.replace(/<img[^>]+src="([^"]*)"[^>]*>/gi, (match, src) => {
    let fileName = 'Ảnh đính kèm'
    if (!src.startsWith('data:')) {
      const urlParts = src.split('/')
      const lastPart = urlParts[urlParts.length - 1]?.split('?')[0]
      if (lastPart && lastPart.length > 0) fileName = decodeURIComponent(lastPart)
    }
    const displayName = fileName.length > 22 ? fileName.substring(0, 19) + '...' : fileName
    const encodedSrc = btoa(encodeURIComponent(src))
    return `__ATTACH__<span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs font-bold text-emerald-700 cursor-pointer hover:bg-emerald-100 transition-colors" data-img-src="${encodedSrc}" title="Nhấn để xem ảnh phóng to"><i class="fa-solid fa-image text-emerald-500"></i> ${displayName} <i class="fa-solid fa-eye text-emerald-400 text-[10px]"></i></span>__/ATTACH__`
  })

  // 2) Replace file attachment spans with green file badges
  text = text.replace(/<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi, (match, fileName) => {
    const trimmedName = fileName.trim()
    const displayName = trimmedName.length > 22 ? trimmedName.substring(0, 19) + '...' : trimmedName
    return `__ATTACH__<span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs font-bold text-emerald-700" title="Tệp: ${trimmedName}"><i class="fa-solid fa-paperclip text-emerald-500"></i> ${displayName} <i class="fa-solid fa-download text-emerald-400 text-[10px]"></i></span>__/ATTACH__`
  })

  // 3) Strip all <br/> tags between consecutive attachments, and group them
  // Remove <br> tags that sit right before/between __ATTACH__ markers
  text = text.replace(/(<br\s*\/?>)+\s*(__ATTACH__)/gi, '$2')
  text = text.replace(/(__\/ATTACH__)\s*(<br\s*\/?>)+\s*(__ATTACH__)/gi, '$1 $3')

  // Wrap the entire attachment group in a flex container
  // Find the first __ATTACH__ and last __/ATTACH__ and wrap them
  const firstAttach = text.indexOf('__ATTACH__')
  if (firstAttach !== -1) {
    const lastAttachEnd = text.lastIndexOf('__/ATTACH__') + '__/ATTACH__'.length
    const before = text.substring(0, firstAttach)
    const attachGroup = text.substring(firstAttach, lastAttachEnd)
    const after = text.substring(lastAttachEnd)
    // Clean up markers and wrap in flex container
    const cleanedGroup = attachGroup.replace(/__ATTACH__|__\/ATTACH__/g, '')
    text = before + protect('<div class="flex flex-wrap items-center gap-1.5 mt-1.5">' + cleanedGroup + '</div>') + after
  }

  // 4) Protect remaining <br> tags
  text = text.replace(/<br\s*\/?>/gi, () => protect('<br/>'))

  // 5) HTML-escape the remaining text
  let escaped = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')

  // 6) Format @mentions
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
  escaped = escaped.replace(/@([^\s@,.:;!?()\n]+)(?![^<]*>|[^<>]*<\/span>)/g, '<span class="text-emerald-600 font-extrabold">@$1</span>')

  // 7) Restore all protected HTML blocks
  protectedBlocks.forEach((block, idx) => {
    escaped = escaped.replace(`__BADGE_${idx}__`, block)
  })

  return escaped
}

// Format title text for display: strip attachment HTML (images/files) from content
// Attachments will be rendered separately below via getTaskAttachments()
const formatTitleText = (titleText) => {
  if (!titleText) return ''
  let text = String(titleText)
  
  // Remove <img> tags (they will be shown as attachment icons below)
  text = text.replace(/<img[^>]+>/gi, '')
  
  // Remove file attachment spans, links, and legacy inline badge containers
  text = text.replace(/<div class="flex flex-wrap items-center gap-1\.5 mt-1\.5">[\s\S]*?<\/div>/gi, '')
  text = text.replace(/<span[^>]*>📎\s*Tệp đính kèm:\s*[^<]*<\/span>/gi, '')
  text = text.replace(/<a[^>]*>📎\s*Tệp đính kèm:\s*[^<]*<\/a>/gi, '')
  
  // Remove trailing <br/> tags left after stripping attachments
  text = text.replace(/(<br\s*\/?>)+\s*$/gi, '')
  
  // Now apply the mention formatting
  return formatTitleWithMentions(text)
}

// Extract image and file attachments from task title HTML
const getTaskAttachments = (task) => {
  if (!task || !task.title) return []
  const titleText = String(task.title)
  const attachments = []
  
  // Extract images from <img> tags
  const imgRegex = /<img[^>]+src="([^"]*)"[^>]*>/gi
  let match
  while ((match = imgRegex.exec(titleText)) !== null) {
    const src = match[1]
    let fileName = 'Ảnh đính kèm'
    if (!src.startsWith('data:')) {
      const urlParts = src.split('/')
      const lastPart = urlParts[urlParts.length - 1]?.split('?')[0]
      if (lastPart && lastPart.length > 0) fileName = decodeURIComponent(lastPart)
    }
    const displayName = fileName.length > 22 ? fileName.substring(0, 19) + '...' : fileName
    attachments.push({ type: 'image', src, name: displayName })
  }
  
  // Extract file attachments from <a href="..." download="...">📎 Tệp đính kèm: ...</a>
  const fileLinkRegex = /<a[^>]+href="([^"]*)"[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/a>/gi
  while ((match = fileLinkRegex.exec(titleText)) !== null) {
    const src = match[1]
    const fileName = match[2].trim()
    const displayName = fileName.length > 22 ? fileName.substring(0, 19) + '...' : fileName
    attachments.push({ type: 'file', src, name: displayName, fullName: fileName })
  }

  // Extract old style file attachments from <span>📎 Tệp đính kèm: ...</span>
  const fileSpanRegex = /<span[^>]*>📎\s*Tệp đính kèm:\s*([^<]+)<\/span>/gi
  while ((match = fileSpanRegex.exec(titleText)) !== null) {
    const fileName = match[1].trim()
    const displayName = fileName.length > 22 ? fileName.substring(0, 19) + '...' : fileName
    if (!attachments.some(a => a.type === 'file' && a.name === displayName)) {
      attachments.push({ type: 'file', src: '#', name: displayName })
    }
  }
  
  return attachments
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

  // Replace matching @User Full Name with green text span
  sortedUsers.forEach(u => {
    if (u && u.name) {
      const escapedName = u.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      const reg = new RegExp(`@${escapedName}`, 'gi')
      text = text.replace(reg, `<span class="text-emerald-600">@${u.name}</span>`)
    }
  })

  // Match any generic @mention if user not in list yet
  text = text.replace(/(@[^\s<]+)(?![^<]*>)/g, '<span class="text-emerald-600">$1</span>')

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
  clearAttachedFiles()
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
  if (isTaskInDoneStage(task)) return
  clearAttachedFiles()
  editingTaskId.value = task.id
  attachedFiles.value = parseAttachmentsFromTitle(task.title)
  newStageTaskTitle.value = stripAttachmentsFromTitle(task.title)
  newStageTaskAssignee.value = task.assignee_id ? String(task.assignee_id) : ''
  newStageTaskMilestoneId.value = task.milestone_id || (selectedMilestone.value ? selectedMilestone.value.id : (effectiveMilestones.value[0]?.id || null))

  const parsed = parseDateTimeStrings(task.due_date || task.created_at)
  newStageTaskDueDate.value = parsed.date
  newStageTaskDueTime.value = parsed.time

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

// CARD PAGINATION & LOAD MORE STATE (MAX 15 INITIAL CARDS)
const visibleCardCount = ref(15)
const isLoadingMore = ref(false)
const isViewingAllLoading = ref(false)

const totalCardsForCurrentView = computed(() => {
  if (isStartStageSelected.value) {
    return startStageTasks.value
  }
  if (selectedMilestone.value) {
    return currentStageTasks.value
  }
  return allProjectCards.value
})

// DISPLAYED CARDS: LIMITED TO visibleCardCount (DEFAULT 15)
const displayedCards = computed(() => {
  return totalCardsForCurrentView.value.slice(0, visibleCardCount.value)
})

const hasMoreCards = computed(() => {
  return totalCardsForCurrentView.value.length > displayedCards.value.length
})

const handleLoadMore = () => {
  if (isLoadingMore.value) return
  isLoadingMore.value = true
  setTimeout(() => {
    visibleCardCount.value += 15
    isLoadingMore.value = false
  }, 350)
}

const handleViewAll = () => {
  if (isViewingAllLoading.value) return
  isViewingAllLoading.value = true
  if (selectedMilestone.value) {
    selectedMilestone.value = null
  }
  setTimeout(() => {
    visibleCardCount.value = 999999
    isViewingAllLoading.value = false
  }, 400)
}

// TASK CARD COLOR DEFINITIONS MATCHING DESIGN
const isAssignedHuhuTask = (task) => {
  if (!task) return false
  if (task.assignee_id || task.assignee?.id) return true
  if (task.title && users.value && users.value.length > 0) {
    const match = task.title.match(/@([^\s@,.:;!?()\n<]+)/)
    if (match && match[1]) {
      const mentionName = match[1].toLowerCase()
      return users.value.some(user => user.name && user.name.toLowerCase() === mentionName)
    }
  }
  return false
}

const getTaskCardBgColor = (task) => {
  if (!task) return 'bg-emerald-600'
  if (isAssignedHuhuTask(task)) {
    return 'bg-amber-500'
  }
  if (task.type === 'comment' || task.is_comment) {
    return 'bg-purple-600'
  }
  return 'bg-emerald-600'
}

const getTaskCardIcon = (task) => {
  if (!task) return 'fa-solid fa-shoe-prints'
  if (isAssignedHuhuTask(task)) {
    return 'fa-solid fa-bell'
  }
  if (task.type === 'comment' || task.is_comment) {
    return 'fa-solid fa-comment-dots'
  }
  return 'fa-solid fa-shoe-prints'
}

const getAssigneeAvatar = (task) => {
  if (!task) return null
  if (task.assignee?.avatar) return task.assignee.avatar
  if (task.assignee_id && users.value) {
    const u = users.value.find(user => user.id == task.assignee_id || String(user.id) === String(task.assignee_id))
    if (u?.avatar) return u.avatar
  }
  if (task.title && users.value && users.value.length > 0) {
    const match = task.title.match(/@([^\s@,.:;!?()\n<]+)/)
    if (match && match[1]) {
      const mentionName = match[1].toLowerCase()
      const u = users.value.find(user => user.name && user.name.toLowerCase() === mentionName)
      if (u?.avatar) return u.avatar
    }
  }
  return defaultAvatar
}

const getAssigneeDisplayName = (task) => {
  if (!task) return ''
  if (task.assignee?.name) return task.assignee.name
  if (task.assignee_id && users.value) {
    const u = users.value.find(user => user.id == task.assignee_id || String(user.id) === String(task.assignee_id))
    if (u) return u.name
  }
  if (task.title && users.value && users.value.length > 0) {
    const match = task.title.match(/@([^\s@,.:;!?()\n<]+)/)
    if (match && match[1]) {
      const mentionName = match[1].toLowerCase()
      const u = users.value.find(user => user.name && user.name.toLowerCase() === mentionName)
      if (u) return u.name
    }
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

// Lấy danh sách thành viên tham gia/cập nhật RIÊNG cho từng chặng
const getAvatarsForMilestone = (stage) => {
  if (!stage) return []
  const msId = stage.id
  const userMap = new Map()

  const addTaskUser = (t) => {
    if (!t) return
    if (t.assignee && t.assignee.id) {
      userMap.set(t.assignee.id, t.assignee)
    } else if (t.assignee_id && users.value) {
      const u = users.value.find(user => user.id == t.assignee_id || String(user.id) === String(t.assignee_id))
      if (u) userMap.set(u.id, u)
    }

    if (t.creator && t.creator.id) {
      userMap.set(t.creator.id, t.creator)
    } else if (t.created_by && users.value) {
      const u = users.value.find(user => user.id == t.created_by || String(user.id) === String(t.created_by))
      if (u) userMap.set(u.id, u)
    }

    if (t.title && users.value && users.value.length > 0) {
      const matches = t.title.match(/@([^\s@,.:;!?()\n<]+)/g)
      if (matches) {
        matches.forEach(m => {
          const mentionName = m.replace('@', '').toLowerCase()
          const u = users.value.find(user => user.name && user.name.toLowerCase() === mentionName)
          if (u) userMap.set(u.id, u)
        })
      }
    }
  }

  if (stage.tasks && Array.isArray(stage.tasks)) {
    stage.tasks.forEach(addTaskUser)
  }

  if (project.value && project.value.tasks && Array.isArray(project.value.tasks)) {
    project.value.tasks.forEach(t => {
      if (t && (t.milestone_id == msId || String(t.milestone_id) === String(msId))) {
        addTaskUser(t)
      }
    })
  }

  return Array.from(userMap.values())
}

// REAL MILESTONES DATA DYNAMIC COMPUTATION FROM BACKEND
const effectiveMilestones = computed(() => {
  if (project.value && project.value.milestones && Array.isArray(project.value.milestones) && project.value.milestones.length > 0) {
    return project.value.milestones
  }
  return []
})

// CHỈ LỌC CÁC CHẶNG CHƯA HOÀN THÀNH CHO MỤC TIÊU HƯỚNG ĐẾN KHI TẠO HÚ HÚ MỚI
const activeTargetMilestones = computed(() => {
  return effectiveMilestones.value.filter(ms => !isStageCompleted(ms))
})

const milestoneLayout = computed(() => {
  const all = effectiveMilestones.value
  if (!all || all.length === 0) {
    return { visibleItems: [], pageCount: 1, hills: [], ridgeline: 'M 35 180 Q 540 -25 1040 180', btnPos: '94.55%' }
  }

  const startX = 100
  const maxContainerW = 940 // Available width before "+ Thêm chặng"
  const fixedDoneW = 95 // Compact spacing for completed stages (Image 2)

  // 1. Dynamic Paginate: Group milestones into pages where completed take 95px and active take 240px
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

  // 3. SVG hill paths and ridgeline (Ground level at 170px for flat completed stage alignment)
  let dPath = `M 35 170 L ${startX} 170`
  const hillPaths = []

  items.forEach((item) => {
    const midX = item.startX + item.spanW / 2
    if (item.isDone) {
      dPath += ` L ${item.endX} 170`
      // Chặng đã done: không có nền xám núi
    } else {
      dPath += ` Q ${midX} -25 ${item.endX} 170`
      hillPaths.push({ d: `M ${item.startX} 290 L ${item.startX} 170 Q ${midX} -25 ${item.endX} 170 L ${item.endX} 290 Z` })
    }
  })

  const btnX = Math.min(1040, curX + 12)
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

const milestoneAvatarsMap = computed(() => {
  const map = {}
  const milestones = effectiveMilestones.value
  if (!milestones || milestones.length === 0) return map

  milestones.forEach(ms => {
    map[ms.id] = []
  })

  let latestCompletedIdx = -1
  milestones.forEach((ms, idx) => {
    if (isStageCompleted(ms)) {
      latestCompletedIdx = idx
    }
  })

  const activeStageUsers = new Set()
  milestones.forEach((ms, idx) => {
    if (idx > latestCompletedIdx) {
      getAvatarsForMilestone(ms).forEach(u => {
        activeStageUsers.add(u.id)
      })
    }
  })

  milestones.forEach((ms, idx) => {
    if (idx > latestCompletedIdx) {
      map[ms.id] = getAvatarsForMilestone(ms)
    } else if (idx === latestCompletedIdx) {
      const completedStageUsers = new Map()
      milestones.forEach((cms, cidx) => {
        if (cidx <= latestCompletedIdx) {
          getAvatarsForMilestone(cms).forEach(u => {
            if (!activeStageUsers.has(u.id)) {
              completedStageUsers.set(u.id, u)
            }
          })
        }
      })
      map[ms.id] = Array.from(completedStageUsers.values())
    } else {
      map[ms.id] = []
    }
  })

  return map
})

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
  return stage.is_completed === true || stage.is_completed === 1 || stage.is_completed === '1' || stage.is_completed === 'true'
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

// LOGIC HIỂN THỊ AVATAR LEO NÚI:
// 1. Chỉ chuyển avatar sang chặng chưa hoàn thành mới khi chặng đó thực sự CÓ CẬP NHẬT/CÔNG VIỆC (getStageTaskCount > 0).
// 2. Khi tạo chặng mới chưa có cập nhật, avatar vẫn ở lại chặng hoàn thành gần nhất.
const targetAvatarStageId = computed(() => {
  const milestones = effectiveMilestones.value
  if (!milestones || milestones.length === 0) return null

  // 1. Tìm chỉ số của chặng đã hoàn thành cuối cùng
  let lastCompletedIdx = -1
  for (let i = milestones.length - 1; i >= 0; i--) {
    if (isStageCompleted(milestones[i])) {
      lastCompletedIdx = i
      break
    }
  }

  // 2. Nếu đã có chặng hoàn thành:
  if (lastCompletedIdx !== -1) {
    const remainingMs = milestones.slice(lastCompletedIdx + 1)

    // CHỈ chuyển sang chặng chưa hoàn thành phía sau KHI CHẶNG ĐÓ CÓ CẬP NHẬT/CÔNG VIỆC ("hú hú") > 0
    if (remainingMs.length > 0) {
      const nextWithTasks = remainingMs.find(ms => !isStageCompleted(ms) && getStageTaskCount(ms) > 0)
      if (nextWithTasks) {
        return nextWithTasks.id
      }
    }

    // Nếu chưa có công việc/cập nhật nào ở chặng mới, VẪN Ở LẠI CHẶNG HOÀN THÀNH GẦN NHẤT
    return milestones[lastCompletedIdx].id
  }

  // 3. Nếu chưa có chặng nào hoàn thành trong dự án:
  const uncompletedWithTasks = milestones.find(ms => !isStageCompleted(ms) && getStageTaskCount(ms) > 0)
  if (uncompletedWithTasks) return uncompletedWithTasks.id

  const firstUncompleted = milestones.find(ms => !isStageCompleted(ms))
  if (firstUncompleted) return firstUncompleted.id

  return milestones[0].id
})

const shouldShowAvatarsFor = (stage) => {
  if (!stage || !stage.id) return false
  return String(stage.id) === String(targetAvatarStageId.value)
}

const stageAvatarsList = computed(() => {
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

  // 1. Thu thập người dùng thực sự có hoạt động/cập nhật ở tất cả các chặng trong dự án
  const milestones = effectiveMilestones.value || []
  milestones.forEach(ms => {
    const activeMsUsers = getStageActiveUsers(ms)
    activeMsUsers.forEach(u => {
      if (u && u.id) userMap.set(u.id, u)
    })
  })

  // 2. Thu thập người tạo/phụ trách các hoạt động trong danh sách task của dự án
  if (project.value && project.value.tasks && Array.isArray(project.value.tasks)) {
    project.value.tasks.forEach(t => {
      const creatorId = t.created_by || t.creator_id || t.creator?.id
      addUserByIdOrObj(creatorId, t.creator)

      const assigneeId = t.assignee_id || t.assignee?.id
      addUserByIdOrObj(assigneeId, t.assignee)
    })
  }

  return Array.from(userMap.values())
})

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
  isStartStageSelected.value = false
  visibleCardCount.value = 15
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

const isTaskInDoneStage = (task) => {
  if (!task) return false
  if (selectedMilestone.value && selectedMilestone.value.is_completed) return true
  const msId = task.milestone_id
  if (!msId) return false
  const ms = effectiveMilestones.value.find(m => String(m.id) === String(msId))
  return ms ? isStageCompleted(ms) : false
}

const parseDateTimeStrings = (dateStr) => {
  if (!dateStr) return { date: '', time: '' }
  let str = String(dateStr)
  let datePart = ''
  let timePart = ''

  if (str.includes('T')) {
    const parts = str.split('T')
    datePart = parts[0]
    if (parts[1]) {
      const t = parts[1].split('.')[0]
      const tp = t.split(':')
      if (tp.length >= 2) timePart = `${tp[0].padStart(2, '0')}:${tp[1].padStart(2, '0')}`
    }
  } else if (str.includes(' ')) {
    const parts = str.split(' ')
    datePart = parts[0]
    if (parts[1]) {
      const tp = parts[1].split(':')
      if (tp.length >= 2) timePart = `${tp[0].padStart(2, '0')}:${tp[1].padStart(2, '0')}`
    }
  } else if (str.match(/^\d{4}-\d{2}-\d{2}$/)) {
    datePart = str
  } else {
    const d = new Date(dateStr)
    if (!isNaN(d.getTime())) {
      const yyyy = d.getFullYear()
      const mm = String(d.getMonth() + 1).padStart(2, '0')
      const dd = String(d.getDate()).padStart(2, '0')
      datePart = `${yyyy}-${mm}-${dd}`
      const hh = String(d.getHours()).padStart(2, '0')
      const min = String(d.getMinutes()).padStart(2, '0')
      timePart = `${hh}:${min}`
    }
  }

  return { date: datePart, time: timePart }
}

const formatDueDateTagForCard = (dateStr) => {
  if (!dateStr) return ''
  const d = parseDateObj(dateStr)
  if (!d) return ''
  const hh = d.getHours()
  const mm = String(d.getMinutes()).padStart(2, '0')
  const date = `${d.getDate()}/${d.getMonth() + 1}`
  return `${hh}:${mm} ${date}`
}

const isPdfAttachment = (name) => /\.pdf$/i.test(name || '')

const getFileExtLabel = (name) => {
  if (!name) return 'FILE'
  const ext = name.split('.').pop()?.toUpperCase()
  return ext && ext.length <= 4 ? ext : 'FILE'
}

const closeSelectedStage = () => {
  selectedMilestone.value = null
  isStartStageSelected.value = false
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

const handleDocumentClick = (e) => {
  activeAssigneeDropdownTaskId.value = null
  if (actionMenuDropdownRef.value && e && e.target && !actionMenuDropdownRef.value.contains(e.target)) {
    isActionMenuOpen.value = false
  }
}

// SUBMIT COMPREHENSIVE "CẬP NHẬT HOẠT ĐỘNG"
const handleAddStageTaskSubmit = async () => {
  if (!newStageTaskTitle.value.trim() && attachedFiles.value.length === 0) return
  const pId = projectId.value
  const msId = newStageTaskMilestoneId.value || (selectedTargetMilestoneId.value && !selectedMilestone.value?.is_completed ? selectedTargetMilestoneId.value : null) || (activeTargetMilestones.value[0]?.id || null)

  let titleText = newStageTaskTitle.value.trim()

  if (attachedFiles.value.length > 0) {
    titleText += await buildAttachmentHtml(attachedFiles.value)
  }
  clearAttachedFiles()

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
  } catch (err) { }
}

const fetchComments = async () => {
  const pId = projectId.value
  if (!pId) return
  try {
    const res = await axios.get('/api/comments', { params: { project_id: pId } })
    activityLogs.value = res.data
  } catch (err) { }
}

const fetchCustomers = async () => {
  try {
    const res = await axios.get('/api/customers')
    customers.value = res.data.customers || res.data
  } catch (err) { }
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
      due_date: null,
      is_completed: false,
      created_by: currentUserId,
      user_id: currentUserId
    })
    toast.success('Đã thêm chặng mới thành công!')
    newMilestone.title = ''
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
        due_date: null,
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
const parseDateObj = (dateStr) => {
  if (!dateStr) return null
  if (dateStr instanceof Date) return dateStr

  const str = String(dateStr).trim()
  if (!str) return null

  // 1. ISO string with T (e.g., "2026-08-12T05:42:00.000000Z")
  if (str.includes('T')) {
    const d = new Date(str)
    if (!isNaN(d.getTime())) return d
  }

  // 2. MySQL format "YYYY-MM-DD HH:mm:ss" or "YYYY-MM-DD HH:mm"
  if (str.includes(' ')) {
    if (str.endsWith('Z') || str.includes('+')) {
      const d = new Date(str)
      if (!isNaN(d.getTime())) return d
    }
    // Convert to ISO UTC string "YYYY-MM-DDTHH:mm:ssZ"
    const utcIso = str.replace(' ', 'T') + 'Z'
    const utcD = new Date(utcIso)
    if (!isNaN(utcD.getTime())) return utcD
  }

  const d = new Date(str)
  return isNaN(d.getTime()) ? null : d
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = parseDateObj(dateStr)
  if (!d) return ''
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
}

const formatDateShort = (dateStr) => {
  if (!dateStr) return ''
  const d = parseDateObj(dateStr)
  if (!d) return ''
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`
}

const formatTimeOnly = (dateStr) => {
  if (!dateStr) return ''
  const d = parseDateObj(dateStr)
  if (!d) return ''
  const hh = String(d.getHours()).padStart(2, '0')
  const mm = String(d.getMinutes()).padStart(2, '0')
  return `${hh}:${mm}`
}



const handleKeydown = (e) => {
  if (e.key === 'Escape' || e.code === 'Escape') {
    if (previewModalImageUrl.value) {
      previewModalImageUrl.value = null
      return
    }
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

  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-5px);
  }
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
