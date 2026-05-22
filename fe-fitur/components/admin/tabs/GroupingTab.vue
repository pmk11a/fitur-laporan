<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-medium text-secondary-700">Grouping / Pengelompokan</h3>
      <button @click="openModal()" class="btn-primary text-xs py-1.5">+ Tambah Group</button>
    </div>

    <div v-if="!store.selectedReportData?.groups?.length" class="card p-8 text-center">
      <p class="text-secondary-400 mb-2">Belum ada aturan grouping</p>
      <p class="text-xs text-secondary-400 mb-4">Grouping menentukan bagaimana data dikelompokkan dan apakah ada sub-total per group.</p>
      <button @click="openModal()" class="btn-secondary text-sm">+ Tambah Group</button>
    </div>

    <div v-else>
      <!-- Group by Level -->
      <div v-for="level in [1, 2, 3]" :key="level" class="mb-4">
        <div v-if="groupsByLevel[level]?.length">
          <h4 class="text-xs font-semibold text-secondary-500 uppercase tracking-wide mb-2">Level {{ level }}</h4>
          <div class="space-y-2">
            <div
              v-for="g in groupsByLevel[level]"
              :key="g.id_group"
              class="card p-4"
              :style="g.style_config?.bgColor ? { backgroundColor: g.style_config.bgColor } : {}"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="px-2 py-0.5 bg-secondary-100 text-secondary-600 rounded text-xs font-mono">
                      {{ g.group_field || '(detail)' }}
                    </span>
                    <span v-if="g.field_value" class="px-2 py-0.5 bg-secondary-200 text-secondary-700 rounded text-xs">= {{ g.field_value }}</span>
                    <span class="px-2 py-0.5 rounded text-xs"
                      :class="g.special_handling !== 'default' ? 'bg-blue-100 text-blue-700' : 'bg-secondary-50 text-secondary-400'">
                      {{ g.special_handling }}
                    </span>
                  </div>
                  <p class="text-sm font-medium text-secondary-800" :class="g.style_config?.bold ? 'font-bold' : ''">
                    {{ g.label }}
                  </p>
                  <div class="flex items-center gap-3 mt-1 text-xs text-secondary-400">
                    <span>Sort: {{ g.sort_order }}</span>
                    <span>Subtotal: {{ g.show_subtotal ? 'Ya' : 'Tidak' }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                  <button @click="openModal(g)" class="p-2 text-secondary-400 hover:text-primary-500 rounded-lg hover:bg-secondary-100" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l4.293-4.293z" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(g)" class="p-2 text-secondary-400 hover:text-red-500 rounded-lg hover:bg-secondary-100" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Group Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">{{ editingGroup ? 'Edit Group' : 'Group Baru' }}</h3>
            <button @click="closeModal" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Level <span class="text-red-500">*</span></label>
                <select v-model.number="form.group_level" class="input-field">
                  <option :value="1">1 — Parent</option>
                  <option :value="2">2 — Subgroup</option>
                  <option :value="3">3 — Detail</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Sort Order</label>
                <input v-model.number="form.sort_order" type="number" class="input-field" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Group Field</label>
                <input v-model="form.group_field" type="text" class="input-field font-mono text-sm" placeholder="NoACC, grupAP1, dsb." />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Field Value</label>
                <input v-model="form.field_value" type="text" class="input-field" placeholder="A, P, dsb." />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Label Tampil <span class="text-red-500">*</span></label>
              <input v-model="form.label" type="text" class="input-field" placeholder="AKTIVA, KEWAJIBAN, dsb." />
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Special Handling</label>
              <select v-model="form.special_handling" class="input-field">
                <option value="default">Default (subtotal per group)</option>
                <option value="running-balance">Running Balance</option>
                <option value="category-label">Category Label</option>
              </select>
            </div>

            <div class="flex gap-4">
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="form.show_subtotal" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Tampilkan Subtotal
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Style JSON</label>
              <textarea v-model="styleJson" rows="2" class="input-field font-mono text-xs"
                placeholder='{"bold": true, "bgColor": "#e8f4f8"}'></textarea>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="closeModal" class="px-4 py-2 text-secondary-600 rounded-lg">Batal</button>
            <button @click="saveGroup" :disabled="store.saving || !form.label" class="btn-primary">Simpan</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import type { AdminGroup } from '~/stores/adminReports'

const store = useAdminReportStore()

const showModal = ref(false)
const editingGroup = ref<AdminGroup | null>(null)
const styleJson = ref('')

const form = reactive({
  group_level: 1,
  group_field: '',
  field_value: '',
  label: '',
  sort_order: 0,
  show_subtotal: true,
  special_handling: 'default',
})

const groupsByLevel = computed(() => {
  const groups = store.selectedReportData?.groups || []
  const byLevel: Record<number, AdminGroup[]> = {}
  for (const g of groups) {
    if (!byLevel[g.group_level]) byLevel[g.group_level] = []
    byLevel[g.group_level].push(g)
  }
  return byLevel
})

function openModal(g?: AdminGroup) {
  editingGroup.value = g || null
  if (g) {
    Object.assign(form, {
      group_level: g.group_level,
      group_field: g.group_field,
      field_value: g.field_value,
      label: g.label,
      sort_order: g.sort_order,
      show_subtotal: g.show_subtotal,
      special_handling: g.special_handling
    })
    styleJson.value = g.style_config ? JSON.stringify(g.style_config) : ''
  } else {
    Object.assign(form, { group_level: 1, group_field: '', field_value: '', label: '', sort_order: 0, show_subtotal: true, special_handling: 'default' })
    styleJson.value = ''
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingGroup.value = null
}

async function saveGroup() {
  let style_config = null
  if (styleJson.value.trim()) {
    try { style_config = JSON.parse(styleJson.value) } catch { /* ignore */ }
  }
  const data = { ...form, style_config }
  if (editingGroup.value) {
    await store.updateGroup(editingGroup.value.id_group, data)
  } else {
    await store.createGroup(data)
  }
  closeModal()
}

async function confirmDelete(g: AdminGroup) {
  if (!confirm(`Hapus grouping "${g.label}"?`)) return
  await store.deleteGroup(g.id_group)
}
</script>