import { defineStore } from 'pinia'
import axios from 'axios'
import { syncAuthSessionToCache, clearAuthSessionFromCache } from '../utils/authSessionCache'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    isLoading: false,
    viewModeRequestId: 0,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setAuth(user, token) {
      const prevUser = JSON.parse(localStorage.getItem('user') || 'null')
      if (prevUser && user && prevUser.id !== user.id) {
        // Purge caches from previous different user
        try {
          Object.keys(localStorage).forEach(key => {
            if (key.startsWith('cached_team_activities') || key.startsWith('cached_schedule_tasks')) {
              localStorage.removeItem(key)
            }
          })
        } catch {}
      }

      this.user = user
      this.token = token
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      
      // Sync auth session to Service Worker cache
      syncAuthSessionToCache(true, user?.id)

      // Set axios header
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

      // Sync push subscription
      import('./browserNotifications').then(({ useBrowserNotificationStore }) => {
        useBrowserNotificationStore().syncSubscription()
      }).catch(err => console.error(err))
    },

    clearAuth() {
      this.user = null
      this.token = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      localStorage.removeItem('cached_team_activities')
      localStorage.removeItem('cached_schedule_tasks')
      try {
        Object.keys(localStorage).forEach(key => {
          if (key.startsWith('cached_team_activities') || key.startsWith('cached_schedule_tasks')) {
            localStorage.removeItem(key)
          }
        })
      } catch {}
      delete axios.defaults.headers.common['Authorization']

      // Clear auth session from Service Worker cache
      clearAuthSessionFromCache()
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
        try {
          const { useBrowserNotificationStore } = await import('./browserNotifications')
          await useBrowserNotificationStore().clearSubscriptionFromServer()
        } catch (err) {
          console.error('Error clearing push subscription on server:', err)
        }
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
        import('./browserNotifications').then(({ useBrowserNotificationStore }) => {
          useBrowserNotificationStore().clearSubscriptionFromServer()
        }).catch(() => {})
        return null
      }
      
      try {
        // Sync axios header if not set yet
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        const res = await axios.get('/api/me')
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
        syncAuthSessionToCache(true, this.user.id)

        // Sync push subscription
        import('./browserNotifications').then(({ useBrowserNotificationStore }) => {
          useBrowserNotificationStore().syncSubscription()
        }).catch(err => console.error(err))

        return this.user
      } catch (err) {
        console.error('Session verification failed, logging out:', err)
        this.clearAuth()
        import('./browserNotifications').then(({ useBrowserNotificationStore }) => {
          useBrowserNotificationStore().clearSubscriptionFromServer()
        }).catch(() => {})
        return null
      }
    },

    async updateProfile(profileData) {
      const payload = {
        name: profileData.name,
        email: profileData.email,
        avatar: profileData.avatar,
      }
      if (profileData.current_password && profileData.new_password) {
        payload.current_password = profileData.current_password
        payload.new_password = profileData.new_password
      }
      const res = await axios.put('/api/me', payload)
      this.user = res.data
      localStorage.setItem('user', JSON.stringify(this.user))
      return this.user
    },

    async updateViewMode(mode) {
      const previousMode = this.user?.view_mode
      const requestId = ++this.viewModeRequestId

      if (this.user) {
        this.user.view_mode = mode
        localStorage.setItem('user', JSON.stringify(this.user))
      }
      try {
        const res = await axios.put('/api/me', { view_mode: mode })
        // Ignore late responses from an earlier click. Otherwise they can
        // overwrite the newest optimistic choice and make the layout flicker.
        if (requestId === this.viewModeRequestId) {
          this.user = res.data
          localStorage.setItem('user', JSON.stringify(this.user))
        }
        return this.user
      } catch (err) {
        console.error('Failed to update view mode:', err)
        if (requestId === this.viewModeRequestId && this.user) {
          this.user.view_mode = previousMode
          localStorage.setItem('user', JSON.stringify(this.user))
        }
        throw err
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
