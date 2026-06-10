/**
 * User preferences auto-loader.
 * Runs once when the Nuxt app boots on the client.
 * Pre-loads critical namespaces so usePreference() is synchronous thereafter.
 */
export default defineNuxtPlugin(async () => {
  const store = useUserPreferencesStore()
  const authStore = useAuthStore()

  // Only load if user is authenticated
  if (!authStore.isAuthenticated && !authStore.token) {
    return
  }

  // Critical namespaces - loaded in parallel
  const critical = ['format', 'ui', 'language', 'timezone']

  try {
    await Promise.all(critical.map((ns) => store.loadNamespace(ns)))
  } catch (e) {
    console.warn('[userPreferences] Failed to load critical namespaces', e)
  }
})
