<template>
  <div class="space-y-6">
    <!-- Basic Info -->
    <div class="bg-secondary-50 rounded-lg p-4">
      <h4 class="text-sm font-semibold text-secondary-700 mb-3">Informasi Dasar</h4>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <span class="text-secondary-500">Source:</span>
          <span class="ml-2 font-medium" :class="config.source === 'database' ? 'text-green-600' : 'text-secondary-600'">
            {{ config.source }}
          </span>
        </div>
        <div>
          <span class="text-secondary-500">Table:</span>
          <span class="ml-2 font-mono text-xs">{{ config.table || '-' }}</span>
        </div>
        <div>
          <span class="text-secondary-500">Key Field:</span>
          <span class="ml-2 font-mono text-xs">{{ config.keyField }}</span>
        </div>
        <div>
          <span class="text-secondary-500">Label Field:</span>
          <span class="ml-2 font-mono text-xs">{{ config.labelField }}</span>
        </div>
      </div>
    </div>

    <!-- Query Mode -->
    <div v-if="config.query" class="bg-blue-50 rounded-lg p-4">
      <h4 class="text-sm font-semibold text-blue-700 mb-2">Custom Query Mode</h4>
      <pre class="text-xs font-mono bg-white p-3 rounded overflow-x-auto">{{ config.query }}</pre>
    </div>

    <!-- Additional Fields -->
    <div v-if="config.additionalFields?.length">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Additional Fields</h4>
      <div class="flex flex-wrap gap-2">
        <span v-for="field in config.additionalFields" :key="field"
          class="px-2 py-1 bg-secondary-100 rounded text-xs font-mono">
          {{ field }}
        </span>
      </div>
    </div>

    <!-- Joins -->
    <div v-if="config.joins?.length">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Joins</h4>
      <div class="space-y-2">
        <pre v-for="(join, idx) in config.joins" :key="idx"
          class="text-xs font-mono bg-secondary-50 p-2 rounded overflow-x-auto">{{ join }}</pre>
      </div>
    </div>

    <!-- Where Extra -->
    <div v-if="config.whereExtra">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Where Extra</h4>
      <pre class="text-xs font-mono bg-secondary-50 p-3 rounded">{{ config.whereExtra }}</pre>
    </div>

    <!-- Parent Filters -->
    <div v-if="config.parent_filters?.length">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Parent Filters</h4>
      <div class="space-y-2">
        <div v-for="(pf, idx) in config.parent_filters" :key="idx"
          class="p-3 bg-yellow-50 rounded-lg border border-yellow-100">
          <div class="flex items-center gap-4 text-sm">
            <div>
              <span class="text-secondary-500">Column:</span>
              <span class="ml-2 font-mono font-medium">{{ pf.source_column }}</span>
            </div>
            <div>
              <span class="text-secondary-500">Operator:</span>
              <span class="ml-2 font-mono">{{ pf.operator }}</span>
            </div>
            <div>
              <span class="text-secondary-500">Type:</span>
              <span class="ml-2 px-1.5 py-0.5 bg-yellow-100 rounded text-xs">{{ pf.type }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alias Fields -->
    <div v-if="config.alias_fields && Object.keys(config.alias_fields).length">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Alias Fields</h4>
      <div class="bg-secondary-50 rounded-lg p-3">
        <table class="w-full text-xs">
          <thead>
            <tr class="text-left text-secondary-500">
              <th class="pb-2">Alias</th>
              <th class="pb-2">Expression</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(expr, alias) in config.alias_fields" :key="alias" class="border-t border-secondary-200">
              <td class="py-2 font-mono font-medium">{{ alias }}</td>
              <td class="py-2 font-mono text-secondary-600">{{ expr }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t border-secondary-200">
      <button @click="testBrowse" :disabled="testing" class="btn-secondary">
        {{ testing ? 'Testing...' : 'Test Browse' }}
      </button>
    </div>

    <!-- Test Results -->
    <div v-if="testResults" class="mt-4">
      <h4 class="text-sm font-semibold text-secondary-700 mb-2">Test Results</h4>
      <div class="bg-green-50 rounded-lg p-4 max-h-60 overflow-y-auto">
        <p class="text-xs text-green-600 mb-2">Found {{ testResults.length }} results:</p>
        <div class="space-y-1">
          <div v-for="(result, idx) in testResults.slice(0, 5)" :key="idx"
            class="text-xs font-mono bg-white p-2 rounded">
            {{ JSON.stringify(result) }}
          </div>
          <p v-if="testResults.length > 5" class="text-xs text-secondary-500 text-center pt-2">
            ... and {{ testResults.length - 5 }} more
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { AdminBrowseConfig } from '~/stores/adminReports'

const props = defineProps<{
  config: AdminBrowseConfig
}>()

const testing = ref(false)
const testResults = ref<any[] | null>(null)

async function testBrowse() {
  testing.value = true
  testResults.value = null
  try {
    const res = await $fetch<any>(`/api/reports/test/browse/${props.config.kodeBrowse}?limit=10`)
    if (res.success) {
      testResults.value = res.data.results
    }
  } catch (e: any) {
    alert('Test failed: ' + e.message)
  } finally {
    testing.value = false
  }
}
</script>