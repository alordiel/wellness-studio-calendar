<template>
  <div class="d-flex justify-space-between">
    <h1 class="text-h4 mb-6">{{ title }}</h1>
    <router-link to="/" class="text-blue-600 hover:text-blue-800 mb-6 inline-block">
      ← Back to Dashboard
    </router-link>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  }
});
</script>
