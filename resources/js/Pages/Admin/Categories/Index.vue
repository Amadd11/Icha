<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';

const props = defineProps({
    categories: Array,
    conferences: Array,
});

const isModalOpen = ref(false);
const editingCategory = ref(null);

const form = useForm({
    conference_id: '',
    name: '',
    badge: '',
    description: '',
});

function openCreateModal() {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.conferences?.[0]?.id || '';
    form.badge = `Track 0${(props.categories?.length || 0) + 1}`;
    isModalOpen.value = true;
}

function openEditModal(c) {
    editingCategory.value = c;
    form.clearErrors();
    form.conference_id = c.conference_id || (props.conferences?.[0]?.id || '');
    form.name = c.name || '';
    form.badge = c.badge || '';
    form.description = c.description || '';
    isModalOpen.value = true;
}

function submit() {
    if (editingCategory.value) {
        form.put(route('admin.categories.update', editingCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: categoryToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

function deleteCategory(c) {
    openDeleteModal({
        item: c,
        title: 'Delete Scientific Track',
        message: `Are you sure you want to delete track "${c.name}"? This category will be soft deleted.`,
        url: route('admin.categories.destroy', c.id),
    });
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
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add New Track
                    </button>
                </div>
            </div>

            <!-- Minimalist Categories Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
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
                                        <button
                                            @click="openEditModal(c)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteCategory(c)"
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

            <!-- Create / Edit Category Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
                <div class="w-full max-w-xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100 text-xs my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{ editingCategory ? 'Edit Scientific Track' : 'Add New Scientific Track' }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">Define research track scope and topic badge for author submissions.</p>
                        </div>
                        <button
                            @click="isModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Target Conference <span class="text-red-500">*</span></label>
                            <select v-model="form.conference_id" class="admin-input" required>
                                <option value="" disabled>Select conference...</option>
                                <option v-for="conf in props.conferences" :key="conf.id" :value="conf.id">{{ conf.title }}</option>
                            </select>
                            <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.conference_id }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block font-bold text-slate-700">Track Name / Title <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" placeholder="e.g. Healthcare Education & Human Capital" class="admin-input" required />
                                <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Badge Code</label>
                                <input v-model="form.badge" type="text" placeholder="e.g. Track 01" class="admin-input" />
                                <span v-if="form.errors.badge" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.badge }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Track Scope & Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Briefly explain the focus topics under this track..."
                                class="admin-input"
                            ></textarea>
                            <span v-if="form.errors.description" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.description }}</span>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                @click="isModalOpen = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold px-6 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ form.processing ? 'Saving...' : (editingCategory ? 'Update Track' : 'Save Track') }}
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
                :item-name="categoryToDelete?.name"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
