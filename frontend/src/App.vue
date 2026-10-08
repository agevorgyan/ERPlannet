<script setup lang="ts">
// Root Application entry
</script>

<template>
  <div id="app" class="min-h-screen bg-slate-50 text-slate-800 font-sans antialiased">
    <router-view />
  </div>
</template>

<style>
/* Global resets & typography */
html, body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: #f8fafc;
  color: #1e293b;
}
</style>
