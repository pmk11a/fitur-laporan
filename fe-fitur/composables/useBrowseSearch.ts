/**
 * useBrowseSearch — composable for browse search + validate logic
 * Used by BrowseAutocomplete component (all 3 modes).
 *
 * SOLID:
 * - D: Depends on API abstraction ($fetch), not hardcoded URL
 * - S: Single responsibility — search + validate only
 *
 * Note: Nuxt 3 auto-imports useRuntimeConfig and useAuthStore.
 */

export interface BrowseSearchResult {
  [key: string]: string | number | null
}

export function useBrowseSearch(browseType: string) {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const results = ref<BrowseSearchResult[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const validatedItems = ref<Map<string, BrowseSearchResult>>(new Map())

  // Lazily resolved config from backend
  const browseConfig = ref<{ keyField: string; labelField: string } | null>(null)

  /**
   * Get effective browse type (map legacy to valid types)
   */
  function getEffectiveBrowseType(): string {
    if (browseType === 'perkiraan') return '1005'
    return browseType
  }

  /**
   * Resolve keyField/labelField from backend config.
   * Cached after first successful fetch; falls back to local maps.
   */
  async function resolveConfig(): Promise<void> {
    if (browseConfig.value) return
    try {
      const effectiveType = getEffectiveBrowseType()
      const resp = await $fetch<{ success: boolean; data: { keyField: string; labelField: string } }>(
        `${config.public.apiBase}/browse/${effectiveType}/config`,
        { headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {} }
      )
      if (resp.success && resp.data) {
        browseConfig.value = { keyField: resp.data.keyField, labelField: resp.data.labelField }
      }
    } catch {
      // fallback handled by getKeyField/getLabelField below
    }
  }

  /**
   * Search records for the browse type.
   * Debounce is handled by the component — this just calls the API.
   */
  async function search(q: string, limit = 20, parentFilters?: Record<string, string>): Promise<BrowseSearchResult[]> {
    loading.value = true
    error.value = null

    try {
      const effectiveType = getEffectiveBrowseType()
      const queryParams: Record<string, string | number> = { q, limit }
      if (parentFilters && typeof parentFilters === 'object') {
        for (const [key, value] of Object.entries(parentFilters)) {
          if (value) {
            queryParams[`parent[${key}]`] = value
          }
        }
      }
      const response = await $fetch<{ success: boolean; data: BrowseSearchResult[] }>(
        `${config.public.apiBase}/browse/${effectiveType}`,
        {
          headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {},
          query: queryParams,
        }
      )

      if (response.success) {
        results.value = response.data
        return response.data
      } else {
        error.value = 'Search failed'
        return []
      }
    } catch (e: any) {
      error.value = e.data?.message || 'Failed to search browse'
      return []
    } finally {
      loading.value = false
    }
  }

  /**
   * Validate a single code — check if it exists in DB.
   * Used by single mode (blur/enter) and tags mode.
   */
  async function validate(code: string, parentFilters?: Record<string, string>): Promise<BrowseSearchResult | null> {
    if (!code || code.trim() === '') {
      loading.value = false
      return null
    }

    // Check cache first
    if (validatedItems.value.has(code)) {
      loading.value = false
      return validatedItems.value.get(code) || null
    }

    try {
      const effectiveType = getEffectiveBrowseType()
      const queryParams: Record<string, string | number> = { q: code, limit: 1 }
      if (parentFilters && typeof parentFilters === 'object') {
        for (const [key, value] of Object.entries(parentFilters)) {
          if (value) {
            queryParams[`parent[${key}]`] = value
          }
        }
      }
      const response = await $fetch<{ success: boolean; data: BrowseSearchResult[] }>(
        `${config.public.apiBase}/browse/${effectiveType}`,
        {
          headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {},
          query: queryParams,
        }
      )

      loading.value = false

      if (response.success && response.data.length > 0) {
        // Exact match check
        const exact = response.data.find(
          (item) => String(item[getKeyField()]).toUpperCase() === code.toUpperCase()
        )
        if (exact) {
          validatedItems.value.set(code, exact)
          return exact
        }
      }

      // Not found → free-text mode should allow this
      return null
    } catch {
      loading.value = false
      return null
    }
  }

  /**
   * Validate multiple codes (batch) — used by tags mode.
   */
  async function validateBatch(codes: string[], parentFilters?: Record<string, string>): Promise<BrowseSearchResult[]> {
    if (!codes || codes.length === 0) return []

    const found: BrowseSearchResult[] = []
    const promises = codes.map(async (code) => {
      const result = await validate(code, parentFilters)
      if (result) {
        found.push(result)
      } else {
        // Free-text — include with just key field
        found.push({ [getKeyField()]: code } as BrowseSearchResult)
      }
    })

    await Promise.all(promises)
    return found
  }

  /**
   * Get all records (no filter) — used by checkbox mode to load full list.
   */
  async function getAll(limit = 500, parentFilters?: Record<string, string>): Promise<BrowseSearchResult[]> {
    return search('', limit, parentFilters)
  }

  function clear() {
    results.value = []
    error.value = null
  }

  // Local fallback maps — only used if backend /config call fails (offline, etc.)
  const keyFieldMap: Record<string, string> = {
    '10141': 'KodeCustSupp', '10142': 'KodeCustSupp', '10143': 'KodeCustSupp',
    '911': 'KodeBrg', '912': 'KodeBrg', '915': 'KodeBrg', '917': 'KodeBrg',
    '120302': 'KodeBrg', '3001101': 'KodeBrg',
    '916': 'KodeGdg', '11002': 'KodeGdg', '11009': 'KodeGdg',
    '1004': 'Devisi',
    '1006': 'KodeVls', '11001': 'KodeVls', '2082': 'KodeVls',
    '1008': 'KodeKategori', '10081': 'KodeKategori',
    '1002': 'KodeBag', '10021': 'KdDep', '1003': 'KodeJab',
    '91117': 'NOSPK', '9111': 'KodeKota', '157': 'KodeSubGrp',
    '10054': 'Nomor', '100409': 'Perkiraan', '100408': 'Perkiraan', 'perkiraan': 'Perkiraan',
  }
  const labelFieldMap: Record<string, string> = {
    '10141': 'NamaCustSupp', '10142': 'NamaCustSupp', '10143': 'NamaCustSupp',
    '911': 'NamaBrg', '912': 'NamaBrg', '915': 'NamaBrg', '917': 'NamaBrg',
    '120302': 'NamaBrg', '3001101': 'NamaBrg',
    '916': 'Nama', '11002': 'Nama', '11009': 'Nama',
    '1004': 'NamaDevisi',
    '1006': 'NamaVls', '11001': 'NamaVls', '2082': 'NamaVls',
    '1008': 'Keterangan', '10081': 'Keterangan',
    '1002': 'Namabag', '10021': 'NmDep', '1003': 'Namajab',
    '91117': 'NamaBrg', '9111': 'NamaKota', '157': 'NamaSubGrp',
    '10054': 'Keterangan', '100409': 'Keterangan', '100408': 'Keterangan',
  }

  function getKeyField(): string {
    return browseConfig.value?.keyField ?? keyFieldMap[browseType] ?? 'Perkiraan'
  }

  function getLabelField(): string {
    return browseConfig.value?.labelField ?? labelFieldMap[browseType] ?? 'Keterangan'
  }

  return {
    results,
    loading,
    error,
    search,
    validate,
    validateBatch,
    getAll,
    clear,
    getKeyField,
    getLabelField,
    resolveConfig,
  }
}