<script setup>
import { useModal } from '@inertiaui/modal-vue';

const modal = useModal();

const props = defineProps({
    post: Object,
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        archived: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getVisibilityIcon = (visibility) => {
    const icons = {
        public: '🌍',
        private: '🔒',
        unlisted: '👁️',
    };
    return icons[visibility] || '🌍';
};
</script>

<template>
    <Modal max-width="10xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate pr-4">
                    {{ post.title }}
                </h2>
                <button
                    @click="modal.close()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition flex-shrink-0"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                <!-- Featured Image -->
                <div v-if="post.featured_image" class="rounded-xl overflow-hidden">
                    <img
                        :src="post.featured_image"
                        :alt="post.title"
                        class="w-full h-64 object-cover"
                    />
                </div>

                <!-- Meta Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                        <span :class="getStatusColor(post.status)" class="px-2 py-1 text-xs rounded-full">
                            {{ post.status }}
                        </span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Visibility</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ getVisibilityIcon(post.visibility) }} {{ post.visibility }}
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Author</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ post.writer || post.user?.name || 'Unknown' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ post.created_at }}
                        </p>
                    </div>
                </div>

                <!-- Category & Tags -->
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        v-if="post.category"
                        class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium"
                    >
                        📁 {{ post.category.name }}
                    </span>
                    <span
                        v-for="tag in post.tags"
                        :key="tag.id"
                        class="px-3 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded-full text-sm"
                    >
                        # {{ tag.name }}
                    </span>
                </div>

                <!-- Content -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        Content
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-gray-800 dark:text-gray-200 text-sm leading-relaxed whitespace-pre-wrap">
                        {{ post.content }}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <button
                    @click="modal.close()"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                >
                    Close
                </button>
                <Link
                    :href="`/posts/${post.id}/edit`"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                >
                    Edit Post
                </Link>
            </div>
        </div>
    </Modal>
</template>