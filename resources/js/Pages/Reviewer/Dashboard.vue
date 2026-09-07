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
const activeAbstract = ref(null);

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

const computedRecommendation = computed(() => {
    if (!form.score_criteria_1 || !form.score_criteria_2) {
        return null;
    }
    return computedTotalScore.value >= 5 ? 'ORAL' : 'POSTER';
});

const assignmentList = computed(() => {
    if (Array.isArray(props.assignments)) {
        return props.assignments;
    }
    if (props.assignments && Array.isArray(props.assignments.data)) {
        return props.assignments.data;
    }
    return [];
});

function getSubmission(assignment) {
    return assignment?.submission || assignment?.round?.submission || {};
}

function openReviewModal(assignment) {
    activeAssignment.value = assignment;
    activeAbstract.value = getSubmission(assignment);
    
    // Load existing scores if available
    if (assignment.review) {
        form.score_criteria_1 = assignment.review.score_criteria_1 || '';
        form.score_criteria_2 = assignment.review.score_criteria_2 || '';
        form.recommendation = assignment.review.recommendation || 'ORAL';
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
    
    if (computedRecommendation.value) {
        form.recommendation = computedRecommendation.value;
    }

    form.post(route('reviewer.assignments.review', activeAssignment.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <ReviewerLayout>
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Reviewer Dashboard</h1>
            <p class="text-slate-500 text-xs mt-1 font-medium">Welcome back, {{ $page.props.auth.user.name }}! Review your assigned scientific submissions below.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
            
            <!-- Total Assigned -->
            <div class="animate-fade-in-up animation-delay-100 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-purple-300">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Assigned</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-slate-900">
                        <AnimatedCounter :value="stats?.total_assigned ?? 0" />
                    </span>
                    <span class="text-xs font-bold text-primary">Submissions</span>
                </div>
            </div>

            <!-- Pending -->
            <div class="animate-fade-in-up animation-delay-200 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-amber-300">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Review</span>
                    <span v-if="(stats?.pending_reviews ?? 0) > 0" class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-black text-amber-600">
                        <AnimatedCounter :value="stats?.pending_reviews ?? 0" />
                    </span>
                    <span class="text-xs font-bold text-amber-600">Requires Action</span>
                </div>
            </div>

            <!-- Completed -->
            <div class="animate-fade-in-up animation-delay-300 bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-emerald-300">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Completed</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-emerald-600">
                        <AnimatedCounter :value="stats?.completed_reviews ?? 0" />
                    </span>
                    <span class="text-xs font-semibold text-emerald-600">Scored</span>
                </div>
            </div>

        </div>

        <!-- Assigned Abstracts Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Assigned Scientific Abstracts</h3>
                <span class="text-xs text-slate-400">{{ assignmentList.length }} items</span>
            </div>
            
            <!-- Empty State -->
            <div v-if="!assignmentList || assignmentList.length === 0" class="p-12 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400 font-bold text-lg">
                    📝
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">No pending reviews</h3>
                <p class="text-slate-500 text-xs">You currently have no abstracts assigned in your tracks.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Code & Track</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase">Title</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-center">Status</th>
                            <th class="px-5 py-3 font-bold text-slate-500 text-[11px] uppercase text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(assignment, index) in assignmentList"
                            :key="assignment.id"
                            class="table-row-stagger hover:bg-slate-50/50 transition"
                            :style="{ animationDelay: `${Math.min(index * 90 + 200, 900)}ms` }"
                        >
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-xs font-bold text-purple-900">{{ getSubmission(assignment).abstract_code }}</span>
                                    <span
                                        v-if="(assignment.round?.round_number ?? 1) > 1"
                                        class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200"
                                    >
                                        Round {{ assignment.round.round_number }} (Revision)
                                    </span>
                                </div>
                                <div class="text-[10px] text-purple-600 font-semibold mt-0.5">{{ getSubmission(assignment).category?.name || 'Scientific Track' }}</div>
                            </td>
                            <td class="px-5 py-3.5 max-w-md">
                                <div class="font-bold text-slate-900 text-xs line-clamp-2" :title="getSubmission(assignment).title">
                                    {{ getSubmission(assignment).title }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                                    assignment.status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                ]">
                                    {{ assignment.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <button
                                    @click="openReviewModal(assignment)"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                                    :class="assignment.status === 'completed' ? 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200' : 'bg-purple-900 text-gold hover:bg-purple-950'"
                                >
                                    {{ assignment.status === 'completed' ? 'View / Edit Score' : 'Score Now' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📝 Blind Review Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-bold text-purple-700 uppercase">{{ activeAbstract?.abstract_code }}</span>
                            <span
                                v-if="(activeAssignment?.round?.round_number ?? 1) > 1"
                                class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200"
                            >
                                Round {{ activeAssignment.round.round_number }}: Revised Submission
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm">Blind Peer Review Form</h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Blinded Abstract Details -->
                    <div class="space-y-4">
                        <div v-if="(activeAssignment?.round?.round_number ?? 1) > 1" class="bg-amber-50/90 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                            <span class="font-bold">📝 Author Resubmission:</span>
                            <p class="mt-0.5 text-[11px]">The participant has revised this abstract following reviewer feedback. Please inspect the updated abstract and attachment below.</p>
                        </div>

                        <div class="bg-purple-50/70 rounded-2xl p-4 border border-purple-100">
                            <span class="text-[10px] font-bold uppercase text-purple-700 tracking-wider">Track: {{ activeAbstract?.category?.name || 'General' }}</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-1 leading-snug">{{ activeAbstract?.title }}</h4>
                        </div>
                        
                        <div v-if="activeAbstract?.keywords">
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">Keywords:</span>
                            <p class="text-xs text-slate-800 font-semibold">{{ activeAbstract.keywords }}</p>
                        </div>

                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">Abstract Content:</span>
                            <div v-if="activeAbstract?.abstract_text" class="bg-slate-50 rounded-xl p-3.5 text-xs text-slate-700 leading-relaxed border border-slate-200 max-h-56 overflow-y-auto whitespace-pre-wrap">
                                {{ activeAbstract.abstract_text }}
                            </div>
                            <div v-else class="bg-slate-50 rounded-xl p-3.5 text-xs text-slate-400 italic border border-slate-200">
                                Abstract content is provided in the attached document. Please download and review the file below.
                            </div>
                        </div>
                        
                        <div v-if="activeAbstract?.file_path" class="pt-1">
                            <a
                                :href="formatStorageUrl(activeAbstract.file_path)"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-purple-100/70 hover:bg-purple-100 border border-purple-200 text-purple-900 font-bold px-3.5 py-2 text-xs transition"
                            >
                                📄 Download Blinded Document
                            </a>
                        </div>
                    </div>

                    <!-- Right: Scoring Form -->
                    <form @submit.prevent="submitReview" class="flex flex-col justify-between space-y-4 bg-slate-50/50 border border-slate-200 rounded-2xl p-5">
                        
                        <div v-if="activeAssignment?.round?.status === 'locked'" class="bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold p-3 rounded-xl">
                            🔒 This round is locked. You can view your submitted scores.
                        </div>

                        <div class="space-y-3.5">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Originality (1-5) <span class="text-red-500">*</span></label>
                                    <select v-model="form.score_criteria_1" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="activeAssignment?.round?.status === 'locked'">
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
                                    <select v-model="form.score_criteria_2" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="activeAssignment?.round?.status === 'locked'">
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
                                <label class="block text-xs font-bold text-slate-700 mb-1">System Recommendation (Auto-calculated)</label>
                                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                                    <div>
                                        <div class="text-[11px] text-slate-600 font-medium">Total Score: <span class="font-bold text-slate-900">{{ computedTotalScore }}/10</span></div>
                                        <div class="text-[10px] text-slate-400">Total &ge; 5 &rarr; Oral | Total &lt; 5 &rarr; Poster</div>
                                    </div>
                                    <div v-if="computedRecommendation" :class="computedRecommendation === 'ORAL' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-blue-100 text-blue-700 border-blue-200'" class="px-3 py-1.5 rounded-lg border font-bold text-xs tracking-wider uppercase">
                                        {{ computedRecommendation === 'ORAL' ? 'Oral Presentation' : 'Poster Presentation' }}
                                    </div>
                                    <div v-else class="text-xs text-slate-400 italic">
                                        Pilih skor di atas
                                    </div>
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
                                    :disabled="activeAssignment?.round?.status === 'locked'"
                                ></textarea>
                                <p v-if="form.errors.summary" class="text-red-500 text-[10px] mt-1">{{ form.errors.summary }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                            <button type="button" @click="isModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                v-if="activeAssignment?.round?.status !== 'locked'"
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-purple-900 hover:bg-purple-950 text-gold font-bold text-xs px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ form.processing ? 'Submitting...' : 'Submit Evaluation' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </ReviewerLayout>
</template>
