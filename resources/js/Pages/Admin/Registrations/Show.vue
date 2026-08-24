<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDateTime } from '@/Utils/formatters';
import { formatRupiah } from '@/Composables/useFormatRupiah';

const props = defineProps({
    registration: Object,
});

const proofModalOpen = ref(false);
const sendingInvoice = ref(false);

function sendInvoiceEmail() {
    if (confirm(`Send invoice email to ${props.registration.user?.email}?`)) {
        sendingInvoice.value = true;
        router.post(route('admin.registrations.send-invoice', props.registration.id), {}, {
            preserveScroll: true,
            onFinish: () => {
                sendingInvoice.value = false;
            }
        });
    }
}

function printInvoice() {
    window.print();
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
    <Head title="Registration Detail - Admin" />
    <AdminLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registration Detail</h1>
                <p class="text-xs text-slate-500">Invoice: {{ registration.invoice_number }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Print Invoice Button -->
                <button
                    type="button"
                    @click="printInvoice"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-4 py-2 text-xs font-bold text-slate-700 transition cursor-pointer shadow-2xs"
                >
                    <span>🖨️</span>
                    <span>Print Invoice</span>
                </button>

                <!-- Send Email Button -->
                <button
                    @click="sendInvoiceEmail"
                    :disabled="sendingInvoice"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-purple-900 px-4 py-2 text-xs font-bold text-gold transition hover:bg-purple-950 disabled:opacity-50 cursor-pointer shadow-xs"
                >
                    <span>📧</span>
                    <span>{{ sendingInvoice ? 'Sending...' : 'Send / Resend Invoice Email' }}</span>
                </button>

                <Link
                    :href="route('admin.registrations.index')"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    &larr; Back to List
                </Link>
            </div>
        </div>

        <div class="max-w-4xl space-y-6">
            <!-- Registration Summary Card -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice</span>
                        <h2 class="text-lg font-bold text-primary font-mono">{{ registration.invoice_number }}</h2>
                    </div>
                    <span :class="[
                        'rounded-full px-3 py-1 text-xs font-bold uppercase',
                        registration.status === 'verified' || registration.status === 'paid' ? 'bg-green-100 text-green-700' :
                        registration.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'
                    ]">
                        {{ registration.status }}
                    </span>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 text-sm">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Participant Info</h3>
                        <p class="font-bold text-slate-800">{{ registration.user?.name }}</p>
                        <p class="text-slate-500">{{ registration.user?.email }}</p>
                        <p class="text-slate-500">Institution: {{ registration.user?.profile?.institution ?? '—' }}</p>
                        <p class="text-slate-400 text-xs mt-2">Registered At: <span class="font-mono text-slate-700">{{ formatDateTime(registration.created_at) }}</span></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Registration Fee Package</h3>
                        <p class="font-bold text-slate-800">{{ registration.registration_fee?.name || registration.registration_type?.name }}</p>
                        <p class="mt-2 text-lg font-extrabold text-primary">{{ formatRupiah(registration.amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Proof Info Card -->
            <div v-if="registration.payment" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Payment & Verification Details</h3>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-1 text-xs">
                        <p class="text-sm font-semibold text-slate-800">Method: {{ registration.payment.payment_method || 'Bank Transfer' }}</p>
                        <p class="text-slate-600">Submitted at: <span class="font-mono font-bold">{{ formatDateTime(registration.payment.paid_at || registration.payment.created_at) }}</span></p>
                        <p v-if="registration.payment.verified_at" class="text-emerald-700 font-bold">Approved at: <span class="font-mono">{{ formatDateTime(registration.payment.verified_at) }}</span></p>
                        <p v-if="registration.payment.verifier" class="text-emerald-800 text-[11px]">Verified by: <strong>{{ registration.payment.verifier.name }}</strong></p>
                    </div>
                    <button
                        @click="proofModalOpen = true"
                        class="rounded-xl bg-purple-900 hover:bg-purple-950 text-gold px-4 py-2.5 text-xs font-bold shadow-sm transition cursor-pointer"
                    >
                         View Proof Modal
                    </button>
                </div>
            </div>
        </div>

        <!-- 🖼️ Payment Proof Preview Modal -->
        <div v-if="proofModalOpen && registration.payment" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 overflow-y-auto">
            <div class="relative w-full max-w-3xl rounded-3xl bg-white shadow-2xl overflow-hidden border border-slate-100 my-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black text-slate-900">Payment Proof Inspection</h3>
                            <span class="rounded-md bg-purple-100 px-2 py-0.5 text-xs font-extrabold text-purple-800 font-mono">
                                {{ registration.invoice_number }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Participant: {{ registration.user?.name }} ({{ registration.user?.email }})</p>
                    </div>
                    <button
                        @click="proofModalOpen = false"
                        class="rounded-xl bg-slate-200/60 p-2 text-slate-600 hover:bg-slate-200 transition cursor-pointer font-bold text-sm"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                    <!-- Left: Document Preview (2 cols) -->
                    <div class="md:col-span-2 flex flex-col items-center justify-center bg-slate-100 rounded-2xl border border-slate-200 p-3 min-h-[320px] max-h-[480px] overflow-hidden">
                        <template v-if="isPdf(registration.payment.proof_file)">
                            <iframe :src="formatStorageUrl(registration.payment.proof_file)" class="w-full h-[400px] rounded-xl border-0"></iframe>
                        </template>
                        <template v-else>
                            <img
                                :src="formatStorageUrl(registration.payment.proof_file)"
                                alt="Payment Proof"
                                class="max-h-[420px] w-auto max-w-full object-contain rounded-xl shadow-xs"
                            />
                        </template>
                    </div>

                    <!-- Right: Info Details & Actions (1 col) -->
                    <div class="md:col-span-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-purple-50/60 border border-purple-100 p-4">
                                <p class="text-[10px] font-black uppercase tracking-wider text-purple-900/70">Total Amount</p>
                                <p class="text-xl font-black text-purple-950 mt-1">{{ formatRupiah(registration.amount) }}</p>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-slate-400 font-medium block">Registration Type</span>
                                    <span class="font-extrabold text-slate-800">{{ registration.registration_fee?.name || registration.registrationFee?.name || 'Standard Registration' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Payment Method</span>
                                    <span class="font-extrabold text-slate-800 uppercase">{{ registration.payment.payment_method || 'Bank Transfer' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Submitted At</span>
                                    <span class="font-semibold text-slate-800">{{ formatDateTime(registration.payment.paid_at || registration.payment.created_at) }}</span>
                                </div>
                                <div v-if="registration.payment.verified_at">
                                    <span class="text-slate-400 font-medium block">Approved At</span>
                                    <span class="font-bold text-emerald-700">{{ formatDateTime(registration.payment.verified_at) }}</span>
                                </div>
                                <div v-if="registration.payment.verifier">
                                    <span class="text-slate-400 font-medium block">Verified By</span>
                                    <span class="font-semibold text-slate-800">{{ registration.payment.verifier.name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Current Status</span>
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-extrabold capitalize mt-0.5 border',
                                        registration.payment.status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        registration.payment.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        {{ registration.payment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="proofModalOpen = false"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold py-2 text-xs transition cursor-pointer"
                        >
                            Close Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
