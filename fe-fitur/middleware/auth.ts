export default defineNuxtRouteMiddleware((to) => {
  // Skip on server-side
  if (import.meta.server) {
    return
  }

  const authStore = useAuthStore()

  // Initialize auth from storage on client
  if (!authStore.isAuthenticated) {
    authStore.initFromStorage()
  }

  // Public routes that don't need auth
  const publicRoutes = ['/login']

  if (publicRoutes.includes(to.path)) {
    // If already logged in, redirect to dashboard
    if (authStore.isAuthenticated) {
      return navigateTo('/dashboard')
    }
    return
  }

  // Protected routes need authentication
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
})