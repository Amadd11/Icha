<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';

const props = defineProps({
    activeConference: Object,
    existingRegistration: Object,
    payment: Object,
    registrationFees: Array,
    userProfile: Object,
});

// Modal state
const isProofModalOpen = ref(false);

// Form 1: Registration Creation
const regForm = useForm({
    registration_fee_id: '',
    currency:            'IDR',
    notes:               '',
});

const selectedFee = computed(() => {
    return props.registrationFees?.find(t => t.id === regForm.registration_fee_id);
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
                    Select your registration fee package and submit payment receipt for {{ activeConference?.title || 'the conference' }}
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
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Step 1: Choose Registration Fee Package</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ activeConference?.title }} — {{ activeConference?.theme }}</p>
                </div>

                <form @submit.prevent="submitRegistration" class="space-y-5">
                    <!-- Fee Package Cards -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Registration Package <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                v-for="fee in registrationFees"
                                :key="fee.id"
                                :class="[
                                    'relative flex cursor-pointer flex-col rounded-xl border p-4 transition',
                                    regForm.registration_fee_id === fee.id
                                        ? 'border-purple-600 bg-purple-50/60 ring-2 ring-purple-600/20'
                                        : 'border-slate-200 bg-white hover:bg-slate-50/50'
                                ]"
                            >
                                <input
                                    type="radio"
                                    :value="fee.id"
                                    v-model="regForm.registration_fee_id"
                                    class="sr-only"
                                    required
                                />
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">{{ fee.name }}</span>
                                    <span
                                        :class="[
                                            'rounded px-2 py-0.5 text-[10px] font-bold uppercase',
                                            fee.mode === 'offline' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'
                                        ]"
                                    >
                                        {{ fee.mode }}
                                    </span>
                                </div>
                                <p class="text-sm font-black text-slate-900 mt-2">
                                    Rp {{ Number(fee.price).toLocaleString('id-ID') }}
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
                            :disabled="regForm.processing || !regForm.registration_fee_id"
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
                        <p class="text-xs text-slate-500 mt-0.5">Package: <strong>{{ existingRegistration.registration_fee?.name || existingRegistration.registration_type?.name }}</strong></p>
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
                            <div class="space-y-1 py-1 font-mono text-xs">
                                <p><span class="text-slate-400">Bank:</span> <strong>Bank Syariah Indonesia (BSI)</strong></p>
                                <p><span class="text-slate-400">Account No:</span> <strong class="text-purple-900 text-sm">7192837465</strong></p>
                                <p><span class="text-slate-400">Account Name:</span> <strong>PANITIA ICHA PIPMARSI</strong></p>
                            </div>
                            <p class="text-[11px] text-slate-500 italic pt-1 border-t border-slate-200">
                                Include Invoice Number <strong>{{ existingRegistration.invoice_number }}</strong> in transfer description.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Payment Proof Upload Form -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">
                            Upload Payment Receipt
                        </h3>

                        <!-- If verified -->
                        <div v-if="payment?.status === 'verified'" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-center text-xs text-emerald-800 space-y-1">
                            <p class="font-black text-sm">✅ Payment Verified</p>
                            <p>Your registration is confirmed. You can now submit your abstract and attend the event.</p>
                            <div v-if="payment?.proof_file" class="pt-3">
                                <button
                                    @click="isProofModalOpen = true"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 underline hover:text-emerald-900 cursor-pointer"
                                >
                                    🔍 View Uploaded Receipt
                                </button>
                            </div>
                        </div>

                        <!-- If not verified yet -->
                        <form v-else @submit.prevent="submitPayment" class="space-y-4">
                            <div v-if="payment?.status === 'rejected'" class="rounded-xl bg-red-50 border border-red-200 p-3 text-xs text-red-700">
                                <p class="font-bold">❌ Previous Proof Rejected</p>
                                <p class="mt-0.5 text-[11px]">{{ payment?.rejection_reason || 'Please upload a clearer receipt photo showing transaction date and amount.' }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
                                <select v-model="paymentForm.payment_method" class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50 py-2 px-3 focus:bg-white focus:border-purple-600">
                                    <option value="Bank Transfer (BSI)">Bank Transfer (BSI)</option>
                                    <option value="Bank Transfer (Mandiri)">Bank Transfer (Mandiri)</option>
                                    <option value="Bank Transfer (BCA)">Bank Transfer (BCA)</option>
                                    <option value="Credit Card / Stripe">Credit Card / Stripe</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Receipt / Transfer Slip File <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    accept="image/*,application/pdf"
                                    @change="onFileChange"
                                    required
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer border border-slate-200 rounded-xl p-1"
                                />
                                <p class="text-[10px] text-slate-400 mt-1">Accepted: JPG, PNG, PDF (Max 5MB)</p>
                            </div>

                            <!-- Preview -->
                            <div v-if="proofPreview" class="pt-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Selected Preview</span>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-2 text-center">
                                    <img v-if="!isPdf(proofPreview)" :src="proofPreview" alt="Receipt Preview" class="max-h-40 mx-auto rounded-lg object-contain shadow-xs" />
                                    <span v-else class="text-xs font-bold text-purple-800">📄 PDF Document Ready to Upload</span>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="paymentForm.processing || !paymentForm.proof_file"
                                class="w-full rounded-xl bg-purple-900 hover:bg-purple-950 text-gold font-bold text-xs py-2.5 transition shadow-xs cursor-pointer disabled:opacity-50"
                            >
                                {{ paymentForm.processing ? 'Uploading...' : 'Submit Payment Proof' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Receipt Modal -->
            <div v-if="isProofModalOpen && payment?.proof_file" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
                <div class="relative max-w-2xl w-full bg-white rounded-2xl p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-sm text-slate-900">Your Uploaded Receipt</h4>
                        <button @click="isProofModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                    </div>
                    <div class="max-h-[65vh] overflow-y-auto text-center">
                        <img
                            v-if="!isPdf(payment.proof_file)"
                            :src="formatStorageUrl(payment.proof_file)"
                            alt="Receipt Proof"
                            class="max-h-[60vh] mx-auto rounded-xl shadow-xs"
                        />
                        <a
                            v-else
                            :href="formatStorageUrl(payment.proof_file)"
                            target="_blank"
                            class="inline-block rounded-xl bg-purple-100 text-purple-800 font-bold px-4 py-2 text-xs"
                        >
                            Open PDF Receipt in New Tab ↗
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </ParticipantLayout>
</template>
