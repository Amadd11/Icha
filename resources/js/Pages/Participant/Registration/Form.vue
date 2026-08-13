<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';

const props = defineProps({
    activeConference: Object,
    existingRegistration: Object,
    payment: Object,
    registrationTypes: Array,
    userProfile: Object,
});

// Modal state
const isProofModalOpen = ref(false);

// Form 1: Registration Creation
const regForm = useForm({
    registration_type_id: '',
    currency:             'IDR',
    notes:                '',
});

const selectedType = computed(() => {
    return props.registrationTypes?.find(t => t.id === regForm.registration_type_id);
});

function submitRegistration() {
    regForm.post(route('participant.registration.store'));
}

// Form 2: Payment Receipt Upload
const proofPreview = ref(props.payment?.proof_file ? '/storage/' + props.payment.proof_file : null);

const paymentForm = useForm({
    registration_id: props.existingRegistration?.id,
    payment_method:  props.payment?.payment_method ?? 'Bank Transfer (BSI)',
    proof_file:      null,
});

function onFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        paymentForm.proof_file = file;
        proofPreview.value = URL.createObjectURL(file);
    }
}

function submitPayment() {
    paymentForm.post(route('participant.payment.submit'), {
        forceFormData: true,
    });
}

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

function isPdf(path) {
    return path && path.toLowerCase().endsWith('.pdf');
}
</script>

<template>
    <Head title="Registration & Payment - Participant" />
    <ParticipantLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registration & Payment Portal</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Select your ticket category and submit payment receipt for {{ activeConference?.title || 'the conference' }}
                </p>
            </div>

            <!-- Profile Incomplete Warning -->
            <div v-if="!userProfile" class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-amber-800 text-xs">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold">⚠️ Profile Incomplete:</span>
                        <span>Please complete your participant profile details before registering.</span>
                    </div>
                    <Link :href="route('participant.profile.edit')" class="rounded-xl bg-amber-600 px-3.5 py-1.5 font-bold text-white hover:bg-amber-700 transition">
                        Complete Profile
                    </Link>
                </div>
            </div>

            <!-- STEP 1: REGISTRATION FORM (If not registered yet) -->
            <div v-if="!existingRegistration" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs max-w-3xl space-y-6">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Step 1: Choose Registration Ticket Category</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ activeConference?.title }} — {{ activeConference?.theme }}</p>
                </div>

                <form @submit.prevent="submitRegistration" class="space-y-5">
                    <!-- Ticket Category Cards -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Registration Category <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                v-for="type in registrationTypes"
                                :key="type.id"
                                :class="[
                                    'relative flex cursor-pointer flex-col rounded-xl border p-4 transition',
                                    regForm.registration_type_id === type.id
                                        ? 'border-purple-600 bg-purple-50/60 ring-2 ring-purple-600/20'
                                        : 'border-slate-200 bg-white hover:bg-slate-50/50'
                                ]"
                            >
                                <input
                                    type="radio"
                                    :value="type.id"
                                    v-model="regForm.registration_type_id"
                                    class="sr-only"
                                    required
                                />
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">{{ type.name }}</span>
                                    <span v-if="type.is_presenter" class="rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-bold text-purple-800">
                                        Presenter
                                    </span>
                                </div>
                                <p class="text-sm font-black text-slate-900 mt-2">
                                    Rp {{ Number(type.price_idr).toLocaleString() }}
                                    <span v-if="type.price_usd > 0" class="text-xs text-slate-400 font-normal"> / ${{ type.price_usd }}</span>
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Currency Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Currency Preference <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 text-xs font-semibold text-slate-700">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" value="IDR" v-model="regForm.currency" class="text-purple-700 focus:ring-purple-700" />
                                <span>Indonesian Rupiah (IDR)</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" value="USD" v-model="regForm.currency" class="text-purple-700 focus:ring-purple-700" />
                                <span>US Dollar (USD)</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button
                            type="submit"
                            :disabled="regForm.processing || !regForm.registration_type_id"
                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-6 py-2.5 transition cursor-pointer shadow-xs disabled:opacity-50"
                        >
                            {{ regForm.processing ? 'Registering...' : 'Register & Generate Invoice →' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 2: INVOICE & PAYMENT PROOF UPLOAD (If registered) -->
            <div v-else class="space-y-6">
                <!-- Registration Summary Header Bar -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Invoice Number</span>
                        <h2 class="text-xl font-black text-purple-950">{{ existingRegistration.invoice_number }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Category: <strong>{{ existingRegistration.registration_type?.name }}</strong></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Payable</span>
                            <span class="text-lg font-black text-slate-900">
                                {{ existingRegistration.currency }} {{ Number(existingRegistration.amount).toLocaleString() }}
                            </span>
                        </div>
                        <span :class="[
                            'rounded-full px-3 py-1 text-xs font-bold uppercase border',
                            payment?.status === 'verified' || existingRegistration.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                            payment?.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' :
                            'bg-amber-50 text-amber-700 border-amber-200'
                        ]">
                            {{ payment?.status ? payment.status.replace('_', ' ') : 'Unpaid' }}
                        </span>
                    </div>
                </div>

                <!-- Two-Column Payment Details & Receipt Upload -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: Bank Transfer Details Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">
                            Bank Transfer Instructions
                        </h3>

                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2 text-xs text-slate-700">
                            <p class="font-bold text-slate-900">Please transfer the exact amount to:</p>
                            <div class="space-y-1 pt-1">
                                <p><strong>Bank Name:</strong> Bank Syariah Indonesia (BSI)</p>
                                <p><strong>Account Number:</strong> <span class="font-mono font-bold text-purple-900">7123-4567-89</span></p>
                                <p><strong>Account Name:</strong> Panitia ICHA Conference</p>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 space-y-1">
                            <p>💡 <strong>Note:</strong> After transferring, please upload a clear image or PDF of your transfer receipt on the right form to speed up admin verification.</p>
                        </div>
                    </div>

                    <!-- Right: Payment Proof Upload Form Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">
                            Payment Receipt Status & Upload
                        </h3>

                        <!-- STATE 1: VERIFIED & APPROVED -->
                        <div v-if="payment && payment.status === 'verified'" class="space-y-4">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-800 space-y-1">
                                <p class="font-bold">✅ Payment Verified & Approved!</p>
                                <p>Your payment receipt has been verified by the committee. The <strong>Submission Portal</strong> is now unlocked.</p>
                            </div>

                            <div class="pt-2 flex items-center justify-between">
                                <button
                                    @click="isProofModalOpen = true"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-800 font-bold text-xs px-4 py-2 transition cursor-pointer"
                                >
                                    Check Uploaded Receipt Proof →
                                </button>
                                <Link
                                    :href="route('participant.submission.index')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2 transition cursor-pointer shadow-xs"
                                >
                                    Submission Portal →
                                </Link>
                            </div>
                        </div>

                        <!-- STATE 2: PENDING VERIFICATION -->
                        <div v-else-if="payment && payment.status === 'pending'" class="space-y-4">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 space-y-1">
                                <p class="font-bold">⏳ Payment Verification in Progress</p>
                                <p>Your uploaded payment receipt is currently being checked by admin. You will receive email notification upon verification.</p>
                            </div>

                            <div class="pt-2">
                                <button
                                    @click="isProofModalOpen = true"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-800 font-bold text-xs px-4 py-2 transition cursor-pointer"
                                >
                                    Check Uploaded Receipt Proof →
                                </button>
                            </div>
                        </div>

                        <!-- STATE 3 & 4: REJECTED OR INITIAL UPLOAD FORM -->
                        <div v-else class="space-y-4">
                            <!-- Rejection Warning Alert if rejected -->
                            <div v-if="payment && payment.status === 'rejected'" class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-700 space-y-1">
                                <p class="font-bold">⚠️ Payment Receipt Rejected by Admin:</p>
                                <p class="font-semibold">{{ payment.rejection_reason ?? 'Invalid proof. Please re-upload a clear transfer receipt.' }}</p>
                                <button
                                    v-if="payment.proof_file"
                                    @click="isProofModalOpen = true"
                                    class="text-[11px] font-bold text-red-800 underline mt-1 block cursor-pointer"
                                >
                                    View Previously Rejected Receipt
                                </button>
                            </div>

                            <form @submit.prevent="submitPayment" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                                    <input v-model="paymentForm.payment_method" type="text" class="admin-input" placeholder="e.g. Bank Transfer BSI" required />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        {{ payment && payment.status === 'rejected' ? 'Re-Upload New Proof File (.jpg, .png, .pdf)' : 'Upload Transfer Proof File (.jpg, .png, .pdf)' }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        @change="onFileChange"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                                        required
                                    />
                                </div>

                                <!-- Button to trigger modal preview if file is selected -->
                                <div v-if="proofPreview" class="pt-1">
                                    <button
                                        type="button"
                                        @click="isProofModalOpen = true"
                                        class="text-xs font-bold text-purple-700 hover:underline cursor-pointer"
                                    >
                                        🔍 Preview Selected File in Modal
                                    </button>
                                </div>

                                <div class="pt-2 flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="paymentForm.processing || !paymentForm.proof_file"
                                        class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-6 py-2.5 transition cursor-pointer shadow-xs disabled:opacity-50"
                                    >
                                        {{ paymentForm.processing ? 'Uploading...' : (payment && payment.status === 'rejected' ? 'Re-Upload Payment Receipt →' : 'Upload Payment Receipt →') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT PROOF MODAL DIALOG -->
            <div v-if="isProofModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                <div class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl border border-slate-200 text-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Payment Receipt Document</h3>
                            <p class="text-[11px] text-slate-500">Invoice {{ existingRegistration?.invoice_number }}</p>
                        </div>
                        <button @click="isProofModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">✕</button>
                    </div>

                    <!-- Modal Body: Image / PDF viewer -->
                    <div class="py-2 flex flex-col items-center justify-center">
                        <template v-if="proofPreview || payment?.proof_file">
                            <div v-if="isPdf(proofPreview || payment?.proof_file)" class="w-full h-80 border rounded-xl overflow-hidden">
                                <iframe :src="proofPreview || formatStorageUrl(payment?.proof_file)" class="w-full h-full"></iframe>
                            </div>
                            <div v-else class="max-h-96 max-w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 flex items-center justify-center">
                                <img :src="proofPreview || formatStorageUrl(payment?.proof_file)" alt="Payment Proof" class="max-h-88 max-w-full object-contain rounded-lg" />
                            </div>
                        </template>
                        <div v-else class="py-12 text-center text-slate-400 font-medium">
                            No proof document uploaded yet.
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-100 pt-3">
                        <button
                            type="button"
                            @click="isProofModalOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer ml-auto"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </ParticipantLayout>
</template>
