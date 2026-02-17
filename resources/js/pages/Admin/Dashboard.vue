<template>
  <div class="">
    <h1>Admin Dashboard</h1>
    <button class="btn" @click="logout">Logout</button>
  </div>
</template>

<script setup lang="ts">
// You can put TS code here if needed
import { router } from '@inertiajs/vue3';

const logout = () => {
  router.post('/logout');
};
</script>

<style scoped>
/* optional styling */
</style>