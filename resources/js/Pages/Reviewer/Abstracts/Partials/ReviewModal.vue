<script setup>
import { computed, watch, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { formatStorageUrl } from '@/Utils/formatters';
import ReviewHistoryTimeline from './ReviewHistoryTimeline.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    assignment: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

function getSubmission(assignment) {
    return assignment?.submission || assignment?.round?.submission || {};
}

const activeAbstract = computed(() => getSubmission(props.assignment));

const form = useForm({
    score_criteria_1: '',
    score_criteria_2: '',
    recommendation: 'ORAL',
    summary: '',
});

const computedTotalScore = computed(() => {
    const s1 = parseInt(form.score_criteria_1) || 0;
    const s2 = parseInt(form.score_criteria_2) || 0;
    return s1 + s2;
});

const REC_MAP = {
    ORAL: 'ORAL',
    POSTER: 'POSTER',
    ACCEPT: 'ORAL',
    ACCEPTED: 'ORAL',
    REVISION: 'REVISION',
    REVISION_REQUIRED: 'REVISION',
    REJECT: 'REJECT',
    REJECTED: 'REJECT',
};

watch(
    () => props.show,
    (val) => {
        if (typeof document !== 'undefined') {
            document.body.classList.toggle('overflow-hidden', val);
        }

        if (val && props.assignment) {
            if (props.assignment.review) {
                form.score_criteria_1 = props.assignment.review.score_criteria_1 || '';
                form.score_criteria_2 = props.assignment.review.score_criteria_2 || '';
                form.recommendation = REC_MAP[(props.assignment.review.recommendation || '').toUpperCase()] ?? 'ORAL';
                form.summary = props.assignment.review.summary || '';
            } else {
                form.reset();
                form.recommendation = 'ORAL';
            }
            form.clearErrors();
        }
    }
);

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.body.classList.remove('overflow-hidden');
    }
});

function handleClose() {
    emit('close');
}

function submitReview() {
    if (!props.assignment) return;

    form.post(route('reviewer.assignments.review', props.assignment.id), {
        preserveScroll: true,
        onSuccess: () => {
            handleClose();
        },
    });
}
</script>

<template>
    <div
        v-if="show"
        @click.self="handleClose"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 overflow-y-auto"
    >
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-auto animate-fade-in">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-bold text-purple-700 uppercase">{{ activeAbstract?.abstract_code }}</span>
                        <span
                            v-if="(assignment?.round?.round_number ?? 1) > 1"
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                        >
                            Revisi {{ (assignment?.round?.round_number ?? 2) - 1 }}
                        </span>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">
                        Abstract Peer Review Form
                    </h3>
                </div>
                <button
                    type="button"
                    @click="handleClose"
                    class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Blinded Abstract Details & History -->
                <div class="space-y-4">
                    <div v-if="(assignment?.round?.round_number ?? 1) > 1" class="bg-amber-50/90 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                        <span class="font-bold">📝 Naskah Hasil Revisi (Revisi {{ (assignment?.round?.round_number ?? 2) - 1 }}):</span>
                        <p class="mt-0.5 text-[11px]">Penulis telah memperbarui naskah sesuai masukan telaah. Silakan periksa naskah revisi dan lampiran terbaru di bawah ini.</p>
                    </div>

                    <!-- Submission Document Box (Prominent) -->
                    <div v-if="activeAbstract?.file_path" class="p-3.5 bg-purple-50/90 border border-purple-200 rounded-2xl flex items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-purple-950 block">
                                Abstract Document
                            </span>
                            <span class="text-[11px] text-purple-700">
                                Unduh dokumen naskah abstrak untuk ditinjau.
                            </span>
                        </div>
                        <a
                            :href="formatStorageUrl(activeAbstract.file_path)"
                            target="_blank"
                            class="inline-flex items-center rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold px-3.5 py-2 text-xs transition shrink-0 shadow-xs"
                        >
                            Download
                        </a>
                    </div>

                    <div class="bg-purple-50/70 rounded-2xl p-4 border border-purple-100">
                        <span class="text-[10px] font-bold uppercase text-purple-700 tracking-wider">Track: {{ activeAbstract?.category?.name || 'General' }}</span>
                        <h4 class="text-sm font-bold text-slate-900 mt-1 leading-snug">{{ activeAbstract?.title }}</h4>
                    </div>

                    <!-- Review Criteria Guidelines Notes -->
                    <div class="rounded-2xl border border-purple-100 bg-purple-50/40 p-4 space-y-2.5">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-900">
                                Review Criteria Guidelines
                            </span>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="bg-white p-3 rounded-xl border border-purple-100 shadow-2xs">
                                <span class="font-bold text-slate-900 text-xs block mb-0.5">Originality:</span>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Novelty of the idea, relevance of the issue to hospital governance, theoretical/practical contribution, and research originality.
                                </p>
                            </div>

                            <div class="bg-white p-3 rounded-xl border border-purple-100 shadow-2xs">
                                <span class="font-bold text-slate-900 text-xs block mb-0.5">Methodology:</span>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Appropriateness of the research design, adequacy of data/samples, depth of analysis, systematic structure, and clarity of conclusions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeAbstract?.keywords">
                        <span class="text-[11px] font-bold text-slate-500 block mb-1">Keywords:</span>
                        <p class="text-xs text-slate-800 font-semibold">{{ activeAbstract.keywords }}</p>
                    </div>

                    <div v-if="activeAbstract?.abstract_text">
                        <span class="text-[11px] font-bold text-slate-500 block mb-1">
                            Abstract Content:
                        </span>
                        <div class="bg-slate-50 rounded-xl p-3.5 text-xs text-slate-700 leading-relaxed border border-slate-200 max-h-56 overflow-y-auto whitespace-pre-wrap">
                            {{ activeAbstract.abstract_text }}
                        </div>
                    </div>

                    <!-- 📜 Riwayat Telaah Sebelumnya -->
                    <ReviewHistoryTimeline :history="assignment?.previous_history" />
                </div>

                <!-- Right: Scoring Form -->
                <form @submit.prevent="submitReview" class="flex flex-col justify-between space-y-4 bg-slate-50/50 border border-slate-200 rounded-2xl p-5">
                    <div v-if="assignment?.is_decided" class="bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold p-3 rounded-xl">
                        🔒 Keputusan naskah telah ditetapkan. Anda hanya dapat melihat riwayat penilaian.
                    </div>

                    <div class="space-y-3.5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Originality (1-5) <span class="text-red-500">*</span></label>
                                <select v-model="form.score_criteria_1" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="assignment?.is_decided">
                                    <option value="" disabled>Select</option>
                                    <option value="1">1 - Poor</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="3">3 - Good</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="5">5 - Excellent</option>
                                </select>
                                <p v-if="form.errors.score_criteria_1" class="text-red-500 text-[10px] mt-1">{{ form.errors.score_criteria_1 }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Methodology (1-5) <span class="text-red-500">*</span></label>
                                <select v-model="form.score_criteria_2" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="assignment?.is_decided">
                                    <option value="" disabled>Select</option>
                                    <option value="1">1 - Poor</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="3">3 - Good</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="5">5 - Excellent</option>
                                </select>
                                <p v-if="form.errors.score_criteria_2" class="text-red-500 text-[10px] mt-1">{{ form.errors.score_criteria_2 }}</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">
                                    Rekomendasi Keputusan <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500 font-medium">
                                    Total Score: <strong class="text-purple-900 font-bold">{{ computedTotalScore }}/10</strong>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <!-- Accept (Oral) -->
                                <label
                                    :class="[
                                        'relative flex flex-col p-2.5 rounded-xl border cursor-pointer transition select-none',
                                        form.recommendation === 'ORAL'
                                            ? 'bg-emerald-50/90 border-emerald-500 ring-2 ring-emerald-500/20 shadow-xs'
                                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                        assignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                    ]"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs font-bold text-emerald-950">Accept (Oral)</span>
                                        </div>
                                        <input
                                            type="radio"
                                            name="recommendation"
                                            value="ORAL"
                                            v-model="form.recommendation"
                                            :disabled="assignment?.is_decided"
                                            class="text-emerald-600 focus:ring-emerald-500 text-xs"
                                        />
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight">Presentasi lisan / oral</div>
                                </label>

                                <!-- Accept (Poster) -->
                                <label
                                    :class="[
                                        'relative flex flex-col p-2.5 rounded-xl border cursor-pointer transition select-none',
                                        form.recommendation === 'POSTER'
                                            ? 'bg-blue-50/90 border-blue-500 ring-2 ring-blue-500/20 shadow-xs'
                                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                        assignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                    ]"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            <span class="text-xs font-bold text-blue-950">Accept (Poster)</span>
                                        </div>
                                        <input
                                            type="radio"
                                            name="recommendation"
                                            value="POSTER"
                                            v-model="form.recommendation"
                                            :disabled="assignment?.is_decided"
                                            class="text-blue-600 focus:ring-blue-500 text-xs"
                                        />
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight">Presentasi poster</div>
                                </label>

                                <!-- Revision Required -->
                                <label
                                    :class="[
                                        'relative flex flex-col p-2.5 rounded-xl border cursor-pointer transition select-none',
                                        form.recommendation === 'REVISION'
                                            ? 'bg-amber-50/90 border-amber-500 ring-2 ring-amber-500/20 shadow-xs'
                                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                        assignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                    ]"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span class="text-xs font-bold text-amber-950">Revision Required</span>
                                        </div>
                                        <input
                                            type="radio"
                                            name="recommendation"
                                            value="REVISION"
                                            v-model="form.recommendation"
                                            :disabled="assignment?.is_decided"
                                            class="text-amber-600 focus:ring-amber-500 text-xs"
                                        />
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight">Memerlukan revisi author</div>
                                </label>

                                <!-- Reject -->
                                <label
                                    :class="[
                                        'relative flex flex-col p-2.5 rounded-xl border cursor-pointer transition select-none',
                                        form.recommendation === 'REJECT'
                                            ? 'bg-rose-50/90 border-rose-500 ring-2 ring-rose-500/20 shadow-xs'
                                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                        assignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                    ]"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span class="text-xs font-bold text-rose-950">Reject</span>
                                        </div>
                                        <input
                                            type="radio"
                                            name="recommendation"
                                            value="REJECT"
                                            v-model="form.recommendation"
                                            :disabled="assignment?.is_decided"
                                            class="text-rose-600 focus:ring-rose-500 text-xs"
                                        />
                                    </div>
                                    <div class="text-[11px] text-slate-500 leading-tight">Ditolak / tidak layak</div>
                                </label>
                            </div>
                            <p v-if="form.errors.recommendation" class="text-red-500 text-[10px] mt-1">{{ form.errors.recommendation }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Review Feedback / Summary</label>
                            <textarea
                                v-model="form.summary"
                                rows="4"
                                class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700"
                                placeholder="Constructive feedback for the author..."
                                :disabled="assignment?.is_decided"
                            ></textarea>
                            <p v-if="form.errors.summary" class="text-red-500 text-[10px] mt-1">{{ form.errors.summary }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="handleClose" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button
                            v-if="!assignment?.is_decided"
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                        >
                            {{ form.processing ? 'Menyimpan...' : (assignment?.review ? 'Perbarui Penilaian' : 'Kirim Penilaian') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
