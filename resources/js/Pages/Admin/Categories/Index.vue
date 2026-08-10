<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    categories: Array,
    conferences: Array,
});

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this track category?')) {
        router.delete(route('admin.categories.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Scientific Tracks Management - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Scientific Tracks & Categories</h1>
                    <p class="text-xs text-slate-500">Manage topic areas, badges, and descriptions for conference submissions.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.categories.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-lg shadow-purple-500/20 transition cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        Add New Track
                    </Link>
                </div>
            </div>

            <!-- Categories Table (Sipanda Style) -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1">category</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-sm tracking-wide">Scientific Tracks List</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Total: {{ props.categories.length }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Badge</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Conference</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Track Title</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Description</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!props.categories || props.categories.length === 0">
                                <td colspan="5" class="px-5 py-10 text-center text-xs text-gray-400">No categories found. Click "Add New Track" to create one.</td>
                            </tr>
                            <tr v-for="c in props.categories" :key="c.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-purple-100 text-purple-700 font-mono text-[11px] font-bold">
                                        {{ c.badge || 'Track' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-bold text-xs text-slate-700">{{ c.conference?.title || 'Default' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                                        <span>{{ c.icon }}</span> {{ c.name }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 max-w-sm text-xs text-slate-500 truncate" :title="c.description">
                                    {{ c.description || '-' }}
                                </td>
                                <td class="px-5 py-4 text-right space-x-2">
                                    <Link
                                        :href="route('admin.categories.edit', c.id)"
                                        class="px-3 py-1.5 rounded-xl bg-purple-50 text-primary hover:bg-primary hover:text-white font-bold text-xs transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteCategory(c.id)"
                                        class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs transition-colors cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
