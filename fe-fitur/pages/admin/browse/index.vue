<template>
  <div class="min-h-screen bg-secondary-100 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-secondary-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-secondary-900">Setting Browse</h1>
          <p class="text-sm text-secondary-500 mt-1">Kelola konfigurasi browse untuk autocomplete dan lookup</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="handleSync" :disabled="store.loading" class="btn-secondary text-sm">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Sync dari Hardcoded
          </button>
          <button @click="showCreateModal = true" class="btn-primary text-sm">+ Browse Baru</button>
        </div>
      </div>
      <!-- Summary Stats -->
      <div class="flex gap-4 mt-4">
        <div class="px-4 py-2 bg-secondary-50 rounded-lg">
          <span class="text-2xl font-bold text-secondary-700">{{ store.summary.hardcoded }}</span>
          <span class="text-sm text-secondary-500 ml-2">Hardcoded</span>
        </div>
        <div class="px-4 py-2 bg-green-50 rounded-lg">
          <span class="text-2xl font-bold text-green-700">{{ store.summary.database }}</span>
          <span class="text-sm text-green-600 ml-2">Database</span>
        </div>
      </div>
      <!-- Error Message -->
      <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600">{{ errorMessage }}</p>
        <p class="text-xs text-red-400 mt-1">Pastikan Anda sudah login sebagai admin</p>
      </div>
      <!-- Success Message -->
      <div v-if="successMessage" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-700">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex">
      <!-- Left Panel: Browse List -->
      <div class="w-96 bg-white border-r border-secondary-200 flex flex-col shrink-0">
        <div class="p-4 border-b border-secondary-200">
          <input v-model="searchQuery" type="text" placeholder="Cari kode browse..." class="input-field text-sm" />
          <div class="flex gap-2 mt-3">
            <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
              class="px-3 py-1 text-xs rounded-full transition-colors"
              :class="activeTab === tab.value ? 'bg-primary-100 text-primary-700 font-medium' : 'bg-secondary-100 text-secondary-600 hover:bg-secondary-200'">
              {{ tab.label }} ({{ getTabCount(tab.value) }})
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto">
          <div v-if="store.loading && store.configs.length === 0" class="p-4 text-center text-secondary-500">
            <div class="animate-spin w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full mx-auto mb-2"></div>
            Memuat...
          </div>
          <div v-else-if="filteredConfigs.length === 0" class="p-4 text-center text-secondary-400 text-sm">
            <p>Tidak ada browse ditemukan</p>
          </div>
          <div v-else>
            <button v-for="config in filteredConfigs" :key="config.kodeBrowse" @click="selectConfig(config.kodeBrowse)"
              class="w-full text-left px-4 py-3 border-b border-secondary-100 hover:bg-secondary-50 transition-colors"
              :class="{ 'bg-primary-50 border-l-2 border-l-primary-500': selectedKode === config.kodeBrowse }">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-secondary-900">{{ config.kodeBrowse }}</p>
                  <p class="text-xs text-secondary-500 mt-0.5">{{ config.keyField }} → {{ config.labelField }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="config.source === 'database' ? 'bg-green-100 text-green-700' : 'bg-secondary-200 text-secondary-500'">
                  {{ config.source }}
                </span>
              </div>
              <p v-if="config.additionalFields?.length" class="text-xs text-secondary-400 mt-1">
                Fields: {{ config.additionalFields.join(', ') }}
              </p>
            </button>
          </div>
        </div>
      </div>

      <!-- Right Panel -->
      <div class="flex-1 flex flex-col min-w-0">
        <!-- Empty State -->
        <div v-if="!selectedKode && !showCreateModal" class="flex-1 flex items-center justify-center">
          <div class="text-center">
            <svg class="w-16 h-16 mx-auto text-secondary-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-secondary-500 mb-2">Pilih browse untuk melihat detail</p>
            <p class="text-sm text-secondary-400">Atau buat browse baru</p>
          </div>
        </div>

        <!-- Create Modal -->
        <div v-else-if="showCreateModal" class="flex-1 flex flex-col overflow-hidden">
          <BrowseConfigForm :initial-data="null" :is-edit="false" @save="handleCreate" @cancel="showCreateModal = false" />
        </div>

        <!-- Config Detail -->
        <div v-else-if="store.selectedConfig" class="flex-1 flex flex-col overflow-hidden">
          <div class="bg-white border-b border-secondary-200 px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-secondary-900">{{ store.selectedConfig.kodeBrowse }}</h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="store.selectedConfig.source === 'database' ? 'bg-green-100 text-green-700' : 'bg-secondary-200 text-secondary-500'">
                  {{ store.selectedConfig.source }}
                </span>
                <span v-if="store.selectedConfig.table" class="text-xs text-secondary-400">
                  Table: {{ store.selectedConfig.table }}
                </span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button v-if="store.selectedConfig.source === 'hardcoded'" @click="handleClone" :disabled="store.loading" class="btn-secondary text-sm">
                Clone ke Database
              </button>
              <button v-if="store.selectedConfig.source === 'database'" @click="startEdit" class="btn-primary text-sm">
                Edit
              </button>
              <button v-if="store.selectedConfig.source === 'database'" @click="handleDelete" :disabled="store.loading" class="btn-danger text-sm">
                Hapus
              </button>
            </div>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div v-if="isEditing" class="p-6">
              <BrowseConfigForm :initial-data="store.selectedConfig" :is-edit="true" @save="handleUpdate" @cancel="isEditing = false" />
            </div>
            <div v-else class="p-6">
              <BrowseConfigDetail :config="store.selectedConfig" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sync Modal -->
    <div v-if="showSyncModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-secondary-900 mb-4">Sync dari Hardcoded</h3>
        <p class="text-sm text-secondary-600 mb-4">Pilih mode sync:</p>
        <div class="space-y-2 mb-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="syncMode" value="missing" class="text-primary-500" />
            <span class="text-sm">Hanya yang belum ada di database</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" v-model="syncMode" value="all" class="text-primary-500" />
            <span class="text-sm">Semua (overwrite database)</span>
          </label>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="showSyncModal = false" class="btn-secondary">Batal</button>
          <button @click="confirmSync" :disabled="store.loading" class="btn-primary">
            {{ store.loading ? 'Syncing...' : 'Sync' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAdminBrowseStore } from '~/stores/adminBrowse'

const store = useAdminBrowseStore()

const searchQuery = ref('')
const activeTab = ref<'all' | 'database' | 'hardcoded'>('all')
const selectedKode = ref<string | null>(null)
const isEditing = ref(false)
const showCreateModal = ref(false)
const showSyncModal = ref(false)
const syncMode = ref<'all' | 'missing'>('missing')
const errorMessage = ref<string | null>(null)

const tabs = [
  { label: 'Semua', value: 'all' as const },
  { label: 'Database', value: 'database' as const },
  { label: 'Hardcoded', value: 'hardcoded' as const },
]

const filteredConfigs = computed(() => {
  let configs = store.configs

  // Filter by tab
  if (activeTab.value !== 'all') {
    configs = configs.filter(c => c.source === activeTab.value)
  }

  // Filter by search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    configs = configs.filter(c =>
      String(c.kodeBrowse).toLowerCase().includes(q) ||
      c.keyField.toLowerCase().includes(q) ||
      c.labelField.toLowerCase().includes(q)
    )
  }

  return configs
})

function getTabCount(tab: 'all' | 'database' | 'hardcoded') {
  if (tab === 'all') return store.configs.length
  return store.configs.filter(c => c.source === tab).length
}

async function selectConfig(kodeBrowse: string) {
  selectedKode.value = kodeBrowse
  isEditing.value = false
  errorMessage.value = null
  console.log('[BrowseAdmin] selectConfig:', kodeBrowse)
  await store.fetchConfig(kodeBrowse)
  console.log('[BrowseAdmin] selectedConfig after fetch:', store.selectedConfig)
  console.log('[BrowseAdmin] store.error:', store.error)
  if (store.error) {
    errorMessage.value = `Gagal memuat config: ${store.error}`
  }
}

function startEdit() { isEditing.value = true }

async function handleClone() {
  if (!selectedKode.value) return
  if (confirm(`Clone browse "${selectedKode.value}" ke database?`)) {
    const result = await store.cloneConfig(selectedKode.value)
    if (result) {
      await store.fetchConfig(selectedKode.value)
      successMessage.value = `✓ Browse ${selectedKode.value} berhasil di-clone ke database`
      setTimeout(() => { successMessage.value = null }, 4000)
    }
  }
}

async function handleDelete() {
  if (!selectedKode.value) return
  if (confirm(`Hapus browse "${selectedKode.value}" dari database?`)) {
    const success = await store.deleteConfig(selectedKode.value)
    if (success) {
      successMessage.value = `✓ Browse ${selectedKode.value} berhasil dihapus`
      setTimeout(() => { successMessage.value = null }, 4000)
      selectedKode.value = null
      store.clearSelected()
    } else if (store.error) {
      errorMessage.value = `Delete gagal: ${store.error}`
    }
  }
}

async function handleCreate(data: any) {
  const result = await store.createConfig(data)
  if (result) {
    showCreateModal.value = false
    await selectConfig(result.kodeBrowse)
    successMessage.value = `✓ Browse ${result.kodeBrowse} berhasil dibuat`
    setTimeout(() => { successMessage.value = null }, 4000)
  }
}

async function handleUpdate(data: any) {
  if (!selectedKode.value) return
  console.log('[BrowseAdmin] handleUpdate called', { kode: selectedKode.value, data })
  errorMessage.value = null
  const result = await store.updateConfig(selectedKode.value, data)
  console.log('[BrowseAdmin] handleUpdate result:', result)
  if (result) {
    isEditing.value = false
    successMessage.value = `✓ Browse ${selectedKode.value} berhasil disimpan`
    setTimeout(() => { successMessage.value = null }, 4000)
  } else if (store.error) {
    errorMessage.value = `Edit gagal: ${store.error}`
  }
}

const successMessage = ref<string | null>(null)

function handleSync() { showSyncModal.value = true }

async function confirmSync() {
  await store.syncConfigs(syncMode.value)
  showSyncModal.value = false
}

async function loadConfigs() {
  errorMessage.value = null
  const result = await store.fetchConfigs()
  if (store.error) {
    errorMessage.value = store.error
  }
}

onMounted(() => {
  loadConfigs()
})
</script>