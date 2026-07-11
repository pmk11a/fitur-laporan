<template>
  <div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-secondary-200">
      <h3 class="text-lg font-semibold text-secondary-900">
        {{ isEdit ? 'Edit Browse' : 'Browse Baru' }}
      </h3>
    </div>

    <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
      <!-- Kode Browse -->
      <div>
        <label class="block text-sm font-medium text-secondary-700 mb-1">Kode Browse *</label>
        <input v-model="form.kodeBrowse" type="text" required :disabled="isEdit"
          class="input-field w-full" placeholder="e.g., 99999" />
        <p v-if="!isEdit" class="text-xs text-secondary-500 mt-1">Kode unik untuk browse ini</p>
      </div>

      <!-- Table Name -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-medium text-secondary-700">Table Name</label>
          <button type="button" @click="showTablePicker = true" class="text-xs text-primary-500 hover:underline">
            Browse Tables
          </button>
        </div>
        <input v-model="form.table" type="text" class="input-field w-full" placeholder="e.g., DBPERKIRAAN" />
        <p class="text-xs text-secondary-500 mt-1">Nama table SQL Server. Kosongkan jika pakai custom Query.</p>
      </div>

      <!-- Query (alternative to Table) -->
      <div>
        <label class="block text-sm font-medium text-secondary-700">Custom Query</label>
        <textarea v-model="form.query" rows="4" class="input-field w-full font-mono text-xs"
          placeholder="SELECT TOP 100 Kode, Nama FROM DBTabel WHERE ..."></textarea>
        <p class="text-xs text-secondary-500 mt-1">SQL lengkap. Kosongkan jika pakai Table + Fields.</p>
      </div>

      <!-- Key Field -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">Key Field *</label>
          <input v-model="form.keyField" type="text" required class="input-field w-full"
            placeholder="e.g., KodeBrg" />
          <p class="text-xs text-secondary-500 mt-1">Field untuk nilai/id</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">Label Field *</label>
          <input v-model="form.labelField" type="text" required class="input-field w-full"
            placeholder="e.g., NamaBrg" />
          <p class="text-xs text-secondary-500 mt-1">Field untuk tampilan nama</p>
        </div>
      </div>

      <!-- Additional Fields -->
      <div>
        <label class="block text-sm font-medium text-secondary-700 mb-1">
          Additional Fields
          <span class="text-xs text-secondary-400 font-normal ml-1">(opsional)</span>
        </label>
        <p class="text-xs text-secondary-500 mb-2">
          Kolom tambahan yang ditampilkan di dropdown browse setelah kode dan nama. Ketik nama kolom, lalu tekan Enter.
        </p>
        <div class="flex flex-wrap gap-2 mb-2 min-h-[28px]">
          <span v-if="form.additionalFields.length === 0" class="text-xs text-secondary-400 italic py-1">
            Belum ada field tambahan.
          </span>
          <span v-for="(field, idx) in form.additionalFields" :key="idx"
            class="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-700 border border-primary-200 rounded text-xs font-medium">
            {{ field }}
            <button type="button" @click="removeField(idx)" class="text-primary-400 hover:text-red-500 leading-none">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>
        </div>
        <div class="flex gap-2">
          <input v-model="newField" type="text" class="input-field flex-1" placeholder="Ketik nama kolom, lalu Enter" @keydown.enter.prevent="addField" />
          <button type="button" @click="addField" class="btn-secondary text-sm">Tambah</button>
        </div>
      </div>

      <!-- Joins -->
      <div>
        <label class="block text-sm font-medium text-secondary-700 mb-1">Joins (SQL)</label>
        <div class="space-y-2">
          <div v-for="(join, idx) in form.joins" :key="idx" class="flex gap-2">
            <input v-model="form.joins[idx]" type="text" class="input-field flex-1 font-mono text-xs"
              placeholder="LEFT JOIN DBTabel ON ..." />
            <button type="button" @click="removeJoin(idx)" class="btn-danger text-sm p-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <button type="button" @click="form.joins.push('')" class="btn-secondary text-sm mt-2">+ Tambah Join</button>
      </div>

      <!-- Where Extra -->
      <div>
        <label class="block text-sm font-medium text-secondary-700 mb-1">Where Extra (SQL)</label>
        <input v-model="form.whereExtra" type="text" class="input-field w-full font-mono text-xs"
          placeholder="AND IsAktif = 1" />
        <p class="text-xs text-secondary-500 mt-1">Kondisi SQL tambahan (tanpa WHERE/AND prefix)</p>
      </div>

      <!-- Parent Filters -->
      <div>
        <label class="block text-sm font-medium text-secondary-700 mb-1">
          Parent Filters
          <span class="text-xs text-secondary-500 font-normal ml-2">Untuk query custom gunakan placeholder <code>&lt;P:NamaField&gt;</code> di SQL</span>
        </label>
        <div class="space-y-2">
          <div v-for="(pf, idx) in form.parent_filters" :key="idx" class="flex gap-2 items-center p-2 bg-secondary-50 rounded">
            <div class="flex-1">
              <div class="flex gap-2 mb-2">
                <input v-model="pf.source_column" type="text" class="input-field flex-1 text-sm"
                  placeholder="Source Column (e.g. NOBUKTI)" title="Key name used in parent filter payload (must match the <P:...> placeholder in SQL)" />
                <input v-model="pf.column" type="text" class="input-field flex-1 text-sm"
                  placeholder="SQL Column (e.g. A.NOBUKTI)" title="Qualified SQL column name in the query" />
                <select v-model="pf.operator" class="input-field w-24 text-sm">
                  <option value="=">=</option>
                  <option value="!=">!=</option>
                  <option value="LIKE">LIKE</option>
                </select>
                <select v-model="pf.type" class="input-field w-24 text-sm">
                  <option value="exact">Exact</option>
                  <option value="partial">Partial</option>
                </select>
              </div>
            </div>
            <button type="button" @click="removeParentFilter(idx)" class="btn-danger text-sm p-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <button type="button" @click="addParentFilter" class="btn-secondary text-sm mt-2">+ Tambah Parent Filter</button>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-2 pt-4 border-t border-secondary-200">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>
    </form>

    <!-- Table Picker Modal -->
    <div v-if="showTablePicker" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showTablePicker = false">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
        <div class="px-4 py-3 border-b border-secondary-200 flex items-center justify-between">
          <h4 class="font-semibold text-secondary-900">Select Table</h4>
          <button @click="showTablePicker = false" class="text-secondary-400 hover:text-secondary-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-4 border-b border-secondary-200">
          <input v-model="tableSearch" type="text" placeholder="Search table..." class="input-field w-full" />
        </div>
        <div class="flex-1 overflow-y-auto p-2">
          <div v-if="loadingTables" class="p-4 text-center text-secondary-500">
            <div class="animate-spin w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
          </div>
          <button v-for="table in filteredTables" :key="table" @click="selectTable(table)" v-else
            class="w-full text-left px-3 py-2 hover:bg-secondary-50 rounded text-sm font-mono">
            {{ table }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useAdminBrowseStore } from '~/stores/adminBrowse'
import type { BrowseConfig, ParentFilter } from '~/stores/adminReports'

const props = defineProps<{
  initialData: BrowseConfig | null
  isEdit: boolean
}>()

const emit = defineEmits<{
  save: [data: Partial<BrowseConfig>]
  cancel: []
}>()

const store = useAdminBrowseStore()

const loading = ref(false)
const showTablePicker = ref(false)
const tableSearch = ref('')
const loadingTables = ref(false)
const newField = ref('')

const form = reactive({
  kodeBrowse: '',
  table: '',
  query: '',
  keyField: '',
  labelField: '',
  additionalFields: [] as string[],
  joins: [] as string[],
  whereExtra: '',
  parent_filters: [] as ParentFilter[],
})

watch(() => props.initialData, (data) => {
  if (data) {
    console.log('[BrowseConfigForm] initialData:', data)
    // Flatten config from nested response shape {kodeBrowse, source, config: {...}}
    const config = data.config || data
    console.log('[BrowseConfigForm] flattened config:', config)
    form.kodeBrowse = data.kodeBrowse || config.kodeBrowse || ''
    form.table = config.table || ''
    form.query = config.query || ''
    form.keyField = config.keyField || ''
    form.labelField = config.labelField || ''
    form.additionalFields = config.additionalFields ? [...config.additionalFields] : []
    form.joins = config.joins ? [...config.joins] : []
    form.whereExtra = config.whereExtra || ''
    form.parent_filters = config.parent_filters ? config.parent_filters.map(pf => ({...pf})) : []
    console.log('[BrowseConfigForm] form populated:', form)
  }
}, { immediate: true })

const filteredTables = computed(() => {
  if (!tableSearch.value) return store.tables.slice(0, 100)
  const q = tableSearch.value.toLowerCase()
  return store.tables.filter(t => t.toLowerCase().includes(q)).slice(0, 100)
})

function addField() {
  if (newField.value && !form.additionalFields.includes(newField.value)) {
    form.additionalFields.push(newField.value.trim())
    newField.value = ''
  }
}

function removeField(idx: number) {
  form.additionalFields.splice(idx, 1)
}

function removeJoin(idx: number) {
  form.joins.splice(idx, 1)
}

function addParentFilter() {
  form.parent_filters.push({ source_column: '', column: '', operator: '=', type: 'exact' })
}

function removeParentFilter(idx: number) {
  form.parent_filters.splice(idx, 1)
}

async function selectTable(table: string) {
  form.table = table
  showTablePicker.value = false
  // Fetch columns for reference
  await store.fetchTableColumns(table)
}

async function loadTables() {
  loadingTables.value = true
  await store.fetchTables()
  loadingTables.value = false
}

watch(showTablePicker, (show) => {
  if (show && store.tables.length === 0) {
    loadTables()
  }
})

function handleSubmit() {
  const data: Record<string, any> = {
    keyField: form.keyField,
    labelField: form.labelField,
    table: form.table || undefined,
    query: form.query || undefined,
    additionalFields: form.additionalFields.length ? form.additionalFields : undefined,
    joins: form.joins.filter(j => j.trim()).length ? form.joins.filter(j => j.trim()) : undefined,
    whereExtra: form.whereExtra || undefined,
    parent_filters: form.parent_filters.filter(pf => pf.source_column).length ? form.parent_filters.filter(pf => pf.source_column) : undefined,
  }

  // Always include kodeBrowse for backend to know which record to update
  if (props.isEdit && form.kodeBrowse) {
    // nothing extra needed, backend uses URL param
  }

  if (!props.isEdit && !data.kodeBrowse) {
    data.kodeBrowse = form.kodeBrowse
  }

  emit('save', data)
}
</script>