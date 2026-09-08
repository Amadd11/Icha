<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';
import AnimatedCounter from '@/Components/UI/AnimatedCounter.vue';

const props = defineProps({
    stats: Object,
    assignments: [Array, Object],
});

const isModalOpen = ref(false);
const activeAssignment = ref(null);
const activePaper = ref(null);

const form = useForm({
    score_criteria_1: '',
    score_criteria_2: '',
    recommendation: 'ACCEPTED',
    summary: '',
});

const computedTotalScore = computed(() => {
    const s1 = parseInt(form.score_criteria_1) || 0;
    const s2 = parseInt(form.score_criteria_2) || 0;
    return s1 + s2;
});

const RECOMMENDATION_GROUPS = {
    accept: ['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED'],
    revision: ['REVISION', 'REVISION_REQUIRED'],
    reject: ['REJECT', 'REJECTED'],
};

function getRecommendationCategory(rec) {
    const upper = (rec || '').toUpperCase();
    for (const [cat, values] of Object.entries(RECOMMENDATION_GROUPS)) {
        if (values.includes(upper)) return cat;
    }
    return 'other';
}

function getRecommendationStyle(rec) {
    const styles = {
        accept:   { cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500', label: 'Accepted' },
        revision: { cls: 'bg-amber-50 text-amber-700 border-amber-200',       dot: 'bg-amber-500',   label: 'Revision' },
        reject:   { cls: 'bg-rose-50 text-rose-700 border-rose-200',          dot: 'bg-rose-500',    label: 'Rejected' },
        other:    { cls: 'bg-purple-50 text-purple-700 border-purple-100',     dot: 'bg-purple-500',  label: rec || 'Reviewed' },
    };
    return styles[getRecommendationCategory(rec)] || styles.other;
}

const assignmentList = computed(() => {
    if (Array.isArray(props.assignments)) return props.assignments;
    if (props.assignments?.data) return props.assignments.data;
    return [];
});

const currentTab = ref('all');

const filteredAssignments = computed(() => {
    const list = assignmentList.value;
    if (currentTab.value === 'pending') {
        return list.filter(a => a.status === 'assigned');
    }
    if (currentTab.value === 'completed') {
        return list.filter(a => a.status === 'completed');
    }
    return list;
});

function getSubmission(assignment) {
    return assignment?.submission || assignment?.round?.submission || {};
}

function openReviewModal(assignment) {
    activeAssignment.value = assignment;
    activePaper.value = getSubmission(assignment);
    
    if (assignment.review) {
        form.score_criteria_1 = assignment.review.score_criteria_1 || '';
        form.score_criteria_2 = assignment.review.score_criteria_2 || '';
        const REC_MAP = {
            ACCEPT: 'ACCEPTED',
            ACCEPTED: 'ACCEPTED',
            ORAL: 'ACCEPTED',
            POSTER: 'ACCEPTED',
            REVISION: 'REVISION',
            REVISION_REQUIRED: 'REVISION',
            REJECT: 'REJECT',
            REJECTED: 'REJECT',
        };
        form.recommendation = REC_MAP[(assignment.review.recommendation || '').toUpperCase()] ?? 'ACCEPTED';
        form.summary = assignment.review.summary || '';
    } else {
        form.reset();
        form.recommendation = 'ACCEPTED';
    }
    
    form.clearErrors();
    isModalOpen.value = true;
}

function submitReview() {
    if (!activeAssignment.value) return;

    form.post(route('reviewer.assignments.review', activeAssignment.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
}
</script>

<template>
    <Head title="Full Paper Reviews - Reviewer" />

    <ReviewerLayout>
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Full Paper Reviews
            </h1>
            <p class="text-slate-500 text-xs mt-1 font-medium">
                Selamat datang, {{ $page.props.auth.user.name }}! Tinjau naskah lengkap artikel ilmiah yang ditugaskan kepada Anda.
            </p>
        </div>

        <!-- Stats Grid (Clickable to switch tab) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
            
            <!-- Total Assigned -->
            <div
                @click="currentTab = 'all'"
                class="animate-fade-in-up animation-delay-100 bg-white rounded-3xl p-6 shadow-sm border transition-all duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                :class="currentTab === 'all' ? 'border-purple-600 ring-2 ring-purple-600/20 shadow-md' : 'border-slate-200/80 hover:border-purple-300'"
            >
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Ditugaskan</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-slate-900">
                        <AnimatedCounter :value="stats?.total_assigned ?? 0" />
                    </span>
                    <span class="text-xs font-bold text-primary">Papers</span>
                </div>
            </div>

            <!-- Pending -->
            <div
                @click="currentTab = 'pending'"
                class="animate-fade-in-up animation-delay-200 bg-white rounded-3xl p-6 shadow-sm border transition-all duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                :class="currentTab === 'pending' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md' : 'border-slate-200/80 hover:border-amber-300'"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Perlu Dinilai</span>
                    <span v-if="(stats?.pending_reviews ?? 0) > 0" class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-amber-600">
                        <AnimatedCounter :value="stats?.pending_reviews ?? 0" />
                    </span>
                    <span class="text-xs font-bold text-amber-600">Menunggu Review</span>
                </div>
            </div>

            <!-- Completed -->
            <div
                @click="currentTab = 'completed'"
                class="animate-fade-in-up animation-delay-300 bg-white rounded-3xl p-6 shadow-sm border transition-all duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                :class="currentTab === 'completed' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'border-slate-200/80 hover:border-emerald-300'"
            >
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Selesai Dinilai</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-emerald-600">
                        <AnimatedCounter :value="stats?.completed_reviews ?? 0" />
                    </span>
                    <span class="text-xs font-semibold text-emerald-600">Tersimpan</span>
                </div>
            </div>

        </div>

        <!-- Assigned Submissions Table with Filter Tabs -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">
                        Assigned Full Papers
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Tinjau naskah lengkap artikel dan dokumen paper yang ditugaskan kepada Anda.
                    </p>
                </div>
                
                <!-- Filter Tabs -->
                <div class="flex items-center gap-1.5 bg-slate-100/80 p-1 rounded-2xl border border-slate-200/60">
                    <button
                        type="button"
                        @click="currentTab = 'all'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5',
                            currentTab === 'all'
                                ? 'bg-white text-purple-900 shadow-xs border border-purple-200/60'
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        <span>Semua</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-slate-200 text-slate-700">
                            {{ stats?.total_assigned ?? 0 }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'pending'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5',
                            currentTab === 'pending'
                                ? 'bg-white text-amber-900 shadow-xs border border-amber-200/60'
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        <span>🟡 Perlu Dinilai</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                            {{ stats?.pending_reviews ?? 0 }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="currentTab = 'completed'"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5',
                            currentTab === 'completed'
                                ? 'bg-white text-emerald-900 shadow-xs border border-emerald-200/60'
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        <span>🟢 Riwayat Selesai</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                            {{ stats?.completed_reviews ?? 0 }}
                        </span>
                    </button>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="!filteredAssignments || filteredAssignments.length === 0" class="p-12 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400 font-bold text-lg">
                    {{ currentTab === 'pending' ? '🎉' : '📝' }}
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">
                    {{ currentTab === 'pending' ? 'Semua naskah paper telah selesai dinilai!' : currentTab === 'completed' ? 'Belum ada naskah yang selesai dinilai.' : 'Tidak ada naskah yang ditugaskan.' }}
                </h3>
                <p class="text-slate-500 text-xs">
                    {{ currentTab === 'pending' ? 'Tidak ada tugas telaah paper yang menunggu tindakan Anda saat ini.' : 'Naskah paper yang telah selesai Anda beri skor akan tercatat di tab ini.' }}
                </p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Code & Track</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Title</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase whitespace-nowrap">
                                Paper
                            </th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-center">Penilaian</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(assignment, index) in filteredAssignments"
                            :key="assignment.id"
                            class="table-row-stagger hover:bg-slate-50/50 transition"
                            :style="{ animationDelay: `${Math.min(index * 90 + 200, 900)}ms` }"
                        >
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-xs font-bold text-purple-900">{{ getSubmission(assignment).abstract_code }}</span>
                                    <!-- Revision Badge (Only shown during revision) -->
                                    <span
                                        v-if="(assignment.round?.round_number ?? 1) > 1"
                                        class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                                    >
                                        Revisi {{ (assignment.round?.round_number ?? 2) - 1 }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-purple-600 font-semibold mt-0.5">{{ getSubmission(assignment).category?.name || 'Scientific Track' }}</div>
                            </td>
                            <td class="px-5 py-3.5 max-w-md">
                                <div class="font-bold text-slate-900 text-xs line-clamp-2" :title="getSubmission(assignment).title">
                                    {{ getSubmission(assignment).title }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <a
                                    v-if="getSubmission(assignment).file_path"
                                    :href="formatStorageUrl(getSubmission(assignment).file_path)"
                                    target="_blank"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg border border-purple-200 bg-purple-50 text-purple-900 font-bold text-xs hover:bg-purple-100 transition shadow-2xs"
                                >
                                    Download
                                </a>
                                <span v-else class="text-xs text-slate-400 italic">No file</span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div v-if="assignment.status === 'completed' && assignment.review" class="inline-flex flex-col items-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wide border shadow-2xs',
                                            getRecommendationStyle(assignment.review.recommendation).cls
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getRecommendationStyle(assignment.review.recommendation).dot"></span>
                                        {{ getRecommendationStyle(assignment.review.recommendation).label }}
                                    </span>
                                    <span
                                        v-if="assignment.review.score_criteria_1 !== null && assignment.review.score_criteria_2 !== null"
                                        class="text-[10px] text-slate-500 font-medium mt-0.5"
                                    >
                                        Skor: {{ (parseInt(assignment.review.score_criteria_1) || 0) + (parseInt(assignment.review.score_criteria_2) || 0) }}/10
                                    </span>
                                </div>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide border bg-amber-50/80 text-amber-700 border-amber-200/80"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    Belum Dinilai
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <button
                                    @click="openReviewModal(assignment)"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer"
                                    :class="assignment.status === 'completed' ? 'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100' : 'bg-purple-50 text-purple-700 border border-purple-200/90 hover:bg-purple-100'"
                                >
                                    {{ assignment.status === 'completed' ? (assignment.is_decided ? 'Lihat Nilai' : 'Edit Nilai') : 'Nilai Sekarang' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📝 Full Paper Review Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-bold text-purple-700 uppercase">{{ activePaper?.abstract_code }}</span>
                            <span
                                v-if="(activeAssignment?.round?.round_number ?? 1) > 1"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                            >
                                Revisi {{ (activeAssignment?.round?.round_number ?? 2) - 1 }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm">
                            Full Paper Peer Review Form
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Blinded Paper Details & History -->
                    <div class="space-y-4">
                        <div v-if="(activeAssignment?.round?.round_number ?? 1) > 1" class="bg-amber-50/90 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                            <span class="font-bold">📝 Naskah Hasil Revisi (Revisi {{ (activeAssignment?.round?.round_number ?? 2) - 1 }}):</span>
                            <p class="mt-0.5 text-[11px]">Penulis telah memperbarui naskah sesuai masukan telaah. Silakan periksa naskah revisi dan lampiran terbaru di bawah ini.</p>
                        </div>

                        <!-- Paper Document Box (Prominent) -->
                        <div v-if="activePaper?.file_path" class="p-3.5 bg-purple-50/90 border border-purple-200 rounded-2xl flex items-center justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold text-purple-950 block">
                                    Paper Document
                                </span>
                                <span class="text-[11px] text-purple-700">
                                    Unduh dokumen paper lengkap untuk ditinjau.
                                </span>
                            </div>
                            <a
                                :href="formatStorageUrl(activePaper.file_path)"
                                target="_blank"
                                class="inline-flex items-center rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold px-3.5 py-2 text-xs transition shrink-0 shadow-xs"
                            >
                                Download
                            </a>
                        </div>

                        <div class="bg-purple-50/70 rounded-2xl p-4 border border-purple-100">
                            <span class="text-[10px] font-bold uppercase text-purple-700 tracking-wider">Track: {{ activePaper?.category?.name || 'General' }}</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-1 leading-snug">{{ activePaper?.title }}</h4>
                        </div>
                        
                        <div v-if="activePaper?.keywords">
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">Keywords:</span>
                            <p class="text-xs text-slate-800 font-semibold">{{ activePaper.keywords }}</p>
                        </div>

                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">
                                Abstract Summary:
                            </span>
                            <div v-if="activePaper?.abstract_text" class="bg-slate-50 rounded-xl p-3.5 text-xs text-slate-700 leading-relaxed border border-slate-200 max-h-56 overflow-y-auto whitespace-pre-wrap">
                                {{ activePaper.abstract_text }}
                            </div>
                            <div v-else class="bg-slate-50 rounded-xl p-3.5 text-xs text-slate-400 italic border border-slate-200">
                                Paper content is provided in the attached document. Please download and review the file above.
                            </div>
                        </div>

                        <!-- 📜 Riwayat Telaah Sebelumnya -->
                        <div v-if="activeAssignment?.previous_history?.length" class="mt-4 pt-4 border-t border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>📜</span> Riwayat Telaah Sebelumnya
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold">{{ activeAssignment.previous_history.length }} telaah terdahulu</span>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(hist, hIdx) in activeAssignment.previous_history"
                                    :key="hIdx"
                                    class="p-3 rounded-2xl border border-slate-200/90 bg-white space-y-1.5 shadow-2xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-slate-900">
                                                {{ hist.round_number === 1 ? 'Telaah Awal' : 'Revisi ' + (hist.round_number - 1) }}
                                            </span>
                                            <span v-if="hist.review?.updated_at" class="text-[10px] text-slate-400 font-medium">
                                                • {{ hist.review.updated_at }}
                                            </span>
                                        </div>
                                        <span
                                            v-if="hist.review"
                                            :class="[
                                                'inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full border',
                                                getRecommendationStyle(hist.review.recommendation).cls
                                            ]"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="getRecommendationStyle(hist.review.recommendation).dot"></span>
                                            {{ getRecommendationStyle(hist.review.recommendation).label }}
                                        </span>
                                        <span v-else class="text-[10px] text-slate-400 italic">Belum dinilai</span>
                                    </div>

                                    <div v-if="hist.review" class="space-y-1.5">
                                        <div class="flex items-center gap-3 text-xs text-slate-600 font-medium">
                                            <span>Skor: <strong class="text-purple-900 font-bold">{{ hist.review.total_score }}/10</strong></span>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-[11px] text-slate-500">K1: {{ hist.review.score_criteria_1 }}/5</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-[11px] text-slate-500">K2: {{ hist.review.score_criteria_2 }}/5</span>
                                        </div>
                                        <div v-if="hist.review.summary" class="bg-slate-50 rounded-xl p-2.5 text-xs text-slate-700 border border-slate-200/80 leading-relaxed whitespace-pre-wrap">
                                            <span class="font-bold text-slate-800 block text-[10px] uppercase tracking-wider mb-0.5">Catatan {{ hist.round_number === 1 ? 'Telaah Awal' : 'Revisi ' + (hist.round_number - 1) }}:</span>
                                            {{ hist.review.summary }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Scoring Form -->
                    <form @submit.prevent="submitReview" class="flex flex-col justify-between space-y-4 bg-slate-50/50 border border-slate-200 rounded-2xl p-5">
                        
                        <div v-if="activeAssignment?.is_decided" class="bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold p-3 rounded-xl">
                            🔒 Keputusan final telah ditetapkan oleh Admin. Anda hanya dapat melihat riwayat penilaian.
                        </div>

                        <div class="space-y-3.5">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Originality (1-5) <span class="text-red-500">*</span></label>
                                    <select v-model="form.score_criteria_1" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="activeAssignment?.is_decided">
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
                                    <select v-model="form.score_criteria_2" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="activeAssignment?.is_decided">
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

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <!-- Accepted -->
                                    <label
                                        :class="[
                                            'relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none',
                                            form.recommendation === 'ACCEPTED'
                                                ? 'bg-emerald-50/90 border-emerald-500 ring-2 ring-emerald-500/20 shadow-xs'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                        ]"
                                    >
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span class="text-xs font-bold text-emerald-950">Accepted</span>
                                            </div>
                                            <input
                                                type="radio"
                                                name="recommendation"
                                                value="ACCEPTED"
                                                v-model="form.recommendation"
                                                :disabled="activeAssignment?.is_decided"
                                                class="text-emerald-600 focus:ring-emerald-500 text-xs"
                                            />
                                        </div>
                                        <div class="text-[11px] text-slate-500 leading-tight">Diterima tanpa revisi mayor</div>
                                    </label>

                                    <!-- Revision Required -->
                                    <label
                                        :class="[
                                            'relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none',
                                            form.recommendation === 'REVISION'
                                                ? 'bg-amber-50/90 border-amber-500 ring-2 ring-amber-500/20 shadow-xs'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                        ]"
                                    >
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                <span class="text-xs font-bold text-amber-950">Revision</span>
                                            </div>
                                            <input
                                                type="radio"
                                                name="recommendation"
                                                value="REVISION"
                                                v-model="form.recommendation"
                                                :disabled="activeAssignment?.is_decided"
                                                class="text-amber-600 focus:ring-amber-500 text-xs"
                                            />
                                        </div>
                                        <div class="text-[11px] text-slate-500 leading-tight">Memerlukan revisi author</div>
                                    </label>

                                    <!-- Rejected -->
                                    <label
                                        :class="[
                                            'relative flex flex-col p-3 rounded-xl border cursor-pointer transition select-none',
                                            form.recommendation === 'REJECT'
                                                ? 'bg-rose-50/90 border-rose-500 ring-2 ring-rose-500/20 shadow-xs'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
                                        ]"
                                    >
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                                <span class="text-xs font-bold text-rose-950">Rejected</span>
                                            </div>
                                            <input
                                                type="radio"
                                                name="recommendation"
                                                value="REJECT"
                                                v-model="form.recommendation"
                                                :disabled="activeAssignment?.is_decided"
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
                                    :disabled="activeAssignment?.is_decided"
                                ></textarea>
                                <p v-if="form.errors.summary" class="text-red-500 text-[10px] mt-1">{{ form.errors.summary }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                            <button type="button" @click="isModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                v-if="!activeAssignment?.is_decided"
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ form.processing ? 'Menyimpan...' : (activeAssignment?.review ? 'Perbarui Penilaian' : 'Kirim Penilaian') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </ReviewerLayout>
</template>
