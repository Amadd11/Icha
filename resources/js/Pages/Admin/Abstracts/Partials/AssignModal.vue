<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    abstract: {
        type: Object,
        default: null,
    },
    reviewers: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'success']);

const assignForm = useForm({
    reviewer_ids: [],
});

const lockedReviewerIds = ref([]);
const activeAssignmentsMap = ref({});

function getReviewStats(item) {
    let completedAssignments = [];
    let totalAssignments = 0;
    let roundNumber = 1;

    if (item && item.review_rounds && item.review_rounds.length > 0) {
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
        roundNumber,
        completedCount: completedAssignments.length,
        totalCount: totalAssignments,
        reviews: completedAssignments,
    };
}

watch(
    () => [props.show, props.abstract],
    ([isOpen, item]) => {
        if (!isOpen || !item) {
            lockedReviewerIds.value = [];
            activeAssignmentsMap.value = {};
            assignForm.reset();
            assignForm.clearErrors();
            return;
        }

        assignForm.clearErrors();
        activeAssignmentsMap.value = {};

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

        lockedReviewerIds.value = lockedIds;
        assignForm.reviewer_ids = [...assignedIds];
    },
    { immediate: true }
);

function isReviewerEligible(rev) {
    if (!props.abstract) return false;
    if (rev.id === props.abstract.user_id) return false;
    if (!props.abstract.category_id) return true;
    return rev.categories?.some(c => c.id === props.abstract.category_id);
}

function toggleReviewer(rev) {
    if (lockedReviewerIds.value.includes(rev.id)) return;
    if (!isReviewerEligible(rev)) return;

    const idx = assignForm.reviewer_ids.indexOf(rev.id);
    if (idx > -1) {
        assignForm.reviewer_ids.splice(idx, 1);
    } else if (assignForm.reviewer_ids.length < 3) {
        assignForm.reviewer_ids.push(rev.id);
    }
}

const eligibleReviewersCount = computed(() => {
    if (!props.reviewers || !props.abstract) return 0;
    return props.reviewers.filter(rev => isReviewerEligible(rev)).length;
});

const sortedReviewers = computed(() => {
    if (!props.reviewers || !props.abstract) return props.reviewers || [];
    return [...props.reviewers].sort((a, b) => {
        const aLocked = lockedReviewerIds.value.includes(a.id) ? 1 : 0;
        const bLocked = lockedReviewerIds.value.includes(b.id) ? 1 : 0;
        if (bLocked !== aLocked) return bLocked - aLocked;

        const aSelected = assignForm.reviewer_ids.includes(a.id) ? 1 : 0;
        const bSelected = assignForm.reviewer_ids.includes(b.id) ? 1 : 0;
        if (bSelected !== aSelected) return bSelected - aSelected;

        const aEligible = isReviewerEligible(a) ? 1 : 0;
        const bEligible = isReviewerEligible(b) ? 1 : 0;
        if (bEligible !== aEligible) return bEligible - aEligible;

        return a.name.localeCompare(b.name);
    });
});

function submitAssign() {
    if (!props.abstract) return;

    assignForm.post(route('admin.abstracts.assign', props.abstract.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
}
</script>

<template>
    <div v-if="show && abstract" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 overflow-y-auto" @click="emit('close')">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-200 my-auto animate-fade-in" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Assign Peer Reviewers</h3>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ abstract.abstract_code }} — {{ abstract.title }}</p>
                </div>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">✕</button>
            </div>

            <form @submit.prevent="submitAssign" class="p-6 space-y-4 text-xs">
                <!-- Error Alert if any -->
                <div v-if="assignForm.errors.reviewer_ids" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-start gap-2">
                    <span class="mt-0.5">⚠️</span>
                    <span class="font-medium leading-relaxed">{{ assignForm.errors.reviewer_ids }}</span>
                </div>

                <!-- Warning if category has fewer than 3 eligible reviewers -->
                <div v-if="eligibleReviewersCount < 3" class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-start gap-2">
                    <span class="mt-0.5 text-base">⚠️</span>
                    <div class="leading-relaxed">
                        <strong>Reviewer Kategori Kurang:</strong> Kategori ini baru memiliki <strong>{{ eligibleReviewersCount }}</strong> reviewer yang memenuhi syarat kepakaran (minimal 3 reviewer dibutuhkan). Silakan daftarkan reviewer dengan kategori <strong>{{ abstract.category?.name || 'terkait' }}</strong> di menu Manajemen User terlebih dahulu.
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
                        <span class="text-xs font-bold text-slate-700 block">Select Reviewers for Track: <strong>{{ abstract.category?.name || 'General' }}</strong></span>
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full" :class="assignForm.reviewer_ids.length >= (getReviewStats(abstract).roundNumber === 1 ? 3 : 1) ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-purple-100 text-purple-900 border border-purple-200'">
                            {{ assignForm.reviewer_ids.length }} / {{ getReviewStats(abstract).roundNumber === 1 ? '3' : '1-3' }} Reviewer
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
                                    v-else-if="rev.id === abstract.user_id"
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
                    <button type="button" @click="emit('close')" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="assignForm.processing || (getReviewStats(abstract).roundNumber === 1 ? assignForm.reviewer_ids.length !== 3 : (assignForm.reviewer_ids.length < 1 || assignForm.reviewer_ids.length > 3))"
                        class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                    >
                        {{ assignForm.processing ? 'Saving...' : (getReviewStats(abstract).roundNumber === 1 ? (assignForm.reviewer_ids.length !== 3 ? 'Select exactly 3 reviewers' : 'Save Assignments') : (assignForm.reviewer_ids.length < 1 ? 'Select at least 1 reviewer' : 'Save Assignments')) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
