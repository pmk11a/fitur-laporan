import { defineStore } from 'pinia'
import type { BrowseConfig, BrowseListItem, TableInfo, AdminBrowseConfig } from './adminReports'
import { useAuthStore } from './auth'

// Flat browse type option with group classification
// Used by Report Filters tab to populate the "Browse Type" dropdown
export interface BrowseTypeOption {
  kodeBrowse: string
  group: string
  source: 'database' | 'hardcoded'
}

const API_BASE = '/admin/browse'

// Helper to build URL with userId
function buildUrl(path: string): string {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  const base = `${config.public.apiBase}${path}`
  const userId = authStore.user?.userId
  return userId ? `${base}${base.includes('?') ? '&' : '?'}userId=${encodeURIComponent(userId)}` : base
}

export const useAdminBrowseStore = defineStore('adminBrowse', {
  state: () => ({
    configs: [] as BrowseListItem[],
    browseTypes: [] as BrowseTypeOption[], // flat list with group classification, used by Report Filters tab
    selectedConfig: null as AdminBrowseConfig | null,
    tables: [] as string[],
    tableColumns: [] as TableInfo[],
    loading: false,
    error: null as string | null,
    lastError: null as any | null,
    summary: {
      hardcoded: 0,
      database: 0,
    },
  }),

  getters: {
    hardcodedConfigs: (state) => state.configs.filter(c => c.source === 'hardcoded'),
    databaseConfigs: (state) => state.configs.filter(c => c.source === 'database'),
    configBySource: (state) => (source: 'database' | 'hardcoded') =>
      state.configs.filter(c => c.source === source),
    browseTypesByGroup: (state) => {
      const groups: Record<string, BrowseTypeOption[]> = {}
      for (const t of state.browseTypes) {
        if (!groups[t.group]) groups[t.group] = []
        groups[t.group].push(t)
      }
      return groups
    },
  },

  actions: {
    // Fetch all browse configs
    async fetchConfigs() {
      this.loading = true
      this.error = null
      this.lastError = null
      try {
        console.log('[AdminBrowse] Fetching configs from:', API_BASE)
        const res = await $fetch<any>(buildUrl(API_BASE))
        console.log('[AdminBrowse] Response:', res)
        if (res.success) {
          this.configs = res.data.configs
          this.summary = res.data.summary
        } else {
          this.error = res.message || 'Failed to fetch configs'
        }
      } catch (e: any) {
        console.error('[AdminBrowse] Error:', e)
        this.error = e.message || 'Failed to fetch configs'
        this.lastError = e
      } finally {
        this.loading = false
      }
    },

    // Fetch flat browse type list with group classification (used by Report Filters tab)
    async fetchBrowseTypes() {
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/list`))
        if (res.success) {
          this.browseTypes = res.data
        }
      } catch (e: any) {
        console.error('[AdminBrowse] Error fetching browse types:', e)
      }
    },

    // Fetch single config detail
    async fetchConfig(kodeBrowse: string) {
      this.loading = true
      this.error = null
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/${kodeBrowse}`))
        if (res.success) {
          this.selectedConfig = res.data
          return res.data
        }
        throw new Error(res.message || 'Config not found')
      } catch (e: any) {
        this.error = e.message || 'Failed to fetch config'
        return null
      } finally {
        this.loading = false
      }
    },

    // Create new config
    async createConfig(data: Partial<BrowseConfig>) {
      this.loading = true
      this.error = null
      try {
        const res = await $fetch<any>(buildUrl(API_BASE), {
          method: 'POST',
          body: data,
        })
        if (res.success) {
          await this.fetchConfigs()
          return res.data
        }
        throw new Error(res.message || 'Failed to create config')
      } catch (e: any) {
        this.error = e.message || 'Failed to create config'
        return null
      } finally {
        this.loading = false
      }
    },

    // Update config (database only)
    async updateConfig(kodeBrowse: string, data: Partial<BrowseConfig>) {
      this.loading = true
      this.error = null
      try {
        console.log('[AdminBrowse] Updating config:', kodeBrowse, data)
        const res = await $fetch<any>(buildUrl(`${API_BASE}/${kodeBrowse}`), {
          method: 'PUT',
          body: data,
        })
        console.log('[AdminBrowse] Update response:', res)
        if (res.success) {
          await this.fetchConfigs()
          if (this.selectedConfig?.kodeBrowse === kodeBrowse) {
            await this.fetchConfig(kodeBrowse)
          }
          return res.data
        }
        throw new Error(res.message || 'Failed to update config')
      } catch (e: any) {
        console.error('[AdminBrowse] Update error:', e)
        this.error = e.message || 'Failed to update config'
        return null
      } finally {
        this.loading = false
      }
    },

    // Delete/deactivate config
    async deleteConfig(kodeBrowse: string) {
      this.loading = true
      this.error = null
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/${kodeBrowse}`), {
          method: 'DELETE',
        })
        if (res.success) {
          await this.fetchConfigs()
          if (this.selectedConfig?.kodeBrowse === kodeBrowse) {
            this.selectedConfig = null
          }
          return true
        }
        throw new Error(res.message || 'Failed to delete config')
      } catch (e: any) {
        this.error = e.message || 'Failed to delete config'
        return false
      } finally {
        this.loading = false
      }
    },

    // Clone hardcoded to database
    async cloneConfig(kodeBrowse: string) {
      this.loading = true
      this.error = null
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/${kodeBrowse}/clone`), {
          method: 'POST',
        })
        if (res.success) {
          await this.fetchConfigs()
          return res.data
        }
        throw new Error(res.message || 'Failed to clone config')
      } catch (e: any) {
        this.error = e.message || 'Failed to clone config'
        return null
      } finally {
        this.loading = false
      }
    },

    // Sync all hardcoded to database
    async syncConfigs(mode: 'all' | 'missing' = 'all') {
      this.loading = true
      this.error = null
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/sync`), {
          method: 'POST',
          body: { mode },
        })
        if (res.success) {
          await this.fetchConfigs()
          return res.data
        }
        throw new Error(res.message || 'Failed to sync')
      } catch (e: any) {
        this.error = e.message || 'Failed to sync'
        return null
      } finally {
        this.loading = false
      }
    },

    // Fetch available tables
    async fetchTables(search: string = '') {
      try {
        const url = search
          ? buildUrl(`${API_BASE}/tables?search=${encodeURIComponent(search)}`)
          : buildUrl(`${API_BASE}/tables`)
        const res = await $fetch<any>(url)
        if (res.success) {
          this.tables = res.data.tables
        }
      } catch (e: any) {
        this.error = e.message || 'Failed to fetch tables'
      }
    },

    // Fetch columns for a table
    async fetchTableColumns(tableName: string) {
      try {
        const res = await $fetch<any>(buildUrl(`${API_BASE}/tables/${encodeURIComponent(tableName)}/columns`))
        if (res.success) {
          this.tableColumns = res.data.columns
          return res.data.columns
        }
        return []
      } catch (e: any) {
        this.error = e.message || 'Failed to fetch columns'
        return []
      }
    },

    // Clear selected config
    clearSelected() {
      this.selectedConfig = null
    },

    // Clear error
    clearError() {
      this.error = null
    },
  },
})
