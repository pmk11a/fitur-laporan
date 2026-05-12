<template>
  <div class="relative">
    <input
      ref="inputRef"
      type="text"
      v-model="searchText"
      @input="onInput"
      @focus="onFocus"
      @blur="onBlur"
      @keydown.down.prevent="moveDown"
      @keydown.up.prevent="moveUp"
      @keydown.enter.prevent="selectHighlighted"
      @keydown.escape="closeDropdown"
      class="input-field"
      :placeholder="placeholder"
      autocomplete="off"
    />

    <!-- Dropdown results -->
    <div
      v-if="isOpen && results.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg overflow-hidden"
    >
      <ul class="max-h-60 overflow-y-auto py-1">
        <li
          v-for="(item, idx) in results"
          :key="item.Perkiraan"
          :class="[
            'px-4 py-2 cursor-pointer text-sm',
            idx === highlightedIndex ? 'bg-primary-50 text-primary-700' : 'hover:bg-secondary-50'
          ]"
          @mousedown.prevent="selectItem(item)"
          @mouseenter="highlightedIndex = idx"
        >
          <span class="font-mono font-medium text-secondary-800">{{ item.Perkiraan }}</span>
          <span class="text-secondary-500 ml-2">{{ item.Keterangan }}</span>
        </li>
      </ul>
    </div>

    <!-- No results -->
    <div
      v-if="isOpen && hasSearched && results.length === 0"
      class="absolute z-50 w-full mt-1 bg-white border border-secondary-200 rounded-lg shadow-lg px-4 py-3 text-sm text-secondary-500"
    >
      Tidak ditemukan. Anda dapat mengetik manual.
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: string
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const config = useRuntimeConfig()
const authStore = useAuthStore()

const searchText = ref(props.modelValue)
const results = ref<{ Perkiraan: string; Keterangan: string }[]>([])
const isOpen = ref(false)
const hasSearched = ref(false)
const highlightedIndex = ref(-1)
const inputRef = ref<HTMLInputElement | null>(null)

let debounceTimer: ReturnType<typeof setTimeout> | null = null

// Sync external modelValue changes into local searchText
watch(() => props.modelValue, (val) => {
  if (val !== searchText.value) searchText.value = val
})

function onInput() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => search(), 250)
}

async function search() {
  const q = searchText.value.trim()
  if (q.length === 0) {
    results.value = []
    isOpen.value = false
    hasSearched.value = false
    return
  }

  try {
    const response = await $fetch<{ success: boolean; data: any[] }>(
      `${config.public.apiBase}/reports/perkiraan/search`,
      { headers: authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {}, query: { q } }
    )
    if (response.success) {
      results.value = response.data
      hasSearched.value = true
      isOpen.value = true
      highlightedIndex.value = -1
    }
  } catch {
    results.value = []
    isOpen.value = false
  }
}

function onFocus() {
  if (searchText.value.trim()) {
    search()
  }
}

function onBlur() {
  setTimeout(() => closeDropdown(), 200)
}

function closeDropdown() {
  isOpen.value = false
  highlightedIndex.value = -1
}

function selectItem(item: { Perkiraan: string; Keterangan: string }) {
  searchText.value = item.Perkiraan
  emit('update:modelValue', item.Perkiraan)
  closeDropdown()
}

function moveDown() {
  if (!isOpen.value) return
  highlightedIndex.value = Math.min(highlightedIndex.value + 1, results.value.length - 1)
}

function moveUp() {
  if (!isOpen.value) return
  highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
}

function selectHighlighted() {
  if (highlightedIndex.value >= 0 && results.value[highlightedIndex.value]) {
    selectItem(results.value[highlightedIndex.value])
  }
}

// Free-text fallback: emit modelValue as user types
watch(searchText, (val) => {
  emit('update:modelValue', val)
})
</script>