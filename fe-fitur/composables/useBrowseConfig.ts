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
  table?: string
  joins?: string[]
  whereExtra?: string
  alias_fields?: Record<string, string>
  parent_filters?: Array<{ source_column: string; operator: string; type: string }>
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
   * NOTE: must check specific codes BEFORE prefixes to avoid mis-match.
   */
  function buildDefaultConfig(browseType: string): BrowseConfig {
    // Specific codes first (must match exact BrowseService.php entries)
    const knownConfig: Record<string, BrowseConfig> = {
      // Perkiraan variants
      '10051': { kodeBrowse: '10051', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: ['Simbol', 'Tipe', 'DK'] },
      '1005': { kodeBrowse: '1005', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: ['Simbol'] },
      '10053': { kodeBrowse: '10053', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: [] },
      '10054': { kodeBrowse: '10054', keyField: 'Nomor', labelField: 'Keterangan', additionalFields: [] },
      'perkiraan': { kodeBrowse: '1005', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: [] },
      // Hutang/ Piutang account codes (used by reports 020301-020306)
      '100409': { kodeBrowse: '100409', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: ['Neraca', 'Kelompok', 'Tipe'] },
      '100408': { kodeBrowse: '100408', keyField: 'Perkiraan', labelField: 'Keterangan', additionalFields: [] },
      // Devisi
      '1004': { kodeBrowse: '1004', keyField: 'Devisi', labelField: 'NamaDevisi', additionalFields: [] },
      // Customer/Supplier
      '10141': { kodeBrowse: '10141', keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: ['Alamat', 'Telpon'] },
      '10142': { kodeBrowse: '10142', keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: ['Alamat', 'Kota', 'DueDate', 'JENIS', 'IsPpn'] },
      '10143': { kodeBrowse: '10143', keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: ['Alamat', 'Telpon'] },
      '1014': { kodeBrowse: '1014', keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: ['Alamat', 'Kota', 'Perkiraan'] },
      // Barang
      '911': { kodeBrowse: '911', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: ['Isi2', 'Sat1', 'Sat2'] },
      '912': { kodeBrowse: '912', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: [] },
      '915': { kodeBrowse: '915', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: [] },
      '917': { kodeBrowse: '917', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: [] },
      '120302': { kodeBrowse: '120302', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: ['Sat1', 'Sat2', 'Isi1', 'Isi2', 'NFix'] },
      '3001101': { kodeBrowse: '3001101', keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: ['Sat1', 'Sat2', 'Isi'] },
      // Gudang
      '916': { kodeBrowse: '916', keyField: 'KodeGdg', labelField: 'Nama', additionalFields: [] },
      '11002': { kodeBrowse: '11002', keyField: 'KodeGdg', labelField: 'Nama', additionalFields: ['Alamat'] },
      '11009': { kodeBrowse: '11009', keyField: 'KodeGdg', labelField: 'Nama', additionalFields: [] },
      // Bagian
      '1002': { kodeBrowse: '1002', keyField: 'KodeBag', labelField: 'Namabag', additionalFields: [] },
      // Valas
      '1006': { kodeBrowse: '1006', keyField: 'KodeVls', labelField: 'NamaVls', additionalFields: ['Kurs'] },
      '11001': { kodeBrowse: '11001', keyField: 'KodeVls', labelField: 'NamaVls', additionalFields: ['Kurs'] },
      '2082': { kodeBrowse: '2082', keyField: 'KodeVls', labelField: 'NamaVls', additionalFields: ['Kurs'] },
      // Kategori
      '1008': { kodeBrowse: '1008', keyField: 'KodeKategori', labelField: 'Keterangan', additionalFields: [] },
      '10081': { kodeBrowse: '10081', keyField: 'KodeKategori', labelField: 'Keterangan', additionalFields: [] },
      // Kota
      '11011': { kodeBrowse: '11011', keyField: 'KodeKota', labelField: 'NamaKota', additionalFields: ['KodeArea', 'NamaArea'] },
      // Sub Grup
      '157': { kodeBrowse: '157', keyField: 'KodeSubGrp', labelField: 'NamaSubGrp', additionalFields: [] },
    }

    if (knownConfig[browseType]) {
      return knownConfig[browseType]
    }

    // Prefix-based fallback (only for unknown codes)
    if (browseType.startsWith('100')) {
      return { kodeBrowse: browseType, keyField: 'Kode', labelField: 'Nama', additionalFields: [] }
    }
    if (browseType.startsWith('91')) {
      return { kodeBrowse: browseType, keyField: 'KodeBrg', labelField: 'NamaBrg', additionalFields: [] }
    }
    if (browseType.startsWith('11')) {
      return { kodeBrowse: browseType, keyField: 'KodeGdg', labelField: 'Nama', additionalFields: [] }
    }
    if (browseType.startsWith('101')) {
      return { kodeBrowse: browseType, keyField: 'KodeCustSupp', labelField: 'NamaCustSupp', additionalFields: [] }
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