<template>
  <div>
    <!-- Menu with children - expandable -->
    <div v-if="hasChildren">
      <button
        @click="expanded = !expanded"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-secondary-300 hover:bg-secondary-800 hover:text-white transition-colors"
        :class="{ 'pl-6': depth === 1, 'pl-9': depth === 2, 'pl-12': depth >= 3 }"
      >
        <!-- Expand/Collapse Icon -->
        <svg
          class="w-4 h-4 flex-shrink-0 transition-transform"
          :class="{ 'rotate-90': expanded }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>

        <!-- Menu Icon -->
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="depth === 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>

        <span v-if="isOpen" class="whitespace-nowrap truncate text-sm">{{ menuName }}</span>
      </button>

      <!-- Children -->
      <div v-if="expanded" class="ml-2">
        <MenuItem
          v-for="child in menu.children"
          :key="child.KODEMENU"
          :menu="child"
          :depth="depth + 1"
          :isOpen="isOpen"
        />
      </div>
    </div>

    <!-- Leaf menu item -->
    <NuxtLink
      v-else
      :to="`/reports/${menu.KODEMENU}`"
      class="flex items-center gap-3 px-3 py-2 rounded-lg text-secondary-300 hover:bg-secondary-800 hover:text-white transition-colors"
      :class="{ 'pl-6': depth === 1, 'pl-9': depth === 2, 'pl-12': depth >= 3 }"
      active-class="bg-primary-500/20 text-primary-400"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <span v-if="isOpen" class="whitespace-nowrap truncate text-sm">{{ menu.NmReport }}</span>
    </NuxtLink>
  </div>
</template>

<script setup lang="ts">
interface MenuItemType {
  KODEMENU: string
  Keterangan: string
  NmReport?: string
  L0: number
  ACCESS: string | number
  children?: MenuItemType[]
}

const props = defineProps<{
  menu: MenuItemType
  depth: number
  isOpen: boolean
}>()

const expanded = ref(false)
const hasChildren = computed(() => props.menu.children && props.menu.children.length > 0)
const menuName = computed(() => props.menu.NmReport || props.menu.Keterangan)
</script>
