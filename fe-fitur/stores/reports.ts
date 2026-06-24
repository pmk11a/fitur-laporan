import { defineStore } from 'pinia'
import { useAuthStore } from './auth'

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
      footer_table?: { rows: string[]; columns: string[] }
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
  visible?: boolean
  config_json?: {
    display_role?: 'summary' | 'detail'
    summary_layout?: 'grid_2col' | 'grid_1col'
    detail_dataset?: string
    t2_sum_fields?: string[]
    bon_giro_fields?: string[]
    summary_fields?: string[]
    right_fields?: string[]
    computed?: Record<string, {
      expression: string
      operands: Record<string, 't1' | 'sum:t1' | 'sum:t2'>
    }>
  }
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
  lastError: string | null
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
    lastError: null,
    filters: {},
    groupingConfig: null
  }),

  getters: {
    /**
     * Check if report is loading
     */
    isLoading: (state) => state.loading || state.generating,

    /**
     * Check if there is an error from last generation
     */
    hasError: (state) => !!state.error || !!state.lastError,

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
          // DEBUG: inspect raw preview response
          // Enable from browser console: __toggleDebugT1(true)
          if (typeof window !== 'undefined' && (window as any).__DEBUG_T1_PAYLOAD__) {
            console.log('[DEBUG] /preview raw response:', response)
            console.log('[DEBUG] datasets keys:', Object.keys(response.datasets || {}))
            const t1 = (response.datasets || {})['T1'] || []
            console.log('[DEBUG] T1 row count:', t1.length)
            if (t1.length > 0) {
              console.log('[DEBUG] T1[0] keys:', Object.keys(t1[0]))
              console.log('[DEBUG] T1[0] full row:', t1[0])
            }
            const t2 = (response.datasets || {})['T2'] || []
            console.log('[DEBUG] T2 row count:', t2.length)
            if (t2.length > 0) {
              console.log('[DEBUG] T2[0] keys:', Object.keys(t2[0]))
              console.log('[DEBUG] T2[0] full row:', t2[0])
            }
            // Also dump config_json of summary dataset
            const summaryDs = (response.config?.datasets || []).find((d: any) => d.config_json?.display_role === 'summary')
            console.log('[DEBUG] summary dataset config_json:', summaryDs?.config_json)
          }
          this.datasets = response.datasets || {}
          this.groupedData = response.groupedData
          this.grandTotal = response.grandTotal || {}
          this.reportData = Object.values(response.datasets)[0] || []
          // Update config (datasets, columns) from preview response to stay in sync
          if (response.config) {
            // Strip out hidden datasets so FE never sees them
            const cleanConfig = {
              ...response.config,
              datasets: (response.config.datasets || []).filter((d: any) => d.visible !== false)
            }
            this.currentReport = { ...this.currentReport, ...cleanConfig }
          }
          // Update columns from config (for dynamic columns like jumlah2)
          this.columns = response.config?.columns || {}
          // Store groupingConfig from database (no hardcoded patterns)
          this.groupingConfig = response.groupingConfig || null

          // Show errors if any dataset failed
          if (response.errors && response.errors.length > 0) {
            console.warn('Report generation warnings:', response.errors)
            this.lastError = response.errors.join('; ')
          } else {
            this.lastError = null
          }
        } else {
          this.error = 'Generation failed'
          this.lastError = response.errors?.join('; ') || null
          this.datasets = {}
          this.groupedData = null
          this.reportData = null
          this.groupingConfig = null
        }
      } catch (error: any) {
        this.error = error.data?.message || 'Failed to generate report'
        this.lastError = error.data?.message || null
        this.datasets = {}
        this.groupedData = null
        this.reportData = null
        this.groupingConfig = null
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
     * Initialize filters from config defaults + defaultPeriod
     */
    initializeFilters() {
      if (!this.currentReport?.filters) return

      this.filters = {}

      const applyDefaultPeriod = () => {
        if (!this.defaultPeriod) return

        const bulan = this.defaultPeriod.bulan
        const tahun = this.defaultPeriod.tahun

        for (const filter of this.currentReport!.filters!) {
          const label = (filter.label || '').toLowerCase()
          const name = (filter.nama_filter || '').toLowerCase()
          const combined = label + ' ' + name

          if ((filter.tipe_input === 'number' || filter.tipe_input === 'month' || filter.tipe_input === 'year')
              && !filter.nilai_default) {
            if (combined.includes('tahun')) {
              this.filters[filter.nama_filter] = tahun.toString()
            } else if (combined.includes('bulan') && !combined.includes('akhhir')) {
              this.filters[filter.nama_filter] = bulan.toString()
            }
          }
        }
      }

      for (const filter of this.currentReport.filters) {
        if (filter.nilai_default) {
          this.filters[filter.nama_filter] = filter.nilai_default
          continue
        }

        const tipe = filter.tipe_input || ''
        const filterName = (filter.nama_filter || '').toLowerCase()
        const filterLabel = (filter.label || '').toLowerCase()
        const filterCombined = `${filterLabel} ${filterName}`

        if (tipe === 'date') {
          const isAwal = filterName.includes('awal') || filterName.includes('mulai') || filterLabel.includes('awal')
          const isAkhir = filterName.includes('akhir') || filterLabel.includes('akhir')
          if (isAwal) {
            this.filters[filter.nama_filter] = this.defaultPeriod?.tglAwal ?? ''
          } else if (isAkhir) {
            this.filters[filter.nama_filter] = this.defaultPeriod?.tglAkhir ?? ''
          } else {
            this.filters[filter.nama_filter] = this.defaultPeriod?.tglAwal ?? ''
          }
        } else if (tipe === 'text' || tipe === 'browse' || tipe === 'perkiraan') {
          // Init non-date filters with empty string so backend receives the key
          // (empty value is replaced with NULL by ReportService for SP calls)
          this.filters[filter.nama_filter] = ''
        }
      }

      applyDefaultPeriod()
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