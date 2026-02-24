<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Activity {
    id: number;
    description: string;
    causer_name: string;
    created_at: string;
}

const props = defineProps<{
    admin: { name: string };
    activities: Activity[];   // 👈 received from Inertia
}>();

function formatDate(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleString('default', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}
</script>

<template>
  <AppLayout title="User Activity">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">

      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-foreground">User Activity</h1>
          <p class="text-sm text-muted-foreground mt-0.5">All recent actions by users</p>
        </div>
        <Link href="/admin/dashboard" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
          ↩ Back to Dashboard
        </Link>
      </div>

      <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground mb-4">Latest Activities</div>
        <div class="flex flex-col divide-y divide-border">

          <div v-for="activity in props.activities" :key="activity.id"
               class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">

            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0 text-sm">
              {{ activity.causer_name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
            </div>

            <div class="flex-1 min-w-0">
              <div class="text-sm text-foreground truncate">{{ activity.description }}</div>
              <div class="text-xs text-muted-foreground mt-0.5">
                {{ activity.causer_name }} • {{ formatDate(activity.created_at) }}
              </div>
            </div>
          </div>

          <p v-if="!props.activities.length" class="text-xs text-muted-foreground py-4 text-center">
            No activity yet
          </p>

        </div>
      </div>

    </div>
  </AppLayout>
</template>