<template>
  <div class="min-h-screen bg-secondary-100 flex flex-col lg:flex-row">
    <!-- Left Panel: Report List -->
    <div class="w-full lg:w-80 bg-white border-r border-secondary-200 flex flex-col shrink-0">
      <!-- Panel Header -->
      <div class="p-4 border-b border-secondary-200">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-semibold text-secondary-700">Setting Laporan</h2>
          <button
            @click="showCreateModal = true"
            class="btn-primary text-xs py-1.5 px-3"
          >
            + Baru
          </button>
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari laporan..."
          class="input-field text-sm"
        />
      </div>

      <!-- Report List -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="store.loading && store.reports.length === 0" class="p-4 text-center text-secondary-500 text-sm">
          <div class="animate-spin w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full mx-auto mb-2"></div>
          Memuat...
        </div>

        <div v-else-if="filteredReports.length === 0" class="p-4 text-center text-secondary-400 text-sm">
          <p>Tidak ada laporan ditemukan</p>
          <button @click="showCreateModal = true" class="mt-2 text-primary-500 hover:underline text-sm">
            + Buat laporan baru
          </button>
        </div>

        <div v-else>
          <button
            v-for="report in filteredReports"
            :key="report.id_laporan"
            @click="store.selectReport(report.id_laporan)"
            class="w-full text-left px-4 py-3 border-b border-secondary-100 hover:bg-secondary-50 transition-colors"
            :class="{
              'bg-primary-50 border-l-2 border-l-primary-500': store.selectedReport?.id_laporan === report.id_laporan
            }"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-900 truncate">
                  {{ report.nama_laporan || report.Keterangan }}
                </p>
                <p class="text-xs text-secondary-400 mt-0.5">
                  {{ report.KODEMENU }}
                </p>
              </div>
              <span
                class="shrink-0 text-xs px-1.5 py-0.5 rounded-full"
                :class="report.status_aktif
                  ? 'bg-green-100 text-green-700'
                  : 'bg-secondary-200 text-secondary-500'"
              >
                {{ report.status_aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Report Designer -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Empty State -->
      <div v-if="!store.selectedReport" class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <svg class="w-16 h-16 mx-auto text-secondary-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-secondary-500 mb-2">Pilih laporan untuk dikonfigurasi</p>
          <p class="text-sm text-secondary-400">Atau buat laporan baru</p>
        </div>
      </div>

      <!-- Report Designer -->
      <template v-else>
        <!-- Designer Header -->
        <div class="bg-white border-b border-secondary-200 px-6 py-3 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-secondary-900">
              {{ store.selectedReport.nama_laporan || store.selectedReport.Keterangan }}
            </h2>
            <p class="text-xs text-secondary-400">{{ store.selectedReport.KODEMENU }}</p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="confirmDelete"
              class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
              title="Hapus Laporan"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
            <button
              @click="router.push('/reports/' + store.selectedReport.KODEMENU)"
              class="btn-secondary text-xs py-1.5"
            >
              Preview
            </button>
          </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white border-b border-secondary-200 px-6">
          <nav class="flex gap-1 -mb-px overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="store.setTab(tab.id)"
              class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
              :class="store.activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-secondary-500 hover:text-secondary-700'"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <admin-tabs-general-tab v-if="store.activeTab === 'general'" />
          <admin-tabs-filters-tab v-else-if="store.activeTab === 'filters'" />
          <admin-tabs-datasets-tab v-else-if="store.activeTab === 'datasets'" />
          <admin-tabs-columns-tab v-else-if="store.activeTab === 'columns'" />
          <admin-tabs-grouping-tab v-else-if="store.activeTab === 'grouping'" />
          <admin-tabs-user-access-tab v-else-if="store.activeTab === 'access'" />
        </div>
      </template>
    </div>

    <!-- Create Report Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">Laporan Baru</h3>
            <button @click="showCreateModal = false" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Laporan <span class="text-red-500">*</span></label>
              <input v-model="createForm.nama_laporan" type="text" class="input-field" placeholder="Nama Laporan" />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi</label>
              <input v-model="createForm.deskripsi" type="text" class="input-field" placeholder="Deskripsi singkat" />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Menu Item (KODEMENU) <span class="text-red-500">*</span></label>
              <select v-model="createForm.KODEMENU" class="input-field">
                <option value="">-- Pilih Menu Item --</option>
                <option v-for="km in store.availableKodeMenu" :key="km.KODEMENU" :value="km.KODEMENU">
                  {{ km.KODEMENU }} — {{ km.Keterangan }}
                </option>
              </select>
              <p class="text-xs text-secondary-400 mt-1">Pilih menu item yang belum terhubung ke laporan lain</p>
            </div>
            <div class="flex items-center gap-2">
              <input v-model="createForm.status_aktif" type="checkbox" id="create-status" class="w-4 h-4 rounded border-secondary-300" />
              <label for="create-status" class="text-sm text-secondary-700">Aktif</label>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="showCreateModal = false" class="px-4 py-2 text-secondary-600 hover:text-secondary-800 rounded-lg">
              Batal
            </button>
            <button
              @click="createNewReport"
              :disabled="store.saving || !createForm.nama_laporan || !createForm.KODEMENU"
              class="btn-primary flex items-center gap-2"
            >
              <svg v-if="store.saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const router = useRouter()
const store = useAdminReportStore()
const authStore = useAuthStore()

// Redirect non-admin users
onMounted(() => {
  if (!authStore.isAdmin) {
    router.push('/dashboard')
    return
  }
  store.fetchReports()
  store.fetchAvailableKodeMenu()
})

const searchQuery = ref('')
const showCreateModal = ref(false)

const createForm = reactive({
  nama_laporan: '',
  deskripsi: '',
  KODEMENU: '',
  status_aktif: true
})

const tabs = [
  { id: 'general', label: 'Umum' },
  { id: 'filters', label: 'Filter' },
  { id: 'datasets', label: 'Dataset' },
  { id: 'columns', label: 'Kolom' },
  { id: 'grouping', label: 'Grouping' },
  { id: 'access', label: 'Akses Pengguna' },
]

const filteredReports = computed(() => {
  if (!searchQuery.value) return store.reports
  const q = searchQuery.value.toLowerCase()
  return store.reports.filter(r =>
    (r.nama_laporan || '').toLowerCase().includes(q) ||
    (r.KODEMENU || '').toLowerCase().includes(q) ||
    (r.Keterangan || '').toLowerCase().includes(q)
  )
})

async function createNewReport() {
  const result = await store.createReport({ ...createForm })
  if (result) {
    showCreateModal.value = false
    Object.assign(createForm, { nama_laporan: '', deskripsi: '', KODEMENU: '', status_aktif: true })
    store.setTab('general')
  }
}

async function confirmDelete() {
  if (!store.selectedReport) return
  if (!confirm(`Hapus laporan "${store.selectedReport.nama_laporan}"?\n\nSemua filter, dataset, kolom, dan grouping akan ikut dihapus.`)) return
  await store.deleteReport(store.selectedReport.id_laporan)
}
</script>