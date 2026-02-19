<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Post {
    id: number;
    title: string;
    writer?: string;
    status: 'published' | 'pending' | 'draft' | 'archived';
    visibility: string;
    content: string;
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

// Post detail modal
const viewingPost = ref<Post | null>(null);
function openPost(post: Post) { viewingPost.value = post; }
function closePost() { viewingPost.value = null; }

let searchTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
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
    if (viewingPost.value?.id === id) closePost();
}
function reject(id: number) {
    router.patch(`/admin/posts/${id}/reject`);
    if (viewingPost.value?.id === id) closePost();
}
function setPending(id: number) {
    router.patch(`/admin/posts/${id}/pending`);
    if (viewingPost.value?.id === id) closePost();
}
function destroy(id: number) {
    confirmDelete.value = null;
    router.delete(`/admin/posts/${id}`);
    if (viewingPost.value?.id === id) closePost();
}

const statusTabs = [
    { key: 'all',       label: 'All',       count: props.stats.pending + props.stats.published + props.stats.draft + props.stats.archived },
    { key: 'pending',   label: 'Pending',   count: props.stats.pending },
    { key: 'published', label: 'Published', count: props.stats.published },
    { key: 'draft',     label: 'Draft',     count: props.stats.draft },
    { key: 'archived',  label: 'Archived',  count: props.stats.archived },
];

const statusMeta: Record<string, { label: string; cls: string }> = {
    published: { label: 'Published', cls: 'bg-green-100 text-green-700' },
    pending:   { label: 'Pending',   cls: 'bg-yellow-100 text-yellow-700' },
    draft:     { label: 'Draft',     cls: 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200' },
    archived:  { label: 'Archived',  cls: 'bg-gray-100 text-gray-500' },
};
</script>

<template>
    <AppLayout title="Manage Posts">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-5">

            <!-- Header -->
            <div>
                <div class="flex items-center gap-1.5 text-xs text-muted-foreground mb-1">
                    <Link href="/admin/dashboard" class="text-indigo-500 hover:underline font-medium">Dashboard</Link>
                    <span>/</span>
                    <span>Posts</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-foreground">Manage Posts</h1>
            </div>

            <!-- Stats strip -->
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 shrink-0"></span>
                    <span class="text-base font-bold text-foreground">{{ stats.pending }}</span>
                    <span class="text-xs text-muted-foreground">Pending review</span>
                </div>
                <div class="flex items-center gap-2 bg-card border border-border rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                    <span class="text-base font-bold text-foreground">{{ stats.published }}</span>
                    <span class="text-xs text-muted-foreground">Published</span>
                </div>
                <div class="flex items-center gap-2 bg-card border border-border rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                    <span class="text-base font-bold text-foreground">{{ stats.draft }}</span>
                    <span class="text-xs text-muted-foreground">Drafts</span>
                </div>
                <div class="flex items-center gap-2 bg-card border border-border rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                    <span class="text-base font-bold text-foreground">{{ stats.archived }}</span>
                    <span class="text-xs text-muted-foreground">Archived</span>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-1 bg-muted p-1 rounded-xl">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.key"
                        @click="setStatus(tab.key)"
                        :class="[
                            'flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            activeStatus === tab.key
                                ? 'bg-card text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground hover:bg-card/60'
                        ]"
                    >
                        {{ tab.label }}
                        <span :class="[
                            'text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center',
                            activeStatus === tab.key ? 'bg-indigo-500 text-white' : 'bg-border text-muted-foreground'
                        ]">{{ tab.count }}</span>
                    </button>
                </div>

                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none">🔍</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search posts…"
                        class="pl-9 pr-4 py-2 border border-border rounded-xl text-sm bg-card text-foreground w-60 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-muted border-b border-border">
                            <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground w-[35%]">Post</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Author</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground hidden md:table-cell">Category</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Status</th>
                            <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground hidden lg:table-cell">Date</th>
                            <th class="text-right px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="post in posts.data"
                            :key="post.id"
                            class="border-b border-border last:border-0 hover:bg-muted/40 transition-colors"
                        >
                            <!-- Title — clicking opens detail modal -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-muted border border-border flex items-center justify-center text-lg shrink-0 bg-cover bg-center"
                                        :style="post.featured_image ? `background-image:url('${post.featured_image}')` : ''"
                                    >
                                        <span v-if="!post.featured_image">📄</span>
                                    </div>
                                    <div>
                                        <button
                                            @click="openPost(post)"
                                            class="text-sm font-semibold text-foreground leading-snug hover:text-indigo-500 transition-colors text-left"
                                        >{{ post.title }}</button>
                                        <div v-if="post.writer" class="text-xs text-muted-foreground mt-0.5">by {{ post.writer }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Author -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-[11px] font-bold flex items-center justify-center shrink-0">
                                        {{ post.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm font-medium text-foreground">{{ post.user.name }}</span>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span v-if="post.category" class="text-xs font-medium bg-muted text-muted-foreground px-2.5 py-1 rounded-md">{{ post.category }}</span>
                                <span v-else class="text-muted-foreground/30">—</span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                <span :class="['inline-flex items-center text-[11px] font-semibold px-2.5 py-1 rounded-full', statusMeta[post.status]?.cls]">
                                    {{ statusMeta[post.status]?.label ?? post.status }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-5 py-3.5 text-xs text-muted-foreground whitespace-nowrap hidden lg:table-cell">
                                {{ post.created_at }}
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <!-- View button -->
                                    <button
                                        @click="openPost(post)"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-muted text-muted-foreground border border-border hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all"
                                    >
                                        👁 View
                                    </button>

                                    <template v-if="post.status === 'pending'">
                                        <button @click="approve(post.id)" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 border border-green-200 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all">✓ Approve</button>
                                        <button @click="reject(post.id)"  class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all">✕ Reject</button>
                                    </template>
                                    <template v-else-if="post.status === 'published'">
                                        <button @click="setPending(post.id)" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-500 hover:text-white hover:border-yellow-500 transition-all">↺ Unpublish</button>
                                    </template>
                                    <template v-else>
                                        <button @click="approve(post.id)" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 border border-green-200 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all">↑ Publish</button>
                                    </template>

                                    <button @click="confirmDelete = post.id" class="inline-flex items-center text-[11px] px-2 py-1.5 rounded-lg bg-muted text-muted-foreground border border-border hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all">🗑</button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="posts.data.length === 0">
                            <td colspan="6" class="text-center py-16">
                                <div class="text-4xl mb-3">📭</div>
                                <div class="text-sm font-bold text-foreground">No posts found</div>
                                <div class="text-xs text-muted-foreground mt-1">Try adjusting your search or filter</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="posts.last_page > 1" class="flex items-center justify-between px-1">
                <span class="text-sm text-muted-foreground">
                    Page {{ posts.current_page }} of {{ posts.last_page }}
                    <span class="text-muted-foreground/40 ml-1">({{ posts.total }} posts)</span>
                </span>
                <div class="flex gap-2">
                    <Link v-if="posts.prev_page_url" :href="posts.prev_page_url" class="px-4 py-1.5 text-sm font-semibold border border-border rounded-lg bg-card text-foreground hover:border-indigo-400 hover:text-indigo-500 transition-all">← Prev</Link>
                    <span v-else class="px-4 py-1.5 text-sm font-semibold border border-border rounded-lg text-muted-foreground/40 cursor-default">← Prev</span>
                    <Link v-if="posts.next_page_url" :href="posts.next_page_url" class="px-4 py-1.5 text-sm font-semibold border border-border rounded-lg bg-card text-foreground hover:border-indigo-400 hover:text-indigo-500 transition-all">Next →</Link>
                    <span v-else class="px-4 py-1.5 text-sm font-semibold border border-border rounded-lg text-muted-foreground/40 cursor-default">Next →</span>
                </div>
            </div>

        </div>

        <!-- ── Post Detail Modal ── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="viewingPost"
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closePost"
                >
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                        appear
                    >
                        <div v-if="viewingPost" class="bg-card border border-border rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">

                            <!-- Featured image -->
                            <div
                                v-if="viewingPost.featured_image"
                                class="h-48 bg-cover bg-center shrink-0"
                                :style="`background-image: url('${viewingPost.featured_image}')`"
                            ></div>
                            <div v-else class="h-28 bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center shrink-0">
                                <span class="text-5xl">📄</span>
                            </div>

                            <!-- Content -->
                            <div class="flex flex-col gap-4 p-6 overflow-y-auto">

                                <!-- Title + close -->
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="text-lg font-extrabold text-foreground leading-snug">{{ viewingPost.title }}</h2>
                                    <button
                                        @click="closePost"
                                        class="shrink-0 w-8 h-8 rounded-lg bg-muted hover:bg-border flex items-center justify-center text-muted-foreground transition-colors text-sm"
                                    >✕</button>
                                </div>

                                <!-- Status badge -->
                                <div>
                                    <span :class="['inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full', statusMeta[viewingPost.status]?.cls]">
                                        {{ statusMeta[viewingPost.status]?.label ?? viewingPost.status }}
                                    </span>
                                </div>

                                <!-- Meta grid -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-muted rounded-xl p-3">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Author</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="w-6 h-6 rounded-full bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                                {{ viewingPost.user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="text-sm font-semibold text-foreground">{{ viewingPost.user.name }}</span>
                                        </div>
                                    </div>

                                    <div class="bg-muted rounded-xl p-3">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Published</div>
                                        <div class="text-sm font-semibold text-foreground mt-1">{{ viewingPost.created_at }}</div>
                                    </div>

                                    <div class="bg-muted rounded-xl p-3">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Category</div>
                                        <div class="text-sm font-semibold text-foreground mt-1">{{ viewingPost.category ?? '—' }}</div>
                                    </div>

                                    <div class="bg-muted rounded-xl p-3">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Visibility</div>
                                        <div class="text-sm font-semibold text-foreground capitalize mt-1">{{ viewingPost.visibility ?? '—' }}</div>
                                    </div>

                                    <div v-if="viewingPost.writer" class="bg-muted rounded-xl p-3 col-span-2">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Writer / Byline</div>
                                        <div class="text-sm font-semibold text-foreground mt-1">{{ viewingPost.writer }}</div>
                                    </div>
                                    <div v-if="viewingPost.content" class="bg-muted rounded-xl p-3 col-span-2">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">content / Byline</div>
                                        <div class="text-sm font-semibold text-foreground mt-1">{{ viewingPost.content }}</div>
                                    </div>
                                </div>

                                <!-- Action buttons inside modal -->
                                <div class="flex flex-wrap gap-2 pt-1 border-t border-border">
                                    <template v-if="viewingPost.status === 'pending'">
                                        <button
                                            @click="approve(viewingPost.id)"
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl bg-green-500 text-white hover:bg-green-600 transition-all"
                                        >✓ Approve & Publish</button>
                                        <button
                                            @click="reject(viewingPost.id)"
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all"
                                        >✕ Reject</button>
                                    </template>
                                    <template v-else-if="viewingPost.status === 'published'">
                                        <button
                                            @click="setPending(viewingPost.id)"
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-500 hover:text-white transition-all"
                                        >↺ Unpublish</button>
                                    </template>
                                    <template v-else>
                                        <button
                                            @click="approve(viewingPost.id)"
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl bg-green-500 text-white hover:bg-green-600 transition-all"
                                        >↑ Publish</button>
                                    </template>
                                    <button
                                        @click="confirmDelete = viewingPost.id; closePost()"
                                        class="inline-flex items-center justify-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-xl bg-muted text-muted-foreground border border-border hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                                    >🗑 Delete</button>
                                </div>

                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Delete Confirm Modal ── -->
        <Teleport to="body">
            <div
                v-if="confirmDelete !== null"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[60]"
                @click.self="confirmDelete = null"
            >
                <div class="bg-card border border-border rounded-2xl p-8 w-[360px] max-w-[90vw] text-center shadow-2xl">
                    <div class="text-4xl mb-3">🗑️</div>
                    <h2 class="text-lg font-extrabold text-foreground mb-1">Delete this post?</h2>
                    <p class="text-sm text-muted-foreground mb-6 leading-relaxed">This action cannot be undone. The post will be permanently removed.</p>
                    <div class="flex gap-3 justify-center">
                        <button @click="confirmDelete = null" class="px-5 py-2 text-sm font-semibold border border-border rounded-xl text-muted-foreground hover:border-indigo-400 hover:text-indigo-500 transition-all">Cancel</button>
                        <button @click="destroy(confirmDelete!)" class="px-5 py-2 text-sm font-semibold bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all shadow-sm">Yes, delete</button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>