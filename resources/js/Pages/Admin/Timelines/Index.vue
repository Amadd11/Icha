<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';

const props = defineProps({
    timelines: Array,
    conferences: Array,
});

const isModalOpen = ref(false);
const editingTimeline = ref(null);

const form = useForm({
    conference_id: '',
    title: '',
    period: '',
    date: '',
    description: '',
    is_completed: false,
    order: 0,
});

function openCreateModal() {
    editingTimeline.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.conferences?.[0]?.id || '';
    form.is_completed = false;
    form.order = (props.timelines?.length || 0) + 1;
    isModalOpen.value = true;
}

function openEditModal(t) {
    editingTimeline.value = t;
    form.clearErrors();
    form.conference_id = t.conference_id || (props.conferences?.[0]?.id || '');
    form.title = t.title || '';
    form.period = t.period || '';
    form.date = t.date || '';
    form.description = t.description || '';
    form.is_completed = Boolean(t.is_completed);
    form.order = t.order ?? 0;
    isModalOpen.value = true;
}

function submit() {
    if (editingTimeline.value) {
        form.put(route('admin.timelines.update', editingTimeline.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.timelines.store'), {
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
    itemToDelete: timelineToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

function deleteTimeline(t) {
    openDeleteModal({
        item: t,
        title: 'Delete Timeline Item',
        message: `Are you sure you want to delete "${t.title}" from timeline?`,
        url: route('admin.timelines.destroy', t.id),
    });
}
</script>

<template>
    <Head title="Timeline & Schedule - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Timeline & Important Dates</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage key milestone dates and schedules for conference events.</p>
                </div>

                <div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add Timeline Item
                    </button>
                </div>
            </div>

            <!-- Minimalist Timelines Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Conference Schedule</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.timelines ? props.timelines.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Order</th>
                                <th scope="col" class="px-5 py-3">Event Title</th>
                                <th scope="col" class="px-5 py-3">Period / Date</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.timelines || props.timelines.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No timeline items found. Click "+ Add Timeline Item" to create one.
                                </td>
                            </tr>
                            <tr v-for="t in props.timelines" :key="t.id" class="hover:bg-slate-50/50 transition">
                                <!-- Order -->
                                <td class="px-5 py-3.5 font-bold text-xs text-slate-400">
                                    #{{ t.order }}
                                </td>

                                <!-- Title & Description -->
                                <td class="px-5 py-3.5 max-w-xs">
                                    <p class="font-bold text-slate-900 text-xs">{{ t.title }}</p>
                                    <p v-if="t.description" class="text-[11px] text-slate-400 mt-0.5 truncate">{{ t.description }}</p>
                                </td>

                                <!-- Period / Date -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-xs text-purple-900">{{ t.period || t.date || 'TBA' }}</span>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                                    {{ t.conference?.title || 'Default Conference' }}
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase border',
                                        t.is_completed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        {{ t.is_completed ? 'Completed' : 'Upcoming' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(t)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteTimeline(t)"
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

            <!-- Create / Edit Timeline Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
                <div class="w-full max-w-xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100 text-xs my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{ editingTimeline ? 'Edit Timeline Milestone' : 'Add Timeline Milestone' }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">Configure schedule periods and milestone descriptions for the conference.</p>
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
                                <option v-for="c in props.conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                            <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.conference_id }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Milestone / Event Title <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" placeholder="e.g. Call for Abstracts & Registration Opens" class="admin-input" required />
                            <span v-if="form.errors.title" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.title }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Period Text (Display)</label>
                                <input v-model="form.period" type="text" placeholder="e.g. July - August 2026" class="admin-input" />
                                <span v-if="form.errors.period" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.period }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Display Order</label>
                                <input v-model="form.order" type="number" min="0" class="admin-input" />
                                <span v-if="form.errors.order" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.order }}</span>
                            </div>
                        </div>

                        <div class="flex items-center pt-2">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                                <input type="checkbox" v-model="form.is_completed" class="rounded border-slate-300 text-purple-700 focus:ring-purple-700" />
                                <span>Mark as Completed Milestone</span>
                            </label>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Milestone Points / Details (One per line)</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="24-25 Jul: PIPMARSI Meeting&#10;5 Aug: TOR & Branding&#10;8 Aug: Committee Formation"
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
                                {{ form.processing ? 'Saving...' : (editingTimeline ? 'Update Milestone' : 'Save Milestone') }}
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
                :item-name="timelineToDelete?.title"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
