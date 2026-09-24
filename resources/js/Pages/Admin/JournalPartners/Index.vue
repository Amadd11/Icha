<script setup>
import { ref, watch, onUnmounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';

const props = defineProps({
    journalPartners: Array,
    conferences: Array,
    selectedConferenceId: Number,
});

const isModalOpen = ref(false);
const editingJournal = ref(null);
const viewingJournal = ref(null);

watch([isModalOpen, viewingJournal], ([modal, view]) => {
    if (typeof document !== 'undefined') {
        if (modal || view) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.body.classList.remove('overflow-hidden');
    }
});

const {
    isOpen: isDeleteModalOpen,
    itemToDelete: journalToDelete,
    modalTitle: deleteModalTitle,
    modalMessage: deleteModalMessage,
    open: openDeleteModal,
    close: closeDeleteModal,
    confirm: confirmDelete,
    isDeleting,
} = useDeleteConfirm();

function promptDelete(j) {
    openDeleteModal({
        item: j,
        title: 'Delete Journal Partner',
        message: `Are you sure you want to delete journal partner "${j.journal_name}"?`,
        url: route('admin.journal-partners.destroy', j.id),
    });
}

const form = useForm({
    conference_id: '',
    journal_name: '',
    university: '',
    website: '',
    loa_information: 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi',
    submission_requirements: '',
});

function openCreateModal() {
    editingJournal.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.selectedConferenceId || props.conferences?.[0]?.id || '';
    form.loa_information = 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi';
    isModalOpen.value = true;
}

function openEditModal(j) {
    editingJournal.value = j;
    form.clearErrors();
    form.conference_id = j.conference_id || (props.conferences?.[0]?.id || '');
    form.journal_name = j.journal_name || '';
    form.university = j.university || '';
    form.website = j.website || '';
    form.loa_information = j.loa_information || '';
    form.submission_requirements = j.submission_requirements || '';
    isModalOpen.value = true;
}

function submit() {
    if (editingJournal.value) {
        form.put(route('admin.journal-partners.update', editingJournal.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.journal-partners.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

function viewDetails(j) {
    viewingJournal.value = j;
}

function closeDetails() {
    viewingJournal.value = null;
}
</script>

<template>
    <Head title="Journal Partners - Admin" />
    <AdminLayout>
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Official Journal Partners</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage scientific journal publication partnerships and manuscript submission guidelines.</p>
            </div>
            <button
                @click="openCreateModal"
                class="px-4 py-2.5 rounded-xl bg-primary hover:bg-purple-900 text-white font-bold text-xs shadow-xs transition self-start sm:self-auto cursor-pointer"
            >
                + Add New Journal Partner
            </button>
        </div>

        <!-- Minimalist Table Card -->
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Partner Journals List</h3>
                <span class="text-xs text-slate-400 font-semibold">Total: {{ props.journalPartners ? props.journalPartners.length : 0 }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 min-w-[200px]">Journal & University</th>
                            <th scope="col" class="px-5 py-3.5 min-w-[200px]">LoA Information</th>
                            <th scope="col" class="px-5 py-3.5 min-w-[90px]">Website</th>
                            <th scope="col" class="px-5 py-3.5 min-w-[240px]">Submission Requirements</th>
                            <th scope="col" class="px-5 py-3.5 text-right min-w-[170px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!props.journalPartners || props.journalPartners.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                No journal partners added yet. Click "+ Add New Journal Partner" to add one.
                            </td>
                        </tr>
                        <tr v-for="j in props.journalPartners" :key="j.id" class="hover:bg-slate-50/60 transition">
                            <!-- Journal & University (Simple clean text, no icon, no bg) -->
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold text-slate-900 text-sm leading-snug">{{ j.journal_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ j.university || '-' }}</div>
                            </td>

                            <!-- LoA Information (Simple clean text, no badge, no icon) -->
                            <td class="px-5 py-4 text-xs text-slate-600 leading-relaxed align-top">
                                {{ j.loa_information || '-' }}
                            </td>

                            <!-- Website (Simple clean link, no badge, no icon) -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <a
                                    v-if="j.website"
                                    :href="j.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:underline font-semibold text-xs"
                                >
                                    Visit
                                </a>
                                <span v-else class="text-slate-400">-</span>
                            </td>

                            <!-- Submission Requirements Preview -->
                            <td class="px-5 py-4 text-xs text-slate-600 align-top">
                                <div class="line-clamp-2 leading-relaxed">
                                    {{ j.submission_requirements || '-' }}
                                </div>
                            </td>

                            <!-- Actions (Simple clean buttons) -->
                            <td class="px-5 py-4 text-right whitespace-nowrap align-top">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        @click="viewDetails(j)"
                                        class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium text-xs transition cursor-pointer"
                                        title="View submission guidelines"
                                    >
                                        Guidelines
                                    </button>
                                    <button
                                        type="button"
                                        @click="openEditModal(j)"
                                        class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-semibold text-xs transition cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        @click="promptDelete(j)"
                                        class="px-2.5 py-1 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold text-xs transition cursor-pointer"
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

        <!-- 📝 Create / Edit Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 overflow-y-auto"
        >
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 space-y-4 animate-fade-in my-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            {{ editingJournal ? 'Edit Journal Partner' : 'Add New Journal Partner' }}
                        </h2>
                        <p class="text-slate-500 text-[11px] mt-0.5">Fill in journal details and manuscript submission guidelines.</p>
                    </div>
                    <button
                        @click="isModalOpen = false"
                        class="text-slate-400 hover:text-slate-700 text-sm font-bold p-1 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-3.5 text-xs">
                    <!-- Conference Selection -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Conference <span class="text-red-500">*</span></label>
                        <select v-model="form.conference_id" class="admin-input" required>
                            <option value="" disabled>Select Conference</option>
                            <option v-for="c in conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </select>
                        <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.conference_id }}</span>
                    </div>

                    <!-- Row 1: Journal Name & University (Grid 2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Journal Name <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.journal_name"
                                type="text"
                                placeholder="e.g. Jurnal Kesehatan Vokasional (JKesVo)"
                                class="admin-input"
                                required
                            />
                            <span v-if="form.errors.journal_name" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.journal_name }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-800 mb-1">University / Institution <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.university"
                                type="text"
                                placeholder="e.g. Universitas Gadjah Mada"
                                class="admin-input"
                                required
                            />
                            <span v-if="form.errors.university" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.university }}</span>
                        </div>
                    </div>

                    <!-- Row 2: Website & LoA Information (Grid 2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">Journal Website URL</label>
                            <input
                                v-model="form.website"
                                type="url"
                                placeholder="https://journal.ugm.ac.id/jkesvo"
                                class="admin-input"
                            />
                            <span v-if="form.errors.website" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.website }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-800 mb-1">LoA Information</label>
                            <input
                                v-model="form.loa_information"
                                type="text"
                                placeholder="e.g. Ya, dapat menerbitkan LoA"
                                class="admin-input"
                            />
                            <span v-if="form.errors.loa_information" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.loa_information }}</span>
                        </div>
                    </div>

                    <!-- Submission Requirements / Guidelines -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Submission Requirements
                        </label>
                        <textarea
                            v-model="form.submission_requirements"
                            rows="3"
                            placeholder="Tuliskan ketentuan scope, template naskah, batas similarity, dll."
                            class="admin-input font-mono text-[11px] leading-relaxed"
                        ></textarea>
                        <span v-if="form.errors.submission_requirements" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.submission_requirements }}</span>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-1.5 rounded-lg bg-primary hover:bg-purple-900 text-white font-semibold text-xs transition shadow-xs cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (editingJournal ? 'Update Journal' : 'Save Journal') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📖 View Guidelines Detail Modal -->
        <div
            v-if="viewingJournal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 overflow-y-auto"
            @click.self="closeDetails"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 space-y-4 animate-fade-in my-auto">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ viewingJournal.journal_name }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ viewingJournal.university }}</p>
                    </div>
                    <button
                        @click="closeDetails"
                        class="text-slate-400 hover:text-slate-700 text-sm font-bold p-1 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-xs text-slate-700">
                    <div>
                        <span class="font-bold text-slate-800 block mb-0.5">LoA Information:</span>
                        <p class="text-slate-600 leading-relaxed">{{ viewingJournal.loa_information || '-' }}</p>
                    </div>

                    <div>
                        <span class="font-bold text-slate-800 block mb-0.5">Submission Requirements:</span>
                        <div class="rounded-lg bg-slate-50 border border-slate-100 p-3.5 leading-relaxed whitespace-pre-line text-slate-600">
                            {{ viewingJournal.submission_requirements || 'Belum ada ketentuan tertulis.' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="closeDetails"
                        class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                    >
                        Close
                    </button>
                    <a
                        v-if="viewingJournal.website"
                        :href="viewingJournal.website"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-4 py-1.5 rounded-lg bg-primary hover:bg-purple-900 text-white font-semibold text-xs transition cursor-pointer"
                    >
                        Visit Website
                    </a>
                </div>
            </div>
        </div>

        <!-- 🗑️ Delete Confirmation Modal -->
        <DeleteConfirmModal
            :is-open="isDeleteModalOpen"
            :title="deleteModalTitle"
            :message="deleteModalMessage"
            :item-name="journalToDelete?.journal_name"
            :is-deleting="isDeleting"
            @close="closeDeleteModal"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
