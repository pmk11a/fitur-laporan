<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-secondary-100 text-secondary-700">
        <tr>
          <th
            v-for="col in visibleColumns"
            :key="col.nama_kolom"
            class="px-4 py-3 text-left font-medium border-b border-secondary-200"
            :class="getAlignmentClass(col.alignment)"
          >
            {{ col.label_tampil }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-secondary-100">
        <template v-if="groupedData && Object.keys(groupedData).length > 0">
          <template v-for="(level1Data, level1Key) in groupedData" :key="level1Key">
            <!-- Level 1 Group Header -->
            <tr v-if="showGroupHeaders" class="bg-secondary-50 font-semibold">
              <td :colspan="visibleColumns.length" class="px-4 py-2">
                {{ level1Data?.label || '' }}
              </td>
            </tr>

            <!-- Render using subgroups -->
            <template v-if="useSubgroups() && level1Data?.subgroups">
              <template v-for="(level2Data, level2Key) in level1Data.subgroups" :key="`${level1Key}-${level2Key}`">
                <!-- Level 2 Group Header -->
                <tr v-if="showGroupHeaders" class="bg-blue-50 font-medium">
                  <td :colspan="visibleColumns.length" class="px-4 py-2 pl-8">
                    {{ level2Data?.label || '' }}
                  </td>
                </tr>

                <!-- Level 2 Items -->
                <template v-if="level2Data?.items">
                  <tr v-for="(item, itemIdx) in level2Data.items" :key="`${level1Key}-${level2Key}-${itemIdx}`" class="hover:bg-secondary-50">
                    <td
                      v-for="col in visibleColumns"
                      :key="`${level1Key}-${level2Key}-${itemIdx}-${col.nama_kolom}`"
                      class="px-4 py-3 border-b border-secondary-100"
                      :class="getAlignmentClass(col.alignment)"
                    >
                      {{ formatCell(getItemValue(item, col, level1Key, itemIdx), col.format_type) }}
                    </td>
                  </tr>
                </template>

                <!-- Level 2 Subtotal -->
                <tr v-if="hasSubtotal(level2Data?.subtotal)" class="bg-blue-100 font-semibold">
                  <td class="px-4 py-2 pl-8 text-right">Sub Total:</td>
                  <td
                    v-for="(col, colIdx) in visibleColumns.slice(1)"
                    :key="`sub-${level1Key}-${level2Key}-${col.nama_kolom}`"
                    class="px-4 py-2"
                    :class="getAlignmentClass(col.alignment)"
                  >
                    <template v-if="col.is_summable">
                      {{ formatCell(level2Data.subtotal[col.nama_kolom], col.format_type) }}
                    </template>
                  </td>
                </tr>
              </template>
            </template>

            <!-- Render flat items (no subgroups) -->
            <template v-else-if="level1Data?.items">
              <tr v-for="(item, itemIdx) in level1Data.items" :key="`${level1Key}-${itemIdx}`" class="hover:bg-secondary-50">
                <td
                  v-for="col in visibleColumns"
                  :key="`${level1Key}-item-${itemIdx}-${col.nama_kolom}`"
                  class="px-4 py-3 border-b border-secondary-100"
                  :class="getAlignmentClass(col.alignment)"
                >
                  {{ formatCell(getItemValue(item, col, level1Key, itemIdx), col.format_type) }}
                </td>
              </tr>
            </template>

            <!-- Level 1 Subtotal -->
            <tr v-if="hasSubtotal(level1Data?.subtotal)" class="bg-secondary-200 font-bold">
              <td class="px-4 py-2">Total {{ level1Data?.label || '' }}:</td>
              <td
                v-for="col in visibleColumns.slice(1)"
                :key="`total-${level1Key}-${col.nama_kolom}`"
                class="px-4 py-2"
                :class="getAlignmentClass(col.alignment)"
              >
                <template v-if="col.is_summable">
                  {{ formatCell(level1Data.subtotal[col.nama_kolom], col.format_type) }}
                </template>
              </td>
            </tr>
          </template>
        </template>

        <!-- Grand Total -->
        <tr v-if="grandTotal && Object.keys(grandTotal).length > 0" class="bg-primary-100 font-bold text-primary-800">
          <td class="px-4 py-3">GRAND TOTAL:</td>
          <td
            v-for="col in visibleColumns.slice(1)"
            :key="`grand-${col.nama_kolom}`"
            class="px-4 py-3"
            :class="getAlignmentClass(col.alignment)"
          >
            <template v-if="col.is_summable">
              {{ formatCell(grandTotal[col.nama_kolom], col.format_type) }}
            </template>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- No Data -->
    <div v-if="isEmpty" class="text-center py-8 text-secondary-500">
      No data available
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Generic GroupedTable Component
 *
 * SOLID: DIP - Depends on config from backend, not hardcoded patterns
 * OCP: Config-based rendering, no strategy pattern matching
 *
 * No more: strategyName, kodeMenu pattern matching
 * Only: groupingConfig from database via backend
 */

const props = defineProps<{
  groupedData: { [key: string]: any } | null
  columns: { [dataset: string]: any[] }
  grandTotal?: { [col: string]: number }
  mainDataset?: string
}>()

// Config-based grouping helpers (from database via backend)
const {
  shouldCalculateRunningBalance,
  getBalanceColumn,
  getStartRowMarker,
  getMarkerColumn,
  useSubgroups,
  showGroupHeaders,
} = useGroupingConfig()

// Visible columns from config
const visibleColumns = computed(() => {
  if (props.mainDataset && props.columns[props.mainDataset]) {
    return props.columns[props.mainDataset].filter(col => col.is_visible !== false)
  }
  return Object.values(props.columns || {})[0]?.filter(col => col.is_visible !== false) || []
})

const isEmpty = computed(() => {
  if (!props.groupedData) return true
  return Object.keys(props.groupedData).length === 0
})

/**
 * Get item value - config-based, not strategy-based
 * Handles running balance for buku-tambahan if configured
 */
function getItemValue(item: any, col: any, groupKey: string, itemIndex: number): any {
  const balanceCol = getBalanceColumn()
  if (shouldCalculateRunningBalance() && col.nama_kolom === balanceCol) {
    const startMarker = getStartRowMarker()
    const markerCol = getMarkerColumn()
    if (!balanceCol || !startMarker || !markerCol) {
      return item[col.nama_kolom]
    }
    const allItems = getAllGroupItems(groupKey)
    return calculateRunningBalance(allItems, itemIndex, balanceCol, startMarker, markerCol)
  }
  return item[col.nama_kolom]
}

/**
 * Get all items for a group (flatten from subgroups)
 */
function getAllGroupItems(groupKey: string): any[] {
  if (!props.groupedData || !props.groupedData[groupKey]) return []
  const group = props.groupedData[groupKey]
  if (!group) return []

  const allItems: any[] = []

  if (group.subgroups && typeof group.subgroups === 'object') {
    for (const subgroup of Object.values(group.subgroups)) {
      if (subgroup?.items) {
        allItems.push(...subgroup.items)
      }
    }
  } else if (group.items) {
    allItems.push(...group.items)
  }

  return allItems
}

// Alignment helper
function getAlignmentClass(alignment: string): string {
  switch (alignment) {
    case 'right': return 'text-right'
    case 'center': return 'text-center'
    default: return 'text-left'
  }
}

// Format cell value
function formatCell(value: any, formatType: string): string {
  if (value === null || value === undefined || value === '') return '-'

  switch (formatType) {
    case 'currency':
    case 'numeric':
      const num = parseFloat(String(value).replace(/[^0-9.-]/g, ''))
      if (isNaN(num)) return String(value)
      return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
    case 'date':
      if (/^\d{4}-\d{2}-\d{2}/.test(String(value))) {
        return new Date(value).toLocaleDateString('id-ID')
      }
      return String(value)
    default:
      return String(value)
  }
}

function hasSubtotal(subtotal: any): boolean {
  if (!subtotal) return false
  return Object.keys(subtotal).length > 0
}
</script>