<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PostForm from './Partials/PostForm.vue';

const props = defineProps({
    categories: Array,
    tags: Array,
});

const handleSubmit = (form) => {
    form.post('/posts', {
        onSuccess: () => {
            router.visit('/posts');
        },
    });
};

const handleCancel = () => {
    router.visit('/posts');
};
</script>

<template>
    <AppLayout title="Create Post">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Post</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Fill in the details below to create a new blog post.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <PostForm
                        :categories="categories"
                        :tags="tags"
                        @submit="handleSubmit"
                        @cancel="handleCancel"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>