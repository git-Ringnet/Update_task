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

app.use(createPinia())
app.use(router)

app.mount('#app')
