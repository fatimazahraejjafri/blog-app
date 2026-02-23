<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    posts: Array,
});

const expandedPosts = ref({});

const toggleExpand = (id) => {
    expandedPosts.value[id] = !expandedPosts.value[id];
};

const timeAgo = (dateStr) => {
    return dateStr; 
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const avatarColor = (name) => {
    const colors = [
        'bg-violet-500', 'bg-blue-500', 'bg-emerald-500',
        'bg-rose-500', 'bg-amber-500', 'bg-indigo-500', 'bg-pink-500'
    ];
    if (!name) return colors[0];
    const idx = name.charCodeAt(0) % colors.length;
    return colors[idx];
};
</script>

<template>
    <AppLayout title="Feed">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-8">
            <div class="max-w-2xl mx-auto px-4">

                <!-- Page Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
<h1 class="text-2xl font-bold ...">{{ $t('app', 'Feed') }}</h1>
<p class="text-sm ...">{{ $t('app', 'latest posts') }}</p>
                    </div>
                    <Link
                        href="/posts/create"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-full transition shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
{{ $t('app', 'new post') }}                    </Link>
                    <div>
                        <!-- Replace the static select in the header -->
<div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-full p-1">
    <Link
        v-for="lang in $page.props.languages"
        :key="lang.code"
        :href="`/locale/${lang.code}`"
        :class="$page.props.locale === lang.code
            ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-white shadow font-semibold'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
        class="px-3 py-1 rounded-full text-sm transition-all duration-200"
    >
        {{ lang.name }}
    </Link>
</div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!posts?.length" class="text-center py-24">
                    <div class="text-5xl mb-4">📭</div>
                    <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No posts yet</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Be the first to share something!</p>
                    <Link href="/posts/create" class="mt-6 inline-block px-5 py-2.5 bg-indigo-600 text-white rounded-full text-sm font-medium hover:bg-indigo-700 transition">
                        Create a Post
                    </Link>
                </div>

                <!-- Feed -->
                <div v-else class="space-y-5">
                    <article
                        v-for="post in posts"
                        :key="post.id"
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-md transition-shadow duration-200"
                    >
                        <!-- Post Header -->
                        <div class="flex items-center gap-3 px-5 pt-4 pb-3">
                            <!-- Avatar -->
                            <div :class="[avatarColor(post.writer || post.user?.name), 'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0']">
                                {{ getInitials(post.writer || post.user?.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">
                                    {{ post.writer || post.user?.name || 'Unknown' }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ timeAgo(post.published_at || post.created_at) }}</span>
                                    <span v-if="post.category" class="text-xs text-indigo-500 dark:text-indigo-400 font-medium">· {{ post.category.name }}</span>
                                </div>
                            </div>
                            <!-- Visibility badge -->
                            <span class="text-xs px-2 py-1 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 font-medium border border-green-100 dark:border-green-800">
                                {{ $t('app', 'published') }}                            </span>
                        </div>

                        <!-- Featured Image -->
                        <div v-if="post.featured_image" class="mx-5 rounded-xl overflow-hidden mb-3">
                            <img
                                :src="post.featured_image"
                                :alt="post.title"
                                class="w-full h-64 object-cover"
                            />
                        </div>

                        <!-- Post Body -->
                        <div class="px-5 pb-3">
                            <h2 class="font-bold text-gray-900 dark:text-white text-base mb-2 leading-snug">
                                {{ post.title }}
                            </h2>
                            <div class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                <p :class="expandedPosts[post.id] ? '' : 'line-clamp-3'">
                                    {{ post.content }}
                                </p>
                                <button
                                    v-if="post.content?.length > 200"
                                    @click="toggleExpand(post.id)"
                                    class="text-indigo-500 hover:text-indigo-600 text-sm font-medium mt-1 focus:outline-none"
                                >
                                {{ expandedPosts[post.id] ? $t('app', 'show_less') : $t('app', 'Read more') }}                                </button>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="post.tags?.length" class="px-5 pb-3 flex flex-wrap gap-1.5">
                            <span
                                v-for="tag in post.tags"
                                :key="tag.id"
                                class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-full"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>

                        <!-- Post Footer / Actions -->
                        <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between">
                    
                            <span class="text-xs text-gray-300 dark:text-gray-600">{{ post.created_at }}</span>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </AppLayout>
</template>