<template>
  <div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-100 shadow-lg py-2 px-4">
    <div class="max-w-md mx-auto flex items-center justify-around">
      
      <!-- Dự án -->
      <router-link
        to="/projects"
        class="flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all"
        :class="$route.path.startsWith('/projects') || $route.path === '/'
          ? 'bg-emerald-50 text-emerald-700 font-bold'
          : 'text-gray-500 hover:text-gray-800'"
      >
        <i class="fa-regular fa-rectangle-list text-lg"></i>
        <span class="text-xs font-semibold">Dự án</span>
      </router-link>

      <!-- Mối quan hệ -->
      <router-link
        to="/customers"
        class="flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all"
        :class="$route.path.startsWith('/customers')
          ? 'bg-emerald-50 text-emerald-700 font-bold'
          : 'text-gray-500 hover:text-gray-800'"
      >
        <i class="fa-solid fa-users text-lg"></i>
        <span class="text-xs font-semibold">Mối quan hệ</span>
      </router-link>

      <!-- Có gì mới -->
      <router-link
        to="/feed"
        class="flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all"
        :class="$route.path.startsWith('/feed')
          ? 'bg-emerald-50 text-emerald-700 font-bold'
          : 'text-gray-500 hover:text-gray-800'"
      >
        <i class="fa-solid fa-bell text-lg"></i>
        <span class="text-xs font-semibold">Có gì mới</span>
      </router-link>

      <!-- Công việc -->
      <router-link
        to="/tasks"
        class="flex flex-col items-center gap-1 px-4 py-1.5 rounded-2xl transition-all"
        :class="$route.path.startsWith('/tasks')
          ? 'bg-emerald-50 text-emerald-700 font-bold'
          : 'text-gray-500 hover:text-gray-800'"
      >
        <i class="fa-regular fa-circle-check text-lg"></i>
        <span class="text-xs font-semibold">Công việc</span>
      </router-link>

    </div>
  </div>
</template>
