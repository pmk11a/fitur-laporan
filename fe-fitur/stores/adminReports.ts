import { defineStore } from 'pinia'

// ============================================================
// Types
// ============================================================

export interface AdminReport {
  id_laporan: number
  KODEMENU: string
  nama_laporan: string
  deskripsi: string | null
  status_aktif: boolean
  footer_bands: any | null
  created_at: string
  updated_at: string
  Keterangan: string | null
  L0: number | null
  icon: string | null
}

export interface AdminFilter {
  id_parameter: number
  id_laporan: number
  nama_filter: string
  label: string
  tipe_input: string
  wajib_isi: boolean
  nilai_default: string | null
  posisi: number
  konfigurasi: any | null
}

export interface AdminDataset {
  id_query: number
  id_laporan: number
  nama_dataset: string
  deskripsi: string | null
  query_sumber_data: string
  urutan: number
}

export interface AdminColumn {
  id_kolom: number
  id_laporan: number
  nama_dataset: string
  nama_kolom: string
  label_tampil: string
  urutan_tampil: number
  format_type: string
  alignment: string
  is_summable: boolean
  is_visible: boolean
}

export interface AdminGroup {
  id_group: number
  id_laporan: number
  group_level: number
  group_field: string
  field_value: string
  label: string
  sort_order: number
  show_subtotal: boolean
  style_config: any | null
  special_handling: string
  config_json: any | null
}

export interface AdminUserAccess {
  USERID: string
  FullName: string
  Access: boolean
  IsDesign: boolean
  IsExport: boolean
}

export interface AdminUser {
  USERID: string
  FullName: string
}

export interface AdminKodeMenu {
  KODEMENU: string
  Keterangan: string
}

// ============================================================
// Store
// ============================================================

interface AdminReportState {
  reports: AdminReport[]
  selectedReport: AdminReport | null
  selectedReportData: {
    filters: AdminFilter[]
    datasets: AdminDataset[]
    columns: { [dataset: string]: AdminColumn[] }
    groups: AdminGroup[]
    access: AdminUserAccess[]
  } | null
  availableKodeMenu: AdminKodeMenu[]
  allUsers: AdminUser[]
  activeTab: string
  loading: boolean
  saving: boolean
  error: string | null
}

export const useAdminReportStore = defineStore('adminReports', {
  state: (): AdminReportState => ({
    reports: [],
    selectedReport: null,
    selectedReportData: null,
    availableKodeMenu: [],
    allUsers: [],
    activeTab: 'general',
    loading: false,
    saving: false,
    error: null,
  }),

  getters: {
    isAdmin: () => useAuthStore().isAdmin,
    groupedColumns: (state) => {
      if (!state.selectedReportData?.columns) return {}
      return state.selectedReportData.columns
    },
  },

  actions: {
    // Helper to build URL with userId
    apiUrl(path: string, addUserId = false): string {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      const base = `${config.public.apiBase}${path}`
      if (!addUserId) return base
      const userId = authStore.user?.userId
      return userId ? `${base}${base.includes('?') ? '&' : '?'}userId=${encodeURIComponent(userId)}` : base
    },

    // ============================================================
    // REPORTS
    // ============================================================

    async fetchReports() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.loading = true
      this.error = null

      try {
        const res = await $fetch<{ success: boolean; data: AdminReport[] }>(
          this.apiUrl('/admin/reports', true),
          { headers: authStore.authHeaders }
        )
        if (res.success) this.reports = res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal memuat daftar laporan'
      } finally {
        this.loading = false
      }
    },

    async selectReport(id: number) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.loading = true
      this.error = null

      try {
        const res = await $fetch<{ success: boolean; data: any }>(
          this.apiUrl(`/admin/reports/${id}`, true),
          { headers: authStore.authHeaders }
        )
        if (res.success) {
          this.selectedReport = res.data
          this.selectedReportData = {
            filters: res.data.filters || [],
            datasets: res.data.datasets || [],
            columns: res.data.columns || {},
            groups: res.data.groups || [],
            access: res.data.access || [],
          }
        }
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal memuat laporan'
      } finally {
        this.loading = false
      }
    },

    async createReport(data: Partial<AdminReport>) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true
      this.error = null

      try {
        const res = await $fetch<{ success: boolean; data: AdminReport }>(
          this.apiUrl('/admin/reports', true),
          {
            method: 'POST',
            headers: authStore.authHeaders,
            body: data,
          }
        )
        if (res.success) {
          this.reports.push(res.data)
          await this.selectReport(res.data.id_laporan)
        }
        return res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal membuat laporan'
        return null
      } finally {
        this.saving = false
      }
    },

    async updateReport(id: number, data: Partial<AdminReport>) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true
      this.error = null

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${id}`, true),
          {
            method: 'PUT',
            headers: authStore.authHeaders,
            body: data,
          }
        )
        if (res.success) {
          const idx = this.reports.findIndex(r => r.id_laporan === id)
          if (idx >= 0) this.reports[idx] = { ...this.reports[idx], ...data }
          if (this.selectedReport?.id_laporan === id) {
            Object.assign(this.selectedReport, data)
          }
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal mengupdate laporan'
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteReport(id: number) {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true
      this.error = null

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${id}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          this.reports = this.reports.filter(r => r.id_laporan !== id)
          if (this.selectedReport?.id_laporan === id) {
            this.selectedReport = null
            this.selectedReportData = null
          }
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal menghapus laporan'
        return false
      } finally {
        this.saving = false
      }
    },

    async fetchAvailableKodeMenu() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const res = await $fetch<{ success: boolean; data: AdminKodeMenu[] }>(
          this.apiUrl('/admin/reports/available-kodemenu', true),
          { headers: authStore.authHeaders }
        )
        if (res.success) this.availableKodeMenu = res.data
      } catch { /* ignore */ }
    },

    // ============================================================
    // FILTERS
    // ============================================================

    async createFilter(data: Partial<AdminFilter>) {
      if (!this.selectedReport) return null
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean; data: AdminFilter }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/filters`, true),
          { method: 'POST', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          this.selectedReportData!.filters.push(res.data)
        }
        return res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal membuat filter'
        return null
      } finally {
        this.saving = false
      }
    },

    async updateFilter(fid: number, data: Partial<AdminFilter>) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/filters/${fid}`, true),
          { method: 'PUT', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          const idx = this.selectedReportData!.filters.findIndex(f => f.id_parameter === fid)
          if (idx >= 0) Object.assign(this.selectedReportData!.filters[idx], data)
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal update filter'
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteFilter(fid: number) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/filters/${fid}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          this.selectedReportData!.filters = this.selectedReportData!.filters.filter(
            f => f.id_parameter !== fid
          )
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal hapus filter'
        return false
      } finally {
        this.saving = false
      }
    },

    async reorderFilters(orders: { id: number; posisi: number }[]) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/filters/reorder`, true),
          { method: 'PATCH', headers: authStore.authHeaders, body: { orders } }
        )
        if (res.success) {
          for (const o of orders) {
            const f = this.selectedReportData!.filters.find(f => f.id_parameter === o.id)
            if (f) f.posisi = o.posisi
          }
          this.selectedReportData!.filters.sort((a, b) => a.posisi - b.posisi)
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal reorder filter'
        return false
      } finally {
        this.saving = false
      }
    },

    // ============================================================
    // DATASETS
    // ============================================================

    async createDataset(data: Partial<AdminDataset>) {
      if (!this.selectedReport) return null
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean; data: AdminDataset }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/datasets`, true),
          { method: 'POST', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          this.selectedReportData!.datasets.push(res.data)
        }
        return res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal membuat dataset'
        return null
      } finally {
        this.saving = false
      }
    },

    async updateDataset(did: number, data: Partial<AdminDataset>) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/datasets/${did}`, true),
          { method: 'PUT', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          const idx = this.selectedReportData!.datasets.findIndex(d => d.id_query === did)
          if (idx >= 0) Object.assign(this.selectedReportData!.datasets[idx], data)
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal update dataset'
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteDataset(did: number) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/datasets/${did}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          this.selectedReportData!.datasets = this.selectedReportData!.datasets.filter(
            d => d.id_query !== did
          )
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal hapus dataset'
        return false
      } finally {
        this.saving = false
      }
    },

    async previewQuery(sql: string, filters: Record<string, any> = {}) {
      if (!this.selectedReport) return null
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const res = await $fetch<{ success: boolean; data: any }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/datasets/preview`, true),
          {
            method: 'POST',
            headers: authStore.authHeaders,
            body: { query_sumber_data: sql, filters },
          }
        )
        return res.success ? res.data : null
      } catch { return null }
    },

    // ============================================================
    // COLUMNS
    // ============================================================

    async createColumn(data: Partial<AdminColumn>) {
      if (!this.selectedReport) return null
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean; data: AdminColumn }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/columns`, true),
          { method: 'POST', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          const ds = data.nama_dataset!
          if (!this.selectedReportData!.columns[ds]) {
            this.selectedReportData!.columns[ds] = []
          }
          this.selectedReportData!.columns[ds].push(res.data)
        }
        return res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal membuat kolom'
        return null
      } finally {
        this.saving = false
      }
    },

    async updateColumn(cid: number, data: Partial<AdminColumn>) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/columns/${cid}`, true),
          { method: 'PUT', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          for (const cols of Object.values(this.selectedReportData!.columns)) {
            const idx = cols.findIndex(c => c.id_kolom === cid)
            if (idx >= 0) { Object.assign(cols[idx], data); break }
          }
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal update kolom'
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteColumn(cid: number) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/columns/${cid}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          for (const cols of Object.values(this.selectedReportData!.columns)) {
            const idx = cols.findIndex(c => c.id_kolom === cid)
            if (idx >= 0) { cols.splice(idx, 1); break }
          }
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal hapus kolom'
        return false
      } finally {
        this.saving = false
      }
    },

    // ============================================================
    // GROUPS
    // ============================================================

    async createGroup(data: Partial<AdminGroup>) {
      if (!this.selectedReport) return null
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean; data: AdminGroup }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/groups`, true),
          { method: 'POST', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          this.selectedReportData!.groups.push(res.data)
        }
        return res.data
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal membuat grouping'
        return null
      } finally {
        this.saving = false
      }
    },

    async updateGroup(gid: number, data: Partial<AdminGroup>) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/groups/${gid}`, true),
          { method: 'PUT', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          const idx = this.selectedReportData!.groups.findIndex(g => g.id_group === gid)
          if (idx >= 0) Object.assign(this.selectedReportData!.groups[idx], data)
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal update grouping'
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteGroup(gid: number) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/groups/${gid}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          this.selectedReportData!.groups = this.selectedReportData!.groups.filter(
            g => g.id_group !== gid
          )
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal hapus grouping'
        return false
      } finally {
        this.saving = false
      }
    },

    // ============================================================
    // USER ACCESS
    // ============================================================

    async grantAccess(data: Partial<AdminUserAccess>) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean; data: AdminUserAccess[] }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/access`, true),
          { method: 'POST', headers: authStore.authHeaders, body: data }
        )
        if (res.success) {
          this.selectedReportData!.access = res.data
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal memberi akses'
        return false
      } finally {
        this.saving = false
      }
    },

    async revokeAccess(userId: string) {
      if (!this.selectedReport) return false
      const config = useRuntimeConfig()
      const authStore = useAuthStore()
      this.saving = true

      try {
        const res = await $fetch<{ success: boolean }>(
          this.apiUrl(`/admin/reports/${this.selectedReport.id_laporan}/access/${userId}`, true),
          { method: 'DELETE', headers: authStore.authHeaders }
        )
        if (res.success) {
          this.selectedReportData!.access = this.selectedReportData!.access.filter(
            a => a.USERID !== userId
          )
        }
        return res.success
      } catch (e: any) {
        this.error = e.data?.message || 'Gagal cabut akses'
        return false
      } finally {
        this.saving = false
      }
    },

    async fetchAllUsers() {
      const config = useRuntimeConfig()
      const authStore = useAuthStore()

      try {
        const res = await $fetch<{ success: boolean; data: AdminUser[] }>(
          this.apiUrl('/admin/users', true),
          { headers: authStore.authHeaders }
        )
        if (res.success) this.allUsers = res.data
      } catch { /* ignore */ }
    },

    setTab(tab: string) {
      this.activeTab = tab
    },

    clearSelection() {
      this.selectedReport = null
      this.selectedReportData = null
    },
  },
})