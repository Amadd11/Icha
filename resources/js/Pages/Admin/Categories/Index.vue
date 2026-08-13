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
    <Head title="Scientific Tracks - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Scientific Tracks & Categories</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage topic areas, badges, and descriptions for conference submissions.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.categories.create')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add New Track
                    </Link>
                </div>
            </div>

            <!-- Minimalist Categories Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Scientific Tracks List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.categories ? props.categories.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Badge</th>
                                <th scope="col" class="px-5 py-3">Track Name</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Description</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.categories || props.categories.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No categories found. Click "+ Add New Track" to create one.
                                </td>
                            </tr>
                            <tr v-for="c in props.categories" :key="c.id" class="hover:bg-slate-50/50 transition">
                                <!-- Badge -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[11px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-200">
                                        {{ c.badge || 'TRACK' }}
                                    </span>
                                </td>

                                <!-- Track Name -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-900 text-xs">
                                        {{ c.name }}
                                    </p>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                                    {{ c.conference?.title || 'Default Conference' }}
                                </td>

                                <!-- Description -->
                                <td class="px-5 py-3.5 max-w-sm text-xs text-slate-500 truncate" :title="c.description">
                                    {{ c.description || '-' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.categories.edit', c.id)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteCategory(c.id)"
                                            class="px-2.5 py-1 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-bold text-xs transition cursor-pointer"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
