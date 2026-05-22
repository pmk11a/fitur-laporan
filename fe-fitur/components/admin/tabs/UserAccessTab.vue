<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-medium text-secondary-700">Akses Pengguna</h3>
      <button @click="openAddModal" class="btn-primary text-xs py-1.5">+ Tambah Akses</button>
    </div>

    <div v-if="!store.selectedReportData?.access?.length" class="card p-8 text-center">
      <p class="text-secondary-400 mb-2">Belum ada pengguna yang mendapat akses</p>
      <button @click="openAddModal" class="btn-secondary text-sm">+ Beri Akses</button>
    </div>

    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-secondary-50 text-secondary-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium">USERID</th>
            <th class="px-4 py-3 text-left font-medium">Nama</th>
            <th class="px-4 py-3 text-center font-medium">Akses</th>
            <th class="px-4 py-3 text-center font-medium">Design</th>
            <th class="px-4 py-3 text-center font-medium">Export</th>
            <th class="px-4 py-3 text-right font-medium w-20">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-secondary-100">
          <tr v-for="acc in store.selectedReportData.access" :key="acc.USERID" class="hover:bg-secondary-50">
            <td class="px-4 py-3 font-mono text-xs text-secondary-600">{{ acc.USERID }}</td>
            <td class="px-4 py-3">{{ acc.FullName }}</td>
            <td class="px-4 py-3 text-center">
              <button @click="toggleAccess(acc, 'Access')" class="p-1">
                <span :class="acc.Access ? 'text-green-500' : 'text-secondary-300'">
                  {{ acc.Access ? '●' : '○' }}
                </span>
              </button>
            </td>
            <td class="px-4 py-3 text-center">
              <button @click="toggleAccess(acc, 'IsDesign')" class="p-1">
                <span :class="acc.IsDesign ? 'text-blue-500' : 'text-secondary-300'">
                  {{ acc.IsDesign ? '●' : '○' }}
                </span>
              </button>
            </td>
            <td class="px-4 py-3 text-center">
              <button @click="toggleAccess(acc, 'IsExport')" class="p-1">
                <span :class="acc.IsExport ? 'text-orange-500' : 'text-secondary-300'">
                  {{ acc.IsExport ? '●' : '○' }}
                </span>
              </button>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="confirmRevoke(acc)" class="p-2 text-secondary-400 hover:text-red-500 rounded-lg" title="Cabut Akses">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 9a4 4 0 000 8h3m-3 0a4 4 0 010-8h3m8 0H9" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7v-3m0 0V4m0 3v3m0-3H14" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add Access Modal -->
    <Teleport to="body">
      <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <h3 class="font-semibold text-secondary-800">Berikan Akses</h3>
            <button @click="showAddModal = false" class="text-secondary-400 hover:text-secondary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-1">Pilih Pengguna</label>
              <select v-model="selectedUser" class="input-field">
                <option value="">-- Pilih --</option>
                <option v-for="u in allUsers" :key="u.USERID" :value="u.USERID">
                  {{ u.USERID }} — {{ u.FullName }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="addForm.Access" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Akses
              </label>
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="addForm.IsDesign" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Design
              </label>
              <label class="flex items-center gap-2 text-sm text-secondary-700">
                <input v-model="addForm.IsExport" type="checkbox" class="w-4 h-4 rounded border-secondary-300" /> Export
              </label>
            </div>
          </div>

          <div class="px-6 py-4 border-t bg-secondary-50 flex justify-end gap-3">
            <button @click="showAddModal = false" class="px-4 py-2 text-secondary-600 rounded-lg">Batal</button>
            <button @click="grantUserAccess" :disabled="store.saving || !selectedUser" class="btn-primary">Berikan Akses</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import type { AdminUserAccess } from '~/stores/adminReports'

const store = useAdminReportStore()

const showAddModal = ref(false)
const selectedUser = ref('')
const addForm = reactive({ Access: true, IsDesign: false, IsExport: false })

const allUsers = computed(() => store.allUsers)

onMounted(() => {
  store.fetchAllUsers()
})

async function openAddModal() {
  if (store.allUsers.length === 0) await store.fetchAllUsers()
  selectedUser.value = ''
  addForm.Access = true
  addForm.IsDesign = false
  addForm.IsExport = false
  showAddModal.value = true
}

async function grantUserAccess() {
  if (!selectedUser.value) return
  await store.grantAccess({ USERID: selectedUser.value, ...addForm })
  showAddModal.value = false
}

async function toggleAccess(acc: AdminUserAccess, field: 'Access' | 'IsDesign' | 'IsExport') {
  await store.grantAccess({ USERID: acc.USERID, [field]: !acc[field] })
}

async function confirmRevoke(acc: AdminUserAccess) {
  if (!confirm(`Cabut akses pengguna "${acc.FullName}"?`)) return
  await store.revokeAccess(acc.USERID)
}
</script>