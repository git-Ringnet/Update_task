import { defineStore } from 'pinia'
import axios from 'axios'

export const useProjectStore = defineStore('project', {
  state: () => ({
    projects: [],
    counts: {
      following: 0,
      not_following: 0,
      completed: 0,
    },
    activeStatus: 'following',
    searchQuery: '',
    isLoading: false,
    customers: [],
    users: [],
    pinningProjectIds: {},
    lastRequestId: 0,
    listSearchQuery: '',
    activePage: 'home',
  }),

  actions: {
    reset() {
      this.projects = []
      this.counts = {
        following: 0,
        not_following: 0,
        completed: 0,
      }
      this.searchQuery = ''
      this.isLoading = false
      this.pinningProjectIds = {}
    },

    async fetchProjects(isSilent = false) {
      this.lastRequestId = (this.lastRequestId || 0) + 1
      const currentId = this.lastRequestId

      if (!isSilent && this.projects.length === 0) {
        this.isLoading = true
      }
      try {
        const queryToUse = this.activePage === 'list' ? this.listSearchQuery : this.searchQuery
        const params = {
          search: queryToUse || '',
        }
        if (this.activeStatus) {
          params.tracking_status = this.activeStatus
        }
        const res = await axios.get('/api/projects', { params })
        if (currentId !== this.lastRequestId) {
          return
        }
        const rawProjects = res.data.projects || []
        const incoming = rawProjects.map(p => ({
          ...p,
          is_pinned: Boolean(p.is_pinned),
        }))

        // Giữ trạng thái ghim local khi request pin đang chạy (tránh poll ghi đè)
        const pendingPinState = {}
        Object.keys(this.pinningProjectIds).forEach((id) => {
          const local = this.projects.find(p => String(p.id) === String(id))
          if (local) pendingPinState[id] = Boolean(local.is_pinned)
        })

        this.projects = incoming.map(p => {
          const pending = pendingPinState[p.id] ?? pendingPinState[String(p.id)]
          if (pending !== undefined) {
            return { ...p, is_pinned: pending }
          }
          return p
        })
        this.sortProjects()
        this.counts = res.data.counts || this.counts
      } catch (err) {
        console.error('Failed to fetch projects:', err)
      } finally {
        this.isLoading = false
      }
    },

    sortProjects() {
      this.projects.sort((a, b) => {
        const aPinned = a.is_pinned ? 1 : 0
        const bPinned = b.is_pinned ? 1 : 0
        if (bPinned !== aPinned) {
          return bPinned - aPinned
        }
        const aOrder = a.sort_order ?? 999999
        const bOrder = b.sort_order ?? 999999
        if (aOrder !== bOrder) {
          return aOrder - bOrder
        }
        return new Date(b.last_activity_at || 0) - new Date(a.last_activity_at || 0)
      })
    },

    async updateHealth(projectId, color) {
      // Optimistic update
      const project = this.projects.find(p => p.id === projectId)
      if (project) {
        project.health = color
      }
      try {
        await axios.patch(`/api/projects/${projectId}/health`, {
          health: color
        })
      } catch (err) {
        console.error('Failed to update health:', err)
        await this.fetchProjects(true)
        throw err
      }
    },

    async togglePin(projectId) {
      const normalizedId = Number(projectId)
      if (!normalizedId) return false

      if (this.pinningProjectIds[normalizedId]) {
        const existing = this.projects.find(p => Number(p.id) === normalizedId)
        return existing ? Boolean(existing.is_pinned) : false
      }

      const project = this.projects.find(p => Number(p.id) === normalizedId)
      if (!project) return false

      const isCurrentlyPinned = Boolean(project.is_pinned)
      const nextState = !isCurrentlyPinned

      this.pinningProjectIds[normalizedId] = true
      project.is_pinned = nextState
      this.sortProjects()

      try {
        const res = await axios.patch(`/api/projects/${normalizedId}/pin`)
        const finalState = res.data?.is_pinned !== undefined
          ? Boolean(res.data.is_pinned)
          : nextState

        project.is_pinned = finalState
        this.sortProjects()
        return finalState
      } catch (err) {
        console.error('Failed to toggle pin:', err)
        project.is_pinned = isCurrentlyPinned
        this.sortProjects()
        throw err
      } finally {
        delete this.pinningProjectIds[normalizedId]
      }
    },

    async createProject(projectData) {
      try {
        await axios.post('/api/projects', projectData)
        await this.fetchProjects(true)
        return true
      } catch (err) {
        console.error('Failed to create project:', err)
        throw err
      }
    },

    async deleteProject(projectId) {
      try {
        await axios.delete(`/api/projects/${projectId}`)
        await this.fetchProjects(true)
        return true
      } catch (err) {
        console.error('Failed to delete project:', err)
        throw err
      }
    },

    async fetchAuxData() {
      try {
        const [cRes, uRes] = await Promise.all([
          axios.get('/api/customers'),
          axios.get('/api/users'),
        ])
        this.customers = cRes.data.customers || cRes.data
        this.users = uRes.data
      } catch (err) {
        console.error('Failed to fetch customers/users:', err)
      }
    }
  }
})
