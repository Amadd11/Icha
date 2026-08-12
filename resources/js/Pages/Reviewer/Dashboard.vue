<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';

const props = defineProps({
    stats: Object,
    assignments: Array,
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

function openReviewModal(assignment) {
    activeAssignment.value = assignment;
    activeAbstract.value = assignment.round?.submission;
    
    // Load existing scores if available
    if (assignment.review) {
        form.score_criteria_1 = assignment.review.score_criteria_1 || '';
        form.score_criteria_2 = assignment.review.score_criteria_2 || '';
        form.recommendation = assignment.review.recommendation || 'ORAL';
        form.summary = assignment.review.summary || '';
    } else {
        form.reset();
        form.recommendation = 'ORAL'; // Default
    }
    
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
    <Head title="Reviewer Dashboard" />

    <ReviewerLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reviewer Dashboard</h1>
            <p class="text-slate-500 mt-1">Welcome back, {{ $page.props.auth.user.name }}! Here's an overview of your review tasks.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Assigned -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Total Assigned</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.total_assigned }}</span>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-1 rounded-md mb-1">Abstracts</span>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Pending</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.pending_reviews }}</span>
                        <span class="text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-1 rounded-md mb-1">Requires Action</span>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Completed</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.completed_reviews }}</span>
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-100 px-2 py-1 rounded-md mb-1">Done</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Deadlines</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.upcoming_deadlines }}</span>
                        <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-md mb-1">Next 7 days</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-lg text-slate-800">Assigned Abstracts</h3>
            </div>
            
            <!-- Empty State -->
            <div v-if="!assignments || assignments.length === 0" class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <span class="material-symbols-outlined text-[32px]">assignment</span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">No pending reviews</h3>
                <p class="text-slate-500 text-sm">You currently have no abstracts assigned to you.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-slate-500 text-xs">Code & Track</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 text-xs">Title</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 text-xs text-center">Status</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 text-xs text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="assignment in assignments" :key="assignment.id" class="hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <div class="font-mono text-xs font-bold text-primary">{{ assignment.round?.submission?.abstract_code }}</div>
                                <div class="text-[10px] text-purple-600 font-semibold mt-0.5">{{ assignment.round?.submission?.category?.name || 'General' }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <div class="font-bold text-slate-800 text-xs truncate" :title="assignment.round?.submission?.title">
                                    {{ assignment.round?.submission?.title }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    assignment.status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                ]">
                                    {{ assignment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="openReviewModal(assignment)"
                                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm"
                                    :class="assignment.status === 'completed' ? 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200' : 'bg-primary text-white hover:bg-primary-dark'"
                                >
                                    {{ assignment.status === 'completed' ? 'View/Edit' : 'Score Now' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900 text-base">Blind Review Form</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left: Blinded Abstract -->
                    <div class="space-y-4">
                        <div class="bg-purple-50 rounded-2xl p-4 border border-purple-100">
                            <span class="text-[10px] font-bold uppercase text-purple-600 tracking-wider">Abstract Code: {{ activeAbstract?.abstract_code }}</span>
                            <h4 class="text-sm font-black text-slate-900 mt-1 leading-tight">{{ activeAbstract?.title }}</h4>
                        </div>
                        
                        <div>
                            <span class="text-xs font-bold text-slate-500 block mb-1">Keywords:</span>
                            <p class="text-xs text-slate-800 font-semibold">{{ activeAbstract?.keywords }}</p>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-slate-500 block mb-1">Abstract Content:</span>
                            <div class="bg-slate-50 rounded-xl p-4 text-xs text-slate-700 leading-relaxed border border-slate-200 h-64 overflow-y-auto whitespace-pre-wrap">
                                {{ activeAbstract?.abstract_text }}
                            </div>
                        </div>
                        
                        <div class="pt-2">
                            <a :href="'/storage/' + activeAbstract?.file_path" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-primary hover:underline">
                                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Download Full Document (Blinded)
                            </a>
                        </div>
                    </div>

                    <!-- Right: Scoring Form -->
                    <form @submit.prevent="submitReview" class="flex flex-col space-y-5 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                        
                        <div v-if="activeAssignment?.round?.status === 'locked'" class="bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold p-3 rounded-xl mb-2">
                            🔒 This round is locked. You can view your scores but cannot change them.
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Criteria 1 (Originality)</label>
                                <select v-model="form.score_criteria_1" class="admin-input" required :disabled="activeAssignment?.round?.status === 'locked'">
                                    <option value="" disabled>Select (1-5)</option>
                                    <option value="1">1 - Poor</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="3">3 - Good</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="5">5 - Excellent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Criteria 2 (Relevance)</label>
                                <select v-model="form.score_criteria_2" class="admin-input" required :disabled="activeAssignment?.round?.status === 'locked'">
                                    <option value="" disabled>Select (1-5)</option>
                                    <option value="1">1 - Poor</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="3">3 - Good</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="5">5 - Excellent</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Recommendation</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                    <input type="radio" v-model="form.recommendation" value="ORAL" class="text-primary focus:ring-primary" :disabled="activeAssignment?.round?.status === 'locked'" /> Oral
                                </label>
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                    <input type="radio" v-model="form.recommendation" value="POSTER" class="text-primary focus:ring-primary" :disabled="activeAssignment?.round?.status === 'locked'" /> Poster
                                </label>
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer text-rose-600">
                                    <input type="radio" v-model="form.recommendation" value="REJECT" class="text-rose-600 focus:ring-rose-600" :disabled="activeAssignment?.round?.status === 'locked'" /> Reject
                                </label>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confidential Comments / Summary</label>
                            <textarea 
                                v-model="form.summary" 
                                class="admin-input flex-1 resize-none" 
                                placeholder="Write your assessment here..."
                                :disabled="activeAssignment?.round?.status === 'locked'"
                            ></textarea>
                        </div>
                        
                        <div v-if="activeAssignment?.round?.status !== 'locked'" class="pt-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark shadow-md transition disabled:opacity-50 w-full sm:w-auto">
                                {{ form.processing ? 'Saving...' : 'Submit Review' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </ReviewerLayout>
</template>
