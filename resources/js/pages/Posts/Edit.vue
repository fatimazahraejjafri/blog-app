<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PostForm from './Partials/PostForm.vue';

const props = defineProps({
    post: Object,
    categories: Array,
    tags: Array,
});

const handleSubmit = (form) => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/posts/${props.post.id}`, {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/posts');
        },
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const handleCancel = () => {
    router.visit('/posts');
};
</script>

<template>
    <AppLayout title="Edit Post">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Post</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Update the details of your blog post.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <PostForm
                        :post="post"
                        :categories="categories"
                        :tags="tags"
                        :is-edit="true"
                        @submit="handleSubmit"
                        @cancel="handleCancel"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>