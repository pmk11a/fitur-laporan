<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-medium text-secondary-700">Dataset / Query</h3>
      <button @click="openModal()" class="btn-primary text-xs py-1.5">+ Tambah Dataset</button>
    </div>

    <div v-if="!store.selectedReportData?.datasets?.length" class="card p-8 text-center">
      <p class="text-secondary-400 mb-2">Belum ada dataset</p>
      <p class="text-xs text-secondary-400 mb-4">Dataset mendefinisikan query SQL atau stored procedure untuk mengambil data.</p>
      <button @click="openModal()" class="btn-secondary text-sm">+ Tambah Dataset</button>
    </div>

    <div v-else class="space-y-3">
      <div v-for="ds in store.selectedReportData.datasets" :key="ds.id_query" class="card p-4" :class="!ds.visible ? 'opacity-60' : ''">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <span class="px-2 py-0.5 bg-primary-50 text-primary-600 rounded text-xs font-mono font-medium">{{ ds.nama_dataset }}</span>
              <span v-if="ds.config_json?.display_role === 'summary'" class="px-2 py-0.5 bg-yellow-50 text-yellow-600 rounded text-xs border border-yellow-200">
                Summary
                <span v-if="ds.config_json?.summary_layout === 'grid_1col'" class="ml-1">1 kolom</span>
                <span v-else class="ml-1">2 kolom</span>
              </span>
              <span v-else-if="ds.config_json?.display_role === 'detail'" class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-xs">Detail</span>
              <span v-if="!ds.visible" class="px-2 py-0.5 bg-secondary-100 text-secondary-500 rounded text-xs border border-secondary-200">
                Hidden
              </span>
              <span class="text-xs text-secondary-400">Urutan: {{ ds.urutan }}</span>
            </div>
            <p v-if="ds.deskripsi" class="text-xs text-secondary-500 mb-2">{{ ds.deskripsi }}</p>
            <div class="bg-secondary-900 text-secondary-100 rounded p-3 font-mono text-xs overflow-x-auto">
              <pre class="whitespace-pre-wrap break-all">{{ ds.query_sumber_data }}</pre>
            </div>
          </div>
          <div class="flex flex-col gap-1 shrink-0">
            <button @click="openModal(ds)" class="p-2 text-secondary-400 hover:text-primary-500 rounded-lg hover:bg-secondary-100" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l4.293-4.293z" />
              </svg>
            </button>
            <button @click="previewDataset(ds)" class="p-2 text-secondary-400 hover:text-green-500 rounded-lg hover:bg-secondary-100" title="Preview">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button @click="confirmDelete(ds)" class="p-2 text-secondary-400 hover:text-red-500 rounded-lg hover:bg-secondary-100" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Dataset Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">{{ editingDataset ? 'Edit Dataset' : 'Dataset Baru' }}</h3>
            <button @click="closeModal" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Dataset <span class="text-red-500">*</span></label>
                <input v-model="form.nama_dataset" type="text" class="input-field font-mono text-sm" placeholder="QuView1, T1, dsb." />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Urutan</label>
                <input v-model.number="form.urutan" type="number" class="input-field" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi</label>
              <input v-model="form.deskripsi" type="text" class="input-field" placeholder="Deskripsi dataset" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Display Role</label>
                <select v-model="form.config_json.display_role" class="input-field">
                  <option value="">— Tidak diatur —</option>
                  <option value="summary">Summary (tampil di footer, bukan tabel)</option>
                  <option value="detail">Detail (tampil di tabel transaksi)</option>
                </select>
              </div>
              <div v-if="form.config_json.display_role === 'summary'">
                <label class="block text-sm font-medium text-secondary-700 mb-1">Layout Kolom</label>
                <select v-model="form.config_json.summary_layout" class="input-field">
                  <option value="grid_2col">2 Kolom</option>
                  <option value="grid_1col">1 Kolom</option>
                </select>
              </div>
            </div>

            <div v-if="form.config_json.display_role === 'summary'" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Field Summary (kiri)</label>
                <p class="text-xs text-secondary-500 mb-2">Pilih kolom T1 yang tampil di kolom kiri footer. Kosongkan = semua kolom T1.</p>
                <div v-if="availableDatasetColumns.length === 0" class="text-xs text-secondary-400 italic">
                  Belum ada kolom di dataset ini. Tambahkan kolom di tab Kolom terlebih dahulu.
                </div>
                <div v-else class="grid grid-cols-2 gap-1 max-h-32 overflow-y-auto border border-secondary-200 rounded p-2 bg-secondary-50">
                  <label v-for="col in availableDatasetColumns" :key="col.nama_kolom" class="flex items-center gap-2 text-xs cursor-pointer hover:bg-white px-1 py-0.5 rounded">
                    <input
                      type="checkbox"
                      :value="col.nama_kolom"
                      v-model="form.config_json.summary_fields"
                      class="rounded border-secondary-300 text-primary-500"
                    />
                    <span class="font-mono">{{ col.nama_kolom }}</span>
                    <span class="text-secondary-400 truncate">{{ col.label_tampil }}</span>
                  </label>
                </div>
              </div>
              <div v-if="form.config_json.summary_layout !== 'grid_1col'">
                <label class="block text-sm font-medium text-secondary-700 mb-1">Field Summary (kanan)</label>
                <p class="text-xs text-secondary-500 mb-2">Pilih kolom T1 yang tampil di kolom kanan footer. Kosongkan = semua kolom T1.</p>
                <div v-if="availableDatasetColumns.length === 0" class="text-xs text-secondary-400 italic">
                  Belum ada kolom di dataset ini.
                </div>
                <div v-else class="grid grid-cols-2 gap-1 max-h-32 overflow-y-auto border border-secondary-200 rounded p-2 bg-secondary-50">
                  <label v-for="col in availableDatasetColumns" :key="col.nama_kolom" class="flex items-center gap-2 text-xs cursor-pointer hover:bg-white px-1 py-0.5 rounded">
                    <input
                      type="checkbox"
                      :value="col.nama_kolom"
                      v-model="form.config_json.right_fields"
                      class="rounded border-secondary-300 text-primary-500"
                    />
                    <span class="font-mono">{{ col.nama_kolom }}</span>
                    <span class="text-secondary-400 truncate">{{ col.label_tampil }}</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <input
                id="dataset-visible"
                v-model="form.visible"
                type="checkbox"
                class="rounded border-secondary-300 text-primary-500 focus:ring-primary-400"
              />
              <label for="dataset-visible" class="text-sm text-secondary-700 select-none cursor-pointer">
                Tampilkan dataset ini di laporan
                <span v-if="!form.visible" class="text-secondary-400 ml-1">(dataset tersembunyi, tidak ikut di-execute)</span>
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Query / Stored Procedure <span class="text-red-500">*</span></label>
              <textarea
                v-model="form.query_sumber_data"
                rows="6"
                class="input-field font-mono text-xs"
                placeholder="SELECT * FROM vwPerkiraan&#10;EXEC Sp_LapJurnal 'BKM', @Devisi, @TglAwal, @TglAkhir"
              ></textarea>
              <p class="text-xs text-secondary-400 mt-1">Gunakan @ParamName sebagai placeholder untuk parameter filter.</p>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="closeModal" class="px-4 py-2 text-secondary-600 rounded-lg">Batal</button>
            <button @click="saveDataset" :disabled="store.saving || !form.nama_dataset || !form.query_sumber_data" class="btn-primary">
              Simpan
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Preview Modal -->
    <Teleport to="body">
      <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[80vh] flex flex-col">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">Preview Query</h3>
            <button @click="showPreview = false" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto p-6">
            <div v-if="previewResult?.loading" class="text-center py-8">
              <div class="animate-spin w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full mx-auto mb-3"></div>
              <p class="text-secondary-500 text-sm">Menjalankan query...</p>
            </div>
            <div v-else-if="previewResult?.message && !previewResult?.columns" class="card p-6 text-center">
              <p class="text-red-500">{{ previewResult.message }}</p>
            </div>
            <div v-else-if="previewResult?.columns">
              <p class="text-xs text-secondary-400 mb-2">{{ previewResult.rowCount }} rows returned</p>
              <table class="w-full text-xs border border-secondary-200 rounded-lg overflow-hidden">
                <thead class="bg-secondary-50">
                  <tr>
                    <th v-for="col in previewResult.columns" :key="col" class="px-3 py-2 text-left font-medium border-b border-secondary-100">{{ col }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-secondary-100">
                  <tr v-for="(row, i) in previewResult.rows" :key="i">
                    <td v-for="col in previewResult.columns" :key="col" class="px-3 py-2 border-b border-secondary-50">{{ row[col] ?? '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import type { AdminDataset } from '~/stores/adminReports'

const store = useAdminReportStore()

const showModal = ref(false)
const showPreview = ref(false)
const editingDataset = ref<AdminDataset | null>(null)
const previewResult = ref<any>(null)

const form = reactive({
  nama_dataset: '',
  query_sumber_data: '',
  deskripsi: '',
  urutan: 1,
  visible: true,
  config_json: {
    display_role: '',
    summary_layout: 'grid_2col',
    summary_fields: [] as string[],
    right_fields: [] as string[]
  }
})

const availableDatasetColumns = computed(() => {
  const dsName = editingDataset.value?.nama_dataset || form.nama_dataset
  if (!dsName) return []
  const cols = store.selectedReportData?.columns?.[dsName] || []
  return cols.filter((c: any) => c.is_visible !== false)
})

function openModal(ds?: AdminDataset) {
  editingDataset.value = ds || null
  if (ds) {
    Object.assign(form, {
      nama_dataset: ds.nama_dataset,
      query_sumber_data: ds.query_sumber_data,
      deskripsi: ds.deskripsi || '',
      urutan: ds.urutan,
      visible: ds.visible !== false,
      config_json: {
        display_role: ds.config_json?.display_role || '',
        summary_layout: ds.config_json?.summary_layout || 'grid_2col',
        summary_fields: ds.config_json?.summary_fields || [],
        right_fields: ds.config_json?.right_fields || []
      }
    })
  } else {
    Object.assign(form, {
      nama_dataset: '',
      query_sumber_data: '',
      deskripsi: '',
      urutan: 1,
      visible: true,
      config_json: { display_role: '', summary_layout: 'grid_2col', summary_fields: [], right_fields: [] }
    })
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingDataset.value = null
}

async function saveDataset() {
  // Build config_json payload (only send if has values)
  const configJson = form.config_json.display_role
    ? { ...form.config_json }
    : undefined

  const payload: any = { ...form, config_json: configJson }

  if (editingDataset.value) {
    await store.updateDataset(editingDataset.value.id_query, payload)
  } else {
    await store.createDataset(payload)
  }
  closeModal()
}

async function confirmDelete(ds: AdminDataset) {
  if (!confirm(`Hapus dataset "${ds.nama_dataset}"?\nSemua kolom terkait juga akan dihapus.`)) return
  await store.deleteDataset(ds.id_query)
}

async function previewDataset(ds: AdminDataset) {
  previewResult.value = { loading: true }
  showPreview.value = true
  previewResult.value = await store.previewQuery(ds.query_sumber_data)
}
</script>