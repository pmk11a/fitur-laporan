<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-secondary-900">Laporan</h1>
      <p class="text-secondary-600">Pilih laporan yang ingin ditampilkan</p>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="menu in menuStore.sortedMenus"
        :key="menu.KODEMENU"
        class="card p-6 hover:shadow-md transition-shadow cursor-pointer"
        @click="openReport(menu)"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-secondary-900 truncate">{{ menu.Keterangan }}</h3>
            <p class="text-sm text-secondary-500 mt-1">{{ menu.KODEMENU }}</p>
            <div class="flex items-center gap-2 mt-3">
              <span class="px-2 py-1 bg-secondary-100 text-secondary-600 text-xs rounded-full">
                ACCESS: {{ menu.ACCESS }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="menuStore.sortedMenus.length === 0 && !menuStore.loading" class="card p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium text-secondary-900 mb-2">Tidak Ada Laporan</h3>
      <p class="text-secondary-500">Tidak ada laporan yang tersedia untuk akses Anda.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const authStore = useAuthStore()
const menuStore = useMenuStore()
const router = useRouter()

interface MenuItem {
  KODEMENU: string
  Keterangan: string
  L0: number
  ACCESS: number
  OL: number
  icon?: string
  type?: string
  children?: MenuItem[]
}

// Ensure menus are loaded even if sidebar hasn't mounted yet
onMounted(async () => {
  if (authStore.isAuthenticated && menuStore.menus.length === 0) {
    await menuStore.fetchMenus()
  }
})

function openReport(menu: MenuItem) {
  router.push(`/reports/${menu.KODEMENU}`)
}
</script>