<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';

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
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Reviewer Dashboard</h1>
            <p class="text-slate-500 text-xs mt-1">Welcome back, {{ $page.props.auth.user.name }}! Review your assigned scientific submissions below.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
            
            <!-- Total Assigned -->
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Assigned</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ stats?.total_assigned ?? 0 }}</span>
                    <span class="text-xs font-semibold text-purple-600">Submissions</span>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Review</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-amber-600">{{ stats?.pending_reviews ?? 0 }}</span>
                    <span class="text-xs font-semibold text-amber-600">Requires Action</span>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Completed</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-emerald-600">{{ stats?.completed_reviews ?? 0 }}</span>
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
                        <tr v-for="assignment in assignmentList" :key="assignment.id" class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-mono text-xs font-bold text-purple-900">{{ getSubmission(assignment).abstract_code }}</div>
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
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50">
                    <div>
                        <span class="text-[10px] font-mono font-bold text-purple-700 uppercase">{{ activeAbstract?.abstract_code }}</span>
                        <h3 class="font-bold text-slate-900 text-sm">Blind Peer Review Form</h3>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Blinded Abstract Details -->
                    <div class="space-y-4">
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
                                <label class="block text-xs font-bold text-slate-700 mb-1">Recommendation <span class="text-red-500">*</span></label>
                                <select v-model="form.recommendation" class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700 font-bold" required :disabled="activeAssignment?.round?.status === 'locked'">
                                    <option value="ORAL">Accept as Oral Presentation</option>
                                    <option value="POSTER">Accept as Poster Presentation</option>
                                    <option value="REVISION">Revision Required</option>
                                    <option value="REJECT">Reject Submission</option>
                                </select>
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
