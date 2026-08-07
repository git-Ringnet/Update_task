<template>
  <div class="min-h-screen bg-[#f8faf9] pb-24">
    <!-- Navbar Component -->
    <Navbar />

    <!-- Full-page loading spinner -->
    <div v-if="!project"
      class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-24 flex flex-col items-center justify-center gap-4">
      <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <span class="text-gray-500 font-semibold text-sm">Đang tải chi tiết dự án...</span>
    </div>

    <!-- Real Content -->
    <main v-else class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <!-- Top Back Link -->
      <div>
        <button @click="goBack" type="button"
          class="inline-flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-emerald-700 transition-colors cursor-pointer select-none focus:outline-none">
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Quay lại danh sách dự án</span>
        </button>
      </div>

      <!-- Top Header Row: Project Title & Health Badge (Left) | Action Button Cards (Right) -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

        <!-- Left: Project Title & Health Status Badge -->
        <div class="flex items-center gap-3.5 flex-wrap min-w-0 flex-1">
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight font-heading break-all min-w-0">
            {{ project.title }}
          </h1>

          <!-- Health Pill Badge -->
          <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider shadow-3xs flex-shrink-0"
            :class="healthBadgeClass(project.health)">
            {{ healthLabelText(project.health) }}
          </span>
        </div>

        <!-- Right: Action Cards (Cập nhật dự án | Mời thành viên) -->
        <div class="flex items-center gap-3.5">
          <!-- Cập nhật dự án Card -->
          <button @click="handleEditProject" type="button"
            class="bg-white border border-gray-200 hover:border-amber-300 hover:bg-amber-50/40 rounded-2xl px-5 py-3.5 flex items-center gap-4 shadow-3xs cursor-pointer transition-all hover:scale-[1.01] select-none">
            <span class="w-8 h-8 rounded-xl bg-amber-100/70 text-amber-700 flex items-center justify-center text-sm">
              <i class="fa-solid fa-pencil"></i>
            </span>
            <span class="text-sm font-extrabold text-gray-900">Cập nhật dự án</span>
            <i class="fa-solid fa-chevron-right text-xs text-gray-400 ml-1"></i>
          </button>

          <!-- Mời thành viên Card -->
          <button @click="openInviteModal" type="button"
            class="bg-white border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40 rounded-2xl px-5 py-3.5 flex items-center gap-4 shadow-3xs cursor-pointer transition-all hover:scale-[1.01] select-none">
            <span class="w-8 h-8 rounded-xl bg-purple-100/70 text-purple-700 flex items-center justify-center text-sm">
              <i class="fa-solid fa-user-plus"></i>
            </span>
            <span class="text-sm font-extrabold text-gray-900">Mời thành viên</span>
            <i class="fa-solid fa-chevron-right text-xs text-gray-400 ml-1"></i>
          </button>
        </div>

      </div>

      <!-- Metadata Row (4 Cards): KHÁCH HÀNG | LEAD | TEAM | BẮT ĐẦU -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Customer Card -->
        <div
          class="bg-white border border-gray-200/80 rounded-2xl p-4 flex items-center gap-3.5 shadow-3xs select-none">
          <span
            class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-lg flex-shrink-0">
            <i class="fa-solid fa-bullseye"></i>
          </span>
          <div class="min-w-0">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">KHÁCH HÀNG</div>
            <div class="text-sm sm:text-base font-extrabold text-gray-900 truncate mt-0.5">
              {{ project.customer ? project.customer.name : 'Chưa phân khách hàng' }}
            </div>
          </div>
        </div>

        <!-- Lead Card (With inline lead dropdown trigger) -->
        <div
          class="bg-white border border-gray-200/80 rounded-2xl p-4 flex items-center gap-3.5 shadow-3xs cursor-pointer hover:bg-emerald-50/20 transition-colors relative select-none"
          ref="leadDropdownRef" @click.stop="toggleLeadSelect">
          <span
            class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg flex-shrink-0">
            <i class="fa-solid fa-user"></i>
          </span>
          <div class="min-w-0 flex-1">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">LEAD</div>
            <div class="text-sm sm:text-base font-extrabold text-gray-900 truncate mt-0.5 flex items-center gap-1.5">
              <span>{{ project.lead ? project.lead.name : 'Chưa giao' }}</span>
              <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
            </div>
          </div>

          <!-- Lead dropdown menu -->
          <div v-if="isLeadSelectOpen"
            class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50 py-1.5 text-left ring-1 ring-black/5 max-h-48 overflow-y-auto">
            <div
              class="px-3 py-1 text-[9px] font-bold text-gray-400 uppercase tracking-wider select-none border-b border-gray-100 mb-1">
              Chuyển Lead Dự Án
            </div>
            <button v-for="u in users" :key="u.id" @click.stop="updateLead(u.id)" type="button"
              class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-gray-700 hover:text-emerald-900 text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer">
              <img
                :src="u.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                class="w-5 h-5 rounded-full object-cover border border-emerald-100" />
              <span class="truncate flex-1">{{ u.name }}</span>
              <i v-if="project.lead && project.lead.id === u.id"
                class="fa-solid fa-check text-[10px] text-emerald-600"></i>
            </button>
          </div>
        </div>

        <!-- Team Card (Click to open invite modal) -->
        <div
          @click="openInviteModal"
          class="bg-white border border-gray-200/80 rounded-2xl p-4 flex items-center gap-3.5 shadow-3xs cursor-pointer hover:bg-blue-50/20 transition-colors select-none">
          <span
            class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg flex-shrink-0">
            <i class="fa-solid fa-users"></i>
          </span>
          <div class="min-w-0 flex-1">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">TEAM</div>
            <div class="text-sm sm:text-base font-extrabold text-gray-900 truncate mt-0.5 flex items-center gap-1.5">
              <span>{{ projectTeamMembers.length }} người</span>
              <i class="fa-solid fa-user-plus text-xs text-blue-400"></i>
            </div>
          </div>
        </div>

        <!-- Started Date Card -->
        <div
          class="bg-white border border-gray-200/80 rounded-2xl p-4 flex items-center gap-3.5 shadow-3xs select-none">
          <span
            class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg flex-shrink-0">
            <i class="fa-solid fa-calendar-days"></i>
          </span>
          <div class="min-w-0">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">BẮT ĐẦU</div>
            <div class="text-sm sm:text-base font-extrabold text-gray-900 truncate mt-0.5">
              {{ formatDate(project.created_at) }}
            </div>
          </div>
        </div>

      </div>

      <!-- MAIN CONTENT 2-COLUMN GRID (Left: HOẠT ĐỘNG | Right: CỘT MỐC + CÔNG VIỆC) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- LEFT COLUMN: HOẠT ĐỘNG -->
        <div class="space-y-6">

          <!-- CARD: HOẠT ĐỘNG -->
          <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-3xs space-y-4 h-[800px] overflow-y-auto">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-rose-500 text-sm"></i>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">HOẠT ĐỘNG</h3>
              </div>
            </div>

            <!-- Quick Comment Form with File & Image Attachments -->
            <form @submit.prevent="handleQuickUpdate" class="space-y-2">
              <input v-model="updateContentText" type="text" placeholder="Gõ cập nhật nhanh... (Enter để lưu)"
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-colors" />
              
              <!-- Attachment Controls -->
              <div class="flex items-center gap-2">
                <input 
                  id="detail-img-input"
                  type="file" 
                  accept="image/*" 
                  multiple 
                  class="hidden" 
                  @change="handleDetailFileSelect($event, true)"
                />
                <label 
                  for="detail-img-input"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg text-[11px] font-bold cursor-pointer transition-colors border border-emerald-200/60 select-none shadow-3xs"
                >
                  <i class="fa-solid fa-image text-emerald-600"></i>
                  <span>Ảnh</span>
                </label>

                <input 
                  id="detail-file-input"
                  type="file" 
                  accept="*" 
                  multiple 
                  class="hidden" 
                  @change="handleDetailFileSelect($event, false)"
                />
                <label 
                  for="detail-file-input"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold cursor-pointer transition-colors border border-slate-200 select-none shadow-3xs"
                >
                  <i class="fa-solid fa-paperclip text-slate-500"></i>
                  <span>File</span>
                </label>
              </div>

              <!-- Attached Files Preview Grid -->
              <div v-if="detailAttachedFiles.length > 0" class="flex items-center gap-2 flex-wrap pt-1">
                <div v-for="(file, fIdx) in detailAttachedFiles" :key="fIdx" class="relative group">
                  <div v-if="file.isImage" class="relative w-12 h-12 rounded-lg overflow-hidden border border-gray-200 shadow-3xs">
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

            <!-- Activities Feed Items with Parsed Compact Attachments -->
            <div class="space-y-3.5">
              <div v-for="log in displayedComments" :key="log.id" class="flex items-start gap-3">
                <img
                  :src="log.user ? (log.user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120') : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'"
                  class="w-7 h-7 rounded-full object-cover border border-gray-100 flex-shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                  <div class="text-xs text-gray-700 leading-normal">
                    <strong class="font-black text-gray-900">{{ log.user ? log.user.name : 'Hệ thống' }}</strong>
                    <span v-if="parseCommentText(log.content)" class="ml-1.5 font-medium text-gray-700 whitespace-pre-line">{{ parseCommentText(log.content) }}</span>
                  </div>

                  <!-- Render Compact Image Pills -->
                  <div v-if="parseCommentImages(log.content).length > 0" class="flex flex-wrap gap-1.5 mt-1.5">
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
                  <div v-if="parseCommentFiles(log.content).length > 0" class="flex flex-wrap gap-1.5 mt-1.5">
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

                  <span class="text-[10px] font-bold text-gray-400 mt-1 block">{{ formatRelativeTime(log.created_at) }}</span>
                </div>
              </div>

              <!-- Empty Activity Feed State -->
              <div v-if="activityLogs.length === 0" class="py-6 text-center text-gray-400 text-xs font-semibold">
                Chưa có cập nhật hoạt động nào.
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN: CỘT MỐC + CÔNG VIỆC -->
        <div class="space-y-6">

          <!-- CARD: CỘT MỐC -->
          <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-3xs space-y-4 h-[387px] overflow-y-auto">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-flag text-emerald-600 text-sm"></i>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">CỘT MỐC</h3>
              </div>
              <button @click="isAddMilestoneOpen = !isAddMilestoneOpen" type="button"
                class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1">
                <span>+ Thêm mốc</span>
              </button>
            </div>

            <!-- Add Milestone Inline Form -->
            <form v-if="isAddMilestoneOpen" @submit.prevent="handleAddMilestone"
              class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-2 animate-fade-in-up">
              <input v-model="newMilestone.title" type="text" required placeholder="Tên cột mốc..."
                class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs font-semibold focus:outline-none focus:border-emerald-500" />
              <input v-model="newMilestone.due_date" type="date"
                class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs font-semibold focus:outline-none focus:border-emerald-500" />
              <div class="flex items-center justify-end gap-2 pt-1">
                <button @click="isAddMilestoneOpen = false" type="button"
                  class="px-2 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700">Hủy</button>
                <button type="submit"
                  class="px-3 py-1 bg-[#10b981] hover:bg-emerald-600 text-white text-xs font-bold rounded-lg shadow-3xs">Lưu</button>
              </div>
            </form>

            <!-- Milestones List Timeline -->
            <div v-if="project.milestones && project.milestones.length > 0"
              class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-emerald-600/80">
              <div v-for="ms in project.milestones" :key="ms.id"
                class="relative flex items-start justify-between gap-2">
                <!-- Marker -->
                <span
                  class="absolute -left-6.5 top-0.5 w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[8px] ring-4 ring-white shadow-3xs flex-shrink-0">
                  <i class="fa-solid fa-check"></i>
                </span>

                <div class="min-w-0 flex-1 group/ms">
                  <div class="flex items-start gap-2 justify-between">
                    <div class="font-extrabold text-gray-900 text-xs leading-normal break-words min-w-0 flex-1">
                      {{ ms.title }}
                    </div>
                    <button @click.stop="handleDeleteMilestone(ms.id)"
                      class="opacity-0 group-hover/ms:opacity-100 transition-opacity text-gray-400 hover:text-rose-600 p-0.5 cursor-pointer flex-shrink-0">
                      <i class="fa-solid fa-trash-can text-[9px]"></i>
                    </button>
                  </div>
                  <div class="text-[9px] text-gray-400 font-bold mt-0.5">
                    Hạn: {{ ms.due_date ? formatDate(ms.due_date) : 'Chưa đặt hạn' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Dotted Empty Milestones State (Matches Mockup) -->
            <div v-else
              class="border-2 border-dashed border-gray-200/90 rounded-2xl p-8 text-center space-y-1.5 select-none bg-gray-50/30">
              <div
                class="w-10 h-10 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-lg mb-1">
                <i class="fa-regular fa-flag"></i>
              </div>
              <div class="text-sm font-extrabold text-gray-900">Chưa có cột mốc nào</div>
              <div class="text-xs text-gray-500 font-medium">Tạo cột mốc để theo dõi tiến độ dự án</div>
            </div>
          </div>

          <!-- CARD: CÔNG VIỆC -->
          <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-3xs space-y-4 h-[387px] overflow-y-auto">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-square-check text-emerald-600 text-sm"></i>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider">CÔNG VIỆC</h3>
              </div>
              <button @click="isAddTaskOpen = !isAddTaskOpen" type="button"
                class="text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1">
                <span>+ Thêm công việc</span>
              </button>
            </div>

            <!-- Add Task Inline Form -->
            <form v-if="isAddTaskOpen" @submit.prevent="handleAddTask"
              class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-2 animate-fade-in-up">
              <input v-model="newTask.title" type="text" required placeholder="Tên công việc..."
                class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs font-semibold focus:outline-none focus:border-emerald-500" />
              <select v-model="newTask.assignee_id"
                class="w-full px-2.5 py-1.5 border border-gray-200 bg-white rounded-lg text-xs font-semibold focus:outline-none focus:border-emerald-500">
                <option value="">-- Giao cho ai --</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
              <div class="flex items-center justify-end gap-2 pt-1">
                <button @click="isAddTaskOpen = false" type="button"
                  class="px-2 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700">Hủy</button>
                <button type="submit"
                  class="px-3 py-1 bg-[#10b981] hover:bg-emerald-600 text-white text-xs font-bold rounded-lg shadow-3xs">Lưu</button>
              </div>
            </form>

            <!-- Tasks List -->
            <div v-if="project.tasks && project.tasks.length > 0" class="space-y-3">
              <div v-for="task in displayedTasks" :key="task.id"
                class="flex items-start gap-3 hover:bg-gray-50/50 p-2 rounded-xl transition-colors group/task border border-gray-100">
                <!-- Task checkbox -->
                <input type="checkbox" :checked="task.status === 'done'" @change="toggleTaskSelect(task)"
                  class="w-4.5 h-4.5 rounded text-emerald-600 accent-emerald-600 border-gray-300 focus:ring-emerald-500 cursor-pointer mt-0.5 flex-shrink-0" />

                <div class="flex-1 min-w-0">
                  <span class="text-xs font-bold leading-normal break-words"
                    :class="task.status === 'done' ? 'text-gray-400 line-through' : 'text-gray-900'">
                    {{ task.title }}
                  </span>

                  <div class="flex items-center gap-2 mt-1">
                    <span v-if="task.assignee"
                      class="text-[9px] text-gray-400 font-bold bg-gray-100 px-1.5 py-0.5 rounded-md flex items-center gap-1">
                      <img :src="task.assignee.avatar" class="w-3 h-3 rounded-full object-cover" />
                      <span>{{ task.assignee.name }}</span>
                    </span>
                    <span v-if="task.due_date" class="text-[9px] text-gray-400 font-bold">
                      Hạn: {{ formatDate(task.due_date) }}
                    </span>
                  </div>
                </div>

                <!-- Hover Controls -->
                <div
                  class="opacity-0 group-hover/task:opacity-100 transition-opacity flex items-center gap-1.5 flex-shrink-0">
                  <button @click.stop="handleDeleteTask(task.id)"
                    class="text-gray-400 hover:text-rose-600 p-0.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Dotted Empty Tasks State (Matches Mockup) -->
            <div v-else
              class="border-2 border-dashed border-gray-200/90 rounded-2xl p-14 text-center space-y-2 select-none bg-gray-50/30">
              <div
                class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50/70 text-emerald-600 flex items-center justify-center text-3xl mb-2 shadow-3xs">
                <i class="fa-regular fa-clipboard"></i>
              </div>
              <div class="text-sm font-extrabold text-gray-900">Chưa có công việc nào</div>
              <div class="text-xs text-gray-500 font-medium">Thêm công việc đầu tiên để bắt đầu</div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Project Modal for editing -->
    <ProjectModal :is-open="isModalOpen" :customers="customers" :users="users" :edit-project="project"
      @close="handleCloseModal" @submit="handleUpdateProjectSubmit" @customer-created="fetchCustomers" />

    <!-- Floating Bulk Task Completion Bar -->
    <transition enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-8 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-8 opacity-0">
      <div v-if="selectedTaskIds.length > 0"
        class="fixed bottom-20 left-1/2 -translate-x-1/2 z-40 bg-white/95 backdrop-blur-md px-6 py-4 rounded-2xl shadow-xl border border-emerald-100/80 flex items-center gap-6 w-[90%] max-w-md justify-between">
        <span class="text-sm font-semibold text-emerald-800">
          Đã chọn <strong class="text-emerald-950 font-bold">{{ selectedTaskIds.length }}</strong> công việc
        </span>
        <div class="flex items-center gap-3">
          <button @click="selectedTaskIds = []" type="button"
            class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl transition-colors cursor-pointer focus:outline-none">
            Hủy chọn
          </button>
          <button @click="goToBulkTaskComplete" type="button"
            class="px-4 py-2 bg-[#2d8a39] hover:bg-[#236e2d] text-white font-semibold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer focus:outline-none">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Cập nhật</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- Invite Member Modal -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="isInviteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs select-none" @click.self="isInviteModalOpen = false">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md border border-gray-200 shadow-2xl space-y-5 animate-fade-in-up">
          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-purple-100/80 text-purple-700 flex items-center justify-center text-lg shadow-3xs">
                <i class="fa-solid fa-user-plus"></i>
              </div>
              <div>
                <h3 class="text-base font-extrabold text-gray-900">Mời thành viên vào team</h3>
                <p class="text-xs text-gray-500 font-semibold mt-0.5">Chọn người dùng để thêm vào dự án này</p>
              </div>
            </div>
            <button @click="isInviteModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
              <i class="fa-solid fa-xmark text-base"></i>
            </button>
          </div>

          <!-- Users List with Checkboxes -->
          <div class="space-y-2 max-h-64 overflow-y-auto pr-1 scrollbar-thin">
            <div
              v-for="u in users"
              :key="u.id"
              @click="toggleUserForTeam(u.id)"
              class="flex items-center justify-between p-3 rounded-2xl border transition-all cursor-pointer select-none"
              :class="selectedUserIdsForTeam.includes(u.id) ? 'bg-purple-50/60 border-purple-200 shadow-3xs' : 'bg-gray-50/50 border-gray-100 hover:bg-gray-100/50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <img :src="u.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120'" class="w-8 h-8 rounded-full object-cover border border-purple-100 flex-shrink-0" />
                <div class="min-w-0">
                  <div class="text-xs font-extrabold text-gray-900 truncate">{{ u.name }}</div>
                  <div class="text-[10px] text-gray-500 font-bold truncate">{{ u.email || 'Thành viên dự án' }}</div>
                </div>
              </div>

              <div class="w-5 h-5 rounded-lg border flex items-center justify-center transition-colors flex-shrink-0"
                :class="selectedUserIdsForTeam.includes(u.id) ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-300 bg-white'"
              >
                <i v-if="selectedUserIdsForTeam.includes(u.id)" class="fa-solid fa-check text-[10px]"></i>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
            <button @click="isInviteModalOpen = false" type="button" class="px-4 py-2.5 text-xs font-bold text-gray-500 hover:text-gray-700 transition-colors cursor-pointer">
              Hủy
            </button>
            <button @click="handleSaveTeamMembers" type="button" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-extrabold rounded-xl shadow-3xs transition-colors flex items-center gap-2 cursor-pointer">
              <i class="fa-solid fa-user-check"></i>
              <span>Cập nhật team ({{ selectedUserIdsForTeam.length }})</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

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
import { ref, reactive, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '../components/Navbar.vue'

import ProjectModal from '../components/ProjectModal.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { useConfirmStore } from '../stores/confirm'

const authStore = useAuthStore()
const router = useRouter()
const goBack = () => {
  if (window.history.state && window.history.state.back) {
    router.back()
  } else {
    router.push('/views')
  }
}
const toast = useToastStore()
const confirmStore = useConfirmStore()

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

const selectedTaskIds = ref([])

const tasksLimit = ref(10)
const commentsLimit = ref(10)

const displayedTasks = computed(() => {
  if (!project.value || !project.value.tasks) return []
  return project.value.tasks.slice(0, tasksLimit.value)
})

const displayedComments = computed(() => {
  return activityLogs.value.slice(0, commentsLimit.value)
})

// Team management states
const isInviteModalOpen = ref(false)
const projectTeamMembers = ref([])
const selectedUserIdsForTeam = ref([])

const loadProjectTeam = () => {
  const saved = localStorage.getItem(`project_team_${projectId}`)
  if (saved) {
    try {
      projectTeamMembers.value = JSON.parse(saved)
      return
    } catch (e) {}
  }
  if (project.value && project.value.lead_id) {
    projectTeamMembers.value = [project.value.lead_id]
  } else {
    projectTeamMembers.value = [1]
  }
}

const openInviteModal = () => {
  if (projectTeamMembers.value.length === 0) {
    loadProjectTeam()
  }
  selectedUserIdsForTeam.value = [...projectTeamMembers.value]
  isInviteModalOpen.value = true
}

const toggleUserForTeam = (userId) => {
  const idx = selectedUserIdsForTeam.value.indexOf(userId)
  if (idx > -1) {
    selectedUserIdsForTeam.value.splice(idx, 1)
  } else {
    selectedUserIdsForTeam.value.push(userId)
  }
}

const handleSaveTeamMembers = () => {
  if (selectedUserIdsForTeam.value.length === 0) {
    toast.error('Vui lòng chọn ít nhất 1 thành viên cho team!')
    return
  }
  projectTeamMembers.value = [...selectedUserIdsForTeam.value]
  localStorage.setItem(`project_team_${projectId}`, JSON.stringify(projectTeamMembers.value))
  toast.success(`Đã cập nhật team! Hiện có ${projectTeamMembers.value.length} thành viên.`)
  isInviteModalOpen.value = false
}

// Load data
const fetchProjectDetail = async () => {
  tasksLimit.value = 10
  commentsLimit.value = 10
  try {
    const res = await axios.get(`/api/projects/${projectId}`)
    if (res.data) {
      project.value = res.data
      loadProjectTeam()
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

const detailAttachedFiles = ref([])
const activePreviewImage = ref(null)

const openImagePreview = (url) => {
  activePreviewImage.value = url
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

const handleDetailFileSelect = async (event, isImageOnly = false) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  for (const file of Array.from(files)) {
    const isImg = file.type.startsWith('image/')
    let fileUrl = ''
    if (isImg) {
      fileUrl = await compressImage(file)
    } else {
      fileUrl = await new Promise((resolve) => {
        const reader = new FileReader()
        reader.onload = (e) => resolve(e.target.result)
        reader.readAsDataURL(file)
      })
    }

    detailAttachedFiles.value.push({
      name: file.name,
      size: file.size,
      type: file.type,
      url: fileUrl,
      isImage: isImg
    })
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
    const fileMarkdown = files.map(f => {
      if (f.isImage) {
        return `![${f.name}](${f.url})`
      } else {
        return `📎 [${f.name}](${f.url})`
      }
    }).join('\n')
    
    finalContent = finalContent ? `${finalContent}\n\n${fileMarkdown}` : fileMarkdown
  }

  updateContentText.value = ''
  detailAttachedFiles.value = []

  try {
    await axios.post('/api/comments', {
      project_id: projectId,
      user_id: authStore.user?.id || 3,
      content: finalContent,
      type: 'comment'
    })
    toast.success('Gửi cập nhật hoạt động thành công!')
    await fetchComments()
  } catch (err) {
    console.error('Failed to post comment:', err)
    toast.error('Gửi cập nhật hoạt động thất bại!')
  }
}

const toggleTaskSelect = async (task) => {
  if (task.status === 'done') {
    // Unchecking a completed task -> immediately set to todo on server
    try {
      await axios.patch(`/api/tasks/${task.id}/status`, {
        status: 'todo',
        user_id: authStore.user?.id || 3
      })
      toast.success('Đã mở lại công việc!')
      await fetchProjectDetail()
      await fetchComments()
    } catch (err) {
      console.error('Failed to toggle task status:', err)
      toast.error('Cập nhật trạng thái công việc thất bại!')
      await fetchProjectDetail()
    }
  } else {
    // Toggle selection for bulk complete
    const idx = selectedTaskIds.value.indexOf(task.id)
    if (idx > -1) {
      selectedTaskIds.value.splice(idx, 1)
    } else {
      selectedTaskIds.value.push(task.id)
    }
  }
}

const goToBulkTaskComplete = () => {
  if (selectedTaskIds.value.length === 0) return
  router.push({
    path: '/tasks/complete',
    query: { ids: selectedTaskIds.value.join(',') }
  })
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
  const confirmed = await confirmStore.show({
    title: 'Xóa cột mốc',
    message: 'Bạn có chắc chắn muốn xóa cột mốc này?'
  })
  if (!confirmed) return
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
  const confirmed = await confirmStore.show({
    title: 'Xóa công việc',
    message: 'Bạn có chắc chắn muốn xóa công việc này?'
  })
  if (!confirmed) return
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
  const confirmed = await confirmStore.show({
    title: 'Xóa dự án',
    message: `Bạn có chắc chắn muốn xóa dự án "${project.value.title}" không?`
  })
  if (!confirmed) return
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
const healthBadgeClass = (status) => {
  if (status === 'red') return 'bg-[#fff1f2] text-rose-800 border border-rose-200'
  if (status === 'green') return 'bg-[#ecfdf5] text-emerald-800 border border-emerald-200'
  return 'bg-[#fffbeb] text-amber-800 border border-amber-200'
}

const healthLabelText = (status) => {
  if (status === 'green') return 'Hoàn thành'
  if (status === 'red') return 'Bỏ theo'
  return 'Đang theo'
}

const statusDotClass = (status) => {
  if (status === 'yellow') return 'bg-amber-400 health-dot-yellow'
  if (status === 'red') return 'bg-rose-500 health-dot-red'
  if (status === 'green') return 'bg-emerald-500 health-dot-green'
  return 'bg-gray-400'
}

const healthLabel = (status) => {
  if (status === 'yellow') return 'AT RISK'
  if (status === 'red') return 'NEEDS CARE'
  if (status === 'green') return 'GREAT!'
  return 'UNKNOWN'
}

const healthDescription = (status) => {
  if (status === 'yellow') return 'Some items need attention.'
  if (status === 'red') return 'Urgent action required.'
  if (status === 'green') return 'Everyone\'s happy and things are moving.'
  return 'No status available.'
}

const healthCardClass = (status) => {
  if (status === 'yellow') return 'bg-[#fffbeb] border-amber-250/80 text-amber-900'
  if (status === 'red') return 'bg-[#fff5f5] border-rose-250/80 text-rose-900'
  if (status === 'green') return 'bg-[#eef8f0] border-emerald-250/85 text-emerald-900'
  return 'bg-gray-50 border-gray-250 text-gray-600'
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
