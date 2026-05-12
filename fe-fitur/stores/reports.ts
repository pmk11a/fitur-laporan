import { defineStore } from 'pinia'

/**
 * Footer Bands Configuration (from dbmasterlaporan.footer_bands)
 */
export interface FooterBandsConfig {
  bands: {
    title?: { enabled: boolean; content: string; align?: string; fontSize?: number }
    pageHeader?: { enabled: boolean; content: string; showOnFirstPage?: boolean }
    pageFooter?: { enabled: boolean; content: string; showDate?: boolean }
    summary?: {
      enabled: boolean
      layout?: { columns: number; alignment: string }
      signatures?: { label: string; position: string }[]
      notes?: { enabled: boolean; label: string; placeholder?: string }
    }
    groups?: { field: string; label: string; showInHeader?: boolean; showSubtotal?: boolean }[]
  }
}

/**
 * Report Configuration
 */
export interface ReportConfig {
  id_laporan?: number
  KODEMENU: string
  Keterangan?: string
  nama_laporan?: string
  deskripsi?: string
  query_sumber_data?: string
  footer_bands?: FooterBandsConfig
  filters: FilterConfig[]
  datasets: DatasetConfig[]
  columns: { [dataset: string]: ColumnConfig[] }
  grouping: GroupConfig[]
}

/**
 * Filter Configuration (from PARAMETER_LAPORAN)
 */
export interface FilterConfig {
  id_parameter: number
  nama_filter: string
  label?: string
  tipe_input: string
  wajib_isi: boolean
  nilai_default?: string
}

/**
 * Dataset Configuration (from dbquerylaporan)
 */
export interface DatasetConfig {
  id_query: number
  nama_dataset: string
  deskripsi: string
  urutan: number
}

/**
 * Column Configuration (from dbkolomlaporan)
 */
export interface ColumnConfig {
  nama_kolom: string
  label_tampil: string
  format_type: string
  alignment: string
  is_summable: boolean
  is_visible: boolean
}

/**
 * Group Configuration (from dbgrouplaporan)
 */
export interface GroupConfig {
  id_group: number
  group_level: number
  group_field: string
  field_value: string
  label: string
  sort_order: number
  show_subtotal: boolean
  style_config: { bold?: boolean; bgColor?: string; fontSize?: number }
}

/**
 * Grouped Data Structure
 */
export interface GroupedData {
  [key: string]: {
    label: string
    items: any[]
    subgroups: {
      [key: string]: {
        label: string
        items: any[]
        subtotal: { [col: string]: number }
      }
    }
    subtotal: { [col: string]: number }
  }
}

/**
 * Menu Item for sidebar
 */
export interface MenuItem {
  KODEMENU: string
  NmReport: string
  L0: number
  ACCESS: string | number
  icon?: string
  children?: MenuItem[]
}

/**
 * Report State
 */
interface ReportState {
  currentReport: ReportConfig | null
  defaultPeriod: { bulan: number; tahun: number; tglAwal: string; tglAkhir: string } | null
  reportData: any[] | null
  datasets: { [name: string]: any[] }
  groupedData: GroupedData | null
  columns: { [dataset: string]: ColumnConfig[] }
  grandTotal: { [col: string]: number }
  menuItems: MenuItem[]
  loading: boolean
  generating: boolean
  error: string | null
  filters: Record<string, any>
  // NEW: Full groupingConfig from database (replaces groupingStrategy)
  groupingConfig: {
    specialHandling: string
    config: any
    groups: any[]
  } | null
}

/**
 * Report Store
 * Manages report list, current report, and generation
 */
export const useReportStore = defineStore('report', {
  state: (): ReportState => ({
    currentReport: null,
    defaultPeriod: null,
    reportData: null,
    datasets: {},
    groupedData: null,
    columns: {},
    grandTotal: {},
    menuItems: [],
    loading: false,
    generating: false,
    error: null,
    filters: {},
    groupingConfig: null
  }),

  getters: {
    /**
     * Check if report is loading
     */
    isLoading: (state) => state.loading || state.generating,

    /**
     * Get required filters
     */
    requiredFilters: (state) => {
      if (!state.currentReport?.filters) return []
      return state.currentReport.filters.filter(f => f.wajib_isi)
    },

    /**
     * Check if report has grouping
     */
    hasGrouping: (state) => {
      return state.groupedData !== null && Object.keys(state.groupedData).length > 0
    },

    /**
     * Get visible columns for main dataset
     */
    visibleColumns: (state) => {
      const mainDataset = state.currentReport?.datasets?.[0]?.nama_dataset
      const cols = mainDataset && state.columns[mainDataset]
        ? state.columns[mainDataset]
        : Object.values(state.columns)[0] || []

      return cols.filter(col => col.is_visible)
    }
  },

  actions: {
    /**
     * Fetch report menu for sidebar
     */
    async fetchMenu() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      if (!authStore.isAuthenticated) {
        authStore.initFromStorage()
      }

      const userId = authStore.user?.userId
      if (!userId) {
        this.menuItems = []
        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await $fetch<{
          success: boolean
          data: { menus: MenuItem[] }
        }>(`${config.public.apiBase}/reports/menu?userId=${encodeURIComponent(userId)}`)

        if (response.success) {
          this.menuItems = response.data.menus || []
        }
      } catch (error: any) {
        this.error = error.data?.message || 'Failed to fetch menu'
        this.useFallbackMenu()
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch report configuration
     */
    async fetchReport(kodeMenu: string) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      if (!authStore.token) return

      this.loading = true
      this.error = null

      try {
        const authStore = useAuthStore()
        const userId = authStore.user?.userId || ''
        const url = `${config.public.apiBase}/reports/${kodeMenu}${userId ? `?userId=${encodeURIComponent(userId)}` : ''}`

        const response = await $fetch<{
          success: boolean
          data: ReportConfig
        }>(url, {
          headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {}
        })

        if (response.success) {
          this.currentReport = response.data
          this.columns = response.data.columns || {}
          this.defaultPeriod = response.data.defaultPeriod || null
          this.initializeFilters()
        }
      } catch (error: any) {
        this.error = error.data?.message || 'Failed to fetch report'
      } finally {
        this.loading = false
      }
    },

    /**
     * Generate report with current filters
     */
    async generateReport(kodeMenu: string) {
      return this.generateReportWithFilters(kodeMenu, this.filters)
    },

    /**
     * Generate report with custom filters (for dynamic filters)
     */
    async generateReportWithFilters(kodeMenu: string, filters: Record<string, any>) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      if (!authStore.token) return

      this.generating = true
      this.error = null

      try {
        const response = await $fetch<{
          success: boolean
          datasets: { [name: string]: any[] }
          groupedData: GroupedData | null
          grandTotal: { [col: string]: number }
          config: ReportConfig
          // NEW: groupingConfig from database (replaces groupingStrategy)
          groupingConfig: {
            specialHandling: string
            config: any
            groups: any[]
          } | null
          errors?: string[]
        }>(`${config.public.apiBase}/reports/${kodeMenu}/preview`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`
          },
          body: { filters }
        })

        if (response.success) {
          this.datasets = response.datasets || {}
          this.groupedData = response.groupedData
          this.grandTotal = response.grandTotal || {}
          this.reportData = Object.values(response.datasets)[0] || []
          // Store groupingConfig from database (no hardcoded patterns)
          this.groupingConfig = response.groupingConfig || null

          // Show errors if any dataset failed
          if (response.errors && response.errors.length > 0) {
            console.warn('Report generation warnings:', response.errors)
          }
        } else {
          this.error = 'Generation failed'
          this.datasets = {}
          this.groupedData = null
          this.reportData = null
          this.groupingConfig = null
        }
      } catch (error: any) {
        this.error = error.data?.message || 'Failed to generate report'
        this.datasets = {}
        this.groupedData = null
        this.reportData = null
        this.groupingStrategy = 'default'
      } finally {
        this.generating = false
      }
    },

    /**
     * Update filter value
     */
    setFilter(key: string, value: any) {
      this.filters[key] = value
    },

    /**
     * Reset all filters to defaults
     */
    resetFilters() {
      this.filters = {}
      if (this.currentReport?.filters) {
        this.initializeFilters()
      }
    },

    /**
     * Initialize filters from config defaults
     */
    initializeFilters() {
      if (!this.currentReport?.filters) return

      this.filters = {}
      for (const filter of this.currentReport.filters) {
        if (filter.nilai_default) {
          this.filters[filter.nama_filter] = filter.nilai_default
        }
      }
    },

    /**
     * Fallback menu when API fails
     */
    useFallbackMenu() {
      const access = useAuthStore().userAccess

      this.menuItems = [
        { KODEMENU: 'REP001', NmReport: 'Laporan Penjualan', L0: 1, ACCESS: 1, icon: 'chart-bar' },
        { KODEMENU: 'REP002', NmReport: 'Laporan Pembelian', L0: 1, ACCESS: 1, icon: 'shopping-cart' }
      ].filter(menu => (menu.ACCESS & access) > 0 || menu.ACCESS === 0)
    },

    /**
     * Clear current report
     */
    clearReport() {
      this.currentReport = null
      this.reportData = null
      this.datasets = {}
      this.groupedData = null
      this.columns = {}
      this.grandTotal = {}
      this.filters = {}
      this.error = null
      this.groupingConfig = null
    }
  }
})