<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
// @ts-ignore
import { ModalLink } from '@inertiaui/modal-vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    posts: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const categoryId = ref(props.filters.category_id || '');
const showDeleteModal = ref(false);
const postToDelete = ref(null);

watch([search, status, categoryId], () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (status.value !== 'all') params.status = status.value;
    if (categoryId.value) params.category_id = categoryId.value;

    router.get('/posts', params, {
        preserveState: true,
        replace: true,
    });
});

const confirmDelete = (post) => {
    postToDelete.value = post;
    showDeleteModal.value = true;
};

const deletePost = () => {
    router.delete(`/posts/${postToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            postToDelete.value = null;
        }
    });
};

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
    <AppLayout title="Posts">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Posts</h1>
                    <Link
                        href="/posts/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    >
                        Create Post
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search posts..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select
                                v-model="status"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select
                                v-model="categoryId"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- No Posts Message -->
                <div v-if="!posts?.data?.length" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No posts found.</p>
                    <Link
                        href="/posts/create"
                        class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    >
                        Create Your First Post
                    </Link>
                </div>

                <!-- Posts Table -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Post</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img
                                            v-if="post.featured_image"
                                            :src="post.featured_image"
                                            :alt="post.title"
                                            class="h-12 w-12 rounded-lg object-cover"
                                        />
                                        <div
                                            v-else
                                            class="h-12 w-12 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center"
                                        >
                                            <span class="text-gray-400 text-xl">📄</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ post.title }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                by {{ post.writer || post.user?.name || 'Unknown' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="post.category"
                                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        {{ post.category.name }}
                                    </span>
                                    <span v-else class="text-gray-400 text-sm">No category</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span :class="getStatusColor(post.status)" class="px-2 py-1 text-xs rounded-full">
                                            {{ post.status }}
                                        </span>
                                        <span :title="post.visibility">{{ getVisibilityIcon(post.visibility) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ post.created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- 👇 Only change here - added ModalLink for View -->
                                    <ModalLink
                                        :href="`/posts/${post.id}`"
                                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 mr-3"
                                    >
                                        View
                                    </ModalLink>
                                    <Link
                                        :href="`/posts/${post.id}/edit`"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(post)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="posts.links" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ posts.from || 0 }} to {{ posts.to || 0 }} of {{ posts.total || 0 }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in posts.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-1 rounded',
                                        link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div
                    v-if="showDeleteModal"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    @click.self="showDeleteModal = false"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete Post</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Are you sure you want to delete "{{ postToDelete?.title }}"? This action cannot be undone.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button
                                @click="showDeleteModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deletePost"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>