<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    abstracts: Array,
    reviewers: Array,
    filters: Object,
});

const selectedStatus = ref(props.filters?.status || 'all');
const activeAbstract = ref(null);
const isReviewModalOpen = ref(false);

const reviewForm = useForm({
    status: 'accepted',
    review_notes: '',
});

function applyFilter() {
    router.get(route('admin.abstracts.index'), {
        status: selectedStatus.value,
    }, { preserveState: true });
}

function openReviewModal(item) {
    activeAbstract.value = item;
    reviewForm.status = item.status === 'pending' || item.status === 'under_review' ? 'accepted' : item.status;
    reviewForm.review_notes = item.review_notes || '';
    isReviewModalOpen.value = true;
}

function submitReview() {
    if (!activeAbstract.value) return;

    reviewForm.post(route('admin.abstracts.review', activeAbstract.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isReviewModalOpen.value = false;
            activeAbstract.value = null;
        },
    });
}

function getReviewStats(item) {
    const catId = item.category_id;
    const trackReviewers = props.reviewers ? props.reviewers.filter(r => r.categories?.some(c => c.id === catId)) : [];
    const totalCount = trackReviewers.length > 0 ? trackReviewers.length : 3;

    let completedAssignments = [];
    if (item.review_rounds && item.review_rounds.length > 0) {
        item.review_rounds.forEach(round => {
            if (round.assignments) {
                round.assignments.forEach(a => {
                    if (a.recommendation || a.comments) {
                        completedAssignments.push(a);
                    }
                });
            }
        });
    }

    const completedCount = completedAssignments.length;
    return {
        completedCount,
        totalCount,
        assignments: completedAssignments,
        trackReviewers,
    };
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
    <Head title="Abstract Submissions - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Minimalist Header & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Abstract Submissions</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Review track feedback and record final editorial decisions.</p>
                </div>

                <!-- Status Filter Select -->
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

            <!-- Minimalist Table Container -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Submissions</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.abstracts ? props.abstracts.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Code</th>
                                <th scope="col" class="px-5 py-3">Author</th>
                                <th scope="col" class="px-5 py-3">Title</th>
                                <th scope="col" class="px-5 py-3">Track / Category</th>
                                <th scope="col" class="px-5 py-3">Review Progress</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.abstracts || props.abstracts.length === 0">
                                <td colspan="7" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No abstract submissions found.
                                </td>
                            </tr>
                            <tr v-for="item in props.abstracts" :key="item.id" class="hover:bg-slate-50/50 transition">
                                <!-- Code -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-purple-900 text-xs">{{ item.abstract_code }}</p>
                                </td>

                                <!-- Author -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-800 text-xs">{{ item.user?.name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ item.user?.email }}</p>
                                </td>

                                <!-- Title -->
                                <td class="px-5 py-3.5 max-w-xs">
                                    <p class="font-semibold text-slate-800 text-xs line-clamp-2" :title="item.title">{{ item.title }}</p>
                                </td>

                                <!-- Track -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-block rounded bg-purple-50 px-2.5 py-1 text-[11px] font-bold text-purple-900 border border-purple-100">
                                        {{ item.category?.name || 'General Track' }}
                                    </span>
                                </td>

                                <!-- Review Progress Column -->
                                <td class="px-5 py-3.5">
                                    <div class="space-y-1">
                                        <span :class="[
                                            'inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold border',
                                            getReviewStats(item).completedCount >= getReviewStats(item).totalCount ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            getReviewStats(item).completedCount > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                        ]">
                                            {{ getReviewStats(item).completedCount }} / {{ getReviewStats(item).totalCount }} Reviewed
                                        </span>
                                        <p class="text-[10px] text-slate-400">
                                            Track Reviewers: {{ getReviewStats(item).totalCount }}
                                        </p>
                                    </div>
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
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            v-if="item.file_path"
                                            :href="formatStorageUrl(item.file_path)"
                                            target="_blank"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            View File
                                        </a>
                                        <button
                                            @click="openReviewModal(item)"
                                            class="px-3 py-1 rounded-lg bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs transition cursor-pointer"
                                        >
                                            Decision
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Minimalist Decision Modal -->
            <div v-if="isReviewModalOpen && activeAbstract" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Editorial Decision</h3>
                            <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-800">
                                {{ activeAbstract.abstract_code }}
                            </span>
                        </div>
                        <button
                            @click="isReviewModalOpen = false"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-700 transition cursor-pointer text-sm"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto text-xs">
                        <!-- Abstract Info -->
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5">
                            <p class="font-bold text-slate-900 text-xs mb-1">{{ activeAbstract.title }}</p>
                            <p class="text-slate-500">Author: {{ activeAbstract.user?.name }} | Track: {{ activeAbstract.category?.name || 'General' }}</p>
                        </div>

                        <!-- Reviewer Feedback -->
                        <div class="space-y-2">
                            <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] border-b border-slate-100 pb-1.5 flex justify-between">
                                <span>Track Reviewers Feedback</span>
                                <span class="text-slate-500">
                                    {{ getReviewStats(activeAbstract).completedCount }} / {{ getReviewStats(activeAbstract).totalCount }} Completed
                                </span>
                            </h4>

                            <div class="space-y-2">
                                <template v-if="getReviewStats(activeAbstract).assignments.length === 0">
                                    <p class="text-slate-400 italic py-2">No reviewer feedback submitted yet.</p>
                                </template>

                                <div
                                    v-for="(rev, idx) in getReviewStats(activeAbstract).assignments"
                                    :key="idx"
                                    class="rounded-xl border border-slate-200 p-3 bg-white space-y-1"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800">{{ rev.reviewer_name }}</span>
                                        <span :class="[
                                            'rounded px-2 py-0.5 text-[10px] font-bold uppercase',
                                            rev.recommendation === 'accept' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                            rev.recommendation === 'reject' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                        ]">
                                            {{ rev.recommendation ? rev.recommendation.replace('_', ' ') : 'Reviewed' }}
                                        </span>
                                    </div>
                                    <p class="text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 text-[11px]">
                                        {{ rev.comments || 'No comment provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitReview" class="space-y-3 border-t border-slate-100 pt-3">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Outcome <span class="text-red-500">*</span></label>
                                <select v-model="reviewForm.status" class="admin-input font-bold" required>
                                    <option value="accepted">Accepted</option>
                                    <option value="revision_required">Revision Required</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Notes for Author</label>
                                <textarea v-model="reviewForm.review_notes" rows="3" class="admin-input" placeholder="Feedback notes for the author..."></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
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

        </div>
    </AdminLayout>
</template>
