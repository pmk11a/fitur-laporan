<template>
  <div class="space-y-6">
    <!-- Form -->
    <div class="card p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Laporan</label>
          <input v-model="form.nama_laporan" type="text" class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">KODEMENU</label>
          <input :value="store.selectedReport?.KODEMENU" type="text" class="input-field bg-secondary-50" disabled />
          <p class="text-xs text-secondary-400 mt-1">Menu item tidak bisa diubah setelah dibuat</p>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-secondary-700 mb-1">Deskripsi</label>
          <input v-model="form.deskripsi" type="text" class="input-field" placeholder="Deskripsi singkat laporan" />
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">Status</label>
          <div class="flex items-center gap-3 mt-1.5">
            <input v-model="form.status_aktif" type="checkbox" id="status-toggle" class="w-4 h-4 rounded border-secondary-300" />
            <label for="status-toggle" class="text-sm text-secondary-700">
              {{ form.status_aktif ? 'Aktif' : 'Nonaktif' }}
            </label>
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-6 pt-4 border-t">
        <button
          @click="saveGeneral"
          :disabled="store.saving"
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

    <!-- Footer Bands Editor -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-secondary-700">Konfigurasi Footer / Tanda Tangan</h3>
        <button @click="showRawJson = !showRawJson" class="text-xs text-primary-500 hover:underline">
          {{ showRawJson ? 'Mode Visual' : 'Mode JSON' }}
        </button>
      </div>

      <!-- Visual Editor -->
      <div v-if="!showRawJson" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-1">Title</label>
          <input v-model="footerBands.bands.title.content" type="text" class="input-field" placeholder="Judul Laporan" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-1">Page Header</label>
            <input v-model="footerBands.bands.pageHeader.content" type="text" class="input-field" placeholder="Header teks" />
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-1">Page Footer</label>
            <input v-model="footerBands.bands.pageFooter.content" type="text" class="input-field" placeholder="Footer teks" />
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-1">Jumlah Kolom TTD</label>
            <input v-model.number="footerBands.bands.summary.layout.columns" type="number" min="1" max="6" class="input-field" />
          </div>
        </div>

        <!-- Signature Section -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">Tanda Tangan</label>
          <div class="space-y-2">
            <div
              v-for="(sig, idx) in footerBands.bands.summary.signatures"
              :key="idx"
              class="flex items-center gap-2"
            >
              <select v-model="sig.position" class="input-field text-sm w-32">
                <option value="left">Kiri</option>
                <option value="center">Tengah</option>
                <option value="right">Kanan</option>
              </select>
              <input v-model="sig.label" type="text" class="input-field text-sm flex-1" placeholder="Label tanda tangan" />
              <button @click="removeSignature(idx)" class="p-1 text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <button @click="addSignature" class="text-xs text-primary-500 hover:underline">+ Tambah Tanda Tangan</button>
          </div>
        </div>

        <div class="flex justify-end">
          <button @click="saveFooterBands" :disabled="store.saving" class="btn-secondary text-sm">Simpan Footer</button>
        </div>
      </div>

      <!-- Raw JSON Editor -->
      <div v-else>
        <textarea
          v-model="rawJson"
          rows="12"
          class="input-field font-mono text-xs"
          placeholder='{ "bands": { "title": { "enabled": true } } }'
        ></textarea>
        <p v-if="jsonError" class="text-xs text-red-500 mt-1">{{ jsonError }}</p>
        <div class="flex justify-end mt-2">
          <button @click="saveRawJson" :disabled="store.saving || !!jsonError" class="btn-secondary text-sm">Simpan JSON</button>
        </div>
      </div>
    </div>

    <!-- Timestamps -->
    <div v-if="store.selectedReport" class="card p-6">
      <h3 class="text-sm font-medium text-secondary-700 mb-3">Informasi</h3>
      <div class="grid grid-cols-2 gap-4 text-sm text-secondary-500">
        <div>
          <span class="font-medium">Dibuat:</span> {{ formatDate(store.selectedReport.created_at) }}
        </div>
        <div>
          <span class="font-medium">Diubah:</span> {{ formatDate(store.selectedReport.updated_at) }}
        </div>
        <div class="col-span-2">
          <span class="font-medium">Menu:</span> {{ store.selectedReport.KODEMENU }} — {{ store.selectedReport.Keterangan }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const store = useAdminReportStore()

const form = reactive({
  nama_laporan: '',
  deskripsi: '',
  status_aktif: true
})

const showRawJson = ref(false)
const rawJson = ref('')
const jsonError = ref('')

const footerBands = reactive({
  bands: {
    title: { enabled: true, content: '', align: 'center' },
    pageHeader: { enabled: true, content: '' },
    pageFooter: { enabled: true, content: '' },
    summary: {
      enabled: true,
      layout: { columns: 3, alignment: 'spread' as const },
      signatures: [] as { label: string; position: 'left' | 'center' | 'right' }[]
    }
  }
})

watch(() => store.selectedReport, (r) => {
  if (r) {
    form.nama_laporan = r.nama_laporan || ''
    form.deskripsi = r.deskripsi || ''
    form.status_aktif = r.status_aktif
    if (r.footer_bands && r.footer_bands.bands) {
      footerBands.bands.title = { ...footerBands.bands.title, ...(r.footer_bands.bands.title || {}) }
      footerBands.bands.pageHeader = { ...footerBands.bands.pageHeader, ...(r.footer_bands.bands.pageHeader || {}) }
      footerBands.bands.pageFooter = { ...footerBands.bands.pageFooter, ...(r.footer_bands.bands.pageFooter || {}) }
      if (r.footer_bands.bands.summary) {
        footerBands.bands.summary.enabled = r.footer_bands.bands.summary.enabled ?? true
        if (r.footer_bands.bands.summary.layout) {
          footerBands.bands.summary.layout = { ...footerBands.bands.summary.layout, ...r.footer_bands.bands.summary.layout }
        }
        footerBands.bands.summary.signatures = r.footer_bands.bands.summary.signatures || []
      }
    }
  }
}, { immediate: true, deep: true })

watch(showRawJson, (v) => {
  if (v) {
    rawJson.value = JSON.stringify(footerBands, null, 2)
    jsonError.value = ''
  }
})

function formatDate(d: string) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID')
}

async function saveGeneral() {
  if (!store.selectedReport) return
  await store.updateReport(store.selectedReport.id_laporan, { ...form })
}

async function saveFooterBands() {
  if (!store.selectedReport) return
  await store.updateReport(store.selectedReport.id_laporan, {
    footer_bands: JSON.parse(JSON.stringify(footerBands))
  })
}

function addSignature() {
  footerBands.bands.summary.signatures.push({ label: '', position: 'left' })
}

function removeSignature(idx: number) {
  footerBands.bands.summary.signatures.splice(idx, 1)
}

function saveRawJson() {
  try {
    const parsed = JSON.parse(rawJson.value)
    jsonError.value = ''
    store.updateReport(store.selectedReport!.id_laporan, { footer_bands: parsed })
  } catch (e) {
    jsonError.value = 'JSON tidak valid'
  }
}
</script>