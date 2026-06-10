<template>
  <div class="space-y-3">
    <div v-if="!columns || columns.length === 0" class="p-4 text-center text-gray-500 text-sm">
      Tidak ada kolom untuk laporan ini
    </div>

    <table v-else class="w-full border-collapse text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th
            v-for="col in columns"
            :key="col.field"
            class="border border-gray-300 px-3 py-2 text-left font-medium"
            :class="headerAlignClass(col)"
          >
            {{ col.label || col.field }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, rowIdx) in rows"
          :key="rowIdx"
          :class="rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
        >
          <td
            v-for="col in columns"
            :key="col.field"
            class="border border-gray-300 px-3 py-1"
            :class="cellAlignClass(col)"
          >
            <span v-if="!col.isNumber">{{ readCellValue(row, col.field) }}</span>
            <span v-else>
              {{ formatCell(row[col.field], col) }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { useNumberFormatter } from '~/composables/useNumberFormatter'

export interface ColumnDef {
  field: string
  label?: string
  isNumber?: boolean
  type?: string
  align?: 'left' | 'right' | 'center'
  headerAlign?: 'left' | 'right' | 'center'
}

const props = defineProps<{
  kode: string
  columns: ColumnDef[]
  rows: Record<string, any>[]
}>()

// Build a per-column formatter map. Each cell uses the column's own key
// (col.{kode}.{field}) so the user can override per-column via the
// preferences panel. Fallback chain is handled inside useNumberFormatter.
const columnFormatters = new Map<string, ReturnType<typeof useNumberFormatter>>()

const getFormatter = (col: ColumnDef) => {
  if (!columnFormatters.has(col.field)) {
    columnFormatters.set(col.field, useNumberFormatter(props.kode, col.field))
  }
  return columnFormatters.get(col.field)!
}

const readCellValue = (row: Record<string, any>, field: string) => {
  const v = row[field]
  if (v === null || v === undefined) return ''
  if (typeof v === 'object') return JSON.stringify(v)
  return String(v)
}

const headerAlignClass = (col: ColumnDef) => {
  const a = col.headerAlign || col.align || (col.isNumber ? 'right' : 'left')
  return a === 'right' ? 'text-right' : a === 'center' ? 'text-center' : 'text-left'
}

const cellAlignClass = (col: ColumnDef) => {
  const a = col.align || (col.isNumber ? 'right' : 'left')
  return a === 'right' ? 'text-right' : a === 'center' ? 'text-center' : 'text-left'
}

const formatCell = (rawValue: any, col: ColumnDef) => {
  // Non-number cells: bypass formatter entirely
  if (!col.isNumber) return readCellValue({ [col.field]: rawValue }, col.field)

  const { format } = getFormatter(col)
  return format(rawValue)
}
</script>
