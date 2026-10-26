<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { formatStorageUrl } from '@/Utils/formatters';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    abstract: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'success']);

const reviewForm = useForm({
    status: 'accepted',
    presentation_type: 'oral',
    review_notes: '',
});

function isDocx(filePath) {
    if (!filePath) return false;
    return filePath.toLowerCase().endsWith('.docx') || filePath.toLowerCase().endsWith('.doc');
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

    // Aggregate unique reviewers:
    // Retain reviewers who completed in previous rounds OR are assigned in the active latest round
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

function getSuggestedDecisionStatus(item) {
    if (!item) return 'accepted';
    if (item.status && !['pending', 'under_review'].includes(item.status)) {
        return item.status;
    }

    const stats = getReviewStats(item);
    if (!stats.reviews.length) return 'accepted';

    let revisionVotes = 0;
    let rejectVotes = 0;
    let acceptVotes = 0;

    stats.reviews.forEach(rev => {
        const rec = (rev.recommendation || '').toUpperCase();
        if (['REVISION', 'REVISION_REQUIRED'].includes(rec)) {
            revisionVotes++;
        } else if (['REJECT', 'REJECTED'].includes(rec)) {
            rejectVotes++;
        } else if (['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED'].includes(rec)) {
            acceptVotes++;
        }
    });

    if (revisionVotes > 0) {
        return 'revision_required';
    } else if (rejectVotes > acceptVotes) {
        return 'rejected';
    }
    return 'accepted';
}

function getSuggestedPresentationType(item) {
    if (!item) return { type: 'oral', label: 'Oral', oralVotes: 0, posterVotes: 0, reason: '' };
    
    const stats = getReviewStats(item);
    const reviews = stats.reviews;
    let oralVotes = 0;
    let posterVotes = 0;

    reviews.forEach(rev => {
        const rec = (rev.recommendation || '').toUpperCase();
        if (['ORAL', 'ACCEPT', 'ACCEPTED'].includes(rec)) {
            oralVotes++;
        } else if (rec === 'POSTER') {
            posterVotes++;
        }
    });

    if (posterVotes > oralVotes) {
        return {
            type: 'poster',
            label: 'Poster',
            oralVotes,
            posterVotes,
            reason: `${posterVotes} of ${oralVotes + posterVotes} reviewers recommended Poster`,
        };
    } else if (oralVotes > posterVotes) {
        return {
            type: 'oral',
            label: 'Oral',
            oralVotes,
            posterVotes,
            reason: `${oralVotes} of ${oralVotes + posterVotes} reviewers recommended Oral`,
        };
    }

    const fallback = item.presentation_type || 'oral';
    return {
        type: fallback,
        label: fallback === 'poster' ? 'Poster' : 'Oral',
        oralVotes,
        posterVotes,
        reason: oralVotes > 0 ? 'Reviewer votes tied (default Oral)' : 'Based on author selection',
    };
}

function getReviewerRecommendationInfo(rec) {
    const raw = (rec || '').toUpperCase();
    if (['ORAL', 'ACCEPT_ORAL'].includes(raw)) {
        return {
            label: 'Accept (Oral)',
            shortLabel: 'Oral',
            type: 'oral',
            badgeCls: 'bg-emerald-50 text-emerald-800 border-emerald-200',
            dotCls: 'bg-emerald-500',
        };
    }
    if (['POSTER', 'ACCEPT_POSTER'].includes(raw)) {
        return {
            label: 'Accept (Poster)',
            shortLabel: 'Poster',
            type: 'poster',
            badgeCls: 'bg-blue-50 text-blue-800 border-blue-200',
            dotCls: 'bg-blue-500',
        };
    }
    if (['REVISION', 'REVISION_REQUIRED'].includes(raw)) {
        return {
            label: 'Revision Required',
            shortLabel: 'Revision',
            type: 'revision',
            badgeCls: 'bg-amber-50 text-amber-800 border-amber-200',
            dotCls: 'bg-amber-500',
        };
    }
    if (['REJECT', 'REJECTED'].includes(raw)) {
        return {
            label: 'Rejected',
            shortLabel: 'Reject',
            type: 'reject',
            badgeCls: 'bg-rose-50 text-rose-800 border-rose-200',
            dotCls: 'bg-rose-500',
        };
    }
    if (['ACCEPT', 'ACCEPTED'].includes(raw)) {
        return {
            label: 'Accepted',
            shortLabel: 'Accept',
            type: 'accept',
            badgeCls: 'bg-emerald-50 text-emerald-800 border-emerald-200',
            dotCls: 'bg-emerald-500',
        };
    }
    return {
        label: rec || 'Pending Review',
        shortLabel: rec || 'Pending',
        type: 'other',
        badgeCls: 'bg-slate-100 text-slate-600 border-slate-200',
        dotCls: 'bg-slate-400',
    };
}

function getVotesBreakdown(item) {
    if (!item) return { oral: 0, poster: 0, revision: 0, reject: 0, total: 0 };
    
    const stats = getReviewStats(item);
    const reviews = stats.reviews;
    let oral = 0;
    let poster = 0;
    let revision = 0;
    let reject = 0;

    reviews.forEach(rev => {
        const info = getReviewerRecommendationInfo(rev.recommendation);
        if (info.type === 'oral' || info.type === 'accept') oral++;
        else if (info.type === 'poster') poster++;
        else if (info.type === 'revision') revision++;
        else if (info.type === 'reject') reject++;
    });

    return { oral, poster, revision, reject, total: reviews.length };
}

function copyReviewerNotesToForm() {
    if (!props.abstract) return;
    const revNotes = getReviewStats(props.abstract).reviews
        .filter(r => r.comments)
        .map((r, idx) => `• Reviewer #${idx + 1} [${getReviewerRecommendationInfo(r.recommendation).shortLabel}]: ${r.comments}`)
        .join('\n');
    if (revNotes) {
        reviewForm.review_notes = revNotes;
    }
}

watch(
    () => [props.show, props.abstract],
    ([isOpen, item]) => {
        if (!isOpen || !item) {
            reviewForm.reset();
            reviewForm.clearErrors();
            return;
        }

        reviewForm.clearErrors();
        reviewForm.status = getSuggestedDecisionStatus(item);

        if (!item.presentation_type || item.status === 'pending' || item.status === 'under_review') {
            const suggestion = getSuggestedPresentationType(item);
            reviewForm.presentation_type = suggestion.type;
        } else {
            reviewForm.presentation_type = item.presentation_type;
        }

        reviewForm.review_notes = item.review_notes || '';

        if (reviewForm.status === 'revision_required' && !reviewForm.review_notes) {
            const revNotes = getReviewStats(item).reviews
                .filter(r => r.comments && ['REVISION', 'REVISION_REQUIRED'].includes((r.recommendation || '').toUpperCase()))
                .map((r, idx) => `• Reviewer #${idx + 1}: ${r.comments}`)
                .join('\n');
            if (revNotes) {
                reviewForm.review_notes = `Reviewer Revision Notes:\n${revNotes}`;
            }
        }
    },
    { immediate: true }
);

function submitReview() {
    if (!props.abstract) return;

    reviewForm.post(route('admin.abstracts.review', props.abstract.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
}
</script>

<template>
    <!-- Decision Modal (Clear, Informative & Clean) -->
    <div v-if="show && abstract" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 overflow-y-auto" @click="emit('close')">
        <div class="relative w-full max-w-3xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-200 my-auto animate-fade-in" @click.stop>
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="font-mono text-xs font-black text-purple-900 bg-purple-100 border border-purple-200 px-2.5 py-0.5 rounded-lg">
                            {{ abstract.abstract_code }}
                        </span>
                        <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-0.5 rounded-lg">
                            {{ abstract.category?.name || 'General Track' }}
                        </span>
                        <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg">
                            Round {{ getReviewStats(abstract).roundNumber === 1 ? '1 (Initial Review)' : `Revision (${getReviewStats(abstract).roundNumber - 1})` }}
                        </span>
                    </div>
                    <!-- Title never truncated / cut off -->
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 break-words leading-snug mt-1.5" :title="abstract.title">
                        {{ abstract.title }}
                    </h3>
                    <p class="text-xs text-slate-500 break-words mt-0.5">
                        Author: <strong class="text-slate-800 font-semibold">{{ abstract.user?.name || abstract.author_name }}</strong>
                        <span v-if="abstract.user?.profile?.institution" class="text-slate-400"> • {{ abstract.user.profile.institution }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a
                        v-if="abstract.file_path"
                        :href="formatStorageUrl(abstract.file_path)"
                        target="_blank"
                        download
                        class="inline-flex items-center gap-1.5 rounded-xl border border-purple-200 bg-white hover:bg-purple-50 text-purple-900 font-bold text-xs px-3 py-1.5 transition shadow-2xs cursor-pointer"
                        title="Download PDF"
                    >
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                        <span>PDF</span>
                    </a>
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded-xl bg-slate-100 hover:bg-slate-200 p-2 text-slate-500 hover:text-slate-800 transition cursor-pointer font-bold text-xs"
                        title="Close"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="submitReview" class="flex-1 overflow-y-auto p-6 space-y-4 text-xs">
                <!-- Section 1: Reviewers Feedback & Vote Breakdown -->
                <div class="bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/60 pb-2.5">
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-purple-700">rate_review</span>
                                <span>Reviewer Evaluation Results</span>
                            </h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Detailed recommendations, criterion scores, and reviewer feedback:
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <!-- Quick Tally Pills -->
                            <span v-if="getVotesBreakdown(abstract).oral > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                {{ getVotesBreakdown(abstract).oral }} Oral
                            </span>
                            <span v-if="getVotesBreakdown(abstract).poster > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-300">
                                {{ getVotesBreakdown(abstract).poster }} Poster
                            </span>
                            <span v-if="getVotesBreakdown(abstract).revision > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                {{ getVotesBreakdown(abstract).revision }} Revision
                            </span>
                            <span v-if="getVotesBreakdown(abstract).reject > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                {{ getVotesBreakdown(abstract).reject }} Reject
                            </span>

                            <span
                                class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border shadow-2xs ml-1"
                                :class="isDecisionReady(abstract) ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300'"
                            >
                                {{ getReviewStats(abstract).completedCount }} / {{ getReviewStats(abstract).targetCount }} Completed
                            </span>
                        </div>
                    </div>

                    <!-- Reviewer Cards Grid -->
                    <div v-if="getReviewStats(abstract).reviews.length === 0" class="rounded-xl bg-white border border-slate-200 p-6 text-center text-slate-400 text-xs">
                        No reviewers have completed their reviews yet.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div
                            v-for="(rev, idx) in (getReviewStats(abstract).hasRevisions ? getReviewStats(abstract).allReviewers : getReviewStats(abstract).reviews)"
                            :key="idx"
                            class="rounded-xl border border-slate-200 bg-white p-3 flex flex-col justify-between space-y-2.5 shadow-2xs hover:shadow-xs transition"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-1.5">
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 text-xs break-words block leading-tight" :title="rev.reviewer_name">
                                            {{ rev.reviewer_name }}
                                        </span>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span class="text-[10px] text-slate-400">Reviewer #{{ idx + 1 }}</span>
                                            <span
                                                v-if="getReviewStats(abstract).hasRevisions"
                                                :class="[
                                                    'text-[9px] font-bold px-1.5 py-0.2 rounded border',
                                                    rev.round_number > 1
                                                        ? 'bg-amber-50 text-amber-800 border-amber-200'
                                                        : 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                                ]"
                                            >
                                                {{ rev.round_number > 1 ? `Round ${rev.round_number} (Revisi)` : 'Round 1 (Approved)' }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Specific recommendation badge -->
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-extrabold shrink-0 border shadow-2xs leading-tight',
                                        getReviewerRecommendationInfo(rev.recommendation).badgeCls
                                    ]">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getReviewerRecommendationInfo(rev.recommendation).dotCls"></span>
                                        <span>{{ getReviewerRecommendationInfo(rev.recommendation).label }}</span>
                                    </span>
                                </div>

                                <!-- Score Display -->
                                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                                    <span class="font-semibold text-slate-500">Total Score:</span>
                                    <span class="font-bold text-purple-950 bg-purple-50 px-2 py-0.5 rounded border border-purple-100">
                                        {{ rev.total_score !== null ? `${rev.total_score} / 10` : '-' }}
                                    </span>
                                </div>
                                <div v-if="rev.score_criteria_1 !== null" class="flex items-center justify-between text-[10px] text-slate-400 mt-0.5">
                                    <span>C1: {{ rev.score_criteria_1 }}/5</span>
                                    <span>C2: {{ rev.score_criteria_2 }}/5</span>
                                </div>
                            </div>

                            <!-- Comments -->
                            <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100 text-[11px] text-slate-700 leading-relaxed max-h-24 overflow-y-auto">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Reviewer Notes:</span>
                                <p class="whitespace-pre-wrap">{{ rev.comments || 'No written remarks.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Banner Status / Auto-Decision -->
                <div v-if="!isDecisionReady(abstract)" class="p-3.5 bg-amber-50 border border-amber-300 text-amber-950 text-xs rounded-2xl flex items-start gap-3 shadow-2xs">
                    <span class="material-symbols-outlined text-amber-600 text-lg shrink-0 mt-0.5">lock</span>
                    <div class="text-[11px] leading-relaxed">
                        <strong class="font-bold text-amber-900 block text-xs mb-0.5">Decision Form Locked</strong>
                        <template v-if="getReviewStats(abstract).roundNumber === 1">
                            Awaiting {{ getReviewStats(abstract).targetCount }} reviewers to complete evaluation (Currently: <strong>{{ getReviewStats(abstract).completedCount }}/{{ getReviewStats(abstract).targetCount }}</strong> completed). The system will automatically calculate consensus and enable decisions once all required reviewers complete their reviews.
                        </template>
                        <template v-else>
                            Awaiting all revision stage reviewers to complete evaluation (Currently: <strong>{{ getReviewStats(abstract).latestRoundCompleted }}/{{ getReviewStats(abstract).latestRoundTotal }}</strong> completed).
                        </template>
                    </div>
                </div>

                <div v-else class="p-4 bg-gradient-to-r from-purple-50/90 via-indigo-50/70 to-purple-50/90 border border-purple-200/90 text-purple-950 text-xs rounded-2xl shadow-2xs space-y-2">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-700 text-lg">auto_awesome</span>
                            <span class="font-extrabold text-xs text-purple-950">System Automated Recommendation</span>
                        </div>
                        
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-semibold text-purple-700">Calculated Outcome:</span>
                            <span :class="[
                                'px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider border shadow-2xs',
                                getSuggestedDecisionStatus(abstract) === 'revision_required' ? 'bg-amber-100 text-amber-900 border-amber-300' :
                                getSuggestedDecisionStatus(abstract) === 'rejected' ? 'bg-rose-100 text-rose-900 border-rose-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300'
                            ]">
                                {{ getSuggestedDecisionStatus(abstract) === 'revision_required' ? 'Revision Required' : getSuggestedDecisionStatus(abstract) === 'rejected' ? 'Rejected' : 'Accepted' }}
                            </span>
                            <span v-if="getSuggestedDecisionStatus(abstract) === 'accepted'" class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-purple-100 text-purple-900 border border-purple-300 shadow-2xs">
                                {{ getSuggestedPresentationType(abstract).label }} Presentation
                            </span>
                        </div>
                    </div>

                    <p class="text-[11px] text-purple-900 leading-relaxed bg-white/75 p-2.5 rounded-xl border border-purple-100">
                        <strong>System Summary:</strong>
                        <span v-if="getSuggestedDecisionStatus(abstract) === 'revision_required'">
                            {{ getVotesBreakdown(abstract).revision }} reviewer(s) requested revisions. Submission is directed to the <strong>Revision</strong> stage for author updates.
                        </span>
                        <span v-else-if="getSuggestedDecisionStatus(abstract) === 'rejected'">
                            {{ getVotesBreakdown(abstract).reject }} reviewer(s) recommended rejecting this manuscript.
                        </span>
                        <span v-else>
                            All reviewers accepted this manuscript. Recommended presentation type: <strong>{{ getSuggestedPresentationType(abstract).label }}</strong> ({{ getSuggestedPresentationType(abstract).reason }}).
                        </span>
                    </p>
                </div>

                <!-- Section 3: Final Decision Determination (Admin Override) -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4 shadow-2xs">
                    <div class="border-b border-slate-100 pb-2">
                        <h4 class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-purple-700">tune</span>
                            <span>Final Decision Determination</span>
                        </h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            The form below is pre-filled based on system recommendations. You may adjust or confirm the decision:
                        </p>
                    </div>

                    <!-- Interactive Status Selection Cards -->
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-2">
                            Select Decision Status <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <!-- Card 1: Accepted -->
                            <div
                                @click="isDecisionReady(abstract) ? reviewForm.status = 'accepted' : null"
                                :class="[
                                    'relative flex flex-col p-3 rounded-xl border-2 transition cursor-pointer select-none text-left',
                                    reviewForm.status === 'accepted'
                                        ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/20 shadow-xs'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 bg-white',
                                    !isDecisionReady(abstract) ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs" :class="reviewForm.status === 'accepted' ? 'text-emerald-950 font-extrabold' : 'text-slate-800'">
                                        Accepted
                                    </span>
                                    <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="reviewForm.status === 'accepted' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300'">
                                        <span v-if="reviewForm.status === 'accepted'" class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-snug">Approved for conference presentation</p>
                                <span v-if="getSuggestedDecisionStatus(abstract) === 'accepted'" class="mt-2 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200 self-start">
                                    System Suggestion
                                </span>
                            </div>

                            <!-- Card 2: Revision Required -->
                            <div
                                @click="isDecisionReady(abstract) ? reviewForm.status = 'revision_required' : null"
                                :class="[
                                    'relative flex flex-col p-3 rounded-xl border-2 transition cursor-pointer select-none text-left',
                                    reviewForm.status === 'revision_required'
                                        ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/20 shadow-xs'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 bg-white',
                                    !isDecisionReady(abstract) ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs" :class="reviewForm.status === 'revision_required' ? 'text-amber-950 font-extrabold' : 'text-slate-800'">
                                        Revision Required
                                    </span>
                                    <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="reviewForm.status === 'revision_required' ? 'border-amber-600 bg-amber-600 text-white' : 'border-slate-300'">
                                        <span v-if="reviewForm.status === 'revision_required'" class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-snug">Author must submit revised manuscript</p>
                                <span v-if="getSuggestedDecisionStatus(abstract) === 'revision_required'" class="mt-2 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-200 self-start">
                                    System Suggestion
                                </span>
                            </div>

                            <!-- Card 3: Rejected -->
                            <div
                                @click="isDecisionReady(abstract) ? reviewForm.status = 'rejected' : null"
                                :class="[
                                    'relative flex flex-col p-3 rounded-xl border-2 transition cursor-pointer select-none text-left',
                                    reviewForm.status === 'rejected'
                                        ? 'border-rose-500 bg-rose-50/70 ring-2 ring-rose-500/20 shadow-xs'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 bg-white',
                                    !isDecisionReady(abstract) ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs" :class="reviewForm.status === 'rejected' ? 'text-rose-950 font-extrabold' : 'text-slate-800'">
                                        Rejected
                                    </span>
                                    <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0" :class="reviewForm.status === 'rejected' ? 'border-rose-600 bg-rose-600 text-white' : 'border-slate-300'">
                                        <span v-if="reviewForm.status === 'rejected'" class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-snug">Manuscript does not meet criteria</p>
                                <span v-if="getSuggestedDecisionStatus(abstract) === 'rejected'" class="mt-2 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-200 self-start">
                                    System Suggestion
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Presentation Type Selection (Visible only when Accepted) -->
                    <div v-if="reviewForm.status === 'accepted'" class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <label class="block font-bold text-purple-950 text-xs">
                                Select Presentation Type <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500">
                                Reviewer Votes: <strong class="text-purple-900 font-bold">{{ getVotesBreakdown(abstract).oral }} Oral</strong> vs <strong class="text-blue-900 font-bold">{{ getVotesBreakdown(abstract).poster }} Poster</strong>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Card Oral -->
                            <div
                                @click="isDecisionReady(abstract) ? reviewForm.presentation_type = 'oral' : null"
                                :class="[
                                    'relative flex items-center justify-between p-3.5 rounded-xl border-2 transition cursor-pointer select-none',
                                    reviewForm.presentation_type === 'oral'
                                        ? 'border-purple-600 bg-purple-50/70 ring-2 ring-purple-600/20 shadow-xs'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 bg-white',
                                    !isDecisionReady(abstract) ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <div>
                                    <span class="font-extrabold text-xs block text-slate-900">Oral Presentation</span>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ getVotesBreakdown(abstract).oral }} Reviewer(s) selected Oral</span>
                                </div>

                                <div class="flex flex-col items-end gap-1">
                                    <span v-if="getSuggestedPresentationType(abstract).type === 'oral'" class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-purple-100 text-purple-900 border border-purple-200">
                                        Majority Vote
                                    </span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="reviewForm.presentation_type === 'oral' ? 'border-purple-600 bg-purple-600 text-white' : 'border-slate-300'">
                                        <span v-if="reviewForm.presentation_type === 'oral'" class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Card Poster -->
                            <div
                                @click="isDecisionReady(abstract) ? reviewForm.presentation_type = 'poster' : null"
                                :class="[
                                    'relative flex items-center justify-between p-3.5 rounded-xl border-2 transition cursor-pointer select-none',
                                    reviewForm.presentation_type === 'poster'
                                        ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-600/20 shadow-xs'
                                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 bg-white',
                                    !isDecisionReady(abstract) ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <div>
                                    <span class="font-extrabold text-xs block text-slate-900">Poster Presentation</span>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ getVotesBreakdown(abstract).poster }} Reviewer(s) selected Poster</span>
                                </div>

                                <div class="flex flex-col items-end gap-1">
                                    <span v-if="getSuggestedPresentationType(abstract).type === 'poster'" class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-900 border border-blue-200">
                                        Majority Vote
                                    </span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="reviewForm.presentation_type === 'poster' ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300'">
                                        <span v-if="reviewForm.presentation_type === 'poster'" class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decision Notes / Feedback for Author -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5 gap-2 flex-wrap">
                            <label class="block font-bold text-slate-800 text-xs">
                                Decision Notes for Author
                            </label>
                            <button
                                type="button"
                                @click="copyReviewerNotesToForm"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 hover:text-purple-900 hover:underline cursor-pointer"
                                title="Copy all reviewer comments to this form"
                            >
                                 <span>Copy Reviewer Notes</span>
                            </button>
                        </div>
                        <textarea
                            v-model="reviewForm.review_notes"
                            rows="3"
                            class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400"
                            placeholder="Official feedback or remarks for the author regarding this decision (optional)..."
                            :disabled="!isDecisionReady(abstract)"
                        ></textarea>
                    </div>
                </div>
            </form>

            <!-- Modal Sticky Footer -->
            <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-100 bg-slate-50/90 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-500 font-mono font-bold">
                        {{ abstract.abstract_code }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="text-[11px] font-semibold text-slate-600">
                        Status: <strong class="text-slate-900 capitalize">{{ reviewForm.status.replace('_', ' ') }}</strong>
                        <span v-if="reviewForm.status === 'accepted'"> ({{ reviewForm.presentation_type === 'poster' ? 'Poster' : 'Oral' }})</span>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer shadow-2xs transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submitReview"
                        :disabled="reviewForm.processing || !isDecisionReady(abstract)"
                        class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer shadow-xs flex items-center gap-1.5"
                    >
                        <span v-if="reviewForm.processing" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                        <span>{{ reviewForm.processing ? 'Saving...' : (!isDecisionReady(abstract) ? (getReviewStats(abstract).roundNumber === 1 ? 'Waiting for 3 Reviews' : 'Waiting for Reviews') : (['accepted', 'revision_required', 'rejected'].includes(abstract.status) ? 'Update Decision' : 'Save Decision')) }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
