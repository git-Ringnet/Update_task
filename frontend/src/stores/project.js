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
  }),

  actions: {
    async fetchProjects(isSilent = false) {
      if (!isSilent && this.projects.length === 0) {
        this.isLoading = true
      }
      try {
        const params = {
          search: this.searchQuery,
        }
        if (this.activeStatus) {
          params.tracking_status = this.activeStatus
        }
        const res = await axios.get('/api/projects', { params })
        this.projects = res.data.projects
        this.counts = res.data.counts
      } catch (err) {
        console.error('Failed to fetch projects:', err)
      } finally {
        this.isLoading = false
      }
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
        await this.fetchProjects(true)
      } catch (err) {
        console.error('Failed to update health:', err)
        await this.fetchProjects(true)
      }
    },

    async togglePin(projectId) {
      const project = this.projects.find(p => p.id === projectId)
      if (project) {
        project.is_pinned = !project.is_pinned
      }
      try {
        await axios.patch(`/api/projects/${projectId}/pin`)
        await this.fetchProjects(true)
      } catch (err) {
        console.error('Failed to toggle pin:', err)
        await this.fetchProjects(true)
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
