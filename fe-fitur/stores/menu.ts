import { defineStore } from 'pinia'

/**
 * Menu Item Interface
 * Matches DBMENUREPORT and Keu-app sidebar response format
 */
export interface MenuItem {
  KODEMENU: string
  Keterangan: string
  L0: number
  ACCESS: number
  OL: number
  icon?: string
  type?: string
  ROUTENAME?: string
}

/**
 * Keu-app Sidebar Response
 */
interface KeuSidebarResponse {
  success: boolean
  data: {
    menus: MenuItem[]
    permissions: string[]
  }
}

/**
 * Menu State Interface
 */
interface MenuState {
  menus: MenuItem[]
  loading: boolean
  error: string | null
  permissions: string[]
}

/**
 * Menu Store
 * Handles fetching and managing sidebar menu items
 *
 * Uses Keu-app API: /api/menus/sidebar
 */
export const useMenuStore = defineStore('menu', {
  state: (): MenuState => ({
    menus: [],
    loading: false,
    error: null,
    permissions: []
  }),

  getters: {
    /**
     * Get menus sorted by OL (order)
     */
    sortedMenus: (state) => {
      return [...state.menus].sort((a, b) => a.OL - b.OL)
    },

    /**
     * Get report-only menus (L0 > 0)
     */
    reportMenus: (state) => {
      return state.menus.filter(m => m.L0 > 0).sort((a, b) => a.OL - b.OL)
    },

    /**
     * Get admin menus (config menus)
     */
    adminMenus: (state) => {
      return state.menus.filter(m => m.type === 'config').sort((a, b) => a.OL - b.OL)
    }
  },

  actions: {
    /**
     * Fetch menus from sidebar API
     * Uses Keu-app API endpoint
     */
    async fetchMenus() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      if (!authStore.token) {
        this.menus = []
        return
      }

      this.loading = true
      this.error = null

      try {
        // Get userId from auth store
        const userId = authStore.user?.userId || authStore.user?.username || ''

        // Use Keu-app sidebar endpoint with userId
        const response = await $fetch<KeuSidebarResponse>(
          `${config.public.apiBase}/menus/sidebar?userId=${encodeURIComponent(userId)}`,
          {
            method: 'GET',
            headers: {
              Authorization: `Bearer ${authStore.token}`
            }
          }
        )

        if (response.success && response.data) {
          this.menus = response.data.menus || []
          this.permissions = response.data.permissions || []
        }
      } catch (error: any) {
        this.error = error.data?.message || 'Failed to fetch menus'
        // Use fallback menus
        this.useFallbackMenus()
      } finally {
        this.loading = false
      }
    },

    /**
     * Fallback menus when API fails
     * Based on standard ERP permissions
     */
    useFallbackMenus() {
      const authStore = useAuthStore()
      const status = authStore.userAccess || 0

      // Fallback report menus
      const fallbackMenus: MenuItem[] = [
        { KODEMENU: 'REP001', Keterangan: 'Laporan Penjualan', L0: 1, ACCESS: 1, OL: 1, icon: 'chart-bar', type: 'report' },
        { KODEMENU: 'REP002', Keterangan: 'Laporan Pembelian', L0: 1, ACCESS: 1, OL: 2, icon: 'shopping-cart', type: 'report' },
        { KODEMENU: 'REP003', Keterangan: 'Laporan Stok Barang', L0: 1, ACCESS: 2, OL: 3, icon: 'cube', type: 'report' },
        { KODEMENU: 'REP004', Keterangan: 'Laporan Keuangan', L0: 1, ACCESS: 4, OL: 4, icon: 'cash', type: 'report' },
        { KODEMENU: 'REP005', Keterangan: 'Laporan Customer', L0: 1, ACCESS: 8, OL: 5, icon: 'users', type: 'report' },
        { KODEMENU: 'REP006', Keterangan: 'Laporan Supplier', L0: 1, ACCESS: 8, OL: 6, icon: 'truck', type: 'report' }
      ]

      // Filter by ACCESS bitmask
      this.menus = fallbackMenus.filter(menu =>
        (menu.ACCESS & status) > 0 || menu.ACCESS === 0
      )
    },

    /**
     * Check if user has access to specific menu
     * @param kodeMenu - Menu code to check
     */
    hasAccess(kodeMenu: string): boolean {
      const menu = this.menus.find(m => m.KODEMENU === kodeMenu)
      if (!menu) return false

      const authStore = useAuthStore()
      if (authStore.isAdmin) return true

      return (menu.ACCESS & authStore.userAccess) > 0
    },

    /**
     * Clear menu state
     */
    clear() {
      this.menus = []
      this.permissions = []
      this.error = null
    }
  }
})