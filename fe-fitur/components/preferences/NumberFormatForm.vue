<template>
  <div class="space-y-3 p-3 bg-gray-50 rounded">
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs font-medium text-gray-700">Style</label>
        <select v-model="local.style" class="w-full border rounded p-1 text-sm">
          <option value="normal">Normal (1.234,56)</option>
          <option value="currency">Currency (Rp 1.234)</option>
          <option value="percent">Percent (12,5%)</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-700">Jumlah Desimal</label>
        <select v-model.number="local.decimal" class="w-full border rounded p-1 text-sm">
          <option :value="0">0</option>
          <option :value="1">1</option>
          <option :value="2">2</option>
          <option :value="3">3</option>
          <option :value="4">4</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-700">Locale</label>
        <select v-model="local.locale" class="w-full border rounded p-1 text-sm">
          <option value="id-ID">id-ID (1.234,56)</option>
          <option value="en-US">en-US (1,234.56)</option>
          <option value="de-DE">de-DE (1.234,56)</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-700">Prefix</label>
        <input
          v-model="local.prefix"
          class="w-full border rounded p-1 text-sm"
          placeholder="misal: Rp "
        />
      </div>
      <div>
        <label class="text-xs font-medium text-gray-700">Suffix</label>
        <input
          v-model="local.suffix"
          class="w-full border rounded p-1 text-sm"
          placeholder="misal: unit"
        />
      </div>
      <div class="flex items-end">
        <label class="flex items-center text-sm">
          <input type="checkbox" v-model="local.compact" class="mr-2" />
          Mode ringkas (1.5jt)
        </label>
      </div>
    </div>

    <div class="flex items-center text-sm">
      <label class="flex items-center">
        <input type="checkbox" v-model="local.showZero" class="mr-2" />
        Tampilkan nilai 0 (00,00)
      </label>
    </div>

    <div class="p-2 bg-white rounded border text-sm font-mono space-y-0.5">
      <p class="text-xs text-gray-500 font-sans mb-1">Preview:</p>
      <p>{{ formatPreview(6563322000.456) }}</p>
      <p>{{ formatPreview(1234.5) }}</p>
      <p>{{ formatPreview(0) }}</p>
      <p>{{ formatPreview(-1500000) }}</p>
    </div>

    <div class="flex gap-2">
      <button
        v-if="!hideSave"
        @click="save"
        class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
      >
        💾 Simpan
      </button>
      <button
        v-if="allowReset"
        @click="reset"
        class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300"
      >
        ↺ Reset
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'

interface NumberFormat {
  decimal: number
  locale: string
  prefix: string
  suffix: string
  compact: boolean
  showZero: boolean
  style: 'normal' | 'currency' | 'percent'
}

const props = defineProps<{
  modelValue: NumberFormat
  hideSave?: boolean
  allowReset?: boolean
}>()

const emit = defineEmits<{
  (e: 'save', value: NumberFormat): void
  (e: 'reset'): void
  (e: 'update:modelValue', value: NumberFormat): void
}>()

const local = reactive<NumberFormat>({ ...props.modelValue })

watch(
  () => props.modelValue,
  (v) => {
    Object.assign(local, v)
  },
  { deep: true }
)

watch(
  local,
  (v) => {
    emit('update:modelValue', { ...v })
  },
  { deep: true }
)

const formatPreview = (val: any) => {
  if (val === null || val === undefined || val === '') {
    return local.showZero === false ? '-' : formatRaw(0)
  }
  const num = Number(val)
  if (isNaN(num)) return String(val)
  return formatRaw(num)
}

const formatRaw = (num: number): string => {
  if (local.style === 'percent') {
    return (num * 100).toFixed(local.decimal).replace('.', ',') + (local.suffix || '%')
  }
  if (local.compact) {
    const abs = Math.abs(num)
    const sign = num < 0 ? '-' : ''
    const isId = !local.locale || local.locale.startsWith('id')
    if (abs >= 1_000_000_000) return sign + (abs / 1_000_000_000).toFixed(1).replace('.', ',') + (isId ? ' M' : 'B')
    if (abs >= 1_000_000) return sign + (abs / 1_000_000).toFixed(1).replace('.', ',') + (isId ? ' jt' : 'M')
    if (abs >= 1_000) return sign + (abs / 1_000).toFixed(1).replace('.', ',') + (isId ? ' rb' : 'K')
    return String(num)
  }
  const formatted = new Intl.NumberFormat(local.locale, {
    minimumFractionDigits: local.decimal,
    maximumFractionDigits: local.decimal,
  }).format(num)
  return (local.prefix || '') + formatted + (local.suffix || '')
}

const save = () => emit('save', { ...local })
const reset = () => emit('reset')
</script>
