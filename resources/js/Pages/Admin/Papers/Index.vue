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
    reviewForm.status = item.status === 'pending' || item.status === 'under_review' ? 'accepted' : item.status;
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

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}
</script>

<template>
    <Head title="Full Paper Submissions - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Full Paper Submissions</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Review final manuscript articles uploaded by approved abstract authors.</p>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Status:</label>
                    <select
                        v-model="selectedStatus"
                        @change="applyFilter"
                        class="rounded-xl border-slate-200 py-1.5 px-3 text-xs font-bold text-slate-800 bg-white focus:border-slate-400 focus:ring-0 outline-none"
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

            <!-- Full Papers Table Container -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Submitted Manuscripts</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.papers ? props.papers.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Paper Code</th>
                                <th scope="col" class="px-5 py-3">Author</th>
                                <th scope="col" class="px-5 py-3">Title</th>
                                <th scope="col" class="px-5 py-3">Track / Category</th>
                                <th scope="col" class="px-5 py-3">Manuscript File</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.papers || props.papers.length === 0">
                                <td colspan="7" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No full paper submissions found.
                                </td>
                            </tr>
                            <tr v-for="item in props.papers" :key="item.id" class="hover:bg-slate-50/50 transition">
                                <!-- Paper Code -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-purple-900 text-xs">{{ item.paper_code || item.abstract?.abstract_code }}</p>
                                </td>

                                <!-- Author -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-800 text-xs">{{ item.user?.name || item.abstract?.user?.name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ item.user?.email || item.abstract?.user?.email }}</p>
                                </td>

                                <!-- Title -->
                                <td class="px-5 py-3.5 max-w-xs">
                                    <p class="font-semibold text-slate-800 text-xs line-clamp-2" :title="item.title || item.abstract?.title">
                                        {{ item.title || item.abstract?.title }}
                                    </p>
                                </td>

                                <!-- Track -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-block rounded bg-purple-50 px-2.5 py-1 text-[11px] font-bold text-purple-900 border border-purple-100">
                                        {{ item.abstract?.category?.name || 'General Track' }}
                                    </span>
                                </td>

                                <!-- Manuscript File -->
                                <td class="px-5 py-3.5">
                                    <a
                                        v-if="item.file_path"
                                        :href="formatStorageUrl(item.file_path)"
                                        target="_blank"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                    >
                                        Download Manuscript
                                    </a>
                                    <span v-else class="text-xs text-slate-400">No file</span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize border',
                                        item.status === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        item.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' :
                                        item.status === 'revision_required' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                        'bg-slate-100 text-slate-600 border-slate-200'
                                    ]">
                                        {{ item.status.replace('_', ' ') }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <button
                                        @click="openReviewModal(item)"
                                        class="px-3 py-1 rounded-lg bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs transition cursor-pointer"
                                    >
                                        Decision
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Minimalist Full Paper Decision Modal -->
            <div v-if="isReviewModalOpen && activePaper" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Full Paper Decision</h3>
                            <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-800">
                                {{ activePaper.paper_code || activePaper.abstract?.abstract_code }}
                            </span>
                        </div>
                        <button
                            @click="isReviewModalOpen = false"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-700 transition cursor-pointer text-sm"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitReview" class="p-5 space-y-4 text-xs">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 space-y-1">
                            <p class="font-bold text-slate-900 text-xs">{{ activePaper.title || activePaper.abstract?.title }}</p>
                            <p class="text-slate-500">Author: {{ activePaper.user?.name || activePaper.abstract?.user?.name }} | Track: {{ activePaper.abstract?.category?.name || 'General' }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Decision Outcome <span class="text-red-500">*</span></label>
                            <select v-model="reviewForm.status" class="admin-input font-bold" required>
                                <option value="accepted">Accepted (Camera Ready)</option>
                                <option value="revision_required">Revision Required</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Editorial Feedback / Notes for Author</label>
                            <textarea v-model="reviewForm.review_notes" rows="3" class="admin-input" placeholder="Provide notes or revision instructions for the author..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="isReviewModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="reviewForm.processing"
                                class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold px-5 py-2 transition disabled:opacity-50 cursor-pointer"
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
