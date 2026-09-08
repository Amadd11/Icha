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
    papers: [Array, Object],
    reviewers: Array,
    filters: Object,
});

const { filters, applyFilter } = useTableFilter('admin.papers.index', {
    status: props.filters?.status || 'all',
});

const { getBadgeClass, getStatusLabel } = useStatusBadge();

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: paperToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

const paperList = computed(() => {
    return Array.isArray(props.papers) ? props.papers : (props.papers?.data || []);
});

const activePaper = ref(null);
const isReviewModalOpen = ref(false);
const isAssignModalOpen = ref(false);
const lockedReviewerIds = ref([]);
const activeAssignmentsMap = ref({});
const activeDropdownItem = ref(null);
const dropdownStyle = ref({});
const reviewerSearch = ref('');

function toggleDropdown(item, event) {
    if (activeDropdownItem.value?.id === item.id) {
        closeDropdown();
        return;
    }

    const buttonRect = event.currentTarget.getBoundingClientRect();
    const dropdownHeight = 145;
    const spaceBelow = window.innerHeight - buttonRect.bottom;
    
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
    review_notes: '',
});

const assignForm = useForm({
    reviewer_ids: [],
});

function openReviewModal(item) {
    activePaper.value = item;
    reviewForm.status = item.status === 'pending' || item.status === 'under_review' ? 'accepted' : item.status;
    reviewForm.review_notes = item.review_notes || '';
    isReviewModalOpen.value = true;
}

function openAssignModal(item) {
    if (!item) return;
    if (item.status === 'revision_required' || getReviewStats(item).roundNumber > 1) {
        return;
    }
    activePaper.value = item;
    assignForm.clearErrors();
    reviewerSearch.value = '';
    activeAssignmentsMap.value = {};
    
    // Find currently assigned reviewer IDs and locked (completed) reviewers from the latest round
    const assignedIds = [];
    const lockedIds = [];
    if (item.review_rounds && item.review_rounds.length > 0) {
        const latestRound = item.review_rounds[item.review_rounds.length - 1];
        if (latestRound && latestRound.assignments) {
            latestRound.assignments.forEach(a => {
                if (a.reviewer_id && !assignedIds.includes(a.reviewer_id) && assignedIds.length < 2) {
                    assignedIds.push(a.reviewer_id);
                    activeAssignmentsMap.value[a.reviewer_id] = a;
                    if (a.status === 'completed' || a.recommendation || a.comments || a.total_score !== null) {
                        lockedIds.push(a.reviewer_id);
                    }
                }
            });
        }
    }

    lockedReviewerIds.value = lockedIds;
    assignForm.reviewer_ids = [...assignedIds];
    isAssignModalOpen.value = true;
}

function toggleReviewer(rev) {
    if (lockedReviewerIds.value.includes(rev.id)) {
        return;
    }
    if (!isReviewerEligible(rev)) {
        return;
    }
    const idx = assignForm.reviewer_ids.indexOf(rev.id);
    if (idx > -1) {
        assignForm.reviewer_ids.splice(idx, 1);
    } else if (assignForm.reviewer_ids.length < 2) {
        assignForm.reviewer_ids.push(rev.id);
    }
}

function isReviewerEligible(rev) {
    if (!activePaper.value) return false;
    // Conflict of interest: Author cannot review their own paper
    if (rev.id === activePaper.value.user_id) return false;
    // If paper has no specific category, any reviewer is eligible
    if (!activePaper.value.category_id) return true;
    return rev.categories?.some(c => c.id === activePaper.value.category_id);
}

const eligibleReviewersCount = computed(() => {
    if (!props.reviewers || !activePaper.value) return 0;
    return props.reviewers.filter(rev => isReviewerEligible(rev)).length;
});

const sortedReviewers = computed(() => {
    if (!props.reviewers || !activePaper.value) return props.reviewers || [];
    
    let list = [...props.reviewers];
    if (reviewerSearch.value.trim()) {
        const q = reviewerSearch.value.toLowerCase().trim();
        list = list.filter(r => r.name.toLowerCase().includes(q) || r.email.toLowerCase().includes(q));
    }

    return list.sort((a, b) => {
        // 1. Locked (completed review) always on top
        const aLocked = lockedReviewerIds.value.includes(a.id) ? 1 : 0;
        const bLocked = lockedReviewerIds.value.includes(b.id) ? 1 : 0;
        if (bLocked !== aLocked) return bLocked - aLocked;

        // 2. Currently selected reviewers second
        const aSelected = assignForm.reviewer_ids.includes(a.id) ? 1 : 0;
        const bSelected = assignForm.reviewer_ids.includes(b.id) ? 1 : 0;
        if (bSelected !== aSelected) return bSelected - aSelected;

        // 3. Category eligible reviewers third
        const aEligible = isReviewerEligible(a) ? 1 : 0;
        const bEligible = isReviewerEligible(b) ? 1 : 0;
        if (bEligible !== aEligible) return bEligible - aEligible;

        // 4. Alphabetical by name
        return a.name.localeCompare(b.name);
    });
});

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

function submitAssign() {
    if (!activePaper.value) return;

    assignForm.post(route('admin.papers.assign', activePaper.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isAssignModalOpen.value = false;
            activePaper.value = null;
        },
    });
}

function deletePaper(item) {
    openDeleteModal({
        item: item,
        title: 'Delete Full Paper Submission',
        message: `Are you sure you want to delete full paper "${item.paper_code} - ${item.title}"? This submission will be soft deleted.`,
        url: route('admin.papers.destroy', item.id),
    });
}

function getReviewStats(item) {
    let completedAssignments = [];
    let totalAssignments = 0;
    let roundNumber = 1;

    if (item.review_rounds && item.review_rounds.length > 0) {
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

function isDecisionReady(item) {
    if (!item) return false;
    const stats = getReviewStats(item);
    if (stats.roundNumber === 1) {
        return stats.completedCount === 2;
    }
    return stats.totalCount > 0 && stats.completedCount >= stats.totalCount;
}

const averageScore = computed(() => {
    if (!activePaper.value) return null;
    const reviews = getReviewStats(activePaper.value).reviews;
    const scores = reviews.map(r => r.total_score).filter(s => s !== null && s !== undefined);
    if (scores.length === 0) return null;
    const sum = scores.reduce((a, b) => a + b, 0);
    return (sum / scores.length).toFixed(1);
});
</script>

<template>
    <Head title="Full Paper Submissions - Admin" />

    <AdminLayout>
        <div class="space-y-6" @click="closeDropdown">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Full Paper Submissions</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage scientific full manuscripts, assign 2 peer reviewers, and record final decisions.</p>
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

            <!-- Papers Table Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-100 bg-slate-50 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 whitespace-nowrap">Code / Author</th>
                                <th scope="col" class="px-5 py-3">Title</th>
                                <th scope="col" class="px-5 py-3 whitespace-nowrap">Review Progress</th>
                                <th scope="col" class="px-5 py-3 whitespace-nowrap">Status</th>
                                <th scope="col" class="px-5 py-3 text-right whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="paperList.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No full papers found in this filter view.
                                </td>
                            </tr>
                            <tr
                                v-for="item in paperList"
                                :key="item.id"
                                class="transition hover:bg-slate-50/50"
                            >
                                <!-- Code / Author -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-purple-900 text-xs">{{ item.paper_code }}</span>
                                    <p class="font-bold text-slate-800 text-xs mt-0.5">{{ item.user?.name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ item.user?.profile?.institution || item.user?.email }}</p>
                                </td>

                                <!-- Title -->
                                <td class="px-5 py-3.5 max-w-md">
                                    <p class="font-semibold text-slate-800 text-xs line-clamp-2" :title="item.title">{{ item.title }}</p>
                                </td>

                                <!-- Review Progress Column (2 Reviewers) -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div v-if="getReviewStats(item).totalCount === 0" class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center whitespace-nowrap rounded-lg px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                                Belum Ditugaskan (0/2)
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-1.5 flex-wrap">
                                            <span :class="[
                                                'inline-flex items-center whitespace-nowrap rounded-lg px-2.5 py-1 text-[11px] font-bold border shadow-2xs',
                                                getReviewStats(item).completedCount >= getReviewStats(item).totalCount && getReviewStats(item).completedCount > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                getReviewStats(item).completedCount > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                            ]">
                                                {{ getReviewStats(item).completedCount }} / {{ getReviewStats(item).totalCount }} Reviewed
                                            </span>
                                            <span v-if="getReviewStats(item).roundNumber > 1" class="whitespace-nowrap text-[10px] font-bold text-amber-800 bg-amber-100 border border-amber-200 rounded-md px-1.5 py-0.5">
                                                Revisi {{ getReviewStats(item).roundNumber - 1 }}
                                            </span>
                                        </div>
                                        <div v-if="getReviewStats(item).totalCount > 0" class="text-[10px] text-slate-400">
                                            <span>
                                                Assigned Reviewers: <strong class="text-slate-600 font-semibold">{{ getReviewStats(item).totalCount }}</strong>
                                            </span>
                                        </div>
                                        <p v-else class="text-[10px] text-amber-600 font-medium leading-tight pt-0.5">
                                            Perlu ditunjuk 2 Reviewer
                                        </p>
                                        <p v-if="item.status === 'revision_required'" class="text-[10px] text-amber-700 font-medium flex items-center gap-1 leading-tight pt-0.5">
                                            <span>⏳</span>
                                            <span>Menunggu author unggah revisi</span>
                                        </p>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center whitespace-nowrap px-3 py-1 rounded-full text-xs font-bold capitalize border shadow-2xs',
                                        getBadgeClass(item.status)
                                    ]">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </td>

                                <!-- Simplified Action Column -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="relative inline-flex items-center justify-end gap-1.5" @click.stop>
                                        <!-- Primary Action: Menunggu Revisi if revision_required, Assign if unassigned, Decision if assigned -->
                                        <span
                                            v-if="item.status === 'revision_required'"
                                            class="inline-flex items-center gap-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1.5 font-medium text-[11px]"
                                        >
                                            <span>⏳</span>
                                            <span>Menunggu Revisi</span>
                                        </span>
                                        <button
                                            v-else-if="getReviewStats(item).totalCount === 0"
                                            @click="openAssignModal(item)"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 hover:text-purple-900 border border-purple-200/90 px-3 py-1.5 font-semibold text-xs transition cursor-pointer shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <span>Assign 2 Reviewers</span>
                                        </button>
                                        <button
                                            v-else
                                            @click="openReviewModal(item)"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 hover:text-amber-950 border border-amber-200/90 px-3 py-1.5 font-semibold text-xs transition cursor-pointer shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                            </svg>
                                            <span>Decision</span>
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
                    :links="props.papers?.links || props.papers?.meta?.links"
                    :from="props.papers?.from || props.papers?.meta?.from"
                    :to="props.papers?.to || props.papers?.meta?.to"
                    :total="props.papers?.total || props.papers?.meta?.total"
                />
            </div>

            <!-- 👥 Assign 2 Reviewers Modal -->
            <div v-if="isAssignModalOpen && activePaper" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-purple-900">{{ activePaper.paper_code }}</span>
                                <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-800">
                                    {{ activePaper.category?.name || 'General Track' }}
                                </span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 mt-1 line-clamp-1" :title="activePaper.title">{{ activePaper.title }}</h3>
                        </div>
                        <button
                            @click="isAssignModalOpen = false"
                            class="rounded-xl bg-slate-200/60 p-2 text-slate-600 hover:bg-slate-200 transition cursor-pointer font-bold text-sm"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitAssign" class="p-6 space-y-4 text-xs">
                        <!-- Rule Info Card -->
                        <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-4 space-y-1 text-slate-600 leading-relaxed">
                            <div class="flex items-center gap-2 font-bold text-purple-950 text-xs">
                                <span>⚖️ Ketentuan Penugasan Full Paper:</span>
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-purple-900/80">
                                <li>Wajib memilih <strong>tepat 2 reviewer</strong> untuk menelaah naskah lengkap.</li>
                                <li>Reviewer harus memiliki bidang kepakaran (*Track*) yang sesuai.</li>
                                <li>Penulis (*Author*) tidak dapat ditugaskan untuk papernya sendiri.</li>
                            </ul>
                        </div>

                        <!-- Error Banner -->
                        <div v-if="assignForm.errors.reviewer_ids" class="rounded-xl bg-red-50 border border-red-200 p-3 text-red-700 font-medium text-xs">
                            {{ assignForm.errors.reviewer_ids }}
                        </div>

                        <!-- Reviewers Selector Container -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700 block">
                                    Pilih 2 Reviewer Track: <strong>{{ activePaper.category?.name || 'General' }}</strong>
                                </span>
                                <span
                                    class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                    :class="assignForm.reviewer_ids.length === 2 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-purple-100 text-purple-900 border-purple-200'"
                                >
                                    {{ assignForm.reviewer_ids.length }} / 2 Reviewer Dipilih
                                </span>
                            </div>

                            <!-- Search Filter -->
                            <div class="mb-2">
                                <input
                                    type="text"
                                    v-model="reviewerSearch"
                                    placeholder="Cari nama atau email reviewer..."
                                    class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 focus:bg-white focus:border-purple-300 outline-none"
                                />
                            </div>
                            
                            <div class="space-y-2 max-h-64 overflow-y-auto border border-slate-100 rounded-2xl p-3 bg-slate-50/50">
                                <div
                                    v-for="rev in sortedReviewers"
                                    :key="rev.id"
                                    @click="toggleReviewer(rev)"
                                    class="flex items-center justify-between p-3 rounded-2xl border transition select-none"
                                    :class="[
                                        lockedReviewerIds.includes(rev.id) ? 'bg-emerald-50/70 border-emerald-300 ring-1 ring-emerald-300 cursor-not-allowed' :
                                        !isReviewerEligible(rev) ? 'bg-slate-100/60 border-slate-200 opacity-50 cursor-not-allowed' :
                                        assignForm.reviewer_ids.includes(rev.id) ? 'bg-purple-50 border-purple-300 ring-1 ring-purple-400 cursor-pointer' : 
                                        assignForm.reviewer_ids.length >= 2 ? 'bg-slate-50 border-slate-200 opacity-60 cursor-not-allowed' :
                                        'bg-white border-slate-200 hover:bg-slate-50 hover:border-purple-200 cursor-pointer shadow-2xs'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            :value="rev.id"
                                            v-model="assignForm.reviewer_ids"
                                            :disabled="lockedReviewerIds.includes(rev.id) || !isReviewerEligible(rev) || (!assignForm.reviewer_ids.includes(rev.id) && assignForm.reviewer_ids.length >= 2)"
                                            @click.stop
                                            class="rounded text-purple-700 focus:ring-purple-700 disabled:opacity-50 h-4 w-4"
                                        />
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-slate-900">{{ rev.name }}</span>
                                                <span v-if="lockedReviewerIds.includes(rev.id)" class="text-[10px] text-emerald-700 font-bold">(Terkunci)</span>
                                            </div>
                                            <span class="text-[10px] text-slate-400">{{ rev.email }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                        <!-- Review Selesai & Terkunci -->
                                        <span
                                            v-if="lockedReviewerIds.includes(rev.id)"
                                            class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 border border-emerald-300 flex items-center gap-1"
                                            title="Reviewer telah menyelesaikan telaah dan tidak dapat dicabut"
                                        >
                                            <span>🔒 Review Selesai</span>
                                            <span v-if="activeAssignmentsMap[rev.id]?.total_score !== null" class="font-mono text-emerald-900">
                                                ({{ activeAssignmentsMap[rev.id]?.total_score }}/10)
                                            </span>
                                        </span>

                                        <!-- Terpilih & Menunggu Penilaian -->
                                        <span
                                            v-else-if="assignForm.reviewer_ids.includes(rev.id)"
                                            class="rounded-full bg-purple-100 text-purple-900 text-[10px] font-bold px-2.5 py-0.5 border border-purple-200 flex items-center gap-1"
                                            title="Reviewer belum menilai, centang dapat dicabut untuk diganti"
                                        >
                                            <span>⏳ Menunggu Penilaian</span>
                                        </span>

                                        <!-- Author Naskah -->
                                        <span
                                            v-else-if="rev.id === activePaper.user_id"
                                            class="rounded-full bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 border border-red-200"
                                            title="Penulis naskah tidak dapat menilai naskahnya sendiri"
                                        >
                                            🚫 Author Naskah
                                        </span>

                                        <!-- Matched Track -->
                                        <span
                                            v-else-if="isReviewerEligible(rev)"
                                            class="rounded-full bg-purple-50 text-purple-800 text-[10px] font-bold px-2 py-0.5 border border-purple-200"
                                        >
                                            🎯 Matched Track
                                        </span>

                                        <!-- Beda Kategori -->
                                        <span
                                            v-else
                                            class="rounded-full bg-slate-200 text-slate-600 text-[10px] font-medium px-2 py-0.5 border border-slate-300"
                                            title="Bidang kepakaran berbeda dengan naskah"
                                        >
                                            Beda Kategori
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="isAssignModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="assignForm.processing || assignForm.reviewer_ids.length !== 2"
                                class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ assignForm.processing ? 'Saving...' : (assignForm.reviewer_ids.length !== 2 ? 'Pilih tepat 2 reviewer' : 'Save Assignments') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ⚖️ Full Paper Decision Modal (with 2 Reviewers Feedback) -->
            <div v-if="isReviewModalOpen && activePaper" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-purple-900">{{ activePaper.paper_code }}</span>
                            <h3 class="text-sm font-bold text-slate-900 line-clamp-1" :title="activePaper.title">{{ activePaper.title }}</h3>
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
                        
                        <!-- Paper Details Banner -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-slate-500 text-[11px]">
                                    Author: <strong class="text-slate-800 font-bold">{{ activePaper.user?.name }}</strong> | 
                                    Track: <strong class="text-purple-900 font-bold">{{ activePaper.category?.name || 'General' }}</strong>
                                </p>
                            </div>
                            <a
                                v-if="activePaper.file_path"
                                :href="formatStorageUrl(activePaper.file_path)"
                                target="_blank"
                                class="inline-flex items-center rounded-xl border border-purple-200 bg-white px-3 py-1.5 text-purple-900 font-bold text-xs hover:bg-purple-50 transition shadow-2xs shrink-0"
                            >
                                Download Paper
                            </a>
                        </div>

                        <!-- Reviewers Feedback Recap (2 Reviewers) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                                    Reviewer Recommendations & Scores (2 Reviewers)
                                </h4>
                                <span v-if="averageScore !== null" class="font-bold text-purple-950 bg-purple-100 border border-purple-200 rounded-full px-2.5 py-0.5 text-[10px]">
                                    Rata-rata Skor: {{ averageScore }} / 10
                                </span>
                            </div>

                            <div v-if="getReviewStats(activePaper).reviews.length === 0" class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-center text-slate-400">
                                Belum ada penilaian dari reviewer untuk naskah full paper ini.
                            </div>
                            <div v-else class="space-y-2.5">
                                <div
                                    v-for="(rev, idx) in getReviewStats(activePaper).reviews"
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
                                            ['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED', 'accepted', 'oral', 'poster'].includes(rev.recommendation) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            ['REJECT', 'REJECTED', 'reject', 'rejected'].includes(rev.recommendation) ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                        ]">
                                            {{
                                                ['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED', 'accepted', 'oral', 'poster'].includes(rev.recommendation) ? 'Accepted' :
                                                ['REJECT', 'REJECTED', 'reject', 'rejected'].includes(rev.recommendation) ? 'Rejected' :
                                                ['REVISION', 'REVISION_REQUIRED', 'revision', 'revision_required'].includes(rev.recommendation) ? 'Revision' :
                                                (rev.recommendation || 'Reviewed')
                                            }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="rev.score_criteria_1 !== null" class="flex gap-4 text-[10px] text-slate-500 font-medium">
                                        <span>Originality: <strong>{{ rev.score_criteria_1 }}/5</strong></span>
                                        <span>Methodology: <strong>{{ rev.score_criteria_2 }}/5</strong></span>
                                    </div>

                                    <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-[11px] leading-relaxed whitespace-pre-wrap">
                                        {{ rev.comments || 'No written comment provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitReview" class="space-y-3 border-t border-slate-100 pt-3">
                            <!-- Warning Banner if not ready for decision (requires 2 reviews) -->
                            <div v-if="!isDecisionReady(activePaper)" class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-center gap-2">
                                <span class="text-base">🔒</span>
                                <span class="leading-relaxed">
                                    <strong>Decision Locked:</strong>
                                    Keputusan final Full Paper hanya dapat dibuat setelah tepat 2 reviewer menyelesaikan penilaian (Saat ini: {{ getReviewStats(activePaper).completedCount }}/2 review selesai).
                                </span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Outcome <span class="text-red-500">*</span></label>
                                <select v-model="reviewForm.status" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white font-bold" required :disabled="!isDecisionReady(activePaper)">
                                    <option value="accepted">Accepted</option>
                                    <option value="revision_required">Revision Required</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Editorial Feedback / Notes for Author</label>
                                <textarea v-model="reviewForm.review_notes" rows="3" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2 px-3 focus:bg-white" placeholder="Provide notes or revision instructions for the author..." :disabled="!isDecisionReady(activePaper)"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="isReviewModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="reviewForm.processing || !isDecisionReady(activePaper)"
                                    class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2.5 transition disabled:opacity-50 cursor-pointer shadow-xs"
                                >
                                    {{ reviewForm.processing ? 'Saving...' : (!isDecisionReady(activePaper) ? 'Awaiting 2 Reviews' : 'Save Decision') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Floating Dropdown Menu (Teleported to body to eliminate table clipping) -->
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
                                v-if="getReviewStats(activeDropdownItem).roundNumber === 1 && activeDropdownItem.status !== 'revision_required'"
                                @click="openAssignModal(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                            >
                                <span class="text-sm">👥</span>
                                <span>Assign 2 Reviewers</span>
                            </button>
                            <a
                                v-if="activeDropdownItem.file_path"
                                :href="formatStorageUrl(activeDropdownItem.file_path)"
                                target="_blank"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                                @click="closeDropdown"
                            >
                                <span class="text-sm">📄</span>
                                <span>Download Paper</span>
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <button
                                @click="deletePaper(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-600 hover:bg-red-50 font-semibold cursor-pointer transition"
                            >
                                <span class="text-sm">🗑️</span>
                                <span>Delete Paper</span>
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
                :item-name="paperToDelete ? `[${paperToDelete.paper_code}] ${paperToDelete.title}` : ''"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
