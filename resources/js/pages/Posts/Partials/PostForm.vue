<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        default: () => ({
            title: '',
            content: '',
            category_id: null,
            writer: '',
            visibility: 'public',
            status: 'draft',
            tags: [],
            featured_image: null,
        })
    },
    categories: Array,
    tags: Array,
    isEdit: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['submit', 'cancel']);

const form = useForm({
    title: props.post.title,
    content: props.post.content,
    category_id: props.post.category_id,
    writer: props.post.writer,
    visibility: props.post.visibility,
    status: props.post.status,
    tags: props.post.tags || [],
    featured_image: null,
});

const imagePreview = ref(props.post.featured_image || null);
const imageInput = ref(null);

const selectImage = () => {
    imageInput.value.click();
};

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];

    if (!file) return;

    form.featured_image = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.featured_image = null;
    imagePreview.value = null;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const toggleTag = (tagId) => {
    const index = form.tags.indexOf(tagId);
    if (index > -1) {
        form.tags.splice(index, 1);
    } else {
        form.tags.push(tagId);
    }
};

const submit = () => {
    emit('submit', form);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Title -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Title <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.title"
                type="text"
                placeholder="Enter post title..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
        </div>

        <!-- Featured Image -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Featured Image
            </label>
            
            <!-- Image Preview -->
            <div v-if="imagePreview" class="mb-4">
                <div class="relative inline-block">
                    <img :src="imagePreview" alt="Preview" class="h-48 w-auto rounded-lg shadow-md" />
                    <button
                        type="button"
                        @click="removeImage"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Upload Button -->
            <div v-else class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-indigo-500 transition cursor-pointer" @click="selectImage">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Click to upload or drag and drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500">PNG, JPG up to 2MB</p>
            </div>

            <input
                ref="imageInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="updatePhotoPreview"
            />
            <p v-if="form.errors.featured_image" class="mt-1 text-sm text-red-500">{{ form.errors.featured_image }}</p>
        </div>

        <!-- Category & Writer Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category
                </label>
                <select
                    v-model="form.category_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                    <option :value="null">No Category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-500">{{ form.errors.category_id }}</p>
            </div>

            <!-- Writer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Writer/Author
                </label>
                <input
                    v-model="form.writer"
                    type="text"
                    placeholder="Author name (optional)"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                />
                <p v-if="form.errors.writer" class="mt-1 text-sm text-red-500">{{ form.errors.writer }}</p>
            </div>
        </div>

        <!-- Tags -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tags
            </label>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tag in tags"
                    :key="tag.id"
                    type="button"
                    @click="toggleTag(tag.id)"
                    :class="[
                        'px-3 py-1 rounded-full text-sm transition',
                        form.tags.includes(tag.id)
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ tag.name }}
                </button>
            </div>
            <p v-if="form.errors.tags" class="mt-1 text-sm text-red-500">{{ form.errors.tags }}</p>
        </div>

        <!-- Content -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Content <span class="text-red-500">*</span>
            </label>
            <textarea
                v-model="form.content"
                rows="10"
                placeholder="Write your post content..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': form.errors.content }"
            ></textarea>
            <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">{{ form.errors.content }}</p>
        </div>

        <!-- Visibility & Status Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Visibility -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Visibility <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.visibility"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="public">🌍 Public</option>
                    <option value="private">🔒 Private</option>
                    <option value="unlisted">👁️ Unlisted</option>
                </select>
                <p v-if="form.errors.visibility" class="mt-1 text-sm text-red-500">{{ form.errors.visibility }}</p>
            </div>

       
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                type="button"
                @click="$emit('cancel')"
                class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                type="button"
                @click="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Saving...</span>
                <span v-else>{{ isEdit ? 'Update Post' : 'Create Post' }}</span>
            </button>
        </div>
    </div>
</template>