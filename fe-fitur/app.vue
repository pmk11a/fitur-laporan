<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Debug helper: enable T1 payload inspection from /preview
// Usage in browser DevTools:  __toggleDebugT1(true)  /  __toggleDebugT1(false)
if (import.meta.client) {
  const KEY = 'debug_t1_payload'
  const w = window as any
  w.__DEBUG_T1_PAYLOAD__ = localStorage.getItem(KEY) === '1'
  w.__toggleDebugT1__ = (on: boolean) => {
    w.__DEBUG_T1_PAYLOAD__ = !!on
    localStorage.setItem(KEY, on ? '1' : '0')
    console.log(`[DEBUG] T1 payload logging: ${on ? 'ON' : 'OFF'}`)
  }
}
</script>