<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import DecisionModal from './Partials/DecisionModal.vue';
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
    per_page: Number(props.filters?.per_page) || 15,
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

watch(isReviewModalOpen, (val) => {
    if (typeof document !== 'undefined') {
        document.body.classList.toggle('overflow-hidden', !!val);
    }
});

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

function openReviewModal(item) {
    activeAbstract.value = item;
    isReviewModalOpen.value = true;
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
    if (!item || !item.review_rounds || item.review_rounds.length === 0) {
        return {
            roundNumber: 1,
            hasRevisions: false,
            completedCount: 0,
            totalCount: 0,
            targetCount: 0,
            latestRoundCompleted: 0,
            latestRoundTotal: 0,
            reviews: [],
            allReviewers: [],
        };
    }

    const isCompleted = (a) => a.status === 'completed' || !!a.recommendation || !!a.comments || a.total_score !== null;

    // Sort rounds ascending by round_number
    const sortedRounds = [...item.review_rounds].sort((a, b) => (a.round_number || 1) - (b.round_number || 1));
    const latestRound = sortedRounds[sortedRounds.length - 1];
    const roundNumber = latestRound.round_number || sortedRounds.length;
    const hasRevisions = roundNumber > 1;

    // Map unique reviewers across rounds:
    // Only keep assignments that were completed in earlier rounds OR assigned in the current round
    const reviewerMap = new Map();

    sortedRounds.forEach(round => {
        if (round.assignments && Array.isArray(round.assignments)) {
            round.assignments.forEach(a => {
                const key = a.reviewer_id || `assignment_${a.id}`;
                const completed = isCompleted(a);
                const isCurrentRound = (round.round_number || 1) === roundNumber;

                if (completed || isCurrentRound) {
                    reviewerMap.set(key, {
                        ...a,
                        round_number: round.round_number || 1,
                        is_latest_round: isCurrentRound,
                        is_completed: completed,
                    });
                }
            });
        }
    });

    const allReviewers = Array.from(reviewerMap.values())
        .sort((a, b) => (a.round_number || 1) - (b.round_number || 1) || (a.id - b.id));
    const completedReviews = allReviewers.filter(a => a.is_completed);

    let latestRoundTotal = 0;
    let latestRoundCompleted = 0;
    if (latestRound && latestRound.assignments) {
        latestRound.assignments.forEach(a => {
            latestRoundTotal++;
            if (isCompleted(a)) {
                latestRoundCompleted++;
            }
        });
    }

    const targetCount = roundNumber === 1
        ? (latestRoundTotal < 3 ? Math.max(1, latestRoundTotal) : 3)
        : Math.max(allReviewers.length, 3);

    return {
        roundNumber,
        hasRevisions,
        completedCount: completedReviews.length,
        totalCount: allReviewers.length,
        targetCount,
        latestRoundCompleted,
        latestRoundTotal,
        reviews: completedReviews,
        allReviewers,
    };
}

function isDecisionReady(item) {
    if (!item) return false;
    if (['accepted', 'revision_required', 'rejected'].includes(item.status)) {
        return true;
    }
    const stats = getReviewStats(item);
    if (stats.roundNumber === 1) {
        return stats.totalCount > 0 && stats.completedCount >= stats.targetCount;
    }
    return stats.latestRoundTotal > 0 && stats.latestRoundCompleted >= stats.latestRoundTotal && stats.completedCount >= stats.targetCount;
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
                    <p class="text-xs text-slate-500 mt-0.5">Manage scientific track papers, monitor automated reviewer evaluations by topic category, and record final decisions.</p>
                </div>

                <!-- Filters & Controls -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Per Page Selector -->
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2.5 py-1 text-xs text-slate-500 shadow-2xs">
                        <span class="text-[11px] font-semibold text-slate-400">Show:</span>
                        <select
                            v-model.number="filters.per_page"
                            @change="applyFilter()"
                            class="border-0 bg-transparent py-0 pl-0 pr-6 text-xs font-bold text-slate-800 focus:ring-0 cursor-pointer"
                        >
                            <option :value="15">15 per page</option>
                            <option :value="25">25 per page</option>
                            <option :value="50">50 per page</option>
                            <option :value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Status Filter Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                        <button
                            v-for="s in ['all', 'pending', 'under_review', 'revision_required', 'accepted', 'rejected']"
                            :key="s"
                            @click="filters.status = s; applyFilter()"
                            :class="[
                                'rounded-xl px-3 py-1.5 text-xs font-bold capitalize transition shadow-xs cursor-pointer',
                                filters.status === s ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'
                            ]"
                        >
                            {{ s === 'all' ? 'All' : s === 'under_review' ? 'Under Review' : s === 'revision_required' ? 'Revision' : s.replace('_', ' ') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Abstracts Table Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-100 bg-slate-50 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-36 sm:w-44 max-w-[180px] whitespace-nowrap">Code / Author</th>
                                <th scope="col" class="px-4 py-3">Title</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Review Progress</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Status</th>
                                <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="abstractList.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">
                                    No abstracts found in this filter view.
                                </td>
                            </tr>
                            <tr
                                v-for="item in abstractList"
                                :key="item.id"
                                class="transition hover:bg-slate-50/50"
                            >
                                <!-- Code / Author (Compact with Line Wrap) -->
                                <td class="px-4 py-3 w-36 sm:w-44 max-w-[180px]">
                                    <span class="inline-block font-mono font-bold text-purple-900 bg-purple-50/90 border border-purple-200/80 px-1.5 py-0.5 rounded text-[11px] leading-tight">
                                        {{ item.abstract_code }}
                                    </span>
                                    <p class="font-bold text-slate-800 text-xs mt-1 break-words leading-tight">
                                        {{ item.user?.name || item.author_name }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 break-words leading-tight mt-0.5">
                                        {{ item.user?.profile?.institution || item.user?.email }}
                                    </p>
                                </td>

                                <!-- Title (Full title, no truncation) -->
                                <td class="px-4 py-3 min-w-[220px]">
                                    <p class="font-semibold text-slate-800 text-xs break-words whitespace-normal leading-relaxed" :title="item.title">{{ item.title }}</p>
                                    <div v-if="item.category?.name" class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                            {{ item.category.name }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Review Progress Column -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div v-if="getReviewStats(item).totalCount === 0" class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center whitespace-nowrap rounded-lg px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                                Awaiting Category Reviewers
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-1.5 flex-wrap">
                                            <span :class="[
                                                'inline-flex items-center whitespace-nowrap rounded-lg px-2.5 py-1 text-[11px] font-bold border shadow-2xs',
                                                isDecisionReady(item) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                getReviewStats(item).completedCount > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                            ]"
                                            :title="getReviewStats(item).hasRevisions ? `${getReviewStats(item).completedCount}/${getReviewStats(item).targetCount} Total Reviewer (${getReviewStats(item).completedCount - getReviewStats(item).latestRoundCompleted} dari Ronde 1 + ${getReviewStats(item).latestRoundCompleted} dari Revisi)` : `${getReviewStats(item).completedCount}/${getReviewStats(item).targetCount} Reviewer selesai`"
                                            >
                                                {{ getReviewStats(item).completedCount }} / {{ getReviewStats(item).targetCount }}
                                            </span>
                                            <span v-if="getReviewStats(item).roundNumber > 1" class="whitespace-nowrap text-[10px] font-bold text-amber-800 bg-amber-100 border border-amber-200 rounded-md px-1.5 py-0.5">
                                                Revision {{ getReviewStats(item).roundNumber - 1 }}
                                            </span>
                                        </div>
                                        <div v-if="getReviewStats(item).totalCount > 0" class="text-[10px] text-slate-400">
                                            <span v-if="getReviewStats(item).roundNumber > 1">
                                                Revisi: <strong class="text-slate-600 font-semibold">{{ getReviewStats(item).latestRoundCompleted }}/{{ getReviewStats(item).latestRoundTotal }} Reviewer</strong>
                                            </span>
                                            <span v-else>
                                                Target: <strong class="text-slate-600 font-semibold">{{ getReviewStats(item).targetCount }} Reviewer{{ getReviewStats(item).targetCount > 1 ? 's' : '' }}</strong>
                                            </span>
                                        </div>
                                        <p v-else class="text-[10px] text-amber-600 font-medium leading-tight pt-0.5">
                                            No reviewers assigned to this category
                                        </p>
                                        <p v-if="item.status === 'revision_required'" class="text-[10px] text-amber-700 font-medium flex items-center gap-1 leading-tight pt-0.5">
                                            <span class="material-symbols-outlined text-[13px] text-amber-600">hourglass_top</span>
                                            <span>Awaiting author revision file</span>
                                        </p>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span :class="[
                                            'inline-flex items-center whitespace-nowrap px-3 py-1 rounded-full text-xs font-bold capitalize border shadow-2xs',
                                            getBadgeClass(item.status)
                                        ]">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                        <span
                                            v-if="item.status === 'accepted' && item.presentation_type"
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider border shadow-2xs"
                                            :class="item.presentation_type === 'oral' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                                        >
                                            {{ item.presentation_type === 'oral' ? 'Oral' : 'Poster' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Simplified Action Column -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="relative inline-flex items-center justify-end gap-1.5" @click.stop>
                                        <!-- Primary Action: If already decided by system/admin, show Edit Decision -->
                                        <button
                                            v-if="['accepted', 'revision_required', 'rejected'].includes(item.status)"
                                            @click="openReviewModal(item)"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200/90 px-2.5 py-1.5 font-semibold text-xs transition cursor-pointer shadow-2xs"
                                            :title="`Current decision: ${getStatusLabel(item.status)}${item.presentation_type ? ' (' + (item.presentation_type === 'poster' ? 'Poster' : 'Oral') + ')' : ''}. Click to modify decision or presentation type.`"
                                        >
                                            <span class="material-symbols-outlined text-[15px] text-purple-600">tune</span>
                                            <span>Edit Decision</span>
                                        </button>
                                        <button
                                            v-else
                                            @click="openReviewModal(item)"
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 font-semibold text-xs transition cursor-pointer shadow-2xs border',
                                                isDecisionReady(item)
                                                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 hover:text-emerald-950 border-emerald-300'
                                                    : 'bg-amber-50 hover:bg-amber-100 text-amber-800 hover:text-amber-950 border-amber-200/90'
                                            ]"
                                            :title="isDecisionReady(item) ? `${getReviewStats(item).completedCount}/${getReviewStats(item).targetCount} Reviewers completed. Ready for final decision.` : `Waiting for review (${getReviewStats(item).completedCount}/${getReviewStats(item).targetCount}). Click to inspect.`"
                                        >
                                            <svg class="w-3.5 h-3.5" :class="isDecisionReady(item) ? 'text-emerald-600' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <!-- Decision Modal (Modular Component) -->
            <DecisionModal
                :show="isReviewModalOpen"
                :abstract="activeAbstract"
                @close="isReviewModalOpen = false; activeAbstract = null"
            />

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
                            class="fixed w-48 rounded-2xl bg-white p-1.5 shadow-2xl border border-slate-200 text-left text-xs space-y-1"
                            @click.stop
                        >
                            <button
                                @click="openReviewModal(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                            >
                                <span class="material-symbols-outlined text-[16px] text-purple-600">tune</span>
                                <span>{{ ['accepted', 'revision_required', 'rejected'].includes(activeDropdownItem.status) ? 'Edit Decision' : 'Review & Decision' }}</span>
                            </button>

                            <a
                                v-if="activeDropdownItem.file_path"
                                :href="formatStorageUrl(activeDropdownItem.file_path)"
                                target="_blank"
                                download
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-purple-50 hover:text-purple-900 font-semibold cursor-pointer transition"
                                @click="closeDropdown"
                            >
                                <span class="material-symbols-outlined text-[16px] text-slate-500">picture_as_pdf</span>
                                <span>View / Download PDF</span>
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <button
                                @click="deleteAbstract(activeDropdownItem); closeDropdown();"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-600 hover:bg-red-50 font-semibold cursor-pointer transition"
                            >
                                <span class="material-symbols-outlined text-[16px] text-rose-500">delete</span>
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
