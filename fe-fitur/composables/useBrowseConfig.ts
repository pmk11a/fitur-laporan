/**
 * useBrowseConfig — fetch & cache browse config from backend
 *
 * Config is cached per session (module-level singleton).
 * SOLID:
 * - D: Depends on API abstraction, not hardcoded
 * - S: Single responsibility — fetch + cache config
 */
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from '~/stores/auth'

export interface BrowseConfig {
  kodeBrowse: string
  keyField: string
  labelField: string
  additionalFields: string[]
}

export function useBrowseConfig() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  // Module-level cache — persists across component instances
  const configCache = new Map<string, BrowseConfig>()

  /**
   * Get effective browse type (map legacy to valid types)
   */
  function getEffectiveBrowseType(browseType: string): string {
    if (browseType === 'perkiraan') return '1005'
    return browseType
  }

  /**
   * Fetch config for a browse type.
   * Caches after first fetch per session.
   */
  async function getConfig(browseType: string): Promise<BrowseConfig | null> {
    if (configCache.has(browseType)) {
      return configCache.get(browseType)!
    }

    const effectiveType = getEffectiveBrowseType(browseType)

    try {
      const response = await $fetch<{ success: boolean; data: BrowseConfig }>(
        `${config.public.apiBase}/browse/${effectiveType}/config`,
        {
          headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {},
        }
      )

      if (response.success) {
        configCache.set(browseType, response.data)
        return response.data
      }
    } catch {
      // Config fetch failed — fallback defaults
    }

    // Fallback defaults if config not available
    const defaults = buildDefaultConfig(browseType)
    configCache.set(browseType, defaults)
    return defaults
  }

  /**
   * Get all browse types.
   */
  async function getAllTypes(): Promise<BrowseConfig[]> {
    try {
      const response = await $fetch<{ success: boolean; data: BrowseConfig[] }>(
        `${config.public.apiBase}/browse/types`,
        {
          headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {},
        }
      )

      if (response.success) {
        return response.data
      }
    } catch {
      // Ignore
    }
    return []
  }

  /**
   * Build default config when backend config unavailable.
   * Based on browse type naming convention.
   */
  function buildDefaultConfig(browseType: string): BrowseConfig {
    // Perkiraan-like types
    if (browseType.startsWith('100') || browseType.startsWith('perkiraan')) {
      return { kodeBrowse: browseType, keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: [] }
    }
    // Map legacy tipe_input values to valid browse types
    if (browseType === 'perkiraan') {
      return { kodeBrowse: '1005', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: [] }
    }
    // Customer/Supplier
    if (browseType.startsWith('101') || browseType.startsWith('cust') || browseType.startsWith('supp')) {
      return { kodeBrowse: browseType, keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: ['Alamat', 'Kota'] }
    }
    // Barang
    if (browseType.startsWith('91') || browseType.startsWith('barang') || browseType.startsWith('12')) {
      return { kodeBrowse: browseType, keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: ['Sat1', 'Sat2'] }
    }
    // Gudang
    if (browseType.startsWith('11') || browseType.startsWith('gudang')) {
      return { kodeBrowse: browseType, keyField: 'KodeGdg', labelField: 'Nama', additionalFields: [] }
    }
    // Default fallback
    return { kodeBrowse: browseType, keyField: 'Kode', labelField: 'Nama', additionalFields: [] }
  }

  return {
    getConfig,
    getAllTypes,
    configCache,
  }
}