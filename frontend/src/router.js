import { createRouter, createWebHistory } from 'vue-router'
import ProjectListPage from './pages/ProjectListPage.vue'
import ProjectDetailPage from './pages/ProjectDetailPage.vue'
import ProjectUpdatePage from './pages/ProjectUpdatePage.vue'
import CustomerListPage from './pages/CustomerListPage.vue'
import CustomerDetailPage from './pages/CustomerDetailPage.vue'
import TaskListPage from './pages/TaskListPage.vue'
import TaskCompletePage from './pages/TaskCompletePage.vue'
import ActivityFeedPage from './pages/ActivityFeedPage.vue'
import LoginPage from './pages/LoginPage.vue'
import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/', redirect: '/projects' },
  { path: '/login', name: 'login', component: LoginPage },
  { path: '/projects', name: 'projects', component: ProjectListPage },
  { path: '/projects/update', name: 'projects-update', component: ProjectUpdatePage },
  { path: '/projects/:id', name: 'project-detail', component: ProjectDetailPage },
  { path: '/customers', name: 'customers', component: CustomerListPage },
  { path: '/customers/:id', name: 'customer-detail', component: CustomerDetailPage },
  { path: '/tasks', name: 'tasks', component: TaskListPage },
  { path: '/tasks/complete', name: 'tasks-complete', component: TaskCompletePage },
  { path: '/feed', name: 'feed', component: ActivityFeedPage },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Verify auth on initial load or reload if token is present
  if (authStore.token && !authStore.user) {
    await authStore.checkAuth()
  }

  const isAuth = authStore.isAuthenticated

  if (to.name === 'login') {
    if (isAuth) {
      next('/projects')
    } else {
      next()
    }
  } else {
    if (!isAuth) {
      next({ name: 'login' })
    } else {
      next()
    }
  }
})

export default router
