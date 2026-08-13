<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';

const props = defineProps({
    activeConference: Object,
    categories: Array,
    abstracts: Array,
    papers: Array,
    isPaid: Boolean,
    statusChecklist: Object,
    userSummary: Object,
});

const activeTab = ref('abstract'); // 'abstract' | 'paper'

// Abstract Form
const abstractForm = useForm({
    title: '',
    category_id: props.categories?.[0]?.id || '',
    presentation_type: 'oral',
    keywords: '',
    abstract_text: '',
    file: null,
});

// Paper Form
const paperForm = useForm({
    title: '',
    abstract_id: props.abstracts?.[0]?.id || '',
    file: null,
});

function handleFileChange(e, type) {
    const file = e.target.files[0];
    if (type === 'abstract') {
        abstractForm.file = file;
    } else {
        paperForm.file = file;
    }
}

function submitAbstract() {
    abstractForm.post(route('participant.submission.abstract.store'), {
        preserveScroll: true,
        onSuccess: () => abstractForm.reset(),
    });
}

function submitPaper() {
    paperForm.post(route('participant.submission.paper.store'), {
        preserveScroll: true,
        onSuccess: () => paperForm.reset(),
    });
}
</script>

<template>
    <Head title="Submission - ICHA 2026" />

    <ParticipantLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Top Banner / Navigation Tabs -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Choose Submission Type</span>
                <div class="inline-flex p-1.5 bg-slate-100 rounded-2xl gap-2">
                    <button
                        @click="activeTab = 'abstract'"
                        :class="[
                            'px-6 py-2.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer',
                            activeTab === 'abstract'
                                ? 'bg-sidebar text-white shadow-md'
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        Abstract Submission
                    </button>
                    <button
                        @click="activeTab = 'paper'"
                        :class="[
                            'px-6 py-2.5 rounded-xl text-xs font-extrabold transition-all duration-200 cursor-pointer',
                            activeTab === 'paper'
                                ? 'bg-sidebar text-white shadow-md'
                                : 'text-slate-600 hover:text-slate-900'
                        ]"
                    >
                        Paper Submission
                    </button>
                </div>
            </div>

            <!-- Locked State if Payment Not Verified -->
            <div v-if="!props.isPaid" class="bg-white rounded-2xl p-8 border border-amber-200 shadow-2xs text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto text-xl font-bold">
                    🔒
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Submission Portal Locked</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                        Payment verification is required before you can submit abstracts or full papers. Please complete registration and upload your payment receipt for admin verification.
                    </p>
                </div>
                <div>
                    <Link
                        :href="route('participant.payment.index')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-xs transition"
                    >
                        Upload Payment Receipt &rarr;
                    </Link>
                </div>
            </div>

            <!-- Main Content Area: Left Form + Guideline | Right User Summary -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <!-- Main Form & Guideline Section (Col-span 3) -->
                <div class="lg:col-span-3 space-y-6">

                    <!-- TAB 1: ABSTRACT SUBMISSION -->
                    <div v-if="activeTab === 'abstract'" class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-100">
                        <div class="border-b border-slate-100 pb-4 mb-6">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">add_circle</span>
                                Abstract Submission
                            </h2>
                        </div>

                        <!-- STATE 1: ALREADY UPLOADED -->
                        <div v-if="props.statusChecklist?.hasUploadedAbstract && props.abstracts.length > 0" class="space-y-6">
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 flex flex-col md:flex-row gap-6 items-center">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                    <span class="material-symbols-outlined text-3xl">check_circle</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-emerald-900 mb-1">Abstract Submitted Successfully</h3>
                                    <p class="text-sm text-emerald-700">You have already submitted your abstract for this conference. You can only submit one abstract per registration.</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Your Submitted Abstract</h4>
                                <div class="space-y-4 text-sm">
                                    <div>
                                        <span class="text-xs text-slate-500 block mb-0.5">Abstract Code</span>
                                        <span class="font-mono font-bold text-primary bg-purple-50 px-2 py-0.5 rounded">{{ props.abstracts[0].abstract_code }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-500 block mb-0.5">Title</span>
                                        <span class="font-bold text-slate-900">{{ props.abstracts[0].title }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-500 block mb-0.5">Track / Category</span>
                                        <span class="font-semibold text-slate-700">{{ props.abstracts[0].category?.name }}</span>
                                    </div>
                                    <div class="pt-2">
                                        <span class="inline-flex px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border border-amber-200 bg-amber-50 text-amber-700">
                                            Status: Under Review
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STATE 2: UPLOAD FORM -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left: Form -->
                            <form @submit.prevent="submitAbstract" class="space-y-4">
                                <h3 class="text-xs font-extrabold text-primary uppercase tracking-wider mb-2">Submit Your Abstract Here</h3>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Abstract Title</label>
                                    <input
                                        v-model="abstractForm.title"
                                        type="text"
                                        placeholder="Enter abstract title..."
                                        class="admin-input"
                                        required
                                    />
                                    <span v-if="abstractForm.errors.title" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ abstractForm.errors.title }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload your Abstract File</label>
                                    <input
                                        type="file"
                                        @change="e => handleFileChange(e, 'abstract')"
                                        accept=".doc,.docx,.pdf"
                                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-primary hover:file:bg-purple-100 cursor-pointer"
                                        required
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Accepted formats: .doc, .docx, .pdf (Max 10MB)</p>
                                    <span v-if="abstractForm.errors.file" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ abstractForm.errors.file }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Choose Topic / Track</label>
                                    <select v-model="abstractForm.category_id" class="admin-input" required>
                                        <option value="" disabled>Select a topic...</option>
                                        <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                    <span v-if="abstractForm.errors.category_id" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ abstractForm.errors.category_id }}</span>
                                </div>

                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        :disabled="abstractForm.processing"
                                        class="w-full bg-primary hover:bg-primary-dark text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-lg shadow-purple-500/20 transition-all duration-200 disabled:opacity-50 cursor-pointer"
                                    >
                                        {{ abstractForm.processing ? 'Submitting...' : 'SUBMIT ABSTRACT' }}
                                    </button>
                                </div>
                            </form>

                            <!-- Right: Guideline -->
                            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">Abstract Guideline</h3>
                                    <ul class="space-y-3 text-xs text-slate-600 font-medium">
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Abstract should be written in English.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Length should be a minimum of 200 words and maximum of 250 words.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Must be concise, clear, structured (problem, objectives, methods, results, conclusion).</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Abstract must be submitted using Microsoft Word format (.doc or .docx) or PDF.</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-6 pt-4 border-t border-slate-200">
                                    <a
                                        href="#"
                                        onclick="alert('Template file download feature initialized.'); return false;"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs py-3 px-4 rounded-xl shadow-sm transition-all text-center"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">download</span>
                                        Download Abstract Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: PAPER SUBMISSION -->
                    <div v-if="activeTab === 'paper'" class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-100">
                        <div class="border-b border-slate-100 pb-4 mb-6">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">description</span>
                                Full Paper Submission
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left: Form -->
                            <form @submit.prevent="submitPaper" class="space-y-4">
                                <h3 class="text-xs font-extrabold text-primary uppercase tracking-wider mb-2">Submit Your Full Paper Here</h3>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Paper Title</label>
                                    <input
                                        v-model="paperForm.title"
                                        type="text"
                                        placeholder="Enter full paper title..."
                                        class="admin-input"
                                        required
                                    />
                                    <span v-if="paperForm.errors.title" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ paperForm.errors.title }}</span>
                                </div>

                                <div v-if="props.abstracts.length > 0">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Link to Approved Abstract</label>
                                    <select v-model="paperForm.abstract_id" class="admin-input">
                                        <option value="">Select linked abstract (Optional)...</option>
                                        <option v-for="abs in props.abstracts" :key="abs.id" :value="abs.id">
                                            [{{ abs.abstract_code }}] {{ abs.title }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Full Paper File</label>
                                    <input
                                        type="file"
                                        @change="e => handleFileChange(e, 'paper')"
                                        accept=".doc,.docx,.pdf"
                                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-primary hover:file:bg-purple-100 cursor-pointer"
                                        required
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Accepted formats: .doc, .docx, .pdf (Max 20MB)</p>
                                    <span v-if="paperForm.errors.file" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ paperForm.errors.file }}</span>
                                </div>

                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        :disabled="paperForm.processing"
                                        class="w-full bg-primary hover:bg-primary-dark text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-lg shadow-purple-500/20 transition-all duration-200 disabled:opacity-50 cursor-pointer"
                                    >
                                        {{ paperForm.processing ? 'Submitting...' : 'SUBMIT FULL PAPER' }}
                                    </button>
                                </div>
                            </form>

                            <!-- Right: Guideline -->
                            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">Full Paper Guideline</h3>
                                    <ul class="space-y-3 text-xs text-slate-600 font-medium">
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Full Paper should be 6 to 10 pages in IEEE/Springer proceedings format.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Ensure all figures and tables are high resolution and referenced correctly.</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-primary font-bold">➤</span>
                                            <span>Plagiarism score must be below 20%.</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-6 pt-4 border-t border-slate-200">
                                    <a
                                        href="#"
                                        onclick="alert('Paper template download initialized.'); return false;"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs py-3 px-4 rounded-xl shadow-sm transition-all text-center"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">download</span>
                                        Download Paper Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submissions History List -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">history</span>
                            My Submissions History
                        </h3>

                        <div v-if="props.abstracts.length === 0 && props.papers.length === 0" class="text-center py-8 text-xs text-slate-400">
                            No submissions uploaded yet. Use the form above to submit your abstract or paper.
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Abstract Items -->
                            <div v-for="abs in props.abstracts" :key="'abs-'+abs.id" class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-purple-100 text-purple-700 font-mono text-[10px] font-bold mb-1">
                                            ABSTRACT: {{ abs.abstract_code }}
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-900">{{ abs.title }}</h4>
                                        <p class="text-[10px] text-slate-500 mt-0.5">Topic: {{ abs.category?.name || 'General' }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider',
                                            abs.status === 'accepted' ? 'bg-emerald-100 text-emerald-700' :
                                            abs.status === 'rejected' ? 'bg-rose-100 text-rose-700' :
                                            abs.status === 'revision_required' ? 'bg-amber-100 text-amber-700' :
                                            'bg-slate-200 text-slate-700'
                                        ]">
                                            {{ abs.status.replace('_', ' ') }}
                                        </span>
                                        <a :href="'/storage/' + abs.file_path" target="_blank" class="block text-[10px] font-bold text-primary hover:underline mt-1.5">
                                            View Abstract File &rarr;
                                        </a>
                                    </div>
                                </div>

                                <!-- Reviewer Feedback Box -->
                                <div v-if="abs.review_notes" class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold text-[11px] text-amber-800">
                                        <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                        Reviewer Feedback / Catatan Panitia:
                                    </div>
                                    <p class="text-[11px] leading-relaxed text-amber-900/90 whitespace-pre-line pl-5">{{ abs.review_notes }}</p>
                                </div>
                            </div>

                            <!-- Full Paper Items -->
                            <div v-for="paper in props.papers" :key="'paper-'+paper.id" class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-mono text-[10px] font-bold mb-1">
                                            FULL PAPER: {{ paper.paper_code }}
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-900">{{ paper.title }}</h4>
                                        <p v-if="paper.abstract" class="text-[10px] text-slate-500 mt-0.5">Linked Abstract: {{ paper.abstract.abstract_code }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider',
                                            paper.status === 'accepted' ? 'bg-emerald-100 text-emerald-700' :
                                            paper.status === 'rejected' ? 'bg-rose-100 text-rose-700' :
                                            paper.status === 'revision_required' ? 'bg-amber-100 text-amber-700' :
                                            'bg-slate-200 text-slate-700'
                                        ]">
                                            {{ paper.status.replace('_', ' ') }}
                                        </span>
                                        <a :href="'/storage/' + paper.file_path" target="_blank" class="block text-[10px] font-bold text-primary hover:underline mt-1.5">
                                            View Manuscript File &rarr;
                                        </a>
                                    </div>
                                </div>

                                <!-- Reviewer Feedback Box -->
                                <div v-if="paper.review_notes" class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold text-[11px] text-amber-800">
                                        <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                        Reviewer Feedback / Catatan Panitia:
                                    </div>
                                    <p class="text-[11px] leading-relaxed text-amber-900/90 whitespace-pre-line pl-5">{{ paper.review_notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Sidebar: User Summary & Status Checklist Card -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-sidebar text-white rounded-3xl p-6 shadow-xl relative overflow-hidden flex flex-col items-center text-center">
                        <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>

                        <!-- User Profile Avatar Circle -->
                        <div class="w-24 h-24 rounded-full bg-white/10 border-2 border-white/20 flex items-center justify-center text-white text-3xl font-black mb-4 shadow-inner">
                            {{ props.userSummary?.name?.charAt(0).toUpperCase() }}
                        </div>

                        <h3 class="text-base font-extrabold tracking-tight text-white">{{ props.userSummary?.name }}</h3>
                        <p class="text-xs font-medium text-purple-200 mt-0.5">{{ props.userSummary?.role }}</p>
                        <span class="inline-block mt-2 px-3 py-1 bg-gold/20 text-gold rounded-full text-[11px] font-extrabold tracking-wider border border-gold/30">
                            {{ props.userSummary?.code }}
                        </span>

                        <div class="w-full border-t border-purple-800/60 my-6"></div>

                        <!-- Checklist Status -->
                        <div class="w-full text-left space-y-3.5">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-purple-200">Upload Abstract:</span>
                                <span v-if="props.statusChecklist?.hasUploadedAbstract" class="flex items-center gap-1 text-emerald-400 font-bold">
                                    Done <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                </span>
                                <span v-else class="text-rose-300 font-bold">Pending</span>
                            </div>

                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-purple-200">Payment:</span>
                                <span v-if="props.statusChecklist?.hasPaid" class="flex items-center gap-1 text-emerald-400 font-bold">
                                    Done <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                </span>
                                <span v-else class="text-rose-300 font-bold">Pending</span>
                            </div>

                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-purple-200">Zoom Link:</span>
                                <span class="text-amber-300 font-bold">{{ props.statusChecklist?.zoomLink }}</span>
                            </div>
                        </div>

                        <div class="w-full border-t border-purple-800/60 my-6"></div>

                        <div class="text-[10px] text-purple-300/60 uppercase font-bold tracking-widest">
                            POWERED BY
                        </div>
                        <div class="flex items-center gap-2 mt-2 opacity-80">
                            <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-5 w-auto object-contain" />
                            <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-5 w-auto object-contain" />
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </ParticipantLayout>
</template>
