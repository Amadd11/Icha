<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reviewers: Array,
    categories: Array,
});

const isModalOpen = ref(false);
const editingReviewer = ref(null);

const form = useForm({
    name: '',
    email: '',
    category_ids: [],
});

function openCreateModal() {
    editingReviewer.value = null;
    form.reset();
    isModalOpen.value = true;
}

function openEditModal(reviewer) {
    editingReviewer.value = reviewer;
    form.name = reviewer.name;
    form.email = reviewer.email;
    form.category_ids = reviewer.categories.map(c => c.id);
    isModalOpen.value = true;
}

function deleteReviewer(reviewer) {
    if (confirm(`Are you sure you want to remove ${reviewer.name} as a reviewer?`)) {
        router.delete(route('admin.reviewers.destroy', reviewer.id), {
            preserveScroll: true,
        });
    }
}

function submit() {
    if (editingReviewer.value) {
        form.put(route('admin.reviewers.update', editingReviewer.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post(route('admin.reviewers.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
}
</script>

<template>
    <Head title="Reviewer Management - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Reviewer Management</h1>
                    <p class="text-xs text-slate-500">Manage reviewer accounts and their assigned tracks/expertise.</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold text-white bg-primary rounded-xl hover:bg-primary-dark transition shadow-md w-full sm:w-auto"
                >
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Add Reviewer
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Name / Email</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Tracks (Expertise)</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!props.reviewers || props.reviewers.length === 0">
                                <td colspan="3" class="px-5 py-10 text-center text-xs text-gray-400">No reviewers found.</td>
                            </tr>
                            <tr v-for="reviewer in props.reviewers" :key="reviewer.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800 text-sm">{{ reviewer.name }}</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ reviewer.email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-if="reviewer.categories.length === 0" class="text-xs text-slate-400 italic">No tracks assigned</span>
                                        <span
                                            v-for="cat in reviewer.categories"
                                            :key="cat.id"
                                            class="inline-flex items-center px-2 py-0.5 rounded border border-purple-200 bg-purple-50 text-purple-700 text-[10px] font-bold"
                                        >
                                            {{ cat.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(reviewer)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="deleteReviewer(reviewer)" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-900 text-sm">{{ editingReviewer ? 'Edit Reviewer' : 'Add New Reviewer' }}</h3>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                            <input v-model="form.name" type="text" class="admin-input" required placeholder="e.g. Dr. John Doe" />
                            <div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                            <input v-model="form.email" type="email" class="admin-input" required placeholder="john@example.com" />
                            <div v-if="form.errors.email" class="text-rose-500 text-[10px] mt-1">{{ form.errors.email }}</div>
                            <p v-if="!editingReviewer" class="text-[10px] text-slate-500 mt-1">Default password for new reviewer is: <strong>password</strong></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Expertise Tracks (Categories)</label>
                            <div class="bg-slate-50 rounded-xl border border-slate-200 p-3 max-h-48 overflow-y-auto space-y-2">
                                <label v-for="cat in categories" :key="cat.id" class="flex items-start gap-2 cursor-pointer p-1 hover:bg-slate-100 rounded">
                                    <input type="checkbox" v-model="form.category_ids" :value="cat.id" class="mt-0.5 rounded text-primary focus:ring-primary border-slate-300" />
                                    <span class="text-xs text-slate-700 font-semibold">{{ cat.name }}</span>
                                </label>
                            </div>
                            <div v-if="form.errors.category_ids" class="text-rose-500 text-[10px] mt-1">{{ form.errors.category_ids }}</div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark transition shadow-md disabled:opacity-50">
                                {{ form.processing ? 'Saving...' : 'Save Reviewer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
