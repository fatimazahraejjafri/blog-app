<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Chart from 'chart.js/auto';

interface AuthorStat  { id: number; name: string; count: number; }
interface PostSummary { id: number; title: string; status: string; }
interface Stats {
    total: number; published: number; pending: number;
    draft: number; archived: number; thisMonth: number;
    topAuthors: AuthorStat[];
}

const props = defineProps<{ admin: { name: string } }>();

const stats = ref<Stats>({
    total: 0, published: 0, pending: 0,
    draft: 0, archived: 0, thisMonth: 0, topAuthors: [],
});
const recentPosts = ref<PostSummary[]>([]);

const monthLabel = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });

const authorGradients = [
    'from-indigo-400 to-indigo-600',
    'from-emerald-400 to-emerald-600',
    'from-amber-400 to-amber-500',
    'from-rose-400 to-rose-500',
];

const statusMeta: Record<string, { icon: string; bg: string; pill: string }> = {
    published: { icon: '📄', bg: 'bg-green-50',  pill: 'bg-green-100 text-green-700' },
    pending:   { icon: '⏳', bg: 'bg-yellow-50', pill: 'bg-yellow-100 text-yellow-700' },
    draft:     { icon: '✏️', bg: 'bg-indigo-50', pill: 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200' },
    archived:  { icon: '🗄️', bg: 'bg-muted',     pill: 'bg-gray-100 text-gray-500' },
};

function initials(name: string) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}
function maxCount(authors: AuthorStat[]) {
    return Math.max(...authors.map(a => a.count), 1);
}

onMounted(async () => {
    try {
        const response = await fetch('/admin/posts/stats');
        const data = await response.json();

        stats.value       = data.stats;
        recentPosts.value = data.recent;

        // Bar chart
        const trendCtx = document.getElementById('trendChart') as HTMLCanvasElement;
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: data.trend.labels,
                    datasets: [{
                        label: 'Posts',
                        data: data.trend.data,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#111827',
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: '#9ca3af', font: { size: 11 } },
                        },
                        y: { display: false, beginAtZero: true },
                    },
                },
            });
        }

        // Donut chart
        const donutCtx = document.getElementById('donutChart') as HTMLCanvasElement;
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [data.stats.published, data.stats.pending, data.stats.draft, data.stats.archived],
                        backgroundColor: ['#22c55e', '#f59e0b', '#6366f1', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                },
            });
        }

    } catch (err) {
        console.error('Failed to fetch stats:', err);
    }
});
</script>

<template>
    <AppLayout title="Admin Dashboard">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">

            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-foreground">Overview</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">Welcome back, {{ admin.name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/admin/posts" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
                        ↗ Manage Posts
                    </Link>
                   
                </div>
            </div>

            <!-- Top grid: chart | stats | authors -->
            <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr_1fr] gap-4">

                <!-- Trend chart -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Posts Analytics</div>
                            <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span> Posts created
                            </div>
                        </div>
                    </div>
                    <div class="h-44 relative">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Stats card -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm flex flex-col gap-4">
                    <div class="flex items-start justify-between">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Total Posts</div>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center text-base">📊</div>
                    </div>
                    <div class="text-4xl font-extrabold tracking-tight text-foreground leading-none">{{ stats.total }}</div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                            Published <strong class="text-foreground ml-auto">{{ stats.published }}</strong>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span class="w-2 h-2 rounded-full bg-yellow-400 shrink-0"></span>
                            Pending <strong class="text-foreground ml-auto">{{ stats.pending }}</strong>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                            Draft <strong class="text-foreground ml-auto">{{ stats.draft }}</strong>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                            Archived <strong class="text-foreground ml-auto">{{ stats.archived }}</strong>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground mb-1">This Month</div>
                        <div class="text-3xl font-extrabold tracking-tight text-foreground leading-none">{{ stats.thisMonth }}</div>
                        <div class="text-xs text-muted-foreground mt-1">{{ monthLabel }}</div>
                    </div>
                </div>

                <!-- Top Authors + donut -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm flex flex-col gap-4">
                    <div class="flex items-start justify-between">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Top Authors</div>
                        <div class="w-9 h-9 rounded-xl bg-yellow-50 flex items-center justify-center text-base">👥</div>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <div v-for="(author, i) in stats.topAuthors.slice(0, 3)" :key="author.id" class="flex items-center gap-2.5">
                            <div :class="['w-8 h-8 rounded-full bg-gradient-to-br text-white text-[11px] font-bold flex items-center justify-center shrink-0', authorGradients[i]]">
                                {{ initials(author.name) }}
                            </div>
                            <span class="text-sm font-semibold text-foreground flex-1 truncate">{{ author.name }}</span>
                            <span class="text-[11px] font-semibold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">{{ author.count }} posts</span>
                        </div>
                        <p v-if="!stats.topAuthors.length" class="text-xs text-muted-foreground">No authors yet</p>
                    </div>

                    <!-- Donut -->
                    <div class="border-t border-border pt-4">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground mb-3">Status Breakdown</div>
                        <div class="flex items-center gap-4">
                            <div class="relative w-[90px] h-[90px] shrink-0">
                                <canvas id="donutChart"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-lg font-extrabold text-foreground leading-none">{{ stats.total }}</span>
                                    <span class="text-[10px] text-muted-foreground">total</span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-green-500 shrink-0"></span>
                                    <span class="text-xs text-muted-foreground flex-1">Published</span>
                                    <span class="text-xs font-bold text-foreground">{{ stats.published }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-yellow-400 shrink-0"></span>
                                    <span class="text-xs text-muted-foreground flex-1">Pending</span>
                                    <span class="text-xs font-bold text-foreground">{{ stats.pending }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-indigo-500 shrink-0"></span>
                                    <span class="text-xs text-muted-foreground flex-1">Draft</span>
                                    <span class="text-xs font-bold text-foreground">{{ stats.draft }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-red-400 shrink-0"></span>
                                    <span class="text-xs text-muted-foreground flex-1">Archived</span>
                                    <span class="text-xs font-bold text-foreground">{{ stats.archived }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom grid: recent activity | author bars -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <!-- Recent Activity -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Recent Activity</div>
                            <div class="text-sm font-bold text-foreground mt-0.5">Latest Posts</div>
                        </div>
                        <Link href="/admin/posts" class="text-xs font-semibold text-indigo-500 hover:underline">View all ↗</Link>
                    </div>
                    <div class="flex flex-col divide-y divide-border">
                        <div v-for="post in recentPosts" :key="post.id" class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                            <div :class="['w-9 h-9 rounded-xl flex items-center justify-center text-base shrink-0', statusMeta[post.status]?.bg ?? 'bg-muted']">
                                {{ statusMeta[post.status]?.icon ?? '📄' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-foreground truncate">{{ post.title }}</div>
                            </div>
                            <span :class="['inline-flex items-center text-[11px] font-semibold px-2.5 py-1 rounded-full capitalize shrink-0', statusMeta[post.status]?.pill ?? 'bg-gray-100 text-gray-500']">
                                {{ post.status }}
                            </span>
                        </div>
                        <p v-if="!recentPosts.length" class="text-xs text-muted-foreground py-4 text-center">No recent posts</p>
                    </div>
                </div>

                <!-- Author performance bars -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                    <div class="mb-4">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Author Performance</div>
                        <div class="text-sm font-bold text-foreground mt-0.5">Post contributions</div>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div v-for="(author, i) in stats.topAuthors.slice(0, 4)" :key="author.id">
                            <div class="flex justify-between text-xs font-semibold mb-1.5">
                                <span class="text-foreground">{{ author.name }}</span>
                                <span class="text-indigo-500">{{ author.count }} posts</span>
                            </div>
                            <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :class="['bg-gradient-to-r', ['from-indigo-400 to-indigo-600','from-emerald-400 to-emerald-600','from-amber-400 to-amber-500','from-rose-400 to-rose-500'][i]]"
                                    :style="{ width: (author.count / maxCount(stats.topAuthors) * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                        <p v-if="!stats.topAuthors.length" class="text-xs text-muted-foreground">No data yet</p>
                    </div>
                </div>
            </div>

            <!-- Export bar -->
            <div class="bg-foreground rounded-2xl px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold text-background">Export your dashboard statistics</div>
                    <div class="text-xs text-background/50 mt-0.5">Download a full report of posts, authors and trends</div>
                </div>
                <button class="px-5 py-2 bg-background text-foreground text-sm font-bold rounded-xl hover:shadow-lg transition-all">
                    ⬇ Export statistics
                </button>
            </div>

        </div>
    </AppLayout>
</template>