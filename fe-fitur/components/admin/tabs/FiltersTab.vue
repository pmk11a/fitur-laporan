<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-medium text-secondary-700">Parameter Filter</h3>
      <button @click="openModal()" class="btn-primary text-xs py-1.5">+ Tambah Filter</button>
    </div>

    <!-- Empty State -->
    <div v-if="!store.selectedReportData?.filters?.length" class="card p-8 text-center">
      <p class="text-secondary-400 mb-2">Belum ada filter dikonfigurasi</p>
      <button @click="openModal()" class="btn-secondary text-sm">+ Tambah Filter Pertama</button>
    </div>

    <!-- Filter List -->
    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-secondary-50 text-secondary-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium w-12">#</th>
            <th class="px-4 py-3 text-left font-medium">Nama Filter</th>
            <th class="px-4 py-3 text-left font-medium">Label</th>
            <th class="px-4 py-3 text-left font-medium">Tipe Input</th>
            <th class="px-4 py-3 text-center font-medium">Wajib</th>
            <th class="px-4 py-3 text-left font-medium">Default</th>
            <th class="px-4 py-3 text-right font-medium w-20">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-secondary-100">
          <tr v-for="(f, idx) in store.selectedReportData.filters" :key="f.id_parameter" class="hover:bg-secondary-50">
            <td class="px-4 py-3 text-secondary-400">{{ idx + 1 }}</td>
            <td class="px-4 py-3 font-mono text-xs text-secondary-600">{{ f.nama_filter }}</td>
            <td class="px-4 py-3">{{ f.label }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 bg-secondary-100 text-secondary-600 rounded text-xs">{{ f.tipe_input }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span v-if="f.wajib_isi" class="text-red-500 text-xs">Wajib</span>
              <span v-else class="text-secondary-300 text-xs">Opsional</span>
            </td>
            <td class="px-4 py-3 text-xs text-secondary-400 truncate max-w-[120px]">{{ f.nilai_default || '-' }}</td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openModal(f)" class="p-1.5 text-secondary-400 hover:text-primary-500 rounded" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l4.293-4.293z" />
                  </svg>
                </button>
                <button @click="confirmDelete(f)" class="p-1.5 text-secondary-400 hover:text-red-500 rounded" title="Hapus">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Filter Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">{{ editingFilter ? 'Edit Filter' : 'Filter Baru' }}</h3>
            <button @click="closeModal" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Filter <span class="text-red-500">*</span></label>
                <input v-model="form.nama_filter" type="text" class="input-field" placeholder="TglAwal" />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Label Tampil <span class="text-red-500">*</span></label>
                <input v-model="form.label" type="text" class="input-field" placeholder="Tanggal Awal" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Tipe Input <span class="text-red-500">*</span></label>
                <select v-model="form.tipe_input" class="input-field">
                  <option value="date">Date</option>
                  <option value="text">Text</option>
                  <option value="number">Number</option>
                  <option value="combobox">Combobox</option>
                  <option value="browse">Browse</option>
                  <option value="perkiraan">Perkiraan</option>
                  <option value="dropdown">Dropdown</option>
                  <option value="checkbox">Checkbox</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Nilai Default</label>
                <input v-model="form.nilai_default" type="text" class="input-field" placeholder="Nilai default" />
              </div>
            </div>

            <!-- Browse-specific config -->
            <div v-if="form.tipe_input === 'browse' || form.tipe_input === 'perkiraan'" class="grid grid-cols-2 gap-4 border-t pt-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Browse Type</label>
                <div class="flex gap-2">
                  <select v-model="form.kode_browse" class="input-field flex-1">
                    <option value="">-- Pilih --</option>
                    <optgroup v-for="group in browseTypeGroups" :key="group.label" :label="group.label">
                      <option v-for="item in group.items" :key="item.value" :value="item.value">
                        {{ item.label }}
                      </option>
                    </optgroup>
                  </select>
                  <input
                    v-model="form.kode_browse"
                    type="text"
                    class="input-field w-32"
                    placeholder="Custom..."
                    @input="onCustomBrowseType"
                    title="Ketik kode browse custom (misal: 10041)"
                  />
                </div>
                <p class="text-xs text-secondary-400 mt-1">Pilih dari list atau ketik kode browse langsung</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Mode</label>
                <select v-model="form.mode" class="input-field">
                  <option value="single">Single (1 item)</option>
                  <option value="tags">Tags (multi, chip)</option>
                  <option value="checkbox">Checkbox (multi, dialog)</option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <input v-model="form.wajib_isi" type="checkbox" id="wajib" class="w-4 h-4 rounded border-secondary-300" />
              <label for="wajib" class="text-sm text-secondary-700">Wajib Diisi</label>
            </div>

            <!-- JSON Preview -->
            <div class="bg-secondary-50 rounded-lg p-3">
              <p class="text-xs font-medium text-secondary-500 mb-1">Konfigurasi JSON</p>
              <pre class="text-xs text-secondary-600 font-mono">{{ konfigurasiJson }}</pre>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="closeModal" class="px-4 py-2 text-secondary-600 rounded-lg">Batal</button>
            <button
              @click="saveFilter"
              :disabled="store.saving || !form.nama_filter || !form.label"
              class="btn-primary"
            >
              Simpan
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import type { AdminFilter } from '~/stores/adminReports'
import { useAdminBrowseStore } from '~/stores/adminBrowse'

const store = useAdminReportStore()
const browseTypesStore = useAdminBrowseStore()

const showModal = ref(false)
const editingFilter = ref<AdminFilter | null>(null)

// Fallback hardcoded browse types (used when API fails or while loading)
const fallbackBrowseTypeGroups = [
  {
    label: 'Perkiraan',
    items: [
      { value: '1005', label: 'Perkiraan (1005)' },
      { value: '10051', label: 'Perkiraan (UserMode)' },
      { value: '10053', label: 'Perkiraan Kas/Bank' },
      { value: '10054', label: 'LR HPP' },
    ]
  },
  {
    label: 'Barang',
    items: [
      { value: '911', label: 'Barang (BJ)' },
      { value: '912', label: 'Barang (All)' },
      { value: '913', label: 'Barang Custom' },
      { value: '915', label: 'Barang Aktif' },
      { value: '917', label: 'Barang Aktif 2' },
      { value: '120302', label: 'Barang (vwBarang)' },
      { value: '3001101', label: 'Barang 3001101' },
    ]
  },
  {
    label: 'Gudang',
    items: [
      { value: '916', label: 'Gudang' },
      { value: '11002', label: 'Gudang 11002' },
      { value: '11009', label: 'Gudang 11009' },
    ]
  },
  {
    label: 'Customer/Supplier',
    items: [
      { value: '10141', label: 'Supplier' },
      { value: '10142', label: 'Customer' },
      { value: '10143', label: 'Expedisi' },
      { value: '1014', label: 'Perkiraan Cust/Supp' },
    ]
  },
  {
    label: 'Lainnya',
    items: [
      { value: '1004', label: 'Devisi' },
      { value: '1002', label: 'Bagian' },
      { value: '1003', label: 'Jabatan' },
      { value: '157', label: 'Sub Grup' },
      { value: '1006', label: 'Valas' },
      { value: '1008', label: 'Kategori' },
      { value: '91117', label: 'SPK' },
    ]
  },
]

// Dynamic browse type groups from API, grouped by category
const browseTypeGroups = computed(() => {
  const sources = browseTypesStore.browseTypes
  if (sources.length > 0) {
    const groups: Record<string, Array<{ value: string; label: string }>> = {}
    for (const t of sources) {
      if (!groups[t.group]) groups[t.group] = []
      const srcBadge = t.source === 'hardcoded' ? ' [Hardcoded]' : ''
      groups[t.group].push({ value: t.kodeBrowse, label: `${t.kodeBrowse}${srcBadge}` })
    }
    const result = Object.entries(groups).map(([label, items]) => ({ label, items }))
    result.sort((a, b) => a.label.localeCompare(b.label))
    return result
  }
  return fallbackBrowseTypeGroups
})

// Expose dynamic sources count for template fallback check
const dynamicSources = computed(() => browseTypesStore.browseTypes)

// Load browse types on mount
onMounted(async () => {
  await Promise.all([
    browseTypesStore.fetchConfigs(),
    browseTypesStore.fetchBrowseTypes(),
  ])
})

const form = reactive({
  nama_filter: '',
  label: '',
  tipe_input: 'date',
  wajib_isi: false,
  nilai_default: '',
  kode_browse: '',
  mode: 'single'
})

const konfigurasiJson = computed(() => {
  if (form.tipe_input === 'browse' || form.tipe_input === 'perkiraan') {
    const cfg: any = {}
    if (form.kode_browse) cfg.kode_browse = form.kode_browse
    if (form.mode !== 'single') cfg.mode = form.mode
    return JSON.stringify(cfg)
  }
  return '{}'
})

function openModal(f?: AdminFilter) {
  editingFilter.value = f || null
  if (f) {
    Object.assign(form, {
      nama_filter: f.nama_filter,
      label: f.label,
      tipe_input: f.tipe_input,
      wajib_isi: f.wajib_isi,
      nilai_default: f.nilai_default || '',
      kode_browse: f.konfigurasi?.kode_browse || '',
      mode: f.konfigurasi?.mode || 'single'
    })
  } else {
    Object.assign(form, { nama_filter: '', label: '', tipe_input: 'date', wajib_isi: false, nilai_default: '', kode_browse: '', mode: 'single' })
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingFilter.value = null
}

async function saveFilter() {
  const konfigurasi = konfigurasiJson.value !== '{}' ? JSON.parse(konfigurasiJson.value) : null
  const data: any = { ...form }
  delete (data as any).kode_browse
  delete (data as any).mode
  if (konfigurasi) data.konfigurasi = JSON.stringify(konfigurasi)

  if (editingFilter.value) {
    await store.updateFilter(editingFilter.value.id_parameter, data)
  } else {
    await store.createFilter(data)
  }
  closeModal()
}

async function confirmDelete(f: AdminFilter) {
  if (!confirm(`Hapus filter "${f.label}"?`)) return
  await store.deleteFilter(f.id_parameter)
}
</script>