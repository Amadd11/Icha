<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';
import AnimatedCounter from '@/Components/UI/AnimatedCounter.vue';

const props = defineProps({
    stats: Object,
    assignments: [Array, Object],
    categories: Array,
});

const isModalOpen = ref(false);
const activeAssignment = ref(null);
const activeAbstract = ref(null);
const selectedCategoryId = ref('all');
const searchQuery = ref('');

watch(isModalOpen, (val) => {
    if (typeof document !== 'undefined') {
        document.body.classList.toggle('overflow-hidden', val);
    }
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.body.classList.remove('overflow-hidden');
    }
});

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

function getSubmission(assignment) {
    return assignment?.submission || assignment?.round?.submission || {};
}

const reviewerCategories = computed(() => {
    if (props.categories && props.categories.length > 0) return props.categories;
    const cats = new Map();
    assignmentList.value.forEach(a => {
        const c = getSubmission(a).category;
        if (c && c.id) cats.set(c.id, c);
    });
    return Array.from(cats.values());
});

const filteredAssignments = computed(() => {
    let list = assignmentList.value;

    if (selectedCategoryId.value !== 'all') {
        list = list.filter(a => getSubmission(a).category?.id === selectedCategoryId.value);
    }

    if (currentTab.value === 'pending') {
        list = list.filter(a => a.status === 'assigned' && !a.is_slot_full);
    } else if (currentTab.value === 'completed') {
        list = list.filter(a => a.status === 'completed');
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(a => {
            const sub = getSubmission(a);
            return (sub.title && sub.title.toLowerCase().includes(q)) ||
                   (sub.abstract_code && sub.abstract_code.toLowerCase().includes(q));
        });
    }

    return list;
});

const computedStats = computed(() => {
    const list = selectedCategoryId.value === 'all'
        ? assignmentList.value
        : assignmentList.value.filter(a => getSubmission(a).category?.id === selectedCategoryId.value);

    return {
        total_assigned: list.length,
        pending_reviews: list.filter(a => a.status === 'assigned' && !a.is_slot_full).length,
        completed_reviews: list.filter(a => a.status === 'completed').length,
    };
});

function openReviewModal(assignment) {
    activeAssignment.value = assignment;
    activeAbstract.value = getSubmission(assignment);
    
    if (assignment.review) {
        form.score_criteria_1 = assignment.review.score_criteria_1 || '';
        form.score_criteria_2 = assignment.review.score_criteria_2 || '';
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
        form.recommendation = REC_MAP[(assignment.review.recommendation || '').toUpperCase()] ?? 'ORAL';
        form.summary = assignment.review.summary || '';
    } else {
        form.reset();
        form.recommendation = 'ORAL';
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
    <Head title="Abstract Reviews - Reviewer" />

    <ReviewerLayout>
        <!-- Page Header (Restored previous design) -->
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Abstract Reviews
            </h1>
            <p class="text-slate-500 text-xs mt-1 font-medium">
                Selamat datang, {{ $page.props.auth.user.name }}! Tinjau naskah abstrak ilmiah yang ditugaskan kepada Anda sesuai bidang kepakaran.
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
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Naskah Tersedia</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-slate-900">
                        <AnimatedCounter :value="computedStats.total_assigned" />
                    </span>
                    <span class="text-xs font-bold text-primary">Abstracts</span>
                </div>
            </div>

            <!-- Pending / Slot Terbuka -->
            <div
                @click="currentTab = 'pending'"
                class="animate-fade-in-up animation-delay-200 bg-white rounded-3xl p-6 shadow-sm border transition-all duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                :class="currentTab === 'pending' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md' : 'border-slate-200/80 hover:border-amber-300'"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Perlu Dinilai / Slot Terbuka</span>
                    <span v-if="computedStats.pending_reviews > 0" class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-amber-600">
                        <AnimatedCounter :value="computedStats.pending_reviews" />
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
                        <AnimatedCounter :value="computedStats.completed_reviews" />
                    </span>
                    <span class="text-xs font-semibold text-emerald-600">Tersimpan</span>
                </div>
            </div>

        </div>

        <!-- Category Selector Pills (Shown when reviewer has multiple categories) -->
        <div v-if="reviewerCategories.length > 1" class="flex items-center gap-2 mb-6 overflow-x-auto pb-1">
            <button
                type="button"
                @click="selectedCategoryId = 'all'"
                :class="[
                    'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border shadow-2xs',
                    selectedCategoryId === 'all'
                        ? 'bg-purple-700 text-white border-purple-700'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                ]"
            >
                Semua Kategori ({{ assignmentList.length }})
            </button>
            <button
                v-for="cat in reviewerCategories"
                :key="cat.id"
                type="button"
                @click="selectedCategoryId = cat.id"
                :class="[
                    'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border shadow-2xs',
                    selectedCategoryId === cat.id
                        ? 'bg-purple-700 text-white border-purple-700'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                ]"
            >
                {{ cat.name }} ({{ assignmentList.filter(a => getSubmission(a).category?.id === cat.id).length }})
            </button>
        </div>

        <!-- Assigned Submissions Table with Filter Tabs (Restored Previous Rich Design) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">
                        Assigned Scientific Abstracts
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Tinjau naskah abstrak ilmiah yang ditugaskan kepada Anda sesuai tahap penilaian.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-56">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari Code / Judul..."
                            class="w-full text-xs rounded-xl border border-slate-200 pl-8 pr-3 py-1.5 focus:ring-2 focus:ring-purple-600 focus:border-purple-600"
                        />
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-1.5 bg-slate-100/80 p-1 rounded-2xl border border-slate-200/60 shrink-0">
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
                                {{ computedStats.total_assigned }}
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
                                {{ computedStats.pending_reviews }}
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
                                {{ computedStats.completed_reviews }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="!filteredAssignments || filteredAssignments.length === 0" class="p-12 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400 font-bold text-lg">
                    {{ searchQuery.trim() ? '🔍' : (currentTab === 'pending' ? '🎉' : '📝') }}
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">
                    {{
                        searchQuery.trim()
                            ? `Tidak ada naskah yang cocok dengan pencarian "${searchQuery}".`
                            : reviewerCategories.length === 0 && assignmentList.length === 0
                                ? 'Belum ada bidang kepakaran yang ditetapkan untuk akun Anda.'
                                : currentTab === 'pending'
                                    ? 'Semua naskah abstrak telah selesai dinilai!'
                                    : currentTab === 'completed'
                                        ? 'Belum ada naskah yang selesai dinilai.'
                                        : 'Tidak ada naskah yang ditemukan.'
                    }}
                </h3>
                <p class="text-slate-500 text-xs">
                    {{
                        searchQuery.trim()
                            ? 'Coba periksa kembali kata kunci judul atau kode naskah abstrak yang Anda cari.'
                            : reviewerCategories.length === 0 && assignmentList.length === 0
                                ? 'Silakan hubungi Administrator untuk menetapkan kategori topik telaah ilmiah pada akun Anda.'
                                : currentTab === 'pending'
                                    ? 'Tidak ada tugas telaah abstrak yang menunggu tindakan Anda saat ini.'
                                    : 'Naskah abstrak yang telah selesai Anda beri skor akan tercatat di tab ini.'
                    }}
                </p>
            </div>

            <!-- Table (Previous Rich Design) -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Code & Track</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Title</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase whitespace-nowrap">Document</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-center whitespace-nowrap">Slot Review</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-center whitespace-nowrap">Penilaian</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(assignment, index) in filteredAssignments"
                            :key="assignment.id"
                            class="table-row-stagger hover:bg-slate-50/50 transition"
                            :style="{ animationDelay: `${Math.min(index * 90 + 200, 900)}ms` }"
                        >
                            <!-- Code & Track -->
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

                            <!-- Title (Full title, no cut-off) -->
                            <td class="px-5 py-3.5 min-w-[220px]">
                                <div
                                    class="font-bold text-slate-900 text-xs break-words whitespace-normal leading-relaxed hover:text-purple-700 transition cursor-pointer"
                                    @click="assignment.can_review ? openReviewModal(assignment) : null"
                                    :title="getSubmission(assignment).title"
                                >
                                    {{ getSubmission(assignment).title }}
                                </div>
                            </td>

                            <!-- Document -->
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

                            <!-- Slot Review / Tahap Telaah -->
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div v-if="(assignment.round?.round_number ?? 1) > 1" class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border shadow-2xs bg-amber-50 text-amber-800 border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Tahap Revisi {{ (assignment.round?.round_number ?? 2) - 1 }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 mt-0.5">Penelaahan Ulang</span>
                                </div>
                                <div v-else class="inline-flex flex-col items-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border shadow-2xs',
                                            assignment.is_slot_full
                                                ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                                : assignment.reviewed_by_count > 0
                                                    ? 'bg-sky-50 text-sky-800 border-sky-200'
                                                    : 'bg-slate-100 text-slate-700 border-slate-200'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="assignment.is_slot_full ? 'bg-emerald-500' : assignment.reviewed_by_count > 0 ? 'bg-sky-500' : 'bg-slate-400'"></span>
                                        {{ assignment.reviewed_by_count ?? 0 }}/3 Terisi
                                    </span>
                                    <span v-if="assignment.user_has_reviewed" class="text-[10px] text-emerald-600 font-bold mt-0.5">
                                        ✓ Anda telah menilai
                                    </span>
                                    <span v-else-if="assignment.is_slot_full" class="text-[10px] text-slate-400 font-medium mt-0.5">
                                        Slot penuh
                                    </span>
                                </div>
                            </td>

                            <!-- Penilaian -->
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
                                    v-else-if="assignment.is_coi"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide border bg-slate-100 text-slate-500 border-slate-200"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Penulis Sendiri (COI)
                                </span>
                                <span
                                    v-else-if="assignment.is_slot_full"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide border bg-slate-100 text-slate-500 border-slate-200"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Slot Penuh (3/3)
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide border bg-amber-50/80 text-amber-700 border-amber-200/80"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    Belum Dinilai
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <!-- Slot Penuh & belum dinilai -->
                                <button
                                    v-if="assignment.is_slot_full && !assignment.user_has_reviewed"
                                    disabled
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed inline-flex items-center gap-1"
                                    title="Slot review sudah penuh (3/3 telah diisi oleh reviewer lain)"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Slot Penuh</span>
                                </button>

                                <!-- COI -->
                                <button
                                    v-else-if="assignment.is_coi"
                                    disabled
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed"
                                    title="Conflict of Interest: Naskah milik Anda sendiri"
                                >
                                    COI
                                </button>

                                <!-- Sudah dinilai -->
                                <button
                                    v-else-if="assignment.user_has_reviewed"
                                    @click="openReviewModal(assignment)"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100"
                                >
                                    {{ assignment.is_decided ? 'Lihat Nilai' : 'Edit Nilai' }}
                                </button>

                                <!-- Slot terbuka -->
                                <button
                                    v-else
                                    @click="openReviewModal(assignment)"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer bg-purple-50 text-purple-700 border border-purple-200/90 hover:bg-purple-100"
                                >
                                    Nilai Sekarang
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Note -->
            <div class="px-6 py-3.5 bg-slate-50/70 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-slate-500">
                <span class="text-[11px] text-slate-500">
                    ℹ️ <strong>Sistem Review Terbuka:</strong> Maksimal 3 reviewer per naskah (siapa cepat mengisi slot review). Kuota akan terisi otomatis setelah reviewer men-submit nilai.
                </span>
                <span class="text-[11px] text-slate-400 italic">
                    First-come, first-served
                </span>
            </div>
        </div>

        <!-- 📝 Abstract Review Modal -->
        <div v-if="isModalOpen" @click.self="isModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-auto animate-fade-in">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-bold text-purple-700 uppercase">{{ activeAbstract?.abstract_code }}</span>
                            <span
                                v-if="(activeAssignment?.round?.round_number ?? 1) > 1"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                            >
                                Revisi {{ (activeAssignment?.round?.round_number ?? 2) - 1 }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm">
                            Abstract Peer Review Form
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Blinded Abstract Details & History -->
                    <div class="space-y-4">
                        <div v-if="(activeAssignment?.round?.round_number ?? 1) > 1" class="bg-amber-50/90 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                            <span class="font-bold">📝 Naskah Hasil Revisi (Revisi {{ (activeAssignment?.round?.round_number ?? 2) - 1 }}):</span>
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
                                            {{ getRecommendationCategory(hist.review.recommendation) === 'accept' ? 'Accept (' + hist.review.recommendation + ')' : getRecommendationStyle(hist.review.recommendation).label }}
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

                                <div class="grid grid-cols-2 gap-2.5">
                                    <!-- Accept (Oral) -->
                                    <label
                                        :class="[
                                            'relative flex flex-col p-2.5 rounded-xl border cursor-pointer transition select-none',
                                            form.recommendation === 'ORAL'
                                                ? 'bg-emerald-50/90 border-emerald-500 ring-2 ring-emerald-500/20 shadow-xs'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60',
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
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
                                                :disabled="activeAssignment?.is_decided"
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
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
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
                                                :disabled="activeAssignment?.is_decided"
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
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
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
                                                :disabled="activeAssignment?.is_decided"
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
                                            activeAssignment?.is_decided ? 'pointer-events-none opacity-60' : ''
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
