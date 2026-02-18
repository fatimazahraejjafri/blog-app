<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Chart from 'chart.js/auto';

interface AuthorStat  { id: number; name: string; count: number; }
interface PostSummary { id: number; title: string; status: string; }
interface Stats {
    total: number; published: number; pending: number;
    draft: number; archived: number; thisMonth: number;
    topAuthors: AuthorStat[];
}

const props = defineProps({ admin: Object });

const stats = ref<Stats>({
    total: 0, published: 0, pending: 0,
    draft: 0, archived: 0, thisMonth: 0, topAuthors: [],
});
const recentPosts = ref<PostSummary[]>([]);
const trendLabels  = ref<string[]>([]);
const trendData    = ref<number[]>([]);

const monthLabel = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });

const authorColors = [
    'linear-gradient(135deg,#818cf8,#6366f1)',
    'linear-gradient(135deg,#34d399,#059669)',
    'linear-gradient(135deg,#fbbf24,#f59e0b)',
    'linear-gradient(135deg,#f87171,#ef4444)',
];

const statusIcon: Record<string, string> = {
    published: '📄', pending: '⏳', draft: '✏️', archived: '🗄️',
};
const statusBg: Record<string, string> = {
    published: '#dcfce7', pending: '#fef3c7', draft: 'rgba(99,102,241,0.08)', archived: '#f3f4f6',
};

function initials(name: string) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function maxCount(authors: AuthorStat[]) {
    return Math.max(...authors.map(a => a.count), 1);
}

const logout = () => router.post('/logout');

onMounted(async () => {
    try {
        const response = await fetch('/admin/posts/stats');
        const data = await response.json();

        stats.value       = data.stats;
        recentPosts.value = data.recent;
        trendLabels.value  = data.trend.labels;
        trendData.value    = data.trend.data;

        // Bar chart — posts trend
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
                            titleFont: { family: 'Plus Jakarta Sans', weight: 'bold' },
                            bodyFont:  { family: 'Plus Jakarta Sans' },
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
                        data: [
                            data.stats.published,
                            data.stats.pending,
                            data.stats.draft,
                            data.stats.archived,
                        ],
                        backgroundColor: ['#22c55e','#f59e0b','#6366f1','#ef4444'],
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
        <div class="dash-wrap">

            <!-- ── Top bar ── -->
            <div class="topbar">
                <div>
                    <h1 class="topbar-title">Overview</h1>
                    <p class="topbar-sub">Welcome back, {{ admin?.name }}</p>
                </div>
                <div class="topbar-actions">
                    <Link href="/admin/posts" class="btn-primary">↗ Manage Posts</Link>
                    <button class="btn-ghost" @click="logout">Logout</button>
                </div>
            </div>

            <!-- ── Top grid ── -->
            <div class="top-grid">

                <!-- Trend chart -->
                <div class="card trend-card">
                    <div class="card-header">
                        <div>
                            <div class="card-label">Posts Analytics</div>
                            <div class="chart-legend">
                                <span class="legend-dot" style="background:#6366f1;"></span> Published
                            </div>
                        </div>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Stats card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-label">Total Posts</div>
                        <div class="card-icon" style="background:rgba(99,102,241,0.08);">📊</div>
                    </div>
                    <div>
                        <span class="card-big-num">{{ stats.total }}</span>
                    </div>
                    <div class="status-dots">
                        <div class="dot-item"><span class="dot" style="background:#22c55e;"></span>Published <strong>{{ stats.published }}</strong></div>
                        <div class="dot-item"><span class="dot" style="background:#f59e0b;"></span>Pending <strong>{{ stats.pending }}</strong></div>
                        <div class="dot-item"><span class="dot" style="background:#6366f1;"></span>Draft <strong>{{ stats.draft }}</strong></div>
                        <div class="dot-item"><span class="dot" style="background:#ef4444;"></span>Archived <strong>{{ stats.archived }}</strong></div>
                    </div>
                    <div class="divider"></div>
                    <div class="card-label">Posts This Month</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.25rem;">
                        <span class="card-big-num" style="font-size:1.9rem;">{{ stats.thisMonth }}</span>
                    </div>
                    <div class="month-label">{{ monthLabel }}</div>
                </div>

                <!-- Top authors + donut -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-label">Top Authors</div>
                        <div class="card-icon" style="background:#fef3c7;">👥</div>
                    </div>
                    <div class="authors-list">
                        <div
                            v-for="(author, i) in stats.topAuthors.slice(0, 3)"
                            :key="author.id"
                            class="author-row"
                        >
                            <div class="author-avatar" :style="{ background: authorColors[i] }">
                                {{ initials(author.name) }}
                            </div>
                            <span class="author-name">{{ author.name }}</span>
                            <span class="author-pill">{{ author.count }} posts</span>
                        </div>
                        <p v-if="!stats.topAuthors.length" class="empty-text">No authors yet</p>
                    </div>

                    <div class="divider"></div>
                    <div class="card-label" style="margin-bottom:0.75rem;">Status Breakdown</div>
                    <div class="donut-wrap">
                        <div class="donut-canvas-wrap">
                            <canvas id="donutChart"></canvas>
                            <div class="donut-center">
                                <div class="donut-num">{{ stats.total }}</div>
                                <div class="donut-lbl">total</div>
                            </div>
                        </div>
                        <div class="donut-legend">
                            <div class="legend-row"><span class="legend-sq" style="background:#22c55e;"></span><span class="legend-lbl">Published</span><span class="legend-val">{{ stats.published }}</span></div>
                            <div class="legend-row"><span class="legend-sq" style="background:#f59e0b;"></span><span class="legend-lbl">Pending</span><span class="legend-val">{{ stats.pending }}</span></div>
                            <div class="legend-row"><span class="legend-sq" style="background:#6366f1;"></span><span class="legend-lbl">Draft</span><span class="legend-val">{{ stats.draft }}</span></div>
                            <div class="legend-row"><span class="legend-sq" style="background:#ef4444;"></span><span class="legend-lbl">Archived</span><span class="legend-val">{{ stats.archived }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Bottom grid ── -->
            <div class="bottom-grid">

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-label">Recent Activity</div>
                            <div style="font-size:0.85rem;font-weight:700;color:#111827;margin-top:0.1rem;">Latest Posts</div>
                        </div>
                        <Link href="/admin/posts" class="btn-ghost" style="font-size:0.75rem;padding:0.35rem 0.8rem;">View all ↗</Link>
                    </div>
                    <div class="activity-list">
                        <div
                            v-for="post in recentPosts"
                            :key="post.id"
                            class="activity-item"
                        >
                            <div class="activity-icon" :style="{ background: statusBg[post.status] ?? '#f3f4f6' }">
                                {{ statusIcon[post.status] ?? '📄' }}
                            </div>
                            <div class="activity-info">
                                <div class="activity-title">{{ post.title }}</div>
                            </div>
                            <span class="status-pill" :class="post.status">{{ post.status }}</span>
                        </div>
                        <p v-if="!recentPosts.length" class="empty-text">No recent posts</p>
                    </div>
                </div>

                <!-- Author bars -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-label">Author Performance</div>
                            <div style="font-size:0.85rem;font-weight:700;color:#111827;margin-top:0.1rem;">Post contributions</div>
                        </div>
                    </div>
                    <div class="bars-list">
                        <div
                            v-for="(author, i) in stats.topAuthors.slice(0, 4)"
                            :key="author.id"
                            class="bar-row"
                        >
                            <div class="bar-meta">
                                <span>{{ author.name }}</span>
                                <span class="bar-count">{{ author.count }} posts</span>
                            </div>
                            <div class="bar-track">
                                <div
                                    class="bar-fill"
                                    :style="{
                                        width: (author.count / maxCount(stats.topAuthors) * 100) + '%',
                                        background: ['linear-gradient(90deg,#818cf8,#6366f1)','linear-gradient(90deg,#34d399,#059669)','linear-gradient(90deg,#fbbf24,#f59e0b)','linear-gradient(90deg,#f87171,#ef4444)'][i]
                                    }"
                                ></div>
                            </div>
                        </div>
                        <p v-if="!stats.topAuthors.length" class="empty-text">No data yet</p>
                    </div>
                </div>
            </div>

            <!-- ── Export bar ── -->
            <div class="export-bar">
                <div>
                    <div style="font-weight:700;color:white;font-size:0.95rem;">Export your dashboard statistics</div>
                    <div style="color:rgba(255,255,255,0.55);font-size:0.8rem;margin-top:2px;">Download a full report of posts, authors and trends</div>
                </div>
                <button class="btn-export">⬇ Export statistics</button>
            </div>

        </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.dash-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.75rem 1.5rem 2.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.4rem;
}

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: flex-start; }
.topbar-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.035em; color: #111827; }
.topbar-sub   { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
.topbar-actions { display: flex; gap: 0.75rem; align-items: center; }

/* Buttons */
.btn-primary {
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0.5rem 1.1rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 0.4rem;
    transition: all 0.2s;
}
.btn-primary:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,0.35); }

.btn-ghost {
    background: transparent;
    color: #6b7280;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.5rem 1.1rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex; align-items: center;
    transition: all 0.2s;
}
.btn-ghost:hover { border-color: #6366f1; color: #6366f1; }

/* Cards */
.card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(229,231,235,0.8);
    box-shadow: 0 2px 16px rgba(99,102,241,0.06), 0 1px 4px rgba(0,0,0,0.04);
    padding: 1.4rem;
    animation: slideUp 0.4s ease both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}
.card-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    color: #6b7280;
}
.card-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.card-big-num {
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: -0.045em;
    color: #111827;
    line-height: 1;
}
.divider { height: 1px; background: #f3f4f6; margin: 1rem 0; }
.month-label { font-size: 0.72rem; color: #9ca3af; margin-top: 0.2rem; }
.empty-text  { font-size: 0.8rem; color: #9ca3af; padding: 0.5rem 0; }

/* Grids */
.top-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1.25rem;
}
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

/* Chart */
.chart-wrap { height: 185px; position: relative; }
.chart-legend {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.73rem; color: #6b7280; margin-top: 0.25rem;
}
.legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

/* Status dots */
.status-dots {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.45rem 0.75rem;
    margin-top: 0.9rem;
}
.dot-item {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.73rem; color: #6b7280;
}
.dot-item strong { color: #111827; margin-left: 2px; }
.dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* Authors */
.authors-list { display: flex; flex-direction: column; gap: 0.55rem; }
.author-row   { display: flex; align-items: center; gap: 0.55rem; }
.author-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.68rem; font-weight: 700; color: white;
    flex-shrink: 0;
}
.author-name { font-size: 0.8rem; font-weight: 600; color: #111827; flex: 1; }
.author-pill {
    background: #e0e0fb;
    color: #6366f1;
    font-size: 0.68rem; font-weight: 600;
    padding: 0.2rem 0.55rem;
    border-radius: 20px;
}

/* Donut */
.donut-wrap        { display: flex; align-items: center; gap: 1rem; }
.donut-canvas-wrap { position: relative; width: 110px; height: 110px; flex-shrink: 0; }
.donut-center {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.donut-num { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.04em; color: #111827; }
.donut-lbl { font-size: 0.62rem; color: #9ca3af; }
.donut-legend { display: flex; flex-direction: column; gap: 0.45rem; flex: 1; }
.legend-row   { display: flex; align-items: center; gap: 0.45rem; }
.legend-sq    { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0; }
.legend-lbl   { font-size: 0.73rem; color: #6b7280; flex: 1; }
.legend-val   { font-size: 0.73rem; font-weight: 700; color: #111827; }

/* Activity */
.activity-list { display: flex; flex-direction: column; gap: 0; }
.activity-item {
    display: flex; align-items: center;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f3f4f6;
    gap: 0.75rem;
}
.activity-item:last-child { border-bottom: none; padding-bottom: 0; }
.activity-item:first-child { padding-top: 0; }
.activity-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.activity-info  { flex: 1; min-width: 0; }
.activity-title {
    font-size: 0.82rem; font-weight: 600; color: #111827;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Status pills */
.status-pill {
    font-size: 0.67rem; font-weight: 600;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    text-transform: capitalize;
    flex-shrink: 0;
}
.status-pill.published { background: #dcfce7; color: #16a34a; }
.status-pill.pending   { background: #fef3c7; color: #d97706; }
.status-pill.draft     { background: rgba(99,102,241,0.08); color: #6366f1; border: 1px solid #e0e0fb; }
.status-pill.archived  { background: #f3f4f6; color: #9ca3af; }

/* Author bars */
.bars-list { display: flex; flex-direction: column; gap: 1rem; margin-top: 0.25rem; }
.bar-row   {}
.bar-meta  { display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem; color: #111827; }
.bar-count { color: #6366f1; }
.bar-track { height: 7px; background: #f3f4f6; border-radius: 10px; overflow: hidden; }
.bar-fill  { height: 100%; border-radius: 10px; transition: width 1s cubic-bezier(.4,0,.2,1); }

/* Export bar */
.export-bar {
    background: #111827;
    border-radius: 14px;
    padding: 1.1rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
}
.btn-export {
    background: white; color: #111827;
    border: none; border-radius: 10px;
    padding: 0.55rem 1.2rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; gap: 0.4rem;
    transition: all 0.2s;
}
.btn-export:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,0.25); }

/* Responsive */
@media (max-width: 1024px) {
    .top-grid    { grid-template-columns: 1fr 1fr; }
    .trend-card  { grid-column: 1 / -1; }
}
@media (max-width: 640px) {
    .top-grid    { grid-template-columns: 1fr; }
    .bottom-grid { grid-template-columns: 1fr; }
    .topbar      { flex-direction: column; gap: 1rem; }
}
</style>