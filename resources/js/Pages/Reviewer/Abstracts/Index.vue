<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import AnimatedCounter from '@/Components/UI/AnimatedCounter.vue';
import ReviewModal from './Partials/ReviewModal.vue';

const props = defineProps({
    stats: Object,
    assignments: [Array, Object],
    categories: Array,
});

const isModalOpen = ref(false);
const activeAssignment = ref(null);
const selectedCategoryId = ref('all');
const searchQuery = ref('');
const currentTab = ref('all');

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
    isModalOpen.value = true;
}
</script>

<template>
    <Head title="Abstract Reviews - Reviewer" />

    <ReviewerLayout>
        <!-- Page Header -->
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Abstract Reviews
            </h1>
            <p class="text-slate-500 text-xs mt-1 font-medium">
                Selamat datang, {{ $page.props.auth.user.name }}! Tinjau naskah abstrak ilmiah yang ditugaskan kepada Anda sesuai bidang kepakaran.
            </p>
        </div>

        <!-- 📊 Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-purple-100/80 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Ditugaskan</span>
                    <span class="text-2xl font-black text-slate-900 mt-1 block">
                        <AnimatedCounter :target="computedStats.total_assigned" />
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Sesuai bidang kepakaran Anda</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl font-bold shadow-inner">
                    📋
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-amber-100 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Perlu Ditinjau</span>
                    <span class="text-2xl font-black text-amber-700 mt-1 block">
                        <AnimatedCounter :target="computedStats.pending_reviews" />
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Menunggu penilaian Anda</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold shadow-inner">
                    ⏳
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-emerald-100 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Selesai Dinilai</span>
                    <span class="text-2xl font-black text-emerald-700 mt-1 block">
                        <AnimatedCounter :target="computedStats.completed_reviews" />
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Rekomendasi terkirim</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold shadow-inner">
                    ✅
                </div>
            </div>
        </div>

        <!-- 📑 Category / Track Tabs -->
        <div v-if="reviewerCategories.length > 0" class="mb-5 overflow-x-auto pb-1">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-2xl inline-flex min-w-full sm:min-w-0">
                <button
                    type="button"
                    @click="selectedCategoryId = 'all'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer',
                        selectedCategoryId === 'all'
                            ? 'bg-purple-700 text-white shadow-2xs'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'
                    ]"
                >
                    Semua Track
                </button>
                <button
                    v-for="cat in reviewerCategories"
                    :key="cat.id"
                    type="button"
                    @click="selectedCategoryId = cat.id"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap cursor-pointer',
                        selectedCategoryId === cat.id
                            ? 'bg-purple-700 text-white shadow-2xs'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'
                    ]"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Toolbar & Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2 p-1 bg-white border border-slate-200/90 rounded-2xl shadow-2xs">
                <button
                    @click="currentTab = 'all'"
                    :class="[
                        'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                        currentTab === 'all'
                            ? 'bg-purple-700 text-white shadow-2xs'
                            : 'text-slate-600 hover:text-purple-700'
                    ]"
                >
                    Semua ({{ assignmentList.length }})
                </button>
                <button
                    @click="currentTab = 'pending'"
                    :class="[
                        'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                        currentTab === 'pending'
                            ? 'bg-amber-600 text-white shadow-2xs'
                            : 'text-slate-600 hover:text-amber-700'
                    ]"
                >
                    Menunggu ({{ computedStats.pending_reviews }})
                </button>
                <button
                    @click="currentTab = 'completed'"
                    :class="[
                        'px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer',
                        currentTab === 'completed'
                            ? 'bg-emerald-700 text-white shadow-2xs'
                            : 'text-slate-600 hover:text-emerald-700'
                    ]"
                >
                    Selesai ({{ computedStats.completed_reviews }})
                </button>
            </div>

            <!-- Search Input -->
            <div class="relative w-full sm:w-72">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari kode atau judul abstrak..."
                    class="w-full text-xs rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-3 focus:bg-white focus:ring-1 focus:ring-purple-700"
                />
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''"
                    type="button"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- 📋 Abstract Assignment Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 uppercase text-[11px] font-bold text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Kode & Naskah Abstrak</th>
                            <th scope="col" class="px-4 py-3.5">Track / Bidang</th>
                            <th scope="col" class="px-3 py-3.5 text-center">Putaran</th>
                            <th scope="col" class="px-4 py-3.5 text-center">Status Kuota</th>
                            <th scope="col" class="px-5 py-3.5 text-center">Penilaian</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="filteredAssignments.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="text-3xl">📭</div>
                                    <p class="font-bold text-slate-700">Tidak ada naskah abstrak</p>
                                    <p class="text-slate-400 text-[11px]">
                                        Belum ada naskah yang ditugaskan kepada Anda atau tidak ada yang sesuai dengan filter pencarian.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="assignment in filteredAssignments"
                            :key="assignment.id"
                            class="hover:bg-slate-50/60 transition"
                        >
                            <!-- Kode & Naskah -->
                            <td class="px-5 py-3.5">
                                <div class="space-y-1 max-w-md">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ getSubmission(assignment).abstract_code || 'ABS-???' }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2">
                                        {{ getSubmission(assignment).title || 'Untitled Abstract' }}
                                    </h4>
                                    <p v-if="getSubmission(assignment).keywords" class="text-[10px] text-slate-400 italic">
                                        Keywords: {{ getSubmission(assignment).keywords }}
                                    </p>
                                </div>
                            </td>

                            <!-- Track -->
                            <td class="px-4 py-3.5 align-middle whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ getSubmission(assignment).category?.name || 'General' }}
                                </span>
                            </td>

                            <!-- Putaran -->
                            <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap">
                                <span
                                    v-if="(assignment.round?.round_number ?? 1) > 1"
                                    class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                                >
                                    Revisi {{ (assignment.round?.round_number ?? 2) - 1 }}
                                </span>
                                <span
                                    v-else
                                    class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600"
                                >
                                    Awal
                                </span>
                            </td>

                            <!-- Status Kuota Reviewer -->
                            <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border',
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

        <!-- 📝 Abstract Review Modal Partial -->
        <ReviewModal
            :show="isModalOpen"
            :assignment="activeAssignment"
            @close="isModalOpen = false"
        />

    </ReviewerLayout>
</template>
