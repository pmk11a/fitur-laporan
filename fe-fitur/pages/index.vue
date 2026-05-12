<template>
  <div></div>
</template>

<script setup lang="ts">
const router = useRouter()

onMounted(() => {
  router.replace('/login')
})
</script>