<template>
  <header class="h-16 bg-white border-b border-secondary-200 flex items-center justify-between px-6">
    <!-- Left: Menu Toggle (Mobile) -->
    <div class="flex items-center gap-4">
      <button
        @click="$emit('toggleSidebar')"
        class="lg:hidden p-2 text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 rounded-lg"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Breadcrumb -->
      <div class="hidden sm:flex items-center gap-2 text-sm">
        <span class="text-secondary-500">Pages</span>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-secondary-900 font-medium">{{ currentPage }}</span>
      </div>
    </div>

    <!-- Right: User Menu -->
    <div class="flex items-center gap-4">
      <!-- Notifications -->
      <button class="relative p-2 text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
      </button>

      <!-- User Dropdown -->
      <div class="relative">
        <button
          @click="showUserMenu = !showUserMenu"
          class="flex items-center gap-3 p-2 hover:bg-secondary-100 rounded-lg transition-colors"
        >
          <div class="w-9 h-9 bg-primary-500 rounded-full flex items-center justify-center">
            <span class="text-white font-medium text-sm">
              {{ userInitials }}
            </span>
          </div>
          <div class="hidden md:block text-left">
            <p class="text-sm font-medium text-secondary-900">{{ authStore.userName }}</p>
            <p class="text-xs text-secondary-500 capitalize">{{ authStore.user?.role || 'User' }}</p>
          </div>
          <svg class="w-5 h-5 text-secondary-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50"
        >
          <div class="px-4 py-2 border-b border-secondary-100">
            <p class="text-sm font-medium text-secondary-900">{{ authStore.userName }}</p>
            <p class="text-xs text-secondary-500">{{ authStore.user?.username }}</p>
          </div>

          <button
            @click="handleLogout"
            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sign Out
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
const emit = defineEmits<{
  toggleSidebar: []
}>()

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const showUserMenu = ref(false)

const currentPage = computed(() => {
  const path = route.path
  if (path === '/dashboard') return 'Dashboard'
  if (path === '/reports') return 'Laporan'
  if (path.startsWith('/admin')) return 'Admin'
  return 'Page'
})

const userInitials = computed(() => {
  const name = authStore.userName
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

async function handleLogout() {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/login')
}

// Close menu on click outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement
    if (!target.closest('.relative')) {
      showUserMenu.value = false
    }
  })
})
</script>