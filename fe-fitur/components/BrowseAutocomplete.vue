<template>
  <div class="relative" :class="{ 'w-full': true }">

    <!-- ======================== MODE: SINGLE ======================== -->
    <template v-if="effectiveMode === 'single'">
      <div class="relative">
        <input
          ref="inputRef"
          type="text"
          v-model="searchText"
          @input="onInput"
          @focus="onFocus"
          @blur="onBlurSingle"
          @keydown="onKeyDownSingle"
          class="input-field"
          :class="{ 'pr-8': browse.loading.value }"
          :placeholder="placeholder"
          :disabled="disabled"
          autocomplete="off"
        />
        <!-- Right-side controls -->
        <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center gap-1 pr-1">
          <!-- Header toggle (only when props.showHeader is true) -->
          <button
            v-if="props.showHeader"
            type="button"
            class="p-1 rounded text-secondary-400 hover:text-primary-500 hover:bg-primary-50 transition-colors"
            @click="headerVisible = !headerVisible"
            :title="headerVisible ? 'Hide columns' : 'Show columns'"
          >
            <svg v-if="headerVisible" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6m-6 4h12m-6 4h6m-6 4h12M3 17h18"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18H4"/>
            </svg>
          </button>
          <!-- Loading spinner -->
          <div v-if="browse.loading.value" class="p-1">
            <svg class="animate-spin w-4 h-4 text-secondary-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Dropdown results -->
      <div
        v-if="isOpen && browse.results.length > 0"
        class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg overflow-hidden"
      >
        <!-- Column headers (visible when toggle is on) -->
        <div
          v-if="headerVisible && (browseConfig?.additionalFields?.length ?? 0) > 0"
          class="flex items-center px-4 py-1.5 bg-primary-50 border-b border-primary-100 text-[10px] font-semibold uppercase tracking-wider text-primary-600"
        >
          <span class="font-mono min-w-[80px]">{{ fieldLabel(browseConfig?.keyField ?? '') }}</span>
          <span class="ml-2 min-w-[100px]">{{ fieldLabel(browseConfig?.labelField ?? '') }}</span>
          <span
            v-for="af in (browseConfig?.additionalFields ?? [])"
            :key="af"
            class="ml-2 min-w-[60px]"
          >{{ fieldLabel(af) }}</span>
        </div>
        <ul class="max-h-60 overflow-y-auto py-1">
          <li
            v-for="(item, idx) in browse.results"
            :key="getKey(item)"
            :class="[
              'px-4 py-2 cursor-pointer text-sm',
              idx === highlightedIndex ? 'bg-primary-50 text-primary-700' : 'hover:bg-secondary-50'
            ]"
            @mousedown.prevent="selectItem(item)"
            @mouseenter="highlightedIndex = idx"
          >
            <span class="font-mono font-medium text-secondary-800">{{ getKey(item) }}</span>
            <span class="text-secondary-500 ml-2">{{ getLabel(item) }}</span>
            <span
              v-for="af in (browseConfig?.additionalFields ?? [])"
              :key="af"
              class="text-secondary-400 ml-2 text-xs"
              >{{ item[af] ?? '' }}</span
            >
          </li>
        </ul>
      </div>

      <!-- No results -->
      <div
        v-if="isOpen && hasSearched && browse.results.length === 0 && searchText.trim() !== ''"
        class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg px-4 py-3 text-sm text-secondary-500"
      >
        Tidak ditemukan. Anda dapat mengetik manual.
      </div>
    </template>

    <!-- ======================== MODE: TAGS ======================== -->
    <template v-else-if="effectiveMode === 'tags'">
      <div
        class="min-h-[42px] w-full border border-secondary-300 rounded-lg bg-white px-2 py-1.5 flex flex-wrap gap-1.5 items-center cursor-text"
        :class="{ 'ring-2 ring-primary-500': isFocused, 'opacity-50': disabled }"
        @click="focusInput"
      >
        <!-- Chips -->
        <span
          v-for="(item, idx) in selectedItems"
          :key="idx"
          class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-700 rounded-md text-sm font-mono"
        >
          <span>{{ getKey(item) }}</span>
          <button
            type="button"
            class="hover:text-primary-900 font-bold text-primary-500"
            @click.stop="removeTag(idx)"
          >×</button>
        </span>

        <!-- Input field -->
        <input
          ref="inputRef"
          type="text"
          v-model="searchText"
          @input="onInput"
          @focus="onFocus"
          @blur="onBlurTags"
          @keydown="onKeyDownTags"
          class="flex-1 min-w-[120px] outline-none bg-transparent text-sm"
          :placeholder="selectedItems.length === 0 ? placeholder : ''"
          :disabled="disabled"
          autocomplete="off"
        />
      </div>

      <!-- Dropdown results -->
      <div
        v-if="isOpen && browse.results.length > 0"
        class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg overflow-hidden"
      >
        <!-- Column headers (visible when toggle is on) -->
        <div
          v-if="headerVisible && (browseConfig?.additionalFields?.length ?? 0) > 0"
          class="flex items-center px-4 py-1.5 bg-primary-50 border-b border-primary-100 text-[10px] font-semibold uppercase tracking-wider text-primary-600"
        >
          <span class="font-mono min-w-[80px]">{{ fieldLabel(browseConfig?.keyField ?? '') }}</span>
          <span class="ml-2 min-w-[100px]">{{ fieldLabel(browseConfig?.labelField ?? '') }}</span>
          <span
            v-for="af in (browseConfig?.additionalFields ?? [])"
            :key="af"
            class="ml-2 min-w-[60px]"
          >{{ fieldLabel(af) }}</span>
        </div>
        <ul class="max-h-60 overflow-y-auto py-1">
          <li
            v-for="(item, idx) in browse.results"
            :key="getKey(item)"
            :class="[
              'px-4 py-2 cursor-pointer text-sm',
              idx === highlightedIndex ? 'bg-primary-50 text-primary-700' : 'hover:bg-secondary-50'
            ]"
            @mousedown.prevent="addTag(item)"
            @mouseenter="highlightedIndex = idx"
          >
            <span class="font-mono font-medium text-secondary-800">{{ getKey(item) }}</span>
            <span class="text-secondary-500 ml-2">{{ getLabel(item) }}</span>
            <span
              v-for="af in (browseConfig?.additionalFields ?? [])"
              :key="af"
              class="text-secondary-400 ml-2 text-xs"
              >{{ item[af] ?? '' }}</span
            >
          </li>
        </ul>
      </div>

      <!-- No results -->
      <div
        v-if="isOpen && hasSearched && browse.results.length === 0 && searchText.trim() !== ''"
        class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg px-4 py-3 text-sm text-secondary-500"
      >
        Tidak ditemukan. Enter untuk tambahkan sebagai free-text.
      </div>
    </template>

    <!-- ======================== MODE: CHECKBOX (DIALOG) ======================== -->
    <template v-else-if="effectiveMode === 'checkbox'">
      <!-- Trigger button -->
      <button
        type="button"
        class="w-full input-field text-left flex items-center justify-between"
        :class="{ 'opacity-50': disabled }"
        :disabled="disabled"
        @click="openCheckboxDialog"
      >
        <span v-if="selectedItems.length === 0" class="text-secondary-400">
          {{ placeholder }}
        </span>
        <span v-else class="font-mono text-sm">
          {{ selectedItems.length }} item{{ selectedItems.length > 1 ? 's' : '' }} dipilih
        </span>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <!-- Dialog overlay -->
      <Teleport to="body">
        <div
          v-if="checkboxDialogOpen"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="closeCheckboxDialog"
        >
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[80vh] flex flex-col">
            <!-- Dialog header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-secondary-200">
              <h3 class="font-semibold text-secondary-800">{{ placeholder || 'Pilih Data' }}</h3>
              <button
                type="button"
                class="text-secondary-400 hover:text-secondary-600"
                @click="closeCheckboxDialog"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Search bar -->
            <div class="px-4 py-3 border-b border-secondary-100">
              <div class="flex gap-2">
                <div class="flex-1 relative">
                  <input
                    ref="checkboxSearchRef"
                    type="text"
                    v-model="checkboxSearchText"
                    @input="onCheckboxSearch"
                    class="w-full input-field pr-8"
                    placeholder="Ketik untuk filter..."
                    autocomplete="off"
                  />
                  <button
                    v-if="checkboxSearchText"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                    @click="checkboxSearchText = ''"
                  >×</button>
                </div>
              </div>
            </div>

            <!-- Loading state -->
            <div v-if="browse.loading.value" class="flex items-center justify-center py-8">
              <svg class="animate-spin w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            </div>

            <!-- Checkbox list -->
            <div v-else class="flex-1 overflow-y-auto py-2">
              <div
                v-if="checkboxResults.length === 0"
                class="px-4 py-8 text-center text-secondary-500"
              >
                Tidak ada data
              </div>
              <label
                v-for="item in checkboxResults"
                :key="getKey(item)"
                class="flex items-center gap-3 px-4 py-2 hover:bg-secondary-50 cursor-pointer"
              >
                <input
                  type="checkbox"
                  :value="getKey(item)"
                  v-model="checkboxSelected"
                  class="w-4 h-4 rounded border-secondary-300 text-primary-500 focus:ring-primary-500"
                />
                <span class="font-mono text-sm font-medium text-secondary-800">{{ getKey(item) }}</span>
                <span class="text-secondary-500 text-sm ml-2">{{ getLabel(item) }}</span>
                <span
                  v-for="af in (browseConfig?.additionalFields ?? [])"
                  :key="af"
                  class="text-secondary-400 text-xs ml-2"
                  >{{ item[af] ?? '' }}</span
                >
              </label>
            </div>

            <!-- Dialog footer -->
            <div class="flex items-center justify-between px-4 py-3 border-t border-secondary-200 bg-secondary-50 rounded-b-xl">
              <span class="text-sm text-secondary-500">
                {{ checkboxSelected.length }} item{{ checkboxSelected.length > 1 ? 's' : '' }} dipilih
              </span>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="px-4 py-2 text-sm text-secondary-600 hover:text-secondary-800"
                  @click="closeCheckboxDialog"
                >Batal</button>
                <button
                  type="button"
                  class="px-4 py-2 bg-primary-500 text-white rounded-lg text-sm hover:bg-primary-600"
                  @click="confirmCheckbox"
                >
                  OK ({{ checkboxSelected.length }})
                </button>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </template>

  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue: string | string[]
  browseType: string
  mode?: 'single' | 'tags' | 'checkbox'
  placeholder?: string
  disabled?: boolean
  parentFilters?: Record<string, string>
  showHeader?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'single',
  placeholder: 'Ketik untuk mencari...',
  disabled: false,
  parentFilters: undefined,
  showHeader: false,
})

onMounted(async () => {
  browseConfig.value = await config.getConfig(props.browseType)
})

const emit = defineEmits<{
  'update:modelValue': [value: string | string[]]
}>()

// ========================================
// MODE COMPUTED
// ========================================
const effectiveMode = computed(() => {
  if (props.mode && ['single', 'tags', 'checkbox'].includes(props.mode)) {
    return props.mode
  }
  return 'single'
})

// ========================================
// USE BROWSE SEARCH
// ========================================
const browse = useBrowseSearch(props.browseType)

// ========================================
// SHARED STATE
// ========================================
const searchText = ref('')
const isOpen = ref(false)
const hasSearched = ref(false)
const highlightedIndex = ref(-1)
const inputRef = ref<HTMLInputElement | null>(null)
const isFocused = ref(false)

// ========================================
// SELECTED ITEMS (shared across modes)
// ========================================
const selectedItems = ref<Record<string, any>[]>([])

// Sync from modelValue
watch(
  () => props.modelValue,
  (val) => {
    if (effectiveMode.value === 'checkbox') {
      // For checkbox, modelValue is string[]
      if (Array.isArray(val)) {
        checkboxSelected.value = val
      }
    } else {
      // For single/tags, modelValue is string or we rebuild from string[]
      if (Array.isArray(val) && val.length > 0) {
        // Tags mode: rebuild chips from string[]
        validateTagsFromCodes(val)
      }
    }
  },
  { immediate: true }
)

// ========================================
// CONFIG HELPERS
// ========================================
const config = useBrowseConfig()
// Use a minimal interface for browse config that includes additionalFields
interface BrowseCfg {
  keyField: string
  labelField: string
  additionalFields: string[]
  table?: string
  joins?: string[]
  whereExtra?: string
  alias_fields?: Record<string, string>
}
const browseConfig = ref<BrowseCfg | null>(null)
const headerVisible = ref(props.showHeader)

// Human-readable labels for common field names used in additionalFields.
// Falls back to the field name itself when no mapping exists.
const FIELD_LABELS: Record<string, string> = {
  Neraca: 'No. Neraca',
  Kelompok: 'Kelompok',
  Tipe: 'Tipe',
  DK: 'DK',
  Simbol: 'Simbol',
  Alamat: 'Alamat',
  Telpon: 'Telpon',
  Kota: 'Kota',
  Kurs: 'Kurs',
  Sat1: 'Satuan 1',
  Sat2: 'Satuan 2',
  Isi: 'Isi',
  Isi1: 'Isi 1',
  Isi2: 'Isi 2',
  NFix: 'NFix',
  DueDate: 'Jatuh Tempo',
  JENIS: 'Jenis',
  IsPpn: 'PPN',
  KodeArea: 'Kode Area',
  NamaArea: 'Nama Area',
}
function fieldLabel(name: string): string {
  return FIELD_LABELS[name] ?? name
}

// Legacy fallback — kept as offline safety net; primary source is now backend API
// DEPRECATED: new browse types should be added to backend (BrowseService::getConfigMap or dbbrowseconfigs)
const knownFieldMap: Record<string, { keyField: string; labelField: string }> = {
  '10051': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '1005': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '100444': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '10053': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '10054': { keyField: 'Nomor', labelField: 'Keterangan' },
  '10055': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '10059': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  'perkiraan': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '10141': { keyField: 'KodeCustSupp', labelField: 'NamaCustSupp' },
  '10142': { keyField: 'KodeCustSupp', labelField: 'NamaCustSupp' },
  '10143': { keyField: 'KodeCustSupp', labelField: 'NamaCustSupp' },
  '1014': { keyField: 'KodeCustSupp', labelField: 'NamaCustSupp' },
  '911': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '912': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '913': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '914': { keyField: 'Lokasi', labelField: 'Lokasi' },
  '915': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '917': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '120302': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '3001101': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '916': { keyField: 'KodeGdg', labelField: 'Nama' },
  '11002': { keyField: 'KodeGdg', labelField: 'Nama' },
  '11009': { keyField: 'KodeGdg', labelField: 'Nama' },
  '1004': { keyField: 'Devisi', labelField: 'NamaDevisi' },
  '11011': { keyField: 'KodeKota', labelField: 'NamaKota' },
  '110011': { keyField: 'KodeSubGrp', labelField: 'NamaSubGrp' },
  '1100112': { keyField: 'KodeGrp', labelField: 'Nama' },
  '110012': { keyField: 'KodeGrp', labelField: 'Nama' },
  '110013': { keyField: 'KodeGrp', labelField: 'Nama' },
  '110014': { keyField: 'KodeSubGrp', labelField: 'NamaSubGrp' },
  '157': { keyField: 'KodeSubGrp', labelField: 'NamaSubGrp' },
  '1576': { keyField: 'KeyNIK', labelField: 'Nama' },
  '1577': { keyField: 'NIK', labelField: 'Nama' },
  '15779': { keyField: 'NIK', labelField: 'Nama' },
  '15780': { keyField: 'NIK', labelField: 'Nama' },
  '100413': { keyField: 'NoMuka', labelField: 'Keterangan' },
  '100412': { keyField: 'NoMuka', labelField: 'Keterangan' },
  '100405': { keyField: 'NoGiro', labelField: 'Bank' },
  '100406': { keyField: 'NoGiro', labelField: 'Bank' },
  '100408': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '100409': { keyField: 'Perkiraan', labelField: 'Keterangan' },
  '1006': { keyField: 'KodeVls', labelField: 'NamaVls' },
  '11001': { keyField: 'KodeVls', labelField: 'NamaVls' },
  '2082': { keyField: 'KodeVls', labelField: 'NamaVls' },
  '1008': { keyField: 'KodeKategori', labelField: 'Keterangan' },
  '10081': { keyField: 'KodeKategori', labelField: 'Keterangan' },
  '1007': { keyField: 'Kodeak', labelField: 'Namaak' },
  '10071': { keyField: 'Kodesubak', labelField: 'Namasubak' },
  '1002': { keyField: 'KodeBag', labelField: 'Namabag' },
  '10021': { keyField: 'KdDep', labelField: 'NmDep' },
  '1003': { keyField: 'KodeJab', labelField: 'Namajab' },
  '251050': { keyField: 'KodeTipe', labelField: 'Nama' },
  '30056': { keyField: 'KodeTipe', labelField: 'Nama' },
  '30057': { keyField: 'KodeTipe', labelField: 'Nama' },
  '110015': { keyField: 'KodeJnsTambahkan', labelField: 'Nama' },
  '110016': { keyField: 'KodeBrg', labelField: 'NamaBrg' },
  '1250': { keyField: 'KodeExp', labelField: 'NamaExp' },
  '91117': { keyField: 'NOSPK', labelField: 'KodeBrg' },
}

onMounted(async () => {
  browseConfig.value = await config.getConfig(props.browseType)
})

function getKeyField(): string {
  if (browseConfig.value) return browseConfig.value.keyField
  return knownFieldMap[props.browseType]?.keyField || 'Perkiraan'
}

function getLabelField(): string {
  if (browseConfig.value) return browseConfig.value.labelField
  return knownFieldMap[props.browseType]?.labelField || 'Keterangan'
}

function getKey(item: Record<string, any>): string {
  return String(item[getKeyField()] || item.key || '')
}

function getLabel(item: Record<string, any>): string {
  return String(item[getLabelField()] || item.label || '')
}

// ========================================
// DEBOUNCE
// ========================================
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function onInput() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    runSearch(searchText.value)
  }, 250)
}

// ========================================
// SEARCH
// ========================================
async function runSearch(q: string) {
  if (effectiveMode.value === 'checkbox') return

  const qTrimmed = q.trim()
  if (qTrimmed.length === 0) {
    browse.results = []
    isOpen.value = false
    hasSearched.value = false
    browse.loading.value = false
    return
  }

  hasSearched.value = true
  highlightedIndex.value = -1

  const data = await browse.search(qTrimmed, 20, props.parentFilters)
  if (qTrimmed !== searchText.value.trim()) return
  browse.results = data
  isOpen.value = true  // Always open dropdown on search (show results or "no results" message)
}

function onFocus() {
  isFocused.value = true
  if (searchText.value.trim()) {
    runSearch(searchText.value)
  }
}

function onBlurSingle() {
  browse.loading.value = false
  setTimeout(() => {
    closeDropdown()
    // Validate exact match
    if (searchText.value.trim()) {
      validateCurrentInput()
    }
  }, 200)
}

// ========================================
// SINGLE MODE
// ========================================
async function validateCurrentInput() {
  const code = searchText.value.trim()
  if (!code) return

  const result = await browse.validate(code, props.parentFilters)
  if (result) {
    // Found — set value
    selectItem(result)
  } else {
    // Not found — free-text, emit as-is
    emit('update:modelValue', code)
  }
}

function selectItem(item: Record<string, any>) {
  const key = getKey(item)
  searchText.value = key
  emit('update:modelValue', key)
  browse.loading.value = false
  closeDropdown()
}

function onKeyDownSingle(e: KeyboardEvent) {
  if (!isOpen.value) return

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, browse.results.length - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
  } else if (e.key === 'Enter' && highlightedIndex.value >= 0) {
    e.preventDefault()
    selectItem(browse.results[highlightedIndex.value])
  } else if (e.key === 'Escape') {
    closeDropdown()
  }
}

// ========================================
// TAGS MODE
// ========================================
function onBlurTags() {
  setTimeout(() => {
    closeDropdown()
    isFocused.value = false
    // Add free-text if any
    if (searchText.value.trim()) {
      addFreeTextTag(searchText.value.trim())
    }
  }, 200)
}

async function addTag(item: Record<string, any>) {
  const key = getKey(item)
  const label = getLabel(item)

  // Avoid duplicate
  if (!selectedItems.value.find((s) => getKey(s) === key)) {
    selectedItems.value.push({ [getKeyField()]: key, [getLabelField()]: label })
    emitFromTags()
  }

  searchText.value = ''
  browse.loading.value = false
  closeDropdown()
}

function addFreeTextTag(code: string) {
  if (!selectedItems.value.find((s) => getKey(s).toUpperCase() === code.toUpperCase())) {
    selectedItems.value.push({ [getKeyField()]: code, [getLabelField()]: '(free-text)' })
    emitFromTags()
  }
  searchText.value = ''
}

function removeTag(idx: number) {
  selectedItems.value.splice(idx, 1)
  emitFromTags()
}

function emitFromTags() {
  const codes = selectedItems.value.map((item) => getKey(item))
  emit('update:modelValue', codes)
}

async function validateTagsFromCodes(codes: string[]) {
  if (!codes || codes.length === 0) {
    selectedItems.value = []
    return
  }

  const validated = await browse.validateBatch(codes, props.parentFilters)
  selectedItems.value = validated.map((v) => {
    const keyField = getKeyField()
    const labelField = getLabelField()
    return {
      [keyField]: v[keyField] || '',
      [labelField]: v[labelField] || '(free-text)',
    }
  })
}

function onKeyDownTags(e: KeyboardEvent) {
  if (e.key === 'Enter') {
    e.preventDefault()
    if (highlightedIndex.value >= 0 && browse.results[highlightedIndex.value]) {
      addTag(browse.results[highlightedIndex.value])
    } else if (searchText.value.trim()) {
      addFreeTextTag(searchText.value.trim())
    }
  } else if (e.key === 'Backspace' && searchText.value === '' && selectedItems.value.length > 0) {
    // Remove last chip on backspace
    selectedItems.value.pop()
    emitFromTags()
  } else if (!isOpen.value && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
    runSearch(searchText.value)
  } else if (e.key === 'Escape') {
    closeDropdown()
  } else if (e.key === 'ArrowDown') {
    e.preventDefault()
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, browse.results.length - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
  }
}

function focusInput() {
  inputRef.value?.focus()
}

// ========================================
// CHECKBOX MODE
// ========================================
const checkboxDialogOpen = ref(false)
const checkboxSearchText = ref('')
const checkboxSelected = ref<string[]>([])
const checkboxResults = ref<Record<string, any>[]>([])
const checkboxSearchRef = ref<HTMLInputElement | null>(null)

let checkboxDebounceTimer: ReturnType<typeof setTimeout> | null = null

async function openCheckboxDialog() {
  if (props.disabled) return
  checkboxDialogOpen.value = true

  // Load all data
  const all = await browse.getAll(500, props.parentFilters)
  checkboxResults.value = all
  checkboxSelected.value = Array.isArray(props.modelValue) ? [...props.modelValue] : []

  // Focus search input after render
  nextTick(() => {
    checkboxSearchRef.value?.focus()
  })
}

function closeCheckboxDialog() {
  checkboxDialogOpen.value = false
  checkboxSearchText.value = ''
}

function confirmCheckbox() {
  // Emit the selected codes as string[]
  emit('update:modelValue', checkboxSelected.value)
  closeCheckboxDialog()
}

function onCheckboxSearch() {
  if (checkboxDebounceTimer) clearTimeout(checkboxDebounceTimer)
  checkboxDebounceTimer = setTimeout(() => {
    filterCheckboxResults(checkboxSearchText.value)
  }, 250)
}

async function filterCheckboxResults(q: string) {
  if (!q.trim()) {
    // Reset to all
    const all = await browse.getAll(500, props.parentFilters)
    checkboxResults.value = all
    return
  }

  const qTrimmed = q.trim()
  const all = await browse.getAll(500, props.parentFilters)
  checkboxResults.value = all.filter((item) => {
    const key = getKey(item).toLowerCase()
    const label = getLabel(item).toLowerCase()
    return key.includes(qTrimmed.toLowerCase()) || label.includes(qTrimmed.toLowerCase())
  })
}

// ========================================
// UTILS
// ========================================
function closeDropdown() {
  isOpen.value = false
  highlightedIndex.value = -1
}
</script>

<style scoped>
.input-field {
  @apply w-full px-3 py-2 border border-secondary-300 rounded-lg text-sm bg-white;
  @apply focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
  @apply disabled:bg-secondary-100 disabled:cursor-not-allowed;
}
</style>