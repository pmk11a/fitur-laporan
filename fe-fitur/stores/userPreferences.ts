import { defineStore } from 'pinia'

/**
 * Universal User Preferences Store
 *
 * Single source of truth for all user-specific settings.
 * Pattern: { namespace: { key: value } }
 *
 * Namespaces:
 *   - format    : number/date formatting
 *   - ui        : theme, layout, sidebar
 *   - table     : page size, hidden cols, sort
 *   - filter    : default filters per report
 *   - dashboard : widget layout
 *   - language  : language code
 *   - timezone  : timezone
 *   - form      : default values for forms
 *   - print     : print preferences
 *   - export    : export preferences
 */

export const useUserPreferencesStore = defineStore('userPreferences', {
  state: () => ({
    // Format: { format: { 'rep._default': {...} }, ui: { theme: {...} } }
    data: {} as Record<string, Record<string, any>>,
    // Track loaded namespaces to avoid refetch
    loaded: {} as Record<string, boolean>,
    // Track in-flight loads
    loading: {} as Record<string, Promise<void> | null>,
  }),

  getters: {
    /**
     * Get value synchronously (returns null if not loaded yet).
     */
    get: (state) => (namespace: string, key: string, defaultValue: any = null): any => {
      return state.data[namespace]?.[key] ?? defaultValue
    },

    /**
     * Get all values in a namespace.
     */
    getNamespace: (state) => (namespace: string): Record<string, any> => {
      return state.data[namespace] || {}
    },
  },

  actions: {
    /**
     * Load a namespace from server (with localStorage cache).
     * Safe to call multiple times — will only fetch once.
     */
    async loadNamespace(namespace: string): Promise<void> {
      if (this.loaded[namespace] && this.data[namespace]) return

      // Coalesce concurrent calls
      if (this.loading[namespace]) {
        return this.loading[namespace]!
      }

      const promise = (async () => {
        // 1. Load from localStorage cache first
        if (import.meta.client) {
          const cached = localStorage.getItem(`prefs.${namespace}`)
          if (cached) {
            try {
              const parsed = JSON.parse(cached)
              this.data[namespace] = { ...this.data[namespace], ...parsed }
            } catch {
              // ignore corrupt cache
            }
          }
        }

        // 2. Sync from server (truth source)
        try {
          const config = useRuntimeConfig()
          const authStore = useAuthStore()
          const headers: Record<string, string> = {
            'Content-Type': 'application/json',
          }
          if (authStore.token) {
            headers.Authorization = `Bearer ${authStore.token}`
          }
          if (authStore.userId) {
            headers['X-User-Id'] = authStore.userId
          }

          const res = await $fetch<{ preferences: Record<string, any> }>(
            `${config.public.apiBase}/preferences?namespace=${namespace}`,
            { headers }
          )

          if (res?.preferences) {
            this.data[namespace] = { ...this.data[namespace], ...res.preferences }
            if (import.meta.client) {
              localStorage.setItem(
                `prefs.${namespace}`,
                JSON.stringify(res.preferences)
              )
            }
          }
        } catch (e) {
          // Network error — keep using cache
          console.warn(`[userPreferences] Failed to sync namespace "${namespace}"`, e)
        }

        this.loaded[namespace] = true
      })()

      this.loading[namespace] = promise
      try {
        await promise
      } finally {
        this.loading[namespace] = null
      }
    },

    /**
     * Set a single preference.
     */
    async set(namespace: string, key: string, value: any): Promise<void> {
      if (!this.data[namespace]) this.data[namespace] = {}
      this.data[namespace][key] = value
      this.loaded[namespace] = true

      // Update localStorage cache
      if (import.meta.client) {
        const cached = JSON.parse(localStorage.getItem(`prefs.${namespace}`) || '{}')
        cached[key] = value
        localStorage.setItem(`prefs.${namespace}`, JSON.stringify(cached))
      }

      // Sync to server (fire-and-forget; local state already updated)
      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()
        const headers: Record<string, string> = {
          'Content-Type': 'application/json',
        }
        if (authStore.token) headers.Authorization = `Bearer ${authStore.token}`
        if (authStore.userId) headers['X-User-Id'] = authStore.userId

        await $fetch(
          `${config.public.apiBase}/preferences/${namespace}/${encodeURIComponent(key)}`,
          { method: 'PUT', body: value, headers }
        )
      } catch (e) {
        console.warn(`[userPreferences] Failed to save ${namespace}.${key}`, e)
      }
    },

    /**
     * Remove a single preference.
     */
    async remove(namespace: string, key: string): Promise<void> {
      if (this.data[namespace]) {
        delete this.data[namespace][key]
      }
      if (import.meta.client) {
        const cached = JSON.parse(localStorage.getItem(`prefs.${namespace}`) || '{}')
        delete cached[key]
        localStorage.setItem(`prefs.${namespace}`, JSON.stringify(cached))
      }

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()
        const headers: Record<string, string> = {}
        if (authStore.token) headers.Authorization = `Bearer ${authStore.token}`
        if (authStore.userId) headers['X-User-Id'] = authStore.userId

        await $fetch(
          `${config.public.apiBase}/preferences/${namespace}/${encodeURIComponent(key)}`,
          { method: 'DELETE', headers }
        )
      } catch (e) {
        console.warn(`[userPreferences] Failed to remove ${namespace}.${key}`, e)
      }
    },

    /**
     * Bulk set multiple preferences.
     */
    async bulkSet(items: Array<{ namespace: string, key: string, value: any }>): Promise<void> {
      for (const item of items) {
        if (!this.data[item.namespace]) this.data[item.namespace] = {}
        this.data[item.namespace][item.key] = item.value
        this.loaded[item.namespace] = true

        if (import.meta.client) {
          const cached = JSON.parse(localStorage.getItem(`prefs.${item.namespace}`) || '{}')
          cached[item.key] = item.value
          localStorage.setItem(`prefs.${item.namespace}`, JSON.stringify(cached))
        }
      }

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()
        const headers: Record<string, string> = { 'Content-Type': 'application/json' }
        if (authStore.token) headers.Authorization = `Bearer ${authStore.token}`
        if (authStore.userId) headers['X-User-Id'] = authStore.userId

        await $fetch(`${config.public.apiBase}/preferences/bulk`, {
          method: 'POST',
          body: items,
          headers,
        })
      } catch (e) {
        console.warn('[userPreferences] Failed bulk save', e)
      }
    },

    /**
     * Clear all cached state (used on logout).
     */
    clearAll(): void {
      this.data = {}
      this.loaded = {}
      this.loading = {}
      if (import.meta.client) {
        const keysToRemove: string[] = []
        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i)
          if (key && key.startsWith('prefs.')) keysToRemove.push(key)
        }
        keysToRemove.forEach((k) => localStorage.removeItem(k))
      }
    },
  },
})
