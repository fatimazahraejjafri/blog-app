<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Post {
    id: number;
    title: string;
    writer?: string;
    status: 'published' | 'pending' | 'draft' | 'archived';
    visibility: string;
    created_at: string;
    user: { name: string };
    category?: string;
    featured_image?: string;
}

interface PaginatedPosts {
    data: Post[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Stats {
    pending: number;
    published: number;
    draft: number;
    archived: number;
}

const props = defineProps<{
    posts: PaginatedPosts;
    filters: { search?: string; status?: string };
    stats: Stats;
}>();

const search = ref(props.filters.search ?? '');
const activeStatus = ref(props.filters.status ?? 'all');
const confirmDelete = ref<number | null>(null);

// Debounced search
let searchTimer: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 400);
});

function applyFilters() {
    router.get('/admin/posts', {
        search: search.value || undefined,
        status: activeStatus.value !== 'all' ? activeStatus.value : undefined,
    }, { preserveState: true, replace: true });
}

function setStatus(status: string) {
    activeStatus.value = status;
    applyFilters();
}

function approve(id: number) {
    router.patch(`/admin/posts/${id}/approve`);
}
function reject(id: number) {
    router.patch(`/admin/posts/${id}/reject`);
}
function setPending(id: number) {
    router.patch(`/admin/posts/${id}/pending`);
}
function destroy(id: number) {
    confirmDelete.value = null;
    router.delete(`/admin/posts/${id}`);
}

const statusTabs = [
    { key: 'all',       label: 'All',       count: props.stats.pending + props.stats.published + props.stats.draft + props.stats.archived },
    { key: 'pending',   label: 'Pending',   count: props.stats.pending },
    { key: 'published', label: 'Published', count: props.stats.published },
    { key: 'draft',     label: 'Draft',     count: props.stats.draft },
    { key: 'archived',  label: 'Archived',  count: props.stats.archived },
];

const statusMeta: Record<string, { label: string; cls: string }> = {
    published: { label: 'Published', cls: 'pill-green' },
    pending:   { label: 'Pending',   cls: 'pill-yellow' },
    draft:     { label: 'Draft',     cls: 'pill-indigo' },
    archived:  { label: 'Archived',  cls: 'pill-gray' },
};
</script>

<template>
        <div class="page-wrap">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <div class="breadcrumb">
                        <Link href="/admin/dashboard" class="bc-link">Dashboard</Link>
                        <span class="bc-sep">/</span>
                        <span class="bc-current">Posts</span>
                    </div>
                    <h1 class="page-title">Manage Posts</h1>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="stats-strip">
                <div class="stat-chip pending-chip">
                    <span class="chip-dot" style="background:#f59e0b;"></span>
                    <span class="chip-num">{{ stats.pending }}</span>
                    <span class="chip-lbl">Pending review</span>
                </div>
                <div class="stat-chip">
                    <span class="chip-dot" style="background:#22c55e;"></span>
                    <span class="chip-num">{{ stats.published }}</span>
                    <span class="chip-lbl">Published</span>
                </div>
                <div class="stat-chip">
                    <span class="chip-dot" style="background:#6366f1;"></span>
                    <span class="chip-num">{{ stats.draft }}</span>
                    <span class="chip-lbl">Drafts</span>
                </div>
                <div class="stat-chip">
                    <span class="chip-dot" style="background:#ef4444;"></span>
                    <span class="chip-num">{{ stats.archived }}</span>
                    <span class="chip-lbl">Archived</span>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <!-- Status tabs -->
                <div class="tabs">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.key"
                        class="tab-btn"
                        :class="{ active: activeStatus === tab.key }"
                        @click="setStatus(tab.key)"
                    >
                        {{ tab.label }}
                        <span class="tab-count">{{ tab.count }}</span>
                    </button>
                </div>

                <!-- Search -->
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search posts…"
                        class="search-input"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th style="width:40%;">Post</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="post in posts.data"
                            :key="post.id"
                            class="post-row"
                        >
                            <!-- Title -->
                            <td>
                                <div class="post-title-cell">
                                    <div
                                        class="post-thumb"
                                        :style="post.featured_image ? `background-image:url('${post.featured_image}')` : ''"
                                    >
                                        <span v-if="!post.featured_image">📄</span>
                                    </div>
                                    <div class="post-meta">
                                        <div class="post-title">{{ post.title }}</div>
                                        <div v-if="post.writer" class="post-writer">by {{ post.writer }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Author -->
                            <td>
                                <div class="author-cell">
                                    <div class="mini-avatar">{{ post.user.name.charAt(0).toUpperCase() }}</div>
                                    <span class="author-name">{{ post.user.name }}</span>
                                </div>
                            </td>

                            <!-- Category -->
                            <td>
                                <span class="category-tag" v-if="post.category">{{ post.category }}</span>
                                <span v-else class="no-cat">—</span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="pill" :class="statusMeta[post.status]?.cls">
                                    {{ statusMeta[post.status]?.label ?? post.status }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="date-cell">{{ post.created_at }}</td>

                            <!-- Actions -->
                            <td>
                                <div class="actions-cell">
                                    <!-- Pending actions -->
                                    <template v-if="post.status === 'pending'">
                                        <button class="action-btn approve-btn" @click="approve(post.id)" title="Approve">
                                            ✓ Approve
                                        </button>
                                        <button class="action-btn reject-btn" @click="reject(post.id)" title="Reject">
                                            ✕ Reject
                                        </button>
                                    </template>

                                    <!-- Published actions -->
                                    <template v-else-if="post.status === 'published'">
                                        <button class="action-btn pending-btn" @click="setPending(post.id)" title="Set to pending">
                                            ↺ Unpublish
                                        </button>
                                    </template>

                                    <!-- Archived actions -->
                                    <template v-else-if="post.status === 'archived'">
                                        <button class="action-btn approve-btn" @click="approve(post.id)" title="Re-publish">
                                            ↑ Publish
                                        </button>
                                    </template>

                                    <!-- Draft actions -->
                                    <template v-else-if="post.status === 'draft'">
                                        <button class="action-btn approve-btn" @click="approve(post.id)" title="Publish">
                                            ↑ Publish
                                        </button>
                                    </template>

                                    <!-- Delete -->
                                    <button
                                        class="action-btn delete-btn"
                                        @click="confirmDelete = post.id"
                                        title="Delete"
                                    >🗑</button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="posts.data.length === 0">
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon">📭</div>
                                <div class="empty-text">No posts found</div>
                                <div class="empty-sub">Try adjusting your search or filter</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="posts.last_page > 1">
                <span class="page-info">
                    Page {{ posts.current_page }} of {{ posts.last_page }}
                    <span class="page-total">({{ posts.total }} posts)</span>
                </span>
                <div class="page-btns">
                    <Link
                        v-if="posts.prev_page_url"
                        :href="posts.prev_page_url"
                        class="page-btn"
                    >← Prev</Link>
                    <span v-else class="page-btn disabled">← Prev</span>

                    <Link
                        v-if="posts.next_page_url"
                        :href="posts.next_page_url"
                        class="page-btn"
                    >Next →</Link>
                    <span v-else class="page-btn disabled">Next →</span>
                </div>
            </div>

        </div>

        <!-- Delete confirm modal -->
        <Teleport to="body">
            <div v-if="confirmDelete !== null" class="modal-overlay" @click.self="confirmDelete = null">
                <div class="modal">
                    <div class="modal-icon">🗑️</div>
                    <h2 class="modal-title">Delete this post?</h2>
                    <p class="modal-sub">This action cannot be undone. The post will be permanently removed.</p>
                    <div class="modal-actions">
                        <button class="btn-ghost" @click="confirmDelete = null">Cancel</button>
                        <button class="btn-danger" @click="destroy(confirmDelete!)">Yes, delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.page-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.75rem 1.5rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; }
.breadcrumb  { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; margin-bottom: 0.3rem; }
.bc-link     { color: #6366f1; text-decoration: none; font-weight: 500; }
.bc-link:hover { text-decoration: underline; }
.bc-sep      { color: #d1d5db; }
.bc-current  { color: #6b7280; }
.page-title  {
    font-size: 1.65rem; font-weight: 800;
    letter-spacing: -0.035em; color: #111827;
}

/* Stats strip */
.stats-strip {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.stat-chip {
    display: flex; align-items: center; gap: 0.5rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.55rem 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.pending-chip { border-color: #fde68a; background: #fffbeb; }
.chip-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.chip-num  { font-size: 1rem; font-weight: 800; color: #111827; letter-spacing: -0.03em; }
.chip-lbl  { font-size: 0.75rem; color: #6b7280; }

/* Toolbar */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Tabs */
.tabs { display: flex; gap: 0.25rem; background: #f3f4f6; padding: 0.25rem; border-radius: 10px; }
.tab-btn {
    display: flex; align-items: center; gap: 0.4rem;
    background: transparent; border: none;
    padding: 0.4rem 0.85rem;
    border-radius: 7px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.78rem; font-weight: 600;
    color: #6b7280; cursor: pointer;
    transition: all 0.18s;
}
.tab-btn:hover  { background: white; color: #111827; }
.tab-btn.active { background: white; color: #111827; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
.tab-count {
    background: #e5e7eb; color: #6b7280;
    font-size: 0.68rem; font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 20px;
    min-width: 20px; text-align: center;
}
.tab-btn.active .tab-count { background: #6366f1; color: white; }

/* Search */
.search-wrap {
    position: relative;
    display: flex; align-items: center;
}
.search-icon {
    position: absolute; left: 0.75rem;
    font-size: 0.85rem; pointer-events: none;
}
.search-input {
    padding: 0.5rem 1rem 0.5rem 2.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.82rem;
    color: #111827;
    background: white;
    width: 240px;
    outline: none;
    transition: border-color 0.2s;
}
.search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

/* Table card */
.table-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 16px rgba(99,102,241,0.05), 0 1px 4px rgba(0,0,0,0.03);
    overflow: hidden;
    animation: fadeUp 0.35s ease both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.posts-table { width: 100%; border-collapse: collapse; }
.posts-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.posts-table th {
    padding: 0.75rem 1.1rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
}
.posts-table td {
    padding: 0.9rem 1.1rem;
    vertical-align: middle;
    font-size: 0.83rem;
    color: #374151;
}
.post-row { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.post-row:last-child { border-bottom: none; }
.post-row:hover { background: #fafafa; }

/* Post title cell */
.post-title-cell { display: flex; align-items: center; gap: 0.75rem; }
.post-thumb {
    width: 40px; height: 40px;
    border-radius: 8px;
    background: #f3f4f6 center/cover no-repeat;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
    border: 1px solid #e5e7eb;
}
.post-title  { font-weight: 600; color: #111827; font-size: 0.85rem; line-height: 1.3; }
.post-writer { font-size: 0.72rem; color: #9ca3af; margin-top: 2px; }

/* Author cell */
.author-cell { display: flex; align-items: center; gap: 0.5rem; }
.mini-avatar {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, #818cf8, #6366f1);
    color: white; font-size: 0.65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.author-name { font-size: 0.8rem; font-weight: 500; color: #374151; }

/* Category */
.category-tag {
    background: #f3f4f6; color: #6b7280;
    font-size: 0.72rem; font-weight: 500;
    padding: 0.2rem 0.6rem; border-radius: 6px;
}
.no-cat { color: #d1d5db; }

/* Date */
.date-cell { color: #9ca3af; font-size: 0.78rem; white-space: nowrap; }

/* Status pills */
.pill {
    display: inline-flex; align-items: center;
    font-size: 0.68rem; font-weight: 600;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    white-space: nowrap;
}
.pill-green  { background: #dcfce7; color: #16a34a; }
.pill-yellow { background: #fef3c7; color: #d97706; }
.pill-indigo { background: rgba(99,102,241,0.1); color: #6366f1; border: 1px solid #e0e0fb; }
.pill-gray   { background: #f3f4f6; color: #9ca3af; }

/* Action buttons */
.actions-cell { display: flex; align-items: center; gap: 0.4rem; justify-content: flex-end; }

.action-btn {
    display: inline-flex; align-items: center; gap: 0.25rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.72rem; font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 7px; border: 1px solid transparent;
    cursor: pointer; transition: all 0.18s;
    white-space: nowrap;
}
.approve-btn { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; }
.approve-btn:hover { background: #16a34a; color: white; }

.reject-btn  { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
.reject-btn:hover { background: #dc2626; color: white; }

.pending-btn { background: #fef3c7; color: #d97706; border-color: #fde68a; }
.pending-btn:hover { background: #d97706; color: white; }

.delete-btn  { background: #f9fafb; color: #9ca3af; border-color: #e5e7eb; padding: 0.3rem 0.55rem; }
.delete-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

/* Empty state */
.empty-state { text-align: center; padding: 4rem 1rem !important; }
.empty-icon  { font-size: 2.5rem; margin-bottom: 0.75rem; }
.empty-text  { font-size: 1rem; font-weight: 700; color: #374151; }
.empty-sub   { font-size: 0.82rem; color: #9ca3af; margin-top: 0.25rem; }

/* Pagination */
.pagination {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0 0.25rem;
}
.page-info  { font-size: 0.8rem; color: #6b7280; }
.page-total { color: #9ca3af; }
.page-btns  { display: flex; gap: 0.5rem; }
.page-btn {
    padding: 0.45rem 1rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.8rem; font-weight: 600;
    color: #374151; text-decoration: none;
    cursor: pointer; transition: all 0.18s;
    display: inline-flex; align-items: center;
}
.page-btn:hover:not(.disabled) { border-color: #6366f1; color: #6366f1; }
.page-btn.disabled { color: #d1d5db; cursor: default; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    z-index: 50;
    animation: fadeIn 0.2s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal {
    background: white;
    border-radius: 18px;
    padding: 2rem;
    width: 360px;
    max-width: 90vw;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    animation: popIn 0.25s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
}
.modal-icon  { font-size: 2.5rem; margin-bottom: 0.75rem; }
.modal-title { font-size: 1.1rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem; }
.modal-sub   { font-size: 0.82rem; color: #6b7280; line-height: 1.5; margin-bottom: 1.5rem; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: center; }

.btn-ghost {
    background: transparent; color: #6b7280;
    border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 0.55rem 1.25rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.85rem; font-weight: 600;
    cursor: pointer; transition: all 0.18s;
}
.btn-ghost:hover { border-color: #6366f1; color: #6366f1; }

.btn-danger {
    background: #ef4444; color: white;
    border: none; border-radius: 10px;
    padding: 0.55rem 1.25rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.85rem; font-weight: 600;
    cursor: pointer; transition: all 0.18s;
}
.btn-danger:hover { background: #dc2626; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

/* Responsive */
@media (max-width: 768px) {
    .tabs { flex-wrap: wrap; }
    .search-input { width: 100%; }
    .toolbar { flex-direction: column; align-items: stretch; }
    .posts-table th:nth-child(3),
    .posts-table td:nth-child(3) { display: none; }
}
</style>