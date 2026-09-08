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
const activeAssignmentsMap = ref({});
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
    if (!item) return;
    if (item.status === 'revision_required' || getReviewStats(item).roundNumber > 1) {
        return;
    }
    activeAbstract.value = item;
    assignForm.clearErrors();
    activeAssignmentsMap.value = {};
    
    // Find currently assigned reviewer IDs and locked (completed) reviewers from the latest round
    const assignedIds = [];
    const lockedIds = [];
    if (item.review_rounds && item.review_rounds.length > 0) {
        const latestRound = item.review_rounds[item.review_rounds.length - 1];
        if (latestRound && latestRound.assignments) {
            latestRound.assignments.forEach(a => {
                if (a.reviewer_id && !assignedIds.includes(a.reviewer_id) && assignedIds.length < 3) {
                    assignedIds.push(a.reviewer_id);
                    activeAssignmentsMap.value[a.reviewer_id] = a;
                    if (a.status === 'completed' || a.recommendation || a.comments || a.total_score !== null) {
                        lockedIds.push(a.reviewer_id);
                    }
                }
            });
        }
    }

    // Notice: Admin has 100% manual control. Unassigned abstracts start with [] (0 reviewers).
    lockedReviewerIds.value = lockedIds;
    assignForm.reviewer_ids = [...assignedIds];
    isAssignModalOpen.value = true;
}

function toggleReviewer(rev) {
    // Reviewers who already completed reviews CANNOT be unassigned
    if (lockedReviewerIds.value.includes(rev.id)) {
        return;
    }
    // Reviewers outside category or author cannot be assigned
    if (!isReviewerEligible(rev)) {
        return;
    }
    const idx = assignForm.reviewer_ids.indexOf(rev.id);
    if (idx > -1) {
        assignForm.reviewer_ids.splice(idx, 1);
    } else if (assignForm.reviewer_ids.length < 3) {
        assignForm.reviewer_ids.push(rev.id);
    }
}

function isReviewerEligible(rev) {
    if (!activeAbstract.value) return false;
    // Conflict of interest: Author cannot review their own paper
    if (rev.id === activeAbstract.value.user_id) return false;
    // If abstract has no specific category, any reviewer is eligible
    if (!activeAbstract.value.category_id) return true;
    return rev.categories?.some(c => c.id === activeAbstract.value.category_id);
}

const eligibleReviewersCount = computed(() => {
    if (!props.reviewers || !activeAbstract.value) return 0;
    return props.reviewers.filter(rev => isReviewerEligible(rev)).length;
});

const sortedReviewers = computed(() => {
    if (!props.reviewers || !activeAbstract.value) return props.reviewers || [];
    return [...props.reviewers].sort((a, b) => {
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

function isDecisionReady(item) {
    if (!item) return false;
    const stats = getReviewStats(item);
    if (stats.roundNumber === 1) {
        return stats.completedCount === 3;
    }
    return stats.totalCount > 0 && stats.completedCount >= stats.totalCount;
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
                                        <div v-else class="flex items-center gap-1.5 flex-wrap">
                                            <span :class="[
                                                'inline-flex items-center rounded-md px-2.5 py-0.5 text-[11px] font-bold border',
                                                getReviewStats(item).completedCount >= getReviewStats(item).totalCount && getReviewStats(item).completedCount > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                getReviewStats(item).completedCount > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                            ]">
                                                {{ getReviewStats(item).completedCount }} / {{ getReviewStats(item).totalCount }} Reviewed
                                            </span>
                                            <span v-if="getReviewStats(item).roundNumber > 1" class="text-[10px] font-bold text-amber-800 bg-amber-100 border border-amber-200 rounded-md px-1.5 py-0.5">
                                                Revisi {{ getReviewStats(item).roundNumber - 1 }}
                                            </span>
                                        </div>
                                        <div v-if="getReviewStats(item).totalCount > 0" class="text-[10px] text-slate-400">
                                            <span v-if="getReviewStats(item).roundNumber > 1">
                                                Reviewer Revisi: <strong class="text-slate-600 font-semibold">{{ getReviewStats(item).totalCount }}</strong>
                                            </span>
                                            <span v-else>
                                                Assigned Reviewers: <strong class="text-slate-600 font-semibold">{{ getReviewStats(item).totalCount }}</strong>
                                            </span>
                                        </div>
                                        <p v-else class="text-[10px] text-amber-600 font-medium">
                                            Perlu ditunjuk oleh Admin
                                        </p>
                                        <p v-if="item.status === 'revision_required'" class="text-[10px] text-amber-700 font-medium flex items-center gap-1">
                                            <span>⏳</span>
                                            <span>Menunggu author unggah revisi</span>
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
                                            <span>Assign</span>
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

                        <!-- Warning if category has fewer than 3 eligible reviewers -->
                        <div v-if="eligibleReviewersCount < 3" class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-start gap-2">
                            <span class="mt-0.5 text-base">⚠️</span>
                            <div class="leading-relaxed">
                                <strong>Reviewer Kategori Kurang:</strong> Kategori ini baru memiliki <strong>{{ eligibleReviewersCount }}</strong> reviewer yang memenuhi syarat kepakaran (minimal 3 reviewer dibutuhkan). Silakan daftarkan reviewer dengan kategori <strong>{{ activeAbstract.category?.name || 'terkait' }}</strong> di menu Manajemen User terlebih dahulu.
                            </div>
                        </div>

                        <!-- Alert jika ada reviewer yang sudah menilai (Terkunci) -->
                        <div v-if="lockedReviewerIds.length > 0" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs rounded-xl flex items-start gap-2">
                            <span class="mt-0.5 text-base">🔒</span>
                            <div class="leading-relaxed">
                                <strong>Penilaian Terkunci:</strong> {{ lockedReviewerIds.length }} reviewer telah menyelesaikan penilaian dan tidak dapat dicabut.
                                <span v-if="lockedReviewerIds.length < 3">
                                    Anda masih dapat mengganti {{ 3 - lockedReviewerIds.length }} reviewer yang belum memberikan penilaian.
                                </span>
                            </div>
                        </div>

                        <!-- Info jika kuota 3/3 penuh dan semua belum terkunci -->
                        <div v-else-if="assignForm.reviewer_ids.length >= 3" class="p-3 bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl flex items-start gap-2">
                            <span class="mt-0.5 text-base">ℹ️</span>
                            <div class="leading-relaxed">
                                <strong>Kuota 3 Reviewer Terpenuhi:</strong> Untuk mengganti reviewer, hapus centang pada reviewer yang bertanda <em>"Menunggu Penilaian"</em> terlebih dahulu.
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700 block">Select Reviewers for Track: <strong>{{ activeAbstract.category?.name || 'General' }}</strong></span>
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full" :class="assignForm.reviewer_ids.length >= (getReviewStats(activeAbstract).roundNumber === 1 ? 3 : 1) ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-purple-100 text-purple-900 border border-purple-200'">
                                    {{ assignForm.reviewer_ids.length }} / {{ getReviewStats(activeAbstract).roundNumber === 1 ? '3' : '1-3' }} Reviewer
                                </span>
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
                                        assignForm.reviewer_ids.length >= 3 ? 'bg-slate-50 border-slate-200 opacity-60 cursor-not-allowed' :
                                        'bg-white border-slate-200 hover:bg-slate-50 hover:border-purple-200 cursor-pointer shadow-2xs'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            :value="rev.id"
                                            v-model="assignForm.reviewer_ids"
                                            :disabled="lockedReviewerIds.includes(rev.id) || !isReviewerEligible(rev) || (!assignForm.reviewer_ids.includes(rev.id) && assignForm.reviewer_ids.length >= 3)"
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

                                        <!-- Terpilih & Menunggu Penilaian (Dapat Dicabut) -->
                                        <span
                                            v-else-if="assignForm.reviewer_ids.includes(rev.id)"
                                            class="rounded-full bg-purple-100 text-purple-900 text-[10px] font-bold px-2.5 py-0.5 border border-purple-200 flex items-center gap-1"
                                            title="Reviewer belum menilai, centang dapat dicabut untuk diganti"
                                        >
                                            <span>⏳ Menunggu Penilaian</span>
                                        </span>

                                        <!-- Author Naskah -->
                                        <span
                                            v-else-if="rev.id === activeAbstract.user_id"
                                            class="rounded-full bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 border border-red-200"
                                            title="Penulis naskah tidak dapat menilai naskahnya sendiri"
                                        >
                                            🚫 Author Naskah
                                        </span>

                                        <!-- Matched Track (Belum Terpilih) -->
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
                                :disabled="assignForm.processing || (getReviewStats(activeAbstract).roundNumber === 1 ? assignForm.reviewer_ids.length !== 3 : (assignForm.reviewer_ids.length < 1 || assignForm.reviewer_ids.length > 3))"
                                class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ assignForm.processing ? 'Saving...' : (getReviewStats(activeAbstract).roundNumber === 1 ? (assignForm.reviewer_ids.length !== 3 ? 'Select exactly 3 reviewers' : 'Save Assignments') : (assignForm.reviewer_ids.length < 1 ? 'Select at least 1 reviewer' : 'Save Assignments')) }}
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

                                    <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-[11px] leading-relaxed">
                                        {{ rev.comments || 'No written comment provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitReview" class="space-y-3 border-t border-slate-100 pt-3">
                            <!-- Warning Banner if not ready for decision -->
                            <div v-if="!isDecisionReady(activeAbstract)" class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-center gap-2">
                                <span class="text-base">🔒</span>
                                <span class="leading-relaxed">
                                    <strong>Decision Locked:</strong>
                                    <template v-if="getReviewStats(activeAbstract).roundNumber === 1">
                                        Keputusan final hanya dapat dibuat setelah tepat 3 reviewer menyelesaikan penilaian (Saat ini: {{ getReviewStats(activeAbstract).completedCount }}/3 review selesai).
                                    </template>
                                    <template v-else>
                                        Keputusan final tahap revisi ini hanya dapat dibuat setelah seluruh reviewer menyelesaikan penilaian (Saat ini: {{ getReviewStats(activeAbstract).completedCount }}/{{ getReviewStats(activeAbstract).totalCount }} review selesai).
                                    </template>
                                </span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Outcome <span class="text-red-500">*</span></label>
                                <select v-model="reviewForm.status" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white font-bold" required :disabled="!isDecisionReady(activeAbstract)">
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
                                        <input type="radio" value="oral" v-model="reviewForm.presentation_type" class="text-purple-700 focus:ring-purple-700" :disabled="!isDecisionReady(activeAbstract)" />
                                        <span class="text-xs">🎤 Oral Presentation</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border bg-white cursor-pointer transition" :class="reviewForm.presentation_type === 'poster' ? 'border-purple-600 ring-1 ring-purple-600 font-bold text-purple-900' : 'border-slate-200 text-slate-700'">
                                        <input type="radio" value="poster" v-model="reviewForm.presentation_type" class="text-purple-700 focus:ring-purple-700" :disabled="!isDecisionReady(activeAbstract)" />
                                        <span class="text-xs">🖼️ Poster Presentation</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Decision Notes for Author</label>
                                <textarea v-model="reviewForm.review_notes" rows="3" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2 px-3 focus:bg-white" placeholder="Feedback notes for the author..." :disabled="!isDecisionReady(activeAbstract)"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="isReviewModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="reviewForm.processing || !isDecisionReady(activeAbstract)"
                                    class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2.5 transition disabled:opacity-50 cursor-pointer shadow-xs"
                                >
                                    {{ reviewForm.processing ? 'Saving...' : (!isDecisionReady(activeAbstract) ? (getReviewStats(activeAbstract).roundNumber === 1 ? 'Awaiting 3 Reviews' : 'Awaiting Reviews') : 'Save Decision') }}
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
                                v-if="getReviewStats(activeDropdownItem).roundNumber === 1 && activeDropdownItem.status !== 'revision_required'"
                                @click="openAssignModal(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                            >
                                <span class="text-sm">👥</span>
                                <span>Assign Reviewers</span>
                            </button>
                            <div
                                v-else
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-400 bg-slate-50 cursor-not-allowed select-none"
                                :title="activeDropdownItem.status === 'revision_required' ? 'Menunggu naskah revisi dari peserta' : 'Reviewer tahap revisi terkunci otomatis'"
                            >
                                <span class="text-sm">🔒</span>
                                <span class="text-[11px] font-medium">Reviewer Terkunci (Revisi)</span>
                            </div>

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
