<template>
  <div class="min-h-screen bg-secondary-100">
    <!-- Header -->
    <div class="bg-white border-b border-secondary-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-secondary-900">
            {{ reportStore.currentReport?.nama_laporan || reportStore.currentReport?.Keterangan || 'Report' }}
          </h1>
          <p v-if="reportStore.currentReport?.deskripsi" class="text-sm text-secondary-500 mt-1">
            {{ reportStore.currentReport.deskripsi }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button
            v-if="reportStore.datasets['T2'] || reportStore.reportData"
            @click="exportReport"
            class="btn-secondary flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export
          </button>

          <button
            @click="router.push('/reports')"
            class="btn-secondary flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6">
      <!-- Loading State: Fetching Report Config -->
      <div v-if="reportStore.loading && !reportStore.currentReport" class="flex items-center justify-center py-20">
        <div class="text-center">
          <div class="animate-spin w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p class="text-secondary-600">Loading report configuration...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="reportStore.error" class="card p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="text-lg font-medium text-secondary-900 mb-2">Error Loading Report</h3>
        <p class="text-secondary-500 mb-4">{{ reportStore.error }}</p>
        <button @click="loadReport" class="btn-primary">
          Retry
        </button>
      </div>

      <!-- Report Content -->
      <template v-else-if="reportStore.currentReport">
        <!-- Filter Panel -->
        <div v-if="effectiveFilters.length > 0" class="card p-6 mb-6">
          <h3 class="text-sm font-medium text-secondary-700 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter Parameters
            <span class="text-xs text-secondary-400">(Kode: {{ accessCode }})</span>
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="filter in effectiveFilters" :key="filter.id">
              <label class="block text-sm font-medium text-secondary-700 mb-1">
                {{ filter.label }}
                <span v-if="filter.required" class="text-red-500">*</span>
              </label>

              <!-- Date Input -->
              <input
                v-if="filter.type === 'date'"
                type="date"
                v-model="dynamicFilterValues[filter.id]"
                class="input-field"
              />

              <!-- Text Input -->
              <input
                v-else-if="filter.type === 'text'"
                type="text"
                v-model="dynamicFilterValues[filter.id]"
                class="input-field"
                :placeholder="filter.label"
              />

              <!-- Number Input -->
              <input
                v-else-if="filter.type === 'number'"
                type="number"
                v-model="dynamicFilterValues[filter.id]"
                class="input-field"
              />

              <!-- Browse Autocomplete (generic) -->
              <BrowseAutocomplete
                v-else-if="filter.type === 'browse'"
                v-model="dynamicFilterValues[filter.id]"
                :browse-type="filter.kode_browse || 'perkiraan'"
                :placeholder="filter.label"
              />

              <!-- Legacy perkiraan type (maps to browse) -->
              <BrowseAutocomplete
                v-else-if="filter.type === 'perkiraan'"
                v-model="dynamicFilterValues[filter.id]"
                browse-type="perkiraan"
                :placeholder="filter.label"
              />

              <!-- Default fallback -->
              <input
                v-else
                type="text"
                v-model="dynamicFilterValues[filter.id]"
                class="input-field"
              />
            </div>
          </div>

          <div class="flex items-center gap-3 mt-4 pt-4 border-t border-secondary-200">
            <button
              @click="generateReport"
              :disabled="reportStore.generating"
              class="btn-primary flex items-center gap-2"
            >
              <svg v-if="reportStore.generating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <span>{{ reportStore.generating ? 'Generating...' : 'Generate Report' }}</span>
            </button>

            <button
              @click="reportStore.resetFilters"
              class="btn-secondary"
            >
              Reset Filters
            </button>
          </div>
        </div>

        <!-- No Filters: Generate Button -->
        <div v-else class="card p-6 mb-6">
          <div class="flex items-center justify-between">
            <p class="text-secondary-600">No filter parameters configured for this report.</p>
            <button
              @click="generateReport"
              :disabled="reportStore.generating"
              class="btn-primary flex items-center gap-2"
            >
              <svg v-if="reportStore.generating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <span>{{ reportStore.generating ? 'Generating...' : 'Generate Report' }}</span>
            </button>
          </div>
        </div>

        <!-- Loading State: Generating Report -->
        <div v-if="reportStore.generating" class="flex items-center justify-center py-20">
          <div class="text-center">
            <div class="animate-spin w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-secondary-600">Generating report...</p>
            <p class="text-sm text-secondary-400 mt-1">This may take a moment</p>
          </div>
        </div>

        <!-- Report Preview -->
        <div v-else-if="reportStore.reportData" class="card overflow-hidden">
          <!-- Preview Header -->
          <div class="px-6 py-4 bg-secondary-50 border-b border-secondary-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span class="text-sm font-medium text-secondary-700">
                {{ (reportStore.datasets['T2'] || reportStore.reportData || []).length }} records found
              </span>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="printReport"
                class="btn-secondary text-sm py-1.5"
              >
                Print
              </button>
            </div>
          </div>

          <!-- Data Table - Generic Multi-Dataset Support -->
          <div class="overflow-x-auto">
            <!-- Special 2-Column Layout for Neraca (20503) - Aktiva & Pasiva side-by-side -->
            <div v-if="allReportDatasets.length === 2" class="grid grid-cols-2 gap-6">
              <div v-for="(dataset, dsIndex) in allReportDatasets" :key="dataset.nama_dataset">

                <!-- Dataset Section Header -->
                <div class="px-4 py-2 bg-secondary-100 border rounded-t-lg flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-secondary-800">
                    {{ dataset.deskripsi || dataset.nama_dataset }}
                  </h3>
                  <span class="text-xs text-secondary-500">
                    {{ getDatasetRecordCount(dataset.nama_dataset) }} records
                  </span>
                </div>

                <!-- Grouped Table for this dataset -->
                <div v-if="hasGrouping" class="border rounded-b-lg overflow-hidden">
                  <GroupedTable
                    :groupedData="getGroupedForDataset(dataset.nama_dataset)"
                    :columns="reportStore.columns"
                    :grandTotal="reportStore.grandTotal"
                    :mainDataset="dataset.nama_dataset"
                  />
                </div>

                <!-- Regular Table for this dataset -->
                <table v-else class="w-full text-sm border rounded-b-lg overflow-hidden">
                  <thead class="bg-secondary-50 text-secondary-700">
                    <tr>
                      <th
                        v-for="(header, idx) in getColumnLabelsForDataset(dataset.nama_dataset)"
                        :key="idx"
                        class="px-4 py-3 text-left font-medium border-b border-secondary-200"
                      >
                        {{ header }}
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-secondary-100">
                    <tr
                      v-for="(row, rowIdx) in (reportStore.datasets[dataset.nama_dataset] || []).slice(0, 100)"
                      :key="rowIdx"
                      class="hover:bg-secondary-50"
                    >
                      <td
                        v-for="(colKey, colIdx) in getColumnHeadersForDataset(dataset.nama_dataset)"
                        :key="colIdx"
                        class="px-4 py-3 border-b border-secondary-100"
                      >
                        {{ formatCell(row[colKey]) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Standard Layout for other reports (stacked) -->
            <div v-else>
              <div v-for="(dataset, dsIndex) in allReportDatasets" :key="dataset.nama_dataset" class="mb-6 last:mb-0">

                <!-- Dataset Section Header -->
                <div class="px-6 py-3 bg-secondary-100 border-b flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-secondary-800">
                    {{ dataset.deskripsi || dataset.nama_dataset }}
                  </h3>
                  <span class="text-xs text-secondary-500">
                    {{ getDatasetRecordCount(dataset.nama_dataset) }} records
                  </span>
                </div>

                <!-- Grouped Table for this dataset -->
                <div v-if="hasGrouping">
                  <GroupedTable
                    :groupedData="getGroupedForDataset(dataset.nama_dataset)"
                    :columns="reportStore.columns"
                    :grandTotal="reportStore.grandTotal"
                    :mainDataset="dataset.nama_dataset"
                  />
                </div>

                <!-- Regular Table for this dataset -->
                <table v-else class="w-full text-sm">
                  <thead class="bg-secondary-50 text-secondary-700">
                    <tr>
                      <th
                        v-for="(header, idx) in getColumnLabelsForDataset(dataset.nama_dataset)"
                        :key="idx"
                        class="px-4 py-3 text-left font-medium border-b border-secondary-200"
                      >
                        {{ header }}
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-secondary-100">
                    <tr
                      v-for="(row, rowIdx) in (reportStore.datasets[dataset.nama_dataset] || []).slice(0, 100)"
                      :key="rowIdx"
                      class="hover:bg-secondary-50"
                    >
                      <td
                        v-for="(colKey, colIdx) in getColumnHeadersForDataset(dataset.nama_dataset)"
                        :key="colIdx"
                        class="px-4 py-3 border-b border-secondary-100"
                      >
                        {{ formatCell(row[colKey]) }}
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- Show all records link for this dataset -->
                <div v-if="getDatasetRecordCount(dataset.nama_dataset) > 100 && !hasGrouping" class="px-6 py-4 text-center text-sm text-secondary-500">
                  Showing first 100 of {{ getDatasetRecordCount(dataset.nama_dataset) }} records.
                  <button @click="showAllRecords = true" class="text-primary-500 hover:underline">
                    Show all
                  </button>
                </div>

              </div>
            </div>
          </div>

          <!-- T1 Summary Section - 2 Column Layout (database-driven) -->
          <div v-if="t1SummaryData" class="mt-4 border-t-2 border-secondary-300 pt-4 bg-secondary-50">
            <div class="grid grid-cols-2 gap-6">
              <!-- Left Column: Cash Details -->
              <div>
                <table class="w-full text-sm">
                  <tbody>
                    <tr v-for="field in t1LeftFields" :key="field.key">
                      <td class="py-1 pr-4 text-secondary-600 w-1/3">{{ field.label }}</td>
                      <td class="py-1 text-secondary-900 font-medium text-right">{{ formatCell(t1SummaryData[field.key]) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Right Column: Totals & Summary -->
              <div>
                <table class="w-full text-sm">
                  <tbody>
                    <tr v-for="field in t1RightFields" :key="field.key">
                      <td class="py-1 pr-4 text-secondary-600 w-1/2">{{ field.label }}</td>
                      <td class="py-1 text-secondary-900 font-medium text-right">{{ formatCell(t1SummaryData[field.key]) }}</td>
                    </tr>
                  </tbody>
                </table>

                <!-- Dynamic Signature Section from footer_bands config -->
                <div
                  v-if="signatureItems.length > 0"
                  class="grid gap-4 mt-4 pt-3 border-t border-secondary-300"
                  :class="signatureGridClass"
                >
                  <div
                    v-for="sig in signatureItems"
                    :key="sig.label"
                    class="text-center"
                  >
                    <p class="text-xs text-secondary-500 mb-8">{{ sig.label }}</p>
                    <p class="text-xs text-secondary-700">(....................)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No Data State -->
        <div v-else class="card p-12 text-center">
          <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-lg font-medium text-secondary-900 mb-2">No Report Data</h3>
          <p class="text-secondary-500">Click "Generate Report" to view the report preview.</p>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const reportStore = useReportStore()

const kodeMenu = computed(() => route.params.kode as string)
const showAllRecords = ref(false)

// Get ACCESS code from current report
const accessCode = computed(() => {
  const access = reportStore.currentReport?.ACCESS
  return typeof access === 'string' ? parseInt(access) : (access || 0)
})

// Get headers and column config from columns definition
// For multi-dataset reports (like Kas Harian), use T2 for detail/transactions
const reportHeaders = computed(() => {
  // For multi-dataset reports, check if T2 exists
  if (reportStore.currentReport?.columns?.['T2']) {
    const cols = reportStore.currentReport.columns['T2']
    return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
  }
  // For single-dataset reports, use first dataset
  if (reportStore.currentReport?.columns) {
    const mainDataset = reportStore.currentReport.datasets?.[0]?.nama_dataset || 'Daftar Perkiraan'
    const cols = reportStore.currentReport.columns[mainDataset] || []
    if (cols.length > 0) {
      return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
    }
  }
  // Fallback to data keys
  const data = reportStore.datasets['T2'] || reportStore.reportData
  if (!data?.length) return []
  return Object.keys(data[0])
})

// Get column labels for display
const columnLabels = computed(() => {
  // For multi-dataset reports, use T2 columns
  if (reportStore.currentReport?.columns?.['T2']) {
    const cols = reportStore.currentReport.columns['T2']
    return cols.filter(c => c.is_visible !== false).map(c => c.label_tampil || c.nama_kolom)
  }
  // For single-dataset reports
  if (reportStore.currentReport?.columns) {
    const mainDataset = reportStore.currentReport.datasets?.[0]?.nama_dataset || 'Daftar Perkiraan'
    const cols = reportStore.currentReport.columns[mainDataset] || []
    if (cols.length > 0) {
      return cols.filter(c => c.is_visible !== false).map(c => c.label_tampil || c.nama_kolom)
    }
  }
  return reportHeaders.value
})

// T1 Summary Data (for multi-dataset reports like Kas Harian)
// Include calculated fields from T2 transactions (mimics .fr3 FastReport script)
const t1SummaryData = computed(() => {
  const t1 = reportStore.datasets['T1']
  const t2 = reportStore.datasets['T2']
  if (!t1 || t1.length === 0) return null

  const data = { ...t1[0] }

  if (t2 && t2.length > 0) {
    // Sum all transaction columns
    const sumDebet = t2.reduce((sum: number, row: any) => sum + parseFloat(row.Debet || 0), 0)
    const sumKredit = t2.reduce((sum: number, row: any) => sum + parseFloat(row.kredit || 0), 0)
    const sumDebet2 = t2.reduce((sum: number, row: any) => sum + parseFloat(row.debet2 || 0), 0)
    const sumKredit2 = t2.reduce((sum: number, row: any) => sum + parseFloat(row.kredit2 || 0), 0)

    // Base values from T1 SP
    const saldoAwal = parseFloat(data.SaldoAwal || 0)
    const saldoAwalD = parseFloat(data.SaldoAwalD || 0)
    const saldoAwalK = parseFloat(data.SaldoAwalK || 0)

    // SaldoAkhirD = SaldoAwal + SUM(debet) - SUM(kredit) - SUM(kredit2)
    data.SaldoAkhirD = saldoAwal + sumDebet + sumDebet2 - sumKredit - sumKredit2

    // SaldoAkhirK = SaldoAwal + SUM(debet2) - SUM(kredit2)
    data.SaldoAkhirK = saldoAwal + sumDebet2 - sumKredit2

    // TotalD = SUM(debet) + SaldoAwalD + SaldoAkhirD
    data.TotalD = sumDebet + saldoAwalD + data.SaldoAkhirD

    // TotalK = SUM(kredit) + SaldoAwalK + SaldoAkhirK
    data.TotalK = sumKredit + saldoAwalK + data.SaldoAkhirK

    // Saldo = if SaldoAkhirD > 0 then SaldoAkhirD else SaldoAkhirK
    data.Saldo = data.SaldoAkhirD > 0 ? data.SaldoAkhirD : data.SaldoAkhirK

    // Tunai = (SUM(debet) + SUM(debet2) + SaldoAwal - SUM(kredit) - SUM(kredit2)) - TotalBonGiro
    const saldoGiro = parseFloat(data.SaldoGiro || 0)
    const saldoBon = parseFloat(data.SaldoBon || 0)
    const saldoBonD = parseFloat(data.SaldoBonD || 0)
    const saldoBonE = parseFloat(data.SaldoBonE || 0)
    const saldoBonA = parseFloat(data.SaldoBonA || 0)
    const saldoBonDH = parseFloat(data.SaldoBonDH || 0)
    const saldoGiroTolakan = parseFloat(data.SaldoGiroTolakan || 0)
    const totalBonGiro = saldoGiro + saldoBon + saldoBonD + saldoBonE + saldoBonA + saldoBonDH + saldoGiroTolakan
    data.Tunai = (sumDebet + sumDebet2 + saldoAwal - sumKredit - sumKredit2) - totalBonGiro
  }

  return data
})

// Get T1 column labels for summary display
const t1Labels = computed(() => {
  if (reportStore.currentReport?.columns?.['T1']) {
    const cols = reportStore.currentReport.columns['T1']
    const labelMap: Record<string, string> = {}
    cols.forEach((c: any) => {
      labelMap[c.nama_kolom] = c.label_tampil || c.nama_kolom
    })
    return labelMap
  }
  return {}
})

// T1 Left Column Fields (kas details: Tunai, Giro, Bon, etc.)
const t1LeftFields = computed(() => {
  if (!reportStore.currentReport?.columns?.['T1']) return []
  // Use all T1 columns for left section (saldo details)
  return reportStore.currentReport.columns['T1']
    .map((c: any) => ({ key: c.nama_kolom, label: c.label_tampil || c.nama_kolom }))
})

// T1 Right Column Fields (totals from SP)
const t1RightFields = computed(() => {
  if (!reportStore.currentReport?.columns?.['T1']) return []
  // T1 from SP has: SaldoAwalD, SaldoAkhirD, TotalD (computed values)
  return [
    { key: 'TotalD', label: 'Total' },
    { key: 'TotalK', label: 'Total (K)' },
    { key: 'SaldoAwalD', label: 'Saldo Awal' },
    { key: 'SaldoAkhirD', label: 'Saldo Akhir' },
    { key: 'SaldoAwalK', label: 'Saldo Awal (K)' },
    { key: 'SaldoAkhirK', label: 'Saldo Akhir (K)' }
  ]
})

// Get signature items from footer_bands config
const signatureItems = computed(() => {
  const footerBands = reportStore.currentReport?.footer_bands
  if (!footerBands?.bands?.summary?.signatures) return []
  return footerBands.bands.summary.signatures
})

// Get grid class for signature layout
const signatureGridClass = computed(() => {
  const footerBands = reportStore.currentReport?.footer_bands
  const cols = footerBands?.bands?.summary?.layout?.columns || signatureItems.value.length
  const alignment = footerBands?.bands?.summary?.layout?.alignment || 'spread'

  // Map alignment to tailwind classes
  const alignClass = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-end',
    spread: 'justify-between'
  }[alignment] || 'justify-between'

  return `grid-cols-${Math.min(cols, 6)} ${alignClass}`
})

function getT1Label(key: string): string {
  return t1Labels.value[key as keyof typeof t1Labels.value] || String(key).replace(/([A-Z])/g, ' $1').trim()
}

// Initialize dynamic filter values (before watch)
const dynamicFilterValues = ref<Record<string, string>>({})

// Initialize filter values when report loads
watch(() => reportStore.currentReport, () => {
  // Use defaultPeriod from dbperiode if available, otherwise fallback to current month
  if (reportStore.defaultPeriod) {
    dynamicFilterValues.value = {
      TglAwal: reportStore.defaultPeriod.tglAwal,
      TglAkhir: reportStore.defaultPeriod.tglAkhir,
      Bulan: String(reportStore.defaultPeriod.bulan),
      Tahun: String(reportStore.defaultPeriod.tahun)
    }
  } else {
    const today = new Date()
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
    dynamicFilterValues.value = {
      TglAwal: firstDay.toISOString().split('T')[0],
      TglAkhir: today.toISOString().split('T')[0],
      Bulan: String(today.getMonth() + 1),
      Tahun: String(today.getFullYear())
    }
  }
}, { immediate: true })

// Get effective filters (from DB masterlaporan ONLY - no hardcoded fallback)
const effectiveFilters = computed(() => {
  // Use filters from dbmasterlaporan (dbparameterlaporan table)
  if (reportStore.currentReport?.filters?.length > 0) {
    return reportStore.currentReport.filters.map((f: any) => ({
      id: f.nama_filter,
      label: f.label || f.nama_filter.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1').trim(),
      type: f.tipe_input || 'text',
      required: f.wajib_isi,
      defaultValue: f.nilai_default
    }))
  }

  // No filters in DB = no filter UI (matching Delphi -1 behavior)
  return []
})

// ===== Generic Multi-Dataset Support =====
const allReportDatasets = computed(() => {
  return reportStore.currentReport?.datasets || []
})

const hasGrouping = computed(() => {
  return reportStore.currentReport?.grouping?.length > 0
})

function getDatasetRecordCount(namaDataset: string): number {
  return (reportStore.datasets[namaDataset] || []).length
}

function getGroupedForDataset(namaDataset: string) {
  if (reportStore.groupedData && typeof reportStore.groupedData === 'object') {
    // Check if groupedData has dataset-specific keys
    if ('_main' in reportStore.groupedData && !reportStore.groupedData[namaDataset]) {
      return reportStore.groupedData['_main']
    }
    return reportStore.groupedData[namaDataset] || reportStore.groupedData['_main'] || null
  }
  return null
}

function getColumnHeadersForDataset(namaDataset: string): string[] {
  const cols = reportStore.columns[namaDataset] || []
  return cols.filter(c => c.is_visible !== false).map(c => c.nama_kolom)
}

function getColumnLabelsForDataset(namaDataset: string): string[] {
  const cols = reportStore.columns[namaDataset] || []
  return cols.filter(c => c.is_visible !== false).map(c => c.label_tampil || c.nama_kolom)
}
// ===== End Generic Multi-Dataset Support =====

// Load report config then auto-generate (for no-filter reports)
onMounted(async () => {
  if (kodeMenu.value) {
    await reportStore.fetchReport(kodeMenu.value)
    // Auto-generate if no filters configured
    if (!reportStore.currentReport?.filters?.length && reportStore.currentReport) {
      await generateReport()
    }
  }
})

// Reload on route change
watch(kodeMenu, () => {
  loadReport()
})

async function loadReport() {
  if (kodeMenu.value) {
    await reportStore.fetchReport(kodeMenu.value)
  }
}

async function generateReport() {
  // Merge dynamic filters with store filters
  const allFilters = {
    ...reportStore.filters,
    ...dynamicFilterValues.value
  }

  if (kodeMenu.value) {
    await reportStore.generateReportWithFilters(kodeMenu.value, allFilters)
  }
}

function formatCell(value: any): string {
  if (value === null || value === undefined) return '-'
  if (typeof value === 'number') return value.toLocaleString('id-ID')
  if (typeof value === 'boolean') return value ? 'Yes' : 'No'
  if (typeof value === 'string') {
    // Handle ".000000" format (empty numeric from SP)
    if (value === '.000000' || value === '.00' || value === '.0') return '-'
    // Handle date formats: "2022-02-01 000000.000" or "2022-02-01"
    const dateMatch = value.match(/^(\d{4}-\d{2}-\d{2})/)
    if (dateMatch) {
      return new Date(dateMatch[1]).toLocaleDateString('id-ID')
    }
  }
  return String(value)
}

function printReport() {
  window.print()
}

async function exportReport(format = 'csv') {
  const datasets = reportStore.datasets
  const detailData = datasets['T2'] || datasets[Object.keys(datasets)[0]]

  if (!detailData || detailData.length === 0) {
    alert('No data to export')
    return
  }

  const reportName = reportStore.currentReport?.nama_laporan || reportStore.currentReport?.Keterangan || 'Report'
  const timestamp = new Date().toISOString().slice(0, 10)
  const filename = `${reportName}_${timestamp}`

  if (format === 'csv') {
    exportCSV(filename, detailData)
  } else if (format === 'print') {
    window.print()
  }
}

function exportCSV(filename: string, data: any[]) {
  if (!data || data.length === 0) return

  // Get column config
  const mainDataset = reportStore.currentReport?.datasets?.[0]?.nama_dataset || 'T2'
  const cols = reportStore.currentReport?.columns?.[mainDataset] || []

  // Build CSV
  const rows: string[] = []

  // Header row from column config
  const headers = cols.map(c => c.label_tampil || c.nama_kolom)
  rows.push(headers.map(h => `"${h}"`).join(','))

  // Data rows
  for (const row of data) {
    const values = cols.map(c => {
      const key = c.nama_kolom
      const val = row[key]
      if (val === null || val === undefined) return '""'
      const str = String(val).replace(/"/g, '""')
      return `"${str}"`
    })
    rows.push(values.join(','))
  }

  // Download
  const csv = rows.join('\n')
  const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${filename}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

// Clear report on leave
onUnmounted(() => {
  reportStore.clearReport()
})
</script>