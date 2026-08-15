<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    registration: Object,
});

const proofModalOpen = ref(false);

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
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registration Detail</h1>
                <p class="text-xs text-slate-500">Invoice: {{ registration.invoice_number }}</p>
            </div>
            <Link
                :href="route('admin.registrations.index')"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="max-w-4xl space-y-6">
            <!-- Registration Summary Card -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice</span>
                        <h2 class="text-lg font-bold text-primary">{{ registration.invoice_number }}</h2>
                    </div>
                    <span :class="[
                        'rounded-full px-3 py-1 text-xs font-bold uppercase',
                        registration.status === 'verified' ? 'bg-green-100 text-green-700' :
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
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Registration Fee Package</h3>
                        <p class="font-bold text-slate-800">{{ registration.registration_fee?.name || registration.registration_type?.name }}</p>
                        <p class="text-slate-500">Rate: {{ registration.is_early_bird ? 'Early Bird' : 'Regular' }}</p>
                        <p class="mt-2 text-lg font-extrabold text-primary">{{ registration.currency }} {{ Number(registration.amount).toLocaleString() }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Proof Info Card -->
            <div v-if="registration.payment" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Payment Proof</h3>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Method: {{ registration.payment.payment_method || 'Bank Transfer' }}</p>
                        <p class="text-xs text-slate-400">Paid at: {{ registration.payment.paid_at ?? '—' }}</p>
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
                            <span class="rounded-md bg-purple-100 px-2 py-0.5 text-xs font-extrabold text-purple-800">
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

                    <!-- Right: Info Details (1 col) -->
                    <div class="md:col-span-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-purple-50/60 border border-purple-100 p-4">
                                <p class="text-[10px] font-black uppercase tracking-wider text-purple-900/70">Total Amount</p>
                                <p class="text-2xl font-black text-purple-950 mt-1">{{ registration.currency }} {{ Number(registration.amount).toLocaleString() }}</p>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-slate-400 font-medium block">Registration Type</span>
                                    <span class="font-extrabold text-slate-800">{{ registration.registration_type?.name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Payment Method</span>
                                    <span class="font-extrabold text-slate-800 uppercase">{{ registration.payment.payment_method || 'Bank Transfer' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Paid At</span>
                                    <span class="font-extrabold text-slate-800">{{ registration.payment.paid_at ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Current Status</span>
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-extrabold capitalize mt-0.5',
                                        registration.payment.status === 'verified' ? 'bg-emerald-100 text-emerald-800' :
                                        registration.payment.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'
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
