<template>
  <div class="flex min-h-screen bg-secondary-100">
    <!-- Sidebar -->
    <Sidebar :is-open="sidebarOpen" @toggle="sidebarOpen = !sidebarOpen" />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col transition-all duration-300"
      :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">

      <!-- Top Bar -->
      <TopBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const sidebarOpen = ref(true)

// Handle responsive sidebar
onMounted(() => {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
})
</script>