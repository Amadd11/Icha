<script setup>
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    registration: Object,
    payment: Object,
});

const proofPreview = ref(props.payment?.proof_file ? '/storage/' + props.payment.proof_file : null);

const form = useForm({
    registration_id: props.registration.id,
    payment_method:  props.payment?.payment_method ?? 'Bank Transfer (BSI / Mandiri)',
    proof_file:      null,
});

function onFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.proof_file = file;
        proofPreview.value = URL.createObjectURL(file);
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
                        <h2 class="text-xl font-extrabold text-primary">{{ registration.invoice_number }}</h2>
                    </div>
                    <span :class="[
                        'rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-wider',
                        registration.status === 'paid' ? 'bg-green-100 text-green-700' :
                        registration.status === 'waiting_verification' ? 'bg-amber-100 text-amber-700' :
                        registration.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'
                    ]">
                        {{ registration.status.replace('_', ' ') }}
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
                    <p><strong>Bank:</strong> Bank Syariah Indonesia (BSI)</p>
                    <p><strong>Account Number:</strong> 7123-4567-89</p>
                    <p><strong>Account Name:</strong> Panitia ICHA 2026</p>
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
                        <label class="mb-1 block text-sm font-medium text-slate-700">Payment Method <span class="text-red-400">*</span></label>
                        <input v-model="form.payment_method" type="text" class="admin-input" required />
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
