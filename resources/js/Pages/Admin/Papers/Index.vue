<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    papers: Array,
    filters: Object,
});

const selectedStatus = ref(props.filters?.status || 'all');
const activePaper = ref(null);
const isReviewModalOpen = ref(false);

const reviewForm = useForm({
    status: 'accepted',
    review_notes: '',
});

function applyFilter() {
    router.get(route('admin.papers.index'), {
        status: selectedStatus.value,
    }, { preserveState: true });
}

function openReviewModal(item) {
    activePaper.value = item;
    reviewForm.status = item.status === 'pending' ? 'accepted' : item.status;
    reviewForm.review_notes = item.review_notes || '';
    isReviewModalOpen.value = true;
}

function submitReview() {
    if (!activePaper.value) return;

    reviewForm.post(route('admin.papers.review', activePaper.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isReviewModalOpen.value = false;
            activePaper.value = null;
        },
    });
}
</script>

<template>
    <Head title="Full Paper Submissions - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Full Paper Submissions</h1>
                    <p class="text-xs text-slate-500">Review final manuscript articles uploaded by approved abstract authors.</p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-500">Status Filter:</label>
                    <select
                        v-model="selectedStatus"
                        @change="applyFilter"
                        class="rounded-xl border-slate-300 py-1.5 px-3 text-xs font-bold text-slate-800 shadow-xs focus:border-purple-600 focus:ring-purple-600 outline-none"
                    >
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="revision_required">Revision Required</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <!-- Papers Table (Sipanda Style) -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1">description</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-sm tracking-wide">Submitted Full Papers</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Total: {{ props.papers.length }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Paper Code</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Author / Participant</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Title & Abstract Link</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">File</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Status</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!props.papers || props.papers.length === 0">
                                <td colspan="6" class="px-5 py-10 text-center text-xs text-gray-400">No full paper submissions found.</td>
                            </tr>
                            <tr v-for="item in props.papers" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-primary">{{ item.paper_code }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800 text-xs">{{ item.user?.name }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ item.user?.email }}</p>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="font-bold text-gray-900 text-xs truncate" :title="item.title">{{ item.title }}</p>
                                    <p v-if="item.abstract" class="text-[10px] text-purple-600 font-semibold mt-0.5">
                                        Abstract: [{{ item.abstract.abstract_code }}]
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <a :href="'/storage/' + item.file_path" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                        <span class="material-symbols-outlined text-[16px]">file_download</span> Download Manuscript
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                        item.status === 'accepted' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                        item.status === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                        item.status === 'revision_required' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                        'bg-slate-100 text-slate-600 border border-slate-200'
                                    ]">
                                        {{ item.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button
                                        @click="openReviewModal(item)"
                                        class="px-3 py-1.5 rounded-xl bg-purple-50 text-primary hover:bg-primary hover:text-white font-bold text-xs transition-colors cursor-pointer"
                                    >
                                        Review Paper
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Review Modal -->
            <div v-if="isReviewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-900 text-sm">Review Full Paper: {{ activePaper?.paper_code }}</h3>
                        <button @click="isReviewModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>

                    <div class="space-y-2 text-xs">
                        <p class="font-bold text-slate-800">{{ activePaper?.title }}</p>
                        <p class="text-slate-500">Author: <span class="font-semibold text-slate-700">{{ activePaper?.user?.name }}</span></p>
                    </div>

                    <form @submit.prevent="submitReview" class="space-y-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Decision Status</label>
                            <select v-model="reviewForm.status" class="admin-input" required>
                                <option value="accepted">Accept Full Paper</option>
                                <option value="revision_required">Request Revision</option>
                                <option value="rejected">Reject Full Paper</option>
                                <option value="under_review">Under Review</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Reviewer Feedback & Notes</label>
                            <textarea
                                v-model="reviewForm.review_notes"
                                rows="4"
                                placeholder="Enter paper feedback, formatting notes, or approval message..."
                                class="admin-input"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="isReviewModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="reviewForm.processing"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark transition shadow-md disabled:opacity-50"
                            >
                                {{ reviewForm.processing ? 'Saving...' : 'Save Decision' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
