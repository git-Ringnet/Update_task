import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import router from './router'
import App from './App.vue'
import './style.css'

// Cấu hình Base URL cho Axios (Mặc định rỗng để đường dẫn /api/... không bị lặp thành /api/api/...)
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || ''

// Thiết lập Authorization header từ localStorage khi tải ứng dụng
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Import auth store after Pinia is registered
import { useAuthStore } from './stores/auth'

// Global Axios response interceptor to handle 401 Unauthorized errors
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const authStore = useAuthStore()
      authStore.clearAuth()
      router.push({ name: 'login' })
    }
    return Promise.reject(error)
  }
)

app.mount('#app')
