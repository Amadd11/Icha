<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';
import { formatStorageUrl } from '@/Utils/formatters';
import { useTableFilter } from '@/Composables/useTableFilter';
import { useStatusBadge } from '@/Composables/useStatusBadge';

const props = defineProps({
    abstracts: [Array, Object],
    reviewers: Array,
    filters: Object,
});

const { filters, applyFilter } = useTableFilter('admin.abstracts.index', {
    status: props.filters?.status || 'all',
});

const { getBadgeClass, getStatusLabel } = useStatusBadge();

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: abstractToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

const abstractList = computed(() => {
    return Array.isArray(props.abstracts) ? props.abstracts : (props.abstracts?.data || []);
});

const activeAbstract = ref(null);
const isReviewModalOpen = ref(false);
const isAssignModalOpen = ref(false);
const lockedReviewerIds = ref([]);
const activeDropdownItem = ref(null);
const dropdownStyle = ref({});

function toggleDropdown(item, event) {
    if (activeDropdownItem.value?.id === item.id) {
        closeDropdown();
        return;
    }

    const buttonRect = event.currentTarget.getBoundingClientRect();
    const dropdownHeight = 145;
    const spaceBelow = window.innerHeight - buttonRect.bottom;
    
    // Check if enough space below, otherwise open upward
    const openUpward = spaceBelow < dropdownHeight && buttonRect.top > dropdownHeight;
    
    const top = openUpward 
        ? buttonRect.top - dropdownHeight - 6 
        : buttonRect.bottom + 6;
        
    const right = Math.max(16, window.innerWidth - buttonRect.right);

    dropdownStyle.value = {
        top: `${top}px`,
        right: `${right}px`,
        transformOrigin: openUpward ? 'bottom right' : 'top right',
    };

    activeDropdownItem.value = item;
}

function closeDropdown() {
    activeDropdownItem.value = null;
}

function isDocx(filePath) {
    if (!filePath) return false;
    return filePath.toLowerCase().endsWith('.docx') || filePath.toLowerCase().endsWith('.doc');
}

function handleScrollOrResize() {
    if (activeDropdownItem.value) {
        closeDropdown();
    }
}

onMounted(() => {
    window.addEventListener('scroll', handleScrollOrResize, true);
    window.addEventListener('resize', handleScrollOrResize);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScrollOrResize, true);
    window.removeEventListener('resize', handleScrollOrResize);
});

const reviewForm = useForm({
    status: 'accepted',
    presentation_type: 'oral',
    review_notes: '',
});

const assignForm = useForm({
    reviewer_ids: [],
});

function openReviewModal(item) {
    activeAbstract.value = item;
    reviewForm.status = item.status === 'pending' || item.status === 'under_review' ? 'accepted' : item.status;
    reviewForm.presentation_type = item.presentation_type || 'oral';
    reviewForm.review_notes = item.review_notes || '';
    isReviewModalOpen.value = true;
}

function openAssignModal(item) {
    activeAbstract.value = item;
    assignForm.clearErrors();
    
    // Find currently assigned reviewer IDs and locked (completed) reviewers from the latest round
    const assignedIds = [];
    const lockedIds = [];
    if (item.review_rounds && item.review_rounds.length > 0) {
        const latestRound = item.review_rounds[item.review_rounds.length - 1];
        if (latestRound && latestRound.assignments) {
            latestRound.assignments.forEach(a => {
                if (a.reviewer_id && !assignedIds.includes(a.reviewer_id) && assignedIds.length < 3) {
                    assignedIds.push(a.reviewer_id);
                    if (a.status === 'completed' || a.recommendation || a.comments || a.total_score !== null) {
                        lockedIds.push(a.reviewer_id);
                    }
                }
            });
        }
    }

    // Notice: Admin has 100% manual control. Unassigned abstracts start with [] (0 reviewers).
    lockedReviewerIds.value = lockedIds;
    assignForm.reviewer_ids = assignedIds;
    isAssignModalOpen.value = true;
}

const sortedReviewers = computed(() => {
    if (!props.reviewers || !activeAbstract.value) return props.reviewers || [];
    const catId = activeAbstract.value.category_id;
    return [...props.reviewers].sort((a, b) => {
        const aMatch = a.categories?.some(c => c.id === catId) ? 1 : 0;
        const bMatch = b.categories?.some(c => c.id === catId) ? 1 : 0;
        return bMatch - aMatch;
    });
});

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

function submitAssign() {
    if (!activeAbstract.value) return;

    assignForm.post(route('admin.abstracts.assign', activeAbstract.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAssignModalOpen.value = false;
            activeAbstract.value = null;
        },
    });
}

function deleteAbstract(item) {
    openDeleteModal({
        item: item,
        title: 'Delete Abstract Submission',
        message: `Are you sure you want to delete abstract "${item.abstract_code} - ${item.title}"? This submission will be soft deleted.`,
        url: route('admin.abstracts.destroy', item.id),
    });
}

function getReviewStats(item) {
    let completedAssignments = [];
    let totalAssignments = 0;
    let roundNumber = 1;

    if (item.review_rounds && item.review_rounds.length > 0) {
        // Look at the latest round
        const latestRound = item.review_rounds[item.review_rounds.length - 1];
        roundNumber = latestRound.round_number || item.review_rounds.length;
        if (latestRound && latestRound.assignments) {
            totalAssignments = latestRound.assignments.length;
            latestRound.assignments.forEach(a => {
                if (a.status === 'completed' || a.recommendation || a.comments || a.total_score !== null) {
                    completedAssignments.push(a);
                }
            });
        }
    }

    return {
        roundNumber: roundNumber,
        completedCount: completedAssignments.length,
        totalCount: totalAssignments,
        reviews: completedAssignments,
    };
}
</script>

<template>
    <Head title="Abstract Submissions - Admin" />

    <AdminLayout>
        <div class="space-y-6" @click="closeDropdown">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Abstract Submissions</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage scientific track papers, assign peer reviewers, and record final decisions.</p>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    <button
                        v-for="s in ['all', 'pending', 'under_review', 'revision_required', 'accepted', 'rejected']"
                        :key="s"
                        @click="filters.status = s; applyFilter()"
                        :class="[
                            'rounded-xl px-3 py-1.5 text-xs font-bold capitalize transition shadow-xs cursor-pointer',
                            filters.status === s ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                        ]"
                    >
                        {{ s === 'all' ? 'All' : s === 'pending' ? 'Pending (Unassigned)' : s.replace('_', ' ') }}
                    </button>
                </div>
            </div>

            <!-- Abstracts Table Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-100 bg-slate-50 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Code / Author</th>
                                <th scope="col" class="px-5 py-3">Title</th>
                                <th scope="col" class="px-5 py-3">Track</th>
                                <th scope="col" class="px-5 py-3">Review Progress</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="abstractList.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No abstracts found in this filter view.
                                </td>
                            </tr>
                            <tr
                                v-for="item in abstractList"
                                :key="item.id"
                                class="transition hover:bg-slate-50/50"
                            >
                                <!-- Code / Author -->
                                <td class="px-5 py-3.5">
                                    <span class="font-mono font-bold text-purple-900 text-xs">{{ item.abstract_code }}</span>
                                    <p class="font-bold text-slate-800 text-xs mt-0.5">{{ item.user?.name || item.author_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ item.user?.profile?.institution || item.user?.email }}</p>
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
                                        <div v-if="getReviewStats(item).totalCount === 0" class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                Belum Ditugaskan (0/3)
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-1.5">
                                            <span :class="[
                                                'inline-flex items-center rounded-md px-2.5 py-0.5 text-[11px] font-bold border',
                                                getReviewStats(item).completedCount >= getReviewStats(item).totalCount && getReviewStats(item).completedCount > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                getReviewStats(item).completedCount > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                            ]">
                                                {{ getReviewStats(item).completedCount }} / {{ getReviewStats(item).totalCount }} Reviewed
                                            </span>
                                            <span v-if="getReviewStats(item).roundNumber > 1" class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-1.5 py-0.5">
                                                R{{ getReviewStats(item).roundNumber }} (Revision)
                                            </span>
                                        </div>
                                        <p v-if="getReviewStats(item).totalCount > 0" class="text-[10px] text-slate-400">
                                            Assigned Reviewers: {{ getReviewStats(item).totalCount }}
                                        </p>
                                        <p v-else class="text-[10px] text-amber-600 font-medium">
                                            Perlu ditunjuk oleh Admin
                                        </p>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize border',
                                        getBadgeClass(item.status)
                                    ]">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </td>

                                <!-- Simplified Action Column -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="relative inline-flex items-center justify-end gap-1.5" @click.stop>
                                        <!-- Primary Action: Assign Reviewers if unassigned, Decision if assigned -->
                                        <button
                                            v-if="getReviewStats(item).totalCount === 0"
                                            @click="openAssignModal(item)"
                                            class="rounded-xl bg-purple-900 hover:bg-purple-950 text-gold px-3.5 py-1.5 font-bold text-xs transition cursor-pointer shadow-2xs flex items-center gap-1"
                                        >
                                            <span>👥 Assign</span>
                                        </button>
                                        <button
                                            v-else
                                            @click="openReviewModal(item)"
                                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 px-3.5 py-1.5 font-bold text-xs transition cursor-pointer shadow-2xs"
                                        >
                                            Decision
                                        </button>

                                        <!-- More Actions Dropdown Toggle -->
                                        <button
                                            @click.stop="toggleDropdown(item, $event)"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition cursor-pointer shadow-2xs"
                                            :class="{ 'bg-slate-100 text-slate-900 border-slate-300 ring-2 ring-purple-100': activeDropdownItem?.id === item.id }"
                                            title="More Actions"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <Pagination
                    :links="props.abstracts?.links || props.abstracts?.meta?.links"
                    :from="props.abstracts?.from || props.abstracts?.meta?.from"
                    :to="props.abstracts?.to || props.abstracts?.meta?.to"
                    :total="props.abstracts?.total || props.abstracts?.meta?.total"
                />
            </div>

            <!-- 👥 Assign Reviewers Modal -->
            <div v-if="isAssignModalOpen && activeAbstract" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden border border-slate-200 my-8">
                    <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Assign Peer Reviewers</h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ activeAbstract.abstract_code }} — {{ activeAbstract.title }}</p>
                        </div>
                        <button @click="isAssignModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitAssign" class="p-6 space-y-4">
                        <!-- Error Alert if any -->
                        <div v-if="assignForm.errors.reviewer_ids" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-start gap-2">
                            <span class="mt-0.5">⚠️</span>
                            <span class="font-medium leading-relaxed">{{ assignForm.errors.reviewer_ids }}</span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700 block">Select Reviewers for Track: <strong>{{ activeAbstract.category?.name || 'General' }}</strong></span>
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full" :class="assignForm.reviewer_ids.length >= 3 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-purple-100 text-purple-900 border border-purple-200'">
                                    {{ assignForm.reviewer_ids.length }} / 3 Reviewer
                                </span>
                            </div>
                            
                            <div class="space-y-2 max-h-60 overflow-y-auto border border-slate-100 rounded-2xl p-3 bg-slate-50/50">
                                <label
                                    v-for="rev in sortedReviewers"
                                    :key="rev.id"
                                    class="flex items-center justify-between p-2.5 rounded-xl border transition"
                                    :class="[
                                        lockedReviewerIds.includes(rev.id) ? 'bg-emerald-50/60 border-emerald-300 ring-1 ring-emerald-300 cursor-not-allowed' :
                                        assignForm.reviewer_ids.includes(rev.id) ? 'bg-purple-50 border-purple-300 ring-1 ring-purple-400 cursor-pointer' : 
                                        assignForm.reviewer_ids.length >= 3 ? 'bg-slate-100/60 border-slate-200 opacity-60 cursor-not-allowed' :
                                        'bg-white border-slate-200 hover:bg-slate-50 cursor-pointer'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            :value="rev.id"
                                            v-model="assignForm.reviewer_ids"
                                            :disabled="lockedReviewerIds.includes(rev.id) || (!assignForm.reviewer_ids.includes(rev.id) && assignForm.reviewer_ids.length >= 3)"
                                            class="rounded text-purple-700 focus:ring-purple-700 disabled:opacity-50"
                                        />
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 block">{{ rev.name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ rev.email }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            v-if="lockedReviewerIds.includes(rev.id)"
                                            class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 border border-emerald-200 flex items-center gap-1"
                                        >
                                            ✓ Review Completed
                                        </span>
                                        <span
                                            v-if="rev.categories?.some(c => c.id === activeAbstract.category_id)"
                                            class="rounded-full bg-purple-100 text-purple-800 text-[10px] font-bold px-2 py-0.5 border border-purple-200"
                                        >
                                            🎯 Matched Track
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="isAssignModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="assignForm.processing"
                                class="rounded-xl bg-purple-900 hover:bg-purple-950 text-gold font-bold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ assignForm.processing ? 'Saving...' : 'Save Assignments' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ⚖️ Minimalist Decision Modal -->
            <div v-if="isReviewModalOpen && activeAbstract" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-purple-900">{{ activeAbstract.abstract_code }}</span>
                            <h3 class="text-sm font-bold text-slate-900 line-clamp-1" :title="activeAbstract.title">{{ activeAbstract.title }}</h3>
                        </div>
                        <button
                            @click="isReviewModalOpen = false"
                            class="rounded-xl bg-slate-200/60 p-2 text-slate-600 hover:bg-slate-200 transition cursor-pointer font-bold text-sm"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 text-xs">
                        
                        <!-- Reviewers Feedback Recap -->
                        <div>
                            <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-2">Reviewer Recommendations & Scores</h4>
                            <div v-if="getReviewStats(activeAbstract).reviews.length === 0" class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-center text-slate-400">
                                No peer reviews submitted yet for this abstract.
                            </div>
                            <div v-else class="space-y-2.5">
                                <div
                                    v-for="(rev, idx) in getReviewStats(activeAbstract).reviews"
                                    :key="idx"
                                    class="rounded-2xl border border-slate-200 p-3.5 bg-white space-y-2 shadow-xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900">{{ rev.reviewer_name }}</span>
                                            <span v-if="rev.total_score !== null && rev.total_score !== undefined" class="rounded-md bg-purple-100 text-purple-900 px-2 py-0.5 text-[10px] font-bold">
                                                Total Score: {{ rev.total_score }} / 10
                                            </span>
                                        </div>
                                        <span :class="[
                                            'rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border',
                                            rev.recommendation === 'ORAL' || rev.recommendation === 'POSTER' || rev.recommendation === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            rev.recommendation === 'REJECT' || rev.recommendation === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                        ]">
                                            {{ rev.recommendation ? rev.recommendation.replace('_', ' ') : 'Reviewed' }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="rev.score_criteria_1 !== null" class="flex gap-4 text-[10px] text-slate-500 font-medium">
                                        <span>Originality: <strong>{{ rev.score_criteria_1 }}/5</strong></span>
                                        <span>Methodology: <strong>{{ rev.score_criteria_2 }}/5</strong></span>
                                    </div>

                                    <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-[11px] leading-relaxed">
                                        {{ rev.comments || 'No written comment provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitReview" class="space-y-3 border-t border-slate-100 pt-3">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Outcome <span class="text-red-500">*</span></label>
                                <select v-model="reviewForm.status" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white font-bold" required>
                                    <option value="accepted">Accepted</option>
                                    <option value="revision_required">Revision Required</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <!-- Presentation Type (When Accepted) -->
                            <div v-if="reviewForm.status === 'accepted'" class="rounded-2xl bg-purple-50/70 border border-purple-200 p-3.5 space-y-2">
                                <label class="block text-xs font-bold text-purple-950">Presentation Type Allocation <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white cursor-pointer transition" :class="reviewForm.presentation_type === 'oral' ? 'border-purple-600 ring-1 ring-purple-600 font-bold text-purple-900' : 'border-slate-200 text-slate-700'">
                                        <input type="radio" value="oral" v-model="reviewForm.presentation_type" class="text-purple-700 focus:ring-purple-700" />
                                        <span class="text-xs">🎤 Oral Presentation</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white cursor-pointer transition" :class="reviewForm.presentation_type === 'poster' ? 'border-purple-600 ring-1 ring-purple-600 font-bold text-purple-900' : 'border-slate-200 text-slate-700'">
                                        <input type="radio" value="poster" v-model="reviewForm.presentation_type" class="text-purple-700 focus:ring-purple-700" />
                                        <span class="text-xs">🖼️ Poster Presentation</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Notes for Author</label>
                                <textarea v-model="reviewForm.review_notes" rows="3" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2 px-3 focus:bg-white" placeholder="Feedback notes for the author..."></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="isReviewModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
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

            <!-- Floating Dropdown Menu (Teleported to body to eliminate table clipping and scrollbar glitches) -->
            <Teleport to="body">
                <transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="activeDropdownItem"
                        class="fixed inset-0 z-50 bg-transparent"
                        @click="closeDropdown"
                    >
                        <div
                            :style="dropdownStyle"
                            class="fixed w-52 rounded-2xl bg-white p-1.5 shadow-2xl border border-slate-200 text-left text-xs space-y-1"
                            @click.stop
                        >
                            <button
                                @click="openAssignModal(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                            >
                                <span class="text-sm">👥</span>
                                <span>Assign Reviewers</span>
                            </button>

                            <a
                                v-if="activeDropdownItem.file_path"
                                :href="formatStorageUrl(activeDropdownItem.file_path)"
                                target="_blank"
                                download
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                                @click="closeDropdown"
                            >
                                <span class="text-sm" v-if="isDocx(activeDropdownItem.file_path)">📝</span>
                                <span class="text-sm" v-else>📄</span>
                                <span v-if="isDocx(activeDropdownItem.file_path)">Unduh Dokumen (.docx)</span>
                                <span v-else>Buka / Unduh PDF</span>
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <button
                                @click="deleteAbstract(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-600 hover:bg-red-50 font-semibold cursor-pointer transition"
                            >
                                <span class="text-sm">🗑️</span>
                                <span>Delete Abstract</span>
                            </button>
                        </div>
                    </div>
                </transition>
            </Teleport>

            <!-- Reusable Delete Confirmation Modal -->
            <DeleteConfirmModal
                :show="isDeleteModalOpen"
                :title="deleteTitle"
                :message="deleteMessage"
                :item-name="abstractToDelete ? `[${abstractToDelete.abstract_code}] ${abstractToDelete.title}` : ''"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
