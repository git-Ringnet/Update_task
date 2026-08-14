import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    isLoading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setAuth(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      
      // Set axios header
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    clearAuth() {
      this.user = null
      this.token = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    },

    async login(username, password) {
      this.isLoading = true
      try {
        const res = await axios.post('/api/login', { username, password })
        this.setAuth(res.data.user, res.data.token)
        return res.data.user
      } catch (err) {
        this.clearAuth()
        throw err.response?.data?.message || 'Đăng nhập thất bại'
      } finally {
        this.isLoading = false
      }
    },

    async googleLogin(email, name, avatar) {
      this.isLoading = true
      try {
        const res = await axios.post('/api/google-login', { email, name, avatar })
        this.setAuth(res.data.user, res.data.token)
        return res.data.user
      } catch (err) {
        this.clearAuth()
        throw err.response?.data?.message || 'Đăng nhập Google thất bại'
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout')
      } catch (err) {
        console.error('Logout error on server:', err)
      } finally {
        this.clearAuth()
        const { useProjectStore } = await import('./project')
        useProjectStore().reset()
      }
    },

    async checkAuth() {
      if (!this.token) {
        this.clearAuth()
        return null
      }
      
      try {
        // Sync axios header if not set yet
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        const res = await axios.get('/api/me')
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return this.user
      } catch (err) {
        console.error('Session verification failed, logging out:', err)
        this.clearAuth()
        return null
      }
    },

    async updateProfile(profileData) {
      const res = await axios.put('/api/me', {
        name: profileData.name,
        email: profileData.email,
        avatar: profileData.avatar
      })
      this.user = res.data
      localStorage.setItem('user', JSON.stringify(this.user))
      return this.user
    },

    async updateViewMode(mode) {
      if (this.user) {
        this.user.view_mode = mode
        localStorage.setItem('user', JSON.stringify(this.user))
      }
      try {
        const res = await axios.put('/api/me', { view_mode: mode })
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return this.user
      } catch (err) {
        console.error('Failed to update view mode:', err)
      }
    },

    async updatePinnedCustomers(pinned) {
      if (this.user) {
        this.user.pinned_customers = pinned
        localStorage.setItem('user', JSON.stringify(this.user))
      }
      try {
        const res = await axios.put('/api/me', { pinned_customers: pinned })
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return this.user
      } catch (err) {
        console.error('Failed to update pinned customers:', err)
      }
    }
  }
})
