<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-medium text-secondary-700">Konfigurasi Kolom</h3>
      <button @click="openModal()" class="btn-primary text-xs py-1.5">+ Tambah Kolom</button>
    </div>

    <!-- Dataset Tabs -->
    <div v-if="datasetNames.length > 1" class="border-b border-secondary-200">
      <nav class="flex gap-1 -mb-px">
        <button
          v-for="ds in datasetNames"
          :key="ds"
          @click="activeDataset = ds"
          class="px-3 py-2 text-xs font-medium border-b-2 transition-colors"
          :class="activeDataset === ds
            ? 'border-primary-500 text-primary-600'
            : 'border-transparent text-secondary-500 hover:text-secondary-700'"
        >
          {{ ds }}
        </button>
      </nav>
    </div>

    <div v-else-if="datasetNames.length === 1" class="flex items-center gap-2">
      <span class="text-xs text-secondary-500">Dataset:</span>
      <span class="px-2 py-0.5 bg-primary-50 text-primary-600 rounded text-xs font-mono">{{ datasetNames[0] }}</span>
    </div>

    <!-- Empty State -->
    <div v-if="!currentColumns.length" class="card p-8 text-center">
      <p class="text-secondary-400 mb-2">Belum ada kolom untuk dataset ini</p>
      <button @click="openModal()" class="btn-secondary text-sm">+ Tambah Kolom</button>
    </div>

    <!-- Column Table -->
    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-secondary-50 text-secondary-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium w-12">#</th>
            <th class="px-4 py-3 text-left font-medium">Nama Kolom</th>
            <th class="px-4 py-3 text-left font-medium">Label</th>
            <th class="px-4 py-3 text-center font-medium">Format</th>
            <th class="px-4 py-3 text-center font-medium">Align</th>
            <th class="px-4 py-3 text-center font-medium">Total</th>
            <th class="px-4 py-3 text-center font-medium">Tampil</th>
            <th class="px-4 py-3 text-right font-medium w-20">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-secondary-100">
          <tr v-for="(col, idx) in currentColumns" :key="col.id_kolom" class="hover:bg-secondary-50">
            <td class="px-4 py-3 text-secondary-400">{{ col.urutan_tampil || idx + 1 }}</td>
            <td class="px-4 py-3 font-mono text-xs text-secondary-600">{{ col.nama_kolom }}</td>
            <td class="px-4 py-3">{{ col.label_tampil || col.nama_kolom }}</td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 bg-secondary-100 text-secondary-600 rounded text-xs">{{ col.format_type }}</span>
            </td>
            <td class="px-4 py-3 text-center text-secondary-500 text-xs">{{ col.alignment }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="col.is_summable ? 'text-green-500' : 'text-secondary-300'">{{ col.is_summable ? 'Ya' : 'Tidak' }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="col.is_visible ? 'text-green-500' : 'text-secondary-300'">{{ col.is_visible ? 'Ya' : 'Tidak' }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openModal(col)" class="p-1.5 text-secondary-400 hover:text-primary-500 rounded" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l4.293-4.293z" />
                  </svg>
                </button>
                <button @click="confirmDelete(col)" class="p-1.5 text-secondary-400 hover:text-red-500 rounded" title="Hapus">
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

    <!-- Column Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">{{ editingColumn ? 'Edit Kolom' : 'Kolom Baru' }}</h3>
            <button @click="closeModal" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Dataset <span class="text-red-500">*</span></label>
              <select v-model="form.nama_dataset" class="input-field">
                <option v-for="ds in datasetNames" :key="ds" :value="ds">{{ ds }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Kolom <span class="text-red-500">*</span></label>
                <input v-model="form.nama_kolom" type="text" class="input-field font-mono text-sm" placeholder="nama_kolom" />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Label Tampil</label>
                <input v-model="form.label_tampil" type="text" class="input-field" placeholder="Label Kolom" />
              </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Format</label>
                <select v-model="form.format_type" class="input-field">
                  <option value="text">Text</option>
                  <option value="date">Date</option>
                  <option value="number">Number</option>
                  <option value="currency">Currency</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Align</label>
                <select v-model="form.alignment" class="input-field">
                  <option value="left">Left</option>
                  <option value="center">Center</option>
                  <option value="right">Right</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Urutan</label>
                <input v-model.number="form.urutan_tampil" type="number" class="input-field" />
              </div>
            </div>
            <div class="flex gap-4">
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="form.is_summable" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Summable (Total)
              </label>
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="form.is_visible" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Visible
              </label>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="closeModal" class="px-4 py-2 text-secondary-600 rounded-lg">Batal</button>
            <button @click="saveColumn" :disabled="store.saving || !form.nama_dataset || !form.nama_kolom" class="btn-primary">Simpan</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import type { AdminColumn } from '~/stores/adminReports'

const store = useAdminReportStore()

const showModal = ref(false)
const editingColumn = ref<AdminColumn | null>(null)
const activeDataset = ref('')

const form = reactive({
  nama_dataset: '',
  nama_kolom: '',
  label_tampil: '',
  urutan_tampil: 0,
  format_type: 'text',
  alignment: 'left',
  is_summable: false,
  is_visible: true
})

const datasetNames = computed(() => Object.keys(store.selectedReportData?.columns || {}))

watch(datasetNames, (names) => {
  if (names.length > 0 && !names.includes(activeDataset.value)) {
    activeDataset.value = names[0]
  }
}, { immediate: true })

const currentColumns = computed(() => {
  const ds = activeDataset.value || datasetNames.value[0]
  return (store.selectedReportData?.columns?.[ds] || []).sort((a: AdminColumn, b: AdminColumn) => a.urutan_tampil - b.urutan_tampil)
})

function openModal(col?: AdminColumn) {
  editingColumn.value = col || null
  if (col) {
    activeDataset.value = col.nama_dataset
    Object.assign(form, {
      nama_dataset: col.nama_dataset,
      nama_kolom: col.nama_kolom,
      label_tampil: col.label_tampil,
      urutan_tampil: col.urutan_tampil,
      format_type: col.format_type,
      alignment: col.alignment,
      is_summable: col.is_summable,
      is_visible: col.is_visible
    })
  } else {
    Object.assign(form, {
      nama_dataset: activeDataset.value || datasetNames.value[0] || '',
      nama_kolom: '', label_tampil: '', urutan_tampil: 0, format_type: 'text',
      alignment: 'left', is_summable: false, is_visible: true
    })
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingColumn.value = null
}

async function saveColumn() {
  if (editingColumn.value) {
    await store.updateColumn(editingColumn.value.id_kolom, { ...form })
  } else {
    await store.createColumn({ ...form })
  }
  closeModal()
}

async function confirmDelete(col: AdminColumn) {
  if (!confirm(`Hapus kolom "${col.label_tampil}"?`)) return
  await store.deleteColumn(col.id_kolom)
}
</script>