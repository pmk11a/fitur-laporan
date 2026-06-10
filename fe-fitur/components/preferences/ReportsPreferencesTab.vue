<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-bold">Preferences Laporan</h2>
      <span class="text-xs text-gray-500">
        Total: {{ store.count }} key
      </span>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 text-sm">
      <strong>Cara kerja:</strong>
      <ol class="list-decimal ml-5 mt-1 space-y-1 text-xs">
        <li>Atur default global di tab <em>Format</em> (berlaku ke semua laporan & kolom)</li>
        <li>Override per-laporan jika perlu (misal laporan bank pakai prefix <em>Rp</em>)</li>
        <li>Override per-kolom jika ada kolom khusus (misal kurs USD)</li>
        <li>Override per-tipe-kolom di tab <em>Tipe</em> (misal semua <em>percent</em> = 2 desimal)</li>
        <li>Resolusi otomatis: kolom spesifik > tipe > laporan > global > system default</li>
      </ol>
    </div>

    <button
      @click="refresh"
      class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm"
    >
      Refresh
    </button>

    <PreferencesPanel
      :available-reports="availableReports"
      :dismissible="false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUserPreferencesStore } from '~/stores/userPreferences'

const props = defineProps<{
  availableReports?: string[]
}>()

const store = useUserPreferencesStore()
const availableReports = ref(props.availableReports || [])

const refresh = async () => {
  await store.refresh()
}

onMounted(async () => {
  await store.refresh()
})
</script>
