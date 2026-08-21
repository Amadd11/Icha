<script setup>
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useClipboard } from '@/Composables/useClipboard';
import { useFileUpload } from '@/Composables/useFileUpload';
import { useStatusBadge } from '@/Composables/useStatusBadge';

const props = defineProps({
    registration: Object,
    payment: Object,
    conference: Object,
});

const activeConf = props.conference || props.registration?.conference;

const { copyItem, copiedKey } = useClipboard();
const { getBadgeClass, getStatusLabel } = useStatusBadge();

const {
    file: selectedProof,
    previewUrl: proofPreview,
    isPdf: isProofPdf,
    handleFileChange,
    error: fileError,
} = useFileUpload({
    allowedTypes: ['.jpg', '.jpeg', '.png', '.pdf'],
    maxSizeMb: 5,
    initialPreview: props.payment?.proof_file ? '/storage/' + props.payment.proof_file : null,
});

const form = useForm({
    registration_id: props.registration.id,
    payment_method:  props.payment?.payment_method ?? 'Bank Transfer (BSI / Mandiri)',
    proof_file:      null,
});

function onFileChange(e) {
    handleFileChange(e);
    if (selectedProof.value) {
        form.proof_file = selectedProof.value;
    }
}

function submit() {
    form.post(route('participant.payment.submit'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Payment Confirmation" />
    <ParticipantLayout>
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">Payment & Invoice</h1>
            <p class="text-xs text-slate-500">Upload payment receipt for verification</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">

            <!-- Invoice Card -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">INVOICE</span>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-extrabold text-primary">{{ registration.invoice_number }}</h2>
                            <button
                                type="button"
                                @click="copyItem('invoice', registration.invoice_number)"
                                class="inline-flex items-center gap-1 text-slate-400 hover:text-primary transition cursor-pointer p-0.5"
                                title="Copy Invoice Number"
                            >
                                <span class="material-symbols-outlined text-[16px] leading-none">
                                    {{ copiedKey === 'invoice' ? 'check' : 'content_copy' }}
                                </span>
                                <span v-if="copiedKey === 'invoice'" class="text-emerald-600 text-[10px] font-bold">Copied!</span>
                            </button>
                        </div>
                    </div>
                    <span :class="[
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border',
                        getBadgeClass(registration.status)
                    ]">
                        {{ getStatusLabel(registration.status) }}
                    </span>
                </div>

                <div class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Category:</span>
                        <span class="font-bold text-slate-800">{{ registration.registration_type?.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Rate:</span>
                        <span class="font-semibold text-slate-800">{{ registration.is_early_bird ? 'Early Bird' : 'Regular' }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-3 text-base">
                        <span class="font-bold text-slate-800">Total Payable:</span>
                        <span class="font-extrabold text-primary">{{ registration.currency }} {{ Number(registration.amount).toLocaleString() }}</span>
                    </div>
                </div>

                <!-- Bank Transfer Instructions -->
                <div class="mt-8 rounded-xl bg-slate-50 p-4 text-xs text-slate-600 space-y-2">
                    <p class="font-bold text-slate-800">Bank Transfer Details:</p>
                    <p><strong>Bank:</strong> {{ activeConf?.bank_name || 'Bank Syariah Indonesia (BSI)' }}</p>
                    <div class="flex items-center gap-2">
                        <p><strong>Account Number:</strong> <span class="font-mono font-bold text-slate-900">{{ activeConf?.bank_account_number || '7192837465' }}</span></p>
                        <button
                            type="button"
                            @click="copyItem('bank', activeConf?.bank_account_number || '7192837465')"
                            class="inline-flex items-center gap-1 text-slate-400 hover:text-purple-800 transition cursor-pointer p-0.5"
                            title="Copy Account Number"
                        >
                            <span class="material-symbols-outlined text-[16px] leading-none">
                                {{ copiedKey === 'bank' ? 'check' : 'content_copy' }}
                            </span>
                            <span v-if="copiedKey === 'bank'" class="text-emerald-600 text-[10px] font-bold">Copied!</span>
                        </button>
                    </div>
                    <p><strong>Account Name:</strong> {{ activeConf?.bank_account_holder || 'PANITIA ICHA PIPMARSI' }}</p>
                    <p v-if="activeConf?.bank_instructions" class="text-[11px] text-slate-500 italic pt-1 border-t border-slate-200">
                        {{ activeConf.bank_instructions }}
                    </p>
                </div>
            </div>

            <!-- Payment Upload Form -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Payment Proof Upload</h2>

                <!-- Rejected Reason Alert -->
                <div v-if="payment && payment.status === 'rejected'" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-700">
                    <p class="font-bold">Payment Rejected:</p>
                    <p>{{ payment.rejection_reason ?? 'Invalid proof. Please re-upload.' }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-700">Payment Method <span class="text-red-500">*</span></label>
                        <select v-model="form.payment_method" class="admin-input text-xs font-semibold py-2.5" required>
                            <optgroup label="Bank Transfer (Indonesia)">
                                <option value="Bank Transfer (BSI)">Bank Syariah Indonesia (BSI)</option>
                                <option value="Bank Transfer (Mandiri)">Bank Mandiri</option>
                                <option value="Bank Transfer (BCA)">Bank Central Asia (BCA)</option>
                                <option value="Bank Transfer (BRI)">Bank Rakyat Indonesia (BRI)</option>
                                <option value="Bank Transfer (BNI)">Bank Negara Indonesia (BNI)</option>
                            </optgroup>
                            <optgroup label="E-Wallet & QRIS">
                                <option value="QRIS / E-Wallet (GoPay, OVO, Dana, ShopeePay)">QRIS / E-Wallet (GoPay, OVO, Dana, ShopeePay)</option>
                            </optgroup>
                            <optgroup label="Credit Card & International">
                                <option value="Credit Card / Debit Card (Visa / Mastercard)">Credit Card / Debit Card (Visa / Mastercard)</option>
                                <option value="International Wire / TT (SWIFT)">International Wire Transfer (SWIFT / Telegraphic Transfer)</option>
                                <option value="PayPal / Stripe">PayPal / Stripe</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="Institutional Sponsorship / Invoice Billing">Institutional Sponsorship / Invoice Billing</option>
                                <option value="Other Payment Method">Other Payment Method</option>
                            </optgroup>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Upload Receipt / Proof File <span class="text-red-400">*</span></label>
                        <input type="file" accept="image/*,.pdf" @change="onFileChange" class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/20" :required="!payment" />
                        <p class="mt-1 text-xs text-slate-400">Allowed formats: JPG, PNG, PDF (Max 5MB)</p>
                        <p v-if="form.errors.proof_file" class="mt-1 text-xs text-red-500">{{ form.errors.proof_file }}</p>
                    </div>

                    <div v-if="proofPreview" class="mt-3 overflow-hidden rounded-xl border border-slate-200 p-2">
                        <img v-if="!proofPreview.endsWith('.pdf')" :src="proofPreview" class="max-h-48 rounded object-contain" />
                        <a v-else :href="proofPreview" target="_blank" class="text-xs font-semibold text-primary underline">View Uploaded PDF</a>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || registration.status === 'paid'"
                        class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50"
                    >
                        {{ form.processing ? 'Uploading...' : (registration.status === 'paid' ? 'Payment Verified ✓' : 'Submit Proof of Payment') }}
                    </button>
                </form>
            </div>

        </div>
    </ParticipantLayout>
</template>
