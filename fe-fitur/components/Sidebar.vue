<template>
  <!-- Mobile Overlay -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/50 z-40 lg:hidden"
    @click="$emit('toggle')"
  />

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed top-0 left-0 h-full z-50 bg-secondary-900 text-white transition-all duration-300 flex flex-col',
      isOpen ? 'w-64' : 'w-20'
    ]"
  >
    <!-- Logo -->
    <div class="h-16 flex items-center px-4 border-b border-secondary-800 flex-shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <span v-if="isOpen" class="font-bold text-lg whitespace-nowrap">Fluffy Bee</span>
      </div>
    </div>

    <!-- User Info & Period -->
    <div v-if="isOpen" class="px-4 py-3 border-b border-secondary-800 flex-shrink-0">
      <div class="text-xs text-secondary-400 mb-1">{{ authStore.userName }}</div>
      <div class="text-sm font-medium text-primary-400">
        Periode: {{ currentPeriod }}
      </div>
    </div>
    <div v-else class="px-2 py-3 border-b border-secondary-800 flex-shrink-0 flex justify-center">
      <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </div>

    <!-- Dashboard Link -->
    <nav class="p-4 space-y-1 flex-shrink-0">
      <NuxtLink
        to="/dashboard"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-secondary-300 hover:bg-secondary-800 hover:text-white transition-colors"
        active-class="bg-primary-500/20 text-primary-400"
      >
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span v-if="isOpen" class="whitespace-nowrap">Dashboard</span>
      </NuxtLink>
    </nav>

    <!-- Search (only when expanded) -->
    <div v-if="isOpen" class="px-4 pb-2 flex-shrink-0">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari laporan..."
          class="w-full pl-9 pr-3 py-2 bg-secondary-800 border border-secondary-700 rounded-lg text-sm text-white placeholder-secondary-400 focus:outline-none focus:border-primary-500"
        />
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-white"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Reports Section - Scrollable -->
    <div class="flex-1 overflow-hidden flex flex-col px-4">
      <p v-if="isOpen" class="text-xs text-secondary-500 uppercase tracking-wider mb-2 flex-shrink-0">Laporan</p>
      <nav class="space-y-1 overflow-y-auto flex-1 custom-scrollbar pb-4">
        <ClientOnly>
          <div v-if="menuStore.loading" class="px-3 py-2 text-secondary-500 text-sm">
            Loading...
          </div>
          <template v-else>
            <!-- Recursive menu items - filtered by search -->
            <MenuItem
              v-for="menu in filteredMenus"
              :key="menu.KODEMENU"
              :menu="menu"
              :depth="0"
              :isOpen="isOpen"
            />
            <!-- No results message -->
            <div v-if="filteredMenus.length === 0 && searchQuery" class="px-3 py-4 text-center text-secondary-400 text-sm">
              Tidak ada hasil untuk "{{ searchQuery }}"
            </div>
          </template>
          <template #fallback>
            <div class="px-3 py-2 text-secondary-500 text-sm">Loading...</div>
          </template>
        </ClientOnly>
      </nav>
    </div>

    <!-- Admin Section -->
    <div v-if="authStore.isAdmin" class="p-4 border-t border-secondary-800 mt-4 flex-shrink-0">
      <p v-if="isOpen" class="text-xs text-secondary-500 uppercase tracking-wider mb-2">Admin</p>
      <nav class="space-y-1">
        <NuxtLink
          to="/admin/reports"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-secondary-300 hover:bg-secondary-800 hover:text-white transition-colors"
          active-class="bg-primary-500/20 text-primary-400"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span v-if="isOpen" class="whitespace-nowrap">Konfigurasi Laporan</span>
        </NuxtLink>
        <NuxtLink
          to="/admin/browse"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-secondary-300 hover:bg-secondary-800 hover:text-white transition-colors"
          active-class="bg-primary-500/20 text-primary-400"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span v-if="isOpen" class="whitespace-nowrap">Setting Browse</span>
        </NuxtLink>
      </nav>
    </div>

    <!-- Collapse Toggle -->
    <button
      @click="$emit('toggle')"
      class="absolute -right-3 top-20 w-6 h-6 bg-secondary-800 rounded-full flex items-center justify-center text-secondary-400 hover:text-white hover:bg-secondary-700 transition-colors hidden lg:flex"
    >
      <svg :class="['w-4 h-4 transition-transform', isOpen ? '' : 'rotate-180']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>
  </aside>
</template>

<script setup lang="ts">
const props = defineProps<{
  isOpen: boolean
}>()

defineEmits<{
  toggle: []
}>()

const authStore = useAuthStore()
const menuStore = useMenuStore()
const searchQuery = ref('')

// Get current period from user data
const currentPeriod = computed(() => {
  const user = authStore.user as any
  if (user?.BULAN && user?.TAHUN) {
    return `${user.BULAN.toString().padStart(2, '0')}/${user.TAHUN}`
  }
  // Fallback to current date
  const now = new Date()
  return `${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()}`
})

// Filter menus by search query
const filteredMenus = computed(() => {
  if (!searchQuery.value.trim()) {
    return menuStore.menus
  }

  const query = searchQuery.value.toLowerCase()

  function filterMenuItems(menus: any[]): any[] {
    return menus
      .map(menu => {
        // Check if this menu matches
        const matches = (menu.NmReport?.toLowerCase().includes(query) ||
                       menu.Keterangan?.toLowerCase().includes(query) ||
                       menu.KODEMENU?.toLowerCase().includes(query))

        // Filter children recursively
        if (menu.children) {
          const filteredChildren = filterMenuItems(menu.children)
          if (filteredChildren.length > 0 || matches) {
            return { ...menu, children: filteredChildren }
          }
          return null
        }

        return matches ? menu : null
      })
      .filter(Boolean)
  }

  return filterMenuItems(menuStore.menus)
})

// Fetch menus on mount
onMounted(async () => {
  // Init auth from storage if not already
  if (!authStore.isAuthenticated) {
    authStore.initFromStorage()
  }
  if (authStore.isAuthenticated) {
    await menuStore.fetchMenus()
  }
})
</script>