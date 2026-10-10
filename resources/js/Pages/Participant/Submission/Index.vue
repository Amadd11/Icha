<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';

const props = defineProps({
    activeConference: Object,
    categories: Array,
    abstracts: Array,
    isPaid: Boolean,
    statusChecklist: Object,
    userSummary: Object,
});

// Abstract Form
const abstractForm = useForm({
    title: '',
    category_id: props.categories?.[0]?.id || '',
    presentation_type: 'oral',
    keywords: '',
    abstract_text: '',
    file: null,
});

watch(() => props.categories, (cats) => {
    if (!abstractForm.category_id && cats && cats.length > 0) {
        abstractForm.category_id = cats[0].id;
    }
}, { immediate: true });

function handleFileChange(e) {
    abstractForm.file = e.target.files[0];
}

const isRevising = ref(false);

function startRevision() {
    if (props.abstracts && props.abstracts.length > 0) {
        abstractForm.title = props.abstracts[0].title || '';
        abstractForm.category_id = props.abstracts[0].category_id || props.categories?.[0]?.id || '';
        abstractForm.keywords = props.abstracts[0].keywords || '';
        abstractForm.abstract_text = props.abstracts[0].abstract_text || '';
    }
    isRevising.value = true;
}

function cancelRevision() {
    isRevising.value = false;
    abstractForm.reset();
}

function submitAbstract() {
    abstractForm.post(route('participant.submission.abstract.store'), {
        preserveScroll: true,
        onSuccess: () => {
            abstractForm.reset();
            isRevising.value = false;
        },
    });
}

function downloadTemplate() {
    if (props.activeConference?.abstract_template && props.activeConference?.slug) {
        window.location.href = route('template.download', {
            conference: props.activeConference.slug,
            type: 'abstract',
        });
    } else {
        alert('Berkas template abstrak belum diunggah oleh panitia.');
    }
}
</script>

<template>
    <Head title="Submission - ICHA 2026" />

    <ParticipantLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Header Banner -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Call for Abstracts / Submission Portal</h1>
                    <p class="text-xs text-slate-500 mt-1">Submit your scientific abstract for peer-review at {{ props.activeConference?.title || 'ICHA 2026' }}.</p>
                </div>
                <button
                    v-if="props.activeConference?.abstract_template"
                    type="button"
                    @click="downloadTemplate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-xs transition shrink-0 cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Download Abstract Template
                </button>
            </div>

            <!-- Main Content Area: Left Form + Guideline | Right User Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <!-- Main Form & Guideline Section (Col-span 3) -->
                <div class="lg:col-span-3 space-y-6">

                    <!-- ABSTRACT SUBMISSION -->
                    <div class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-100">
                        <div class="border-b border-slate-100 pb-4 mb-6">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">add_circle</span>
                                Abstract Submission
                            </h2>
                        </div>

                        <!-- STATE 1: ALREADY UPLOADED -->
                        <div v-if="props.statusChecklist?.hasUploadedAbstract && props.abstracts.length > 0 && !isRevising" class="space-y-6">
                            <!-- 1. ACCEPTED STATE -->
                            <div v-if="props.abstracts[0].status === 'accepted'" class="rounded-2xl border border-emerald-300 bg-emerald-50/90 p-6 flex flex-col md:flex-row gap-5 items-center justify-between shadow-xs">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 shadow-2xs">
                                        <span class="material-symbols-outlined text-3xl">check_circle</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <h3 class="text-base font-extrabold text-emerald-950">
                                                Abstract Accepted for {{ props.abstracts[0].presentation_type === 'poster' ? 'Poster' : 'Oral' }} Presentation
                                            </h3>
                                            <span v-if="props.statusChecklist?.hasPaid" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                                Registration &amp; Payment Confirmed
                                            </span>
                                        </div>
                                        <p v-if="props.statusChecklist?.hasPaid" class="text-xs text-emerald-800 leading-relaxed">
                                            Selamat! Naskah abstrak ilmiah Anda telah diterima dan pembayaran registrasi presenter telah terverifikasi. Anda resmi terdaftar sebagai Pemakalah (Presenter) pada konferensi ini.
                                        </p>
                                        <p v-else class="text-xs text-emerald-800 leading-relaxed">
                                            Congratulations! Your paper has passed peer review. Please proceed to the registration portal to choose your presenter package and complete payment.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <Link
                                        v-if="props.statusChecklist?.hasPaid"
                                        :href="route('participant.registration.create')"
                                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-purple-900 hover:bg-purple-950 text-gold px-5 py-3 text-xs font-bold shadow-xs transition hover:-translate-y-0.5 cursor-pointer"
                                    >
                                        View Invoice &amp; Receipt &rarr;
                                    </Link>
                                    <Link
                                        v-else
                                        :href="route('participant.registration.create')"
                                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-xs font-bold shadow-xs transition hover:-translate-y-0.5 cursor-pointer"
                                    >
                                        Proceed to Payment &rarr;
                                    </Link>
                                </div>
                            </div>

                            <!-- 2. REJECTED STATE -->
                            <div v-else-if="props.abstracts[0].status === 'rejected'" class="rounded-2xl border border-rose-300 bg-rose-50/90 p-6 space-y-3 shadow-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                                        <span class="material-symbols-outlined text-2xl">cancel</span>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-extrabold text-rose-950">Abstract Not Accepted for Presentation</h3>
                                        <p class="text-xs text-rose-800">
                                            Thank you for submitting your abstract. The scientific committee decided that this submission cannot be accepted for presentation. No presenter payment is required.
                                        </p>
                                    </div>
                                </div>
                                <div v-if="props.abstracts[0].review_notes" class="p-3 bg-white rounded-xl border border-rose-200 text-xs text-rose-900 leading-relaxed">
                                    <strong>Committee Decision Note:</strong> {{ props.abstracts[0].review_notes }}
                                </div>
                                <div class="pt-2 flex items-center gap-3">
                                    <Link
                                        :href="route('participant.registration.create')"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 text-xs font-bold transition shadow-xs"
                                    >
                                        Attend as Non-Presenter Audience &rarr;
                                    </Link>
                                </div>
                            </div>

                            <!-- 3. UNDER REVIEW / PENDING -->
                            <div v-else-if="props.abstracts[0].status === 'pending' || props.abstracts[0].status === 'under_review'" class="rounded-2xl border border-blue-200 bg-blue-50/80 p-6 flex flex-col md:flex-row gap-5 items-center">
                                <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-600 shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-3xl">hourglass_top</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-extrabold text-blue-950 mb-0.5">Abstract Under Peer Review</h3>
                                    <p class="text-xs text-blue-800 leading-relaxed">
                                        Your abstract is currently being evaluated by the peer review committee. You do not need to make any payment while under review. Once a decision is finalized, you will receive notification and payment options.
                                    </p>
                                </div>
                            </div>

                            <!-- Revision Notice Box -->
                            <div v-if="props.abstracts[0].status === 'revision_required'" class="rounded-2xl border border-amber-300 bg-amber-50/80 p-5 space-y-4">
                                <div class="flex items-center gap-2 text-amber-950 font-extrabold text-sm">
                                    <span class="material-symbols-outlined text-amber-700 text-lg">warning</span>
                                    Abstract Revision Required
                                </div>

                                <!-- Committee Decision Note -->
                                <p v-if="props.abstracts[0].review_notes" class="text-xs text-amber-900 bg-white p-3 rounded-xl border border-amber-200 leading-relaxed font-medium">
                                    <strong>Committee Decision Note:</strong> {{ props.abstracts[0].review_notes }}
                                </p>

                                <!-- Peer Reviewers Feedback Cards -->
                                <div v-if="props.abstracts[0].reviewer_feedbacks?.length" class="space-y-2.5">
                                    <span class="text-[11px] font-bold text-amber-950 uppercase tracking-wider block">
                                        Peer Reviewer Evaluation &amp; Revision Notes:
                                    </span>
                                    <div class="grid gap-2.5">
                                        <div
                                            v-for="rf in props.abstracts[0].reviewer_feedbacks"
                                            :key="rf.reviewer_alias + '-' + rf.round_number"
                                            class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-2xs space-y-2"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-800">{{ rf.reviewer_alias }}</span>
                                                    <span v-if="rf.round_number > 1" class="text-[10px] text-purple-700 bg-purple-50 border border-purple-100 px-1.5 py-0.5 rounded font-bold">
                                                        Round {{ rf.round_number }}
                                                    </span>
                                                </div>
                                                <span
                                                    :class="[
                                                        'px-2 py-0.5 rounded-md text-[10px] font-bold',
                                                        rf.recommendation === 'ORAL' ? 'bg-emerald-100 text-emerald-800' :
                                                        rf.recommendation === 'POSTER' ? 'bg-blue-100 text-blue-800' :
                                                        rf.recommendation === 'REVISION' ? 'bg-amber-100 text-amber-800' :
                                                        'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ rf.recommendation }}
                                                </span>
                                            </div>
                                            <div v-if="rf.score_criteria_1 || rf.score_criteria_2" class="flex gap-4 text-[11px] text-slate-500 font-semibold">
                                                <span>Originality: <strong class="text-slate-700">{{ rf.score_criteria_1 }}/5</strong></span>
                                                <span>Methodology: <strong class="text-slate-700">{{ rf.score_criteria_2 }}/5</strong></span>
                                            </div>
                                            <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 leading-relaxed whitespace-pre-line">
                                                {{ rf.comments || 'No specific written feedback provided.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button
                                        @click="startRevision"
                                        class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-xs transition cursor-pointer"
                                    >
                                        Upload Revised Abstract &rarr;
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Your Submitted Abstract</h4>
                                    <button
                                        v-if="props.abstracts[0].status !== 'accepted'"
                                        @click="startRevision"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-primary font-bold text-xs transition cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">sync</span>
                                        Replace / Update File
                                    </button>
                                </div>

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
                                    <div class="pt-2 flex flex-wrap items-center gap-3">
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border',
                                            props.abstracts[0].status === 'accepted' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' :
                                            props.abstracts[0].status === 'rejected' ? 'border-rose-200 bg-rose-50 text-rose-700' :
                                            props.abstracts[0].status === 'revision_required' ? 'border-amber-200 bg-amber-50 text-amber-700' :
                                            'border-purple-200 bg-purple-50 text-purple-700'
                                        ]">
                                            Status: {{ props.abstracts[0].status ? props.abstracts[0].status.replace('_', ' ') : 'Under Review' }}
                                        </span>
                                        <span v-if="props.abstracts[0].status === 'accepted' && props.abstracts[0].presentation_type" class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border border-purple-200 bg-purple-50 text-purple-900">
                                            {{ props.abstracts[0].presentation_type === 'poster' ? 'Poster' : 'Oral' }}
                                        </span>
                                        <span
                                            v-if="props.abstracts[0].status === 'accepted' && props.abstracts[0].reviewer_feedbacks?.some(rf => rf.round_number > 1)"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border border-emerald-300 bg-emerald-100 text-emerald-800"
                                        >
                                            <span class="material-symbols-outlined text-[13px]">published_with_changes</span>
                                            Revised Manuscript Accepted
                                        </span>

                                        <a
                                            v-if="props.abstracts[0].file_path"
                                            :href="formatStorageUrl(props.abstracts[0].file_path)"
                                            target="_blank"
                                            download
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-primary font-bold text-xs shadow-2xs transition cursor-pointer"
                                        >
                                            <span class="material-symbols-outlined text-[16px]">download</span>
                                            <span>Download Current Abstract</span>
                                        </a>
                                    </div>

                                    <!-- Committee Decision Note if any -->
                                    <div
                                        v-if="props.abstracts[0].review_notes"
                                        :class="[
                                            'mt-4 p-3.5 rounded-xl border text-xs space-y-1',
                                            props.abstracts[0].status === 'accepted'
                                                ? 'bg-emerald-50/80 border-emerald-200 text-emerald-950'
                                                : props.abstracts[0].status === 'rejected'
                                                    ? 'bg-rose-50/80 border-rose-200 text-rose-950'
                                                    : 'bg-amber-50 border-amber-200 text-amber-950'
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'flex items-center gap-1.5 font-bold',
                                                props.abstracts[0].status === 'accepted'
                                                    ? 'text-emerald-800'
                                                    : props.abstracts[0].status === 'rejected'
                                                        ? 'text-rose-800'
                                                        : 'text-amber-800'
                                            ]"
                                        >
                                            <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                            Committee Decision Note:
                                        </div>
                                        <p
                                            :class="[
                                                'leading-relaxed whitespace-pre-line pl-5',
                                                props.abstracts[0].status === 'accepted'
                                                    ? 'text-emerald-900/90'
                                                    : props.abstracts[0].status === 'rejected'
                                                        ? 'text-rose-900/90'
                                                        : 'text-amber-900/90'
                                            ]"
                                        >{{ props.abstracts[0].review_notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STATE 2: UPLOAD FORM -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left: Form -->
                            <form @submit.prevent="submitAbstract" class="space-y-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-extrabold text-primary uppercase tracking-wider">
                                        {{ isRevising ? '🔄 Replace / Update Abstract File' : 'Submit Your Abstract Here' }}
                                    </h3>
                                    <button v-if="isRevising" type="button" @click="cancelRevision" class="text-xs font-bold text-slate-400 hover:text-slate-700">
                                        ✕ Cancel
                                    </button>
                                </div>

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
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ isRevising ? 'Upload Replacement Abstract File (.pdf)' : 'Upload your Abstract File (.pdf)' }}
                                    </label>
                                    <input
                                        type="file"
                                        @change="handleFileChange"
                                        accept=".pdf,application/pdf"
                                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-primary hover:file:bg-purple-100 cursor-pointer"
                                        required
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Accepted format: .pdf only (Max 10MB)</p>
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

                                <div class="pt-2 flex items-center gap-3">
                                    <button
                                        v-if="isRevising"
                                        type="button"
                                        @click="cancelRevision"
                                        class="w-1/3 border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs py-3 px-4 rounded-2xl transition cursor-pointer"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="abstractForm.processing"
                                        :class="[
                                            'bg-primary hover:bg-primary-dark text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-lg shadow-purple-500/20 transition-all duration-200 disabled:opacity-50 cursor-pointer',
                                            isRevising ? 'w-2/3' : 'w-full'
                                        ]"
                                    >
                                        {{ abstractForm.processing ? 'Saving...' : (isRevising ? 'REPLACE ABSTRACT' : 'SUBMIT ABSTRACT') }}
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
                                            <span>Abstract must be submitted in PDF format (.pdf).</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-6 pt-4 border-t border-slate-200">
                                    <button
                                        type="button"
                                        @click="downloadTemplate"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs py-3 px-4 rounded-xl shadow-sm transition-all text-center cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">download</span>
                                        Download Abstract Template
                                    </button>
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

                        <div v-if="props.abstracts.length === 0" class="text-center py-8 text-xs text-slate-400">
                            No abstracts uploaded yet. Use the form above to submit your scientific abstract.
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
                                        <a :href="formatStorageUrl(abs.file_path)" target="_blank" class="block text-[10px] font-bold text-primary hover:underline mt-1.5">
                                            View Abstract File &rarr;
                                        </a>
                                    </div>
                                </div>

                                <!-- Reviewer Feedback & Committee Notes -->
                                <div v-if="abs.review_notes || abs.reviewer_feedbacks?.length" class="space-y-3 pt-2 border-t border-slate-200/60">
                                    <!-- Committee Decision Note -->
                                    <div
                                        v-if="abs.review_notes"
                                        :class="[
                                            'p-3 rounded-xl border text-xs space-y-1',
                                            abs.status === 'accepted' ? 'bg-emerald-50/80 border-emerald-200 text-emerald-950' :
                                            abs.status === 'rejected' ? 'bg-rose-50/80 border-rose-200 text-rose-950' :
                                            'bg-amber-50/80 border-amber-200 text-amber-950'
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'flex items-center gap-1.5 font-bold text-[11px]',
                                                abs.status === 'accepted' ? 'text-emerald-800' :
                                                abs.status === 'rejected' ? 'text-rose-800' :
                                                'text-amber-800'
                                            ]"
                                        >
                                            <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                            Committee Decision Note:
                                        </div>
                                        <p
                                            :class="[
                                                'text-[11px] leading-relaxed whitespace-pre-line pl-5',
                                                abs.status === 'accepted' ? 'text-emerald-900/90' :
                                                abs.status === 'rejected' ? 'text-rose-900/90' :
                                                'text-amber-900/90'
                                            ]"
                                        >{{ abs.review_notes }}</p>
                                    </div>

                                    <!-- Peer Reviewers Cards -->
                                    <div v-if="abs.reviewer_feedbacks?.length" class="space-y-2">
                                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-purple-600">reviews</span>
                                            Peer Reviewer Evaluation Results (Double-Blind):
                                        </span>
                                        <div class="grid gap-2 sm:grid-cols-1">
                                            <div
                                                v-for="rf in abs.reviewer_feedbacks"
                                                :key="rf.reviewer_alias + '-' + rf.round_number"
                                                class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs space-y-1.5"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-bold text-slate-800">{{ rf.reviewer_alias }}</span>
                                                        <span v-if="rf.round_number > 1" class="text-[10px] text-purple-700 bg-purple-50 border border-purple-100 px-1.5 py-0.5 rounded font-bold">
                                                            Round {{ rf.round_number }}
                                                        </span>
                                                    </div>
                                                    <span
                                                        :class="[
                                                            'px-2 py-0.5 rounded-md text-[10px] font-bold',
                                                            rf.recommendation === 'ORAL' ? 'bg-emerald-100 text-emerald-800' :
                                                            rf.recommendation === 'POSTER' ? 'bg-blue-100 text-blue-800' :
                                                            rf.recommendation === 'REVISION' ? 'bg-amber-100 text-amber-800' :
                                                            'bg-red-100 text-red-800'
                                                        ]"
                                                    >
                                                        {{ rf.recommendation }}
                                                    </span>
                                                </div>
                                                <div v-if="rf.score_criteria_1 || rf.score_criteria_2" class="flex gap-4 text-[11px] text-slate-500 font-semibold">
                                                    <span>Originality: <strong class="text-slate-700">{{ rf.score_criteria_1 }}/5</strong></span>
                                                    <span>Methodology: <strong class="text-slate-700">{{ rf.score_criteria_2 }}/5</strong></span>
                                                </div>
                                                <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 leading-relaxed whitespace-pre-line">
                                                    {{ rf.comments || 'No specific written remarks provided.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
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
                        <p class="text-xs font-medium text-purple-200 mt-0.5">
                            {{ props.statusChecklist?.hasPaid ? 'Presenter (Verified)' : (props.statusChecklist?.isAbstractAccepted ? 'Presenter (Accepted)' : (props.userSummary?.role || 'Participant')) }}
                        </p>
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
                                <span v-else class="text-purple-300 font-medium text-[11px]">
                                    {{ props.statusChecklist?.isAbstractAccepted ? 'Payment Due' : 'After Acceptance' }}
                                </span>
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
