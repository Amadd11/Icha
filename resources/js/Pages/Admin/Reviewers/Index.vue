<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';

const props = defineProps({
    reviewers: Array,
    categories: Array,
});

const isModalOpen = ref(false);
const editingReviewer = ref(null);

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: reviewerToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

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
    openDeleteModal({
        item: reviewer,
        title: 'Remove Reviewer',
        message: `Are you sure you want to remove reviewer "${reviewer.name}"? They will lose reviewer privileges.`,
        url: route('admin.reviewers.destroy', reviewer.id),
    });
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
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Reviewers & Track Expertise</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage reviewer accounts and their assigned scientific tracks/expertise.</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                >
                    + Add New Reviewer
                </button>
            </div>

            <!-- Minimalist Reviewers Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Peer-Reviewers Directory</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.reviewers ? props.reviewers.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Reviewer Name & Email</th>
                                <th scope="col" class="px-5 py-3">Track Expertise</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.reviewers || props.reviewers.length === 0">
                                <td colspan="3" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No reviewers registered yet. Click "+ Add New Reviewer" to add one.
                                </td>
                            </tr>
                            <tr v-for="reviewer in props.reviewers" :key="reviewer.id" class="hover:bg-slate-50/50 transition">
                                <!-- Name & Email -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-900 text-xs">{{ reviewer.name }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ reviewer.email }}</p>
                                </td>

                                <!-- Tracks -->
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap gap-1.5">
                                        <template v-if="reviewer.categories && reviewer.categories.length > 0">
                                            <span
                                                v-for="cat in reviewer.categories"
                                                :key="cat.id"
                                                class="inline-block rounded-md px-2.5 py-0.5 text-[11px] font-bold bg-purple-50 text-purple-800 border border-purple-200"
                                            >
                                                {{ cat.name }}
                                            </span>
                                        </template>
                                        <span v-else class="text-xs text-slate-400 italic">No tracks assigned</span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(reviewer)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteReviewer(reviewer)"
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

            <!-- Reviewer Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-lg border border-slate-200 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">{{ editingReviewer ? 'Edit Reviewer' : 'Add New Reviewer' }}</h3>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" class="admin-input" placeholder="e.g. Dr. Jane Smith" required />
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Email Address <span class="text-red-500">*</span></label>
                            <input v-model="form.email" type="email" class="admin-input" placeholder="reviewer@university.ac.id" required />
                        </div>

                        <div>
                            <label class="mb-1.5 block font-bold text-slate-700">Assigned Scientific Tracks (Expertise)</label>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                                <label v-for="cat in props.categories" :key="cat.id" class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                    <input type="checkbox" :value="cat.id" v-model="form.category_ids" class="rounded border-slate-300 text-purple-700 focus:ring-purple-700" />
                                    <span>{{ cat.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="isModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 px-5 py-2 font-bold cursor-pointer transition">
                                {{ form.processing ? 'Saving...' : 'Save Reviewer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reusable Delete Confirmation Modal -->
            <DeleteConfirmModal
                :show="isDeleteModalOpen"
                :title="deleteTitle"
                :message="deleteMessage"
                :item-name="reviewerToDelete ? `${reviewerToDelete.name} (${reviewerToDelete.email})` : ''"
                confirm-text="Yes, Remove"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
