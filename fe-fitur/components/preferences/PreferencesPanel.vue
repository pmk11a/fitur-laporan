<template>
  <div class="bg-white border rounded-lg shadow-sm p-4 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="font-bold text-lg">Pengaturan Saya</h3>
      <button
        v-if="dismissible"
        @click="emitClose"
        class="text-gray-500 hover:text-gray-700 text-xl"
      >x</button>
    </div>

    <div class="border-b flex gap-1 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="tabClass(tab.id)"
      >{{ tab.icon }} {{ tab.label }}</button>
    </div>

    <!-- TAB: Format -->
    <section v-if="activeTab === 'format'" class="space-y-3">
      <div>
        <h4 class="font-medium mb-1">Default Global</h4>
        <p class="text-xs text-gray-500 mb-2">
          Berlaku untuk semua laporan dan kolom yang tidak di-customize
        </p>
        <NumberFormatForm
          :model-value="globalFormat"
          @save="onSaveGlobal"
        />
      </div>

      <div v-if="availableReports.length > 0">
        <h4 class="font-medium mb-1">Default per Laporan</h4>
        <p class="text-xs text-gray-500 mb-2">
          Override default untuk laporan tertentu (semua kolom)
        </p>
        <details class="border rounded p-2">
          <summary class="cursor-pointer text-sm">Pilih laporan...</summary>
          <div v-for="kode in availableReports" :key="kode" class="mt-2">
            <div class="flex items-center justify-between">
              <strong class="text-sm">{{ kode }}</strong>
              <span v-if="hasPref('format', 'rep.' + kode)" class="text-xs text-blue-600">custom</span>
            </div>
            <NumberFormatForm
              :model-value="getFormat('rep.' + kode)"
              @save="(v) => savePref('format', 'rep.' + kode, v)"
              @reset="removePref('format', 'rep.' + kode)"
            />
          </div>
        </details>
      </div>
    </section>

    <!-- TAB: Tipe Kolom -->
    <section v-if="activeTab === 'types'" class="space-y-3">
      <p class="text-xs text-gray-500 mb-2">
        Default per tipe kolom. Otomatis berlaku ke semua kolom bertipe sama.
      </p>
      <div v-for="type in columnTypes" :key="type" class="border rounded p-2">
        <div class="flex items-center justify-between mb-2">
          <strong class="capitalize text-sm">{{ type }}</strong>
          <span v-if="hasPref('format', 'col._type.' + type)" class="text-xs text-blue-600">custom</span>
        </div>
        <NumberFormatForm
          :model-value="getFormat('col._type.' + type)"
          @save="(v) => savePref('format', 'col._type.' + type, v)"
          @reset="removePref('format', 'col._type.' + type)"
        />
      </div>
    </section>

    <!-- TAB: Tampilan -->
    <section v-if="activeTab === 'ui'" class="space-y-3">
      <h4 class="font-medium">Tampilan</h4>
      <div>
        <label class="text-sm">Theme</label>
        <select :value="themeMode" @change="onThemeChange" class="w-full border rounded p-2 text-sm">
          <option value="light">Light</option>
          <option value="dark">Dark</option>
          <option value="auto">Auto (Sistem)</option>
        </select>
      </div>
      <div>
        <label class="text-sm">Accent Color</label>
        <select :value="accentColor" @change="onAccentChange" class="w-full border rounded p-2 text-sm">
          <option value="blue">Blue</option>
          <option value="green">Green</option>
          <option value="purple">Purple</option>
          <option value="red">Red</option>
        </select>
      </div>
    </section>

    <!-- TAB: Regional -->
    <section v-if="activeTab === 'regional'" class="space-y-3">
      <h4 class="font-medium">Regional</h4>
      <div>
        <label class="text-sm">Bahasa</label>
        <select :value="languageCode" @change="onLanguageChange" class="w-full border rounded p-2 text-sm">
          <option value="id">Bahasa Indonesia</option>
          <option value="en">English</option>
        </select>
      </div>
      <div>
        <label class="text-sm">Zona Waktu</label>
        <select :value="timezoneValue" @change="onTimezoneChange" class="w-full border rounded p-2 text-sm">
          <option value="Asia/Jakarta">WIB (Jakarta)</option>
          <option value="Asia/Makassar">WITA (Makassar)</option>
          <option value="Asia/Jayapura">WIT (Jayapura)</option>
          <option value="UTC">UTC</option>
        </select>
      </div>
    </section>

    <!-- TAB: Debug -->
    <section v-if="activeTab === 'debug'" class="space-y-3">
      <h4 class="font-medium">Debug</h4>
      <p class="text-xs text-gray-500">Semua preference yang tersimpan (cached):</p>
      <pre class="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-96">{{ allPrefsJson }}</pre>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUserPreferencesStore } from '~/stores/userPreferences'
import { usePreference } from '~/composables/usePreference'

const props = defineProps<{
  dismissible?: boolean
  availableReports?: string[]
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const store = useUserPreferencesStore()
const activeTab = ref('format')

const tabs = [
  { id: 'format', label: 'Format', icon: '#' },
  { id: 'types', label: 'Tipe', icon: '@' },
  { id: 'ui', label: 'Tampilan', icon: '*' },
  { id: 'regional', label: 'Regional', icon: '~' },
  { id: 'debug', label: 'Debug', icon: '?' },
]

const columnTypes = ['currency', 'percent', 'qty', 'integer', 'number']
const availableReports = computed(() => props.availableReports || [])

const allPrefsJson = computed(() => JSON.stringify(store.data, null, 2))

onMounted(() => {
  store.loadNamespace('format')
  store.loadNamespace('ui')
  store.loadNamespace('language')
  store.loadNamespace('timezone')
})

const DEFAULT_FORMAT = {
  decimal: 2,
  locale: 'id-ID',
  prefix: '',
  suffix: '',
  compact: false,
  showZero: true,
  style: 'normal' as const,
}

const { value: globalFormat } = usePreference('format', 'rep._default', DEFAULT_FORMAT)

const themeMode = computed(() => store.get('ui', 'theme', { mode: 'light' }).mode)
const accentColor = computed(() => store.get('ui', 'accent', 'blue'))
const languageCode = computed(() => store.get('language', 'code', 'id'))
const timezoneValue = computed(() => store.get('timezone', 'value', 'Asia/Jakarta'))

const tabClass = (id: string) => {
  return activeTab.value === id
    ? 'px-3 py-2 text-sm whitespace-nowrap border-b-2 border-blue-500 text-blue-600 font-medium'
    : 'px-3 py-2 text-sm whitespace-nowrap border-b-2 border-transparent text-gray-600 hover:text-gray-900'
}

const emitClose = () => emit('close')

const hasPref = (namespace: string, key: string) => {
  return store.data[namespace]?.[key] !== undefined
}

const getFormat = (key: string) => {
  return store.get('format', key, DEFAULT_FORMAT)
}

const savePref = async (namespace: string, key: string, value: any) => {
  await store.set(namespace, key, value)
}

const removePref = async (namespace: string, key: string) => {
  await store.remove(namespace, key)
}

const onSaveGlobal = (v: any) => {
  savePref('format', 'rep._default', v)
}

const onThemeChange = (e: Event) => {
  const mode = (e.target as HTMLSelectElement).value
  savePref('ui', 'theme', { mode })
  if (import.meta.client) {
    document.documentElement.classList.toggle('dark', mode === 'dark')
  }
}

const onAccentChange = (e: Event) => {
  savePref('ui', 'accent', (e.target as HTMLSelectElement).value)
}

const onLanguageChange = (e: Event) => {
  savePref('language', 'code', (e.target as HTMLSelectElement).value)
}

const onTimezoneChange = (e: Event) => {
  savePref('timezone', 'value', (e.target as HTMLSelectElement).value)
}
</script>
