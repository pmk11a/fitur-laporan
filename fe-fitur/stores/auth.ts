import { defineStore } from 'pinia'

/**
 * Unified User Interface
 * Compatible with both Keu-app and be-fitur API responses
 */
export interface User {
  // Common fields
  id: string
  userId: string
  username: string
  name: string
  tingkat: number
  status: number
  kodeBag: string | null
  KodeKasir?: string | null
  Kodegdg?: string | null

  // Computed
  isAdmin: boolean
  access: number
}

/**
 * Auth State Interface
 */
interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  loading: boolean
  permissions: string[]
}

/**
 * Auth Store
 * Handles login/logout and user state management
 *
 * Compatible with:
 * - Keu-app API (port 8000)
 * - be-fitur API (port 8080)
 */
export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isAuthenticated: false,
    loading: false,
    permissions: []
  }),

  getters: {
    /**
     * Get display name (FullName or USERID)
     */
    userName: (state) => state.user?.name || state.user?.userId || 'User',

    /**
     * Get user access level (STATUS field)
     */
    userAccess: (state) => state.user?.status || 0,

    /**
     * Check if current user is admin
     * Admin criteria: TINGKAT >= 99 or STATUS >= 255
     */
    isAdmin: (state) => state.user?.isAdmin ?? false,

    /**
     * Get USERID
     */
    userId: (state) => state.user?.userId || ''
  },

  actions: {
    /**
     * Login with userId and password
     * Works with Keu-app API format
     *
     * @param userId - User ID (from DBFLPASS)
     * @param password - Plain text password
     */
    async login(userId: string, password: string): Promise<{ success: boolean; message?: string }> {
      const config = useRuntimeConfig()
      this.loading = true

      try {
        // Call login endpoint - supports both formats
        const response = await $fetch<any>(`${config.public.apiBase}/auth/login`, {
          method: 'POST',
          body: { userId, password }
        })

        // Handle be-fitur format: { user: {...}, token: "..." }
        const userData = response.user || response.data?.user
        const tokenData = response.token || response.data?.access_token

        if (userData && tokenData) {
          // Map response to unified User format
          this.user = this.mapUser(userData)
          this.token = tokenData
          this.permissions = response.permissions || response.data?.permissions || []
          this.isAuthenticated = true

          // Persist to localStorage
          if (import.meta.client) {
            localStorage.setItem('auth_token', tokenData)
            localStorage.setItem('auth_user', JSON.stringify(this.user))
          }

          return { success: true }
        }

        return { success: false, message: response.message || 'Login failed' }
      } catch (error: any) {
        const errorMessage = error.data?.message ||
          error.data?.errors?.userId?.[0] ||
          'Login failed'

        return { success: false, message: errorMessage }
      } finally {
        this.loading = false
      }
    },

    /**
     * Map API user response to unified User interface
     */
    mapUser(apiUser: any): User {
      // Handle lowercase keys (be-fitur format)
      const tingkat = apiUser.tingkat || apiUser.TINGKAT || 0
      const status = apiUser.status || apiUser.STATUS || 0
      const userId = apiUser.id || apiUser.userId || apiUser.USERID || ''
      const username = apiUser.username || apiUser.UID || apiUser.userId || ''
      const name = apiUser.name || apiUser.FullName || apiUser.fullName || userId

      return {
        id: userId,
        userId,
        username,
        name,
        tingkat,
        status,
        kodeBag: apiUser.kodeBag || null,
        KodeKasir: apiUser.KodeKasir || null,
        Kodegdg: apiUser.Kodegdg || null,
        isAdmin: tingkat >= 99 || status >= 255 || (status & 128) > 0,
        access: status
      }
    },

    /**
     * Logout and clear auth state
     */
    async logout() {
      const config = useRuntimeConfig()

      try {
        await $fetch(`${config.public.apiBase}/auth/logout`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        })
      } catch {
        // Ignore logout API errors, still clear local state
      } finally {
        this.clearAuth()
      }
    },

    /**
     * Clear authentication state
     */
    clearAuth() {
      this.user = null
      this.token = null
      this.permissions = []
      this.isAuthenticated = false

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    /**
     * Fetch current user from /me endpoint
     */
    async fetchUser() {
      const config = useRuntimeConfig()
      const token = this.token || (import.meta.client ? localStorage.getItem('auth_token') : null)

      if (!token) return

      try {
        const response = await $fetch<{
          success: boolean
          data: {
            user: any
            permissions: string[]
          }
        }>(`${config.public.apiBase}/auth/me`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        if (response.success && response.data) {
          this.user = this.mapUser(response.data.user)
          this.permissions = response.data.permissions || []
          this.token = token
          this.isAuthenticated = true
        }
      } catch {
        this.clearAuth()
      }
    },

    /**
     * Initialize auth from localStorage
     * Call this on app mount
     */
    initFromStorage() {
      if (import.meta.client) {
        const token = localStorage.getItem('auth_token')
        const userStr = localStorage.getItem('auth_user')

        if (token && userStr) {
          try {
            this.token = token
            this.user = JSON.parse(userStr)
            this.isAuthenticated = true
          } catch {
            this.clearAuth()
          }
        }
      }
    },

    /**
     * Check if user has specific permission
     * @param accessCode - Access code to check
     */
    hasPermission(accessCode: number): boolean {
      // Admins have all permissions
      if (this.isAdmin) return true

      // Check permissions array
      return this.permissions.includes(String(accessCode))
    }
  }
})