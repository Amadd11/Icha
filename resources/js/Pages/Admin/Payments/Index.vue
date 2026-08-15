<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatStorageUrl } from '@/Utils/formatters';
import { formatRupiah } from '@/Composables/useFormatRupiah';

const props = defineProps({
    payments: Object,
    currentFilter: String,
});

const selectedPayment = ref(null);
const proofModalOpen = ref(false);
const rejectionModalOpen = ref(false);

const form = useForm({
    action:           'approve',
    rejection_reason: '',
});

function approve(payment) {
    if (confirm(`Approve payment for ${payment.registration?.invoice_number}?`)) {
        form.action = 'approve';
        form.post(route('admin.payments.verify', payment.id), {
            onSuccess: () => {
                proofModalOpen.value = false;
            }
        });
    }
}

function openProofModal(payment) {
    selectedPayment.value = payment;
    proofModalOpen.value = true;
}

function openRejectModal(payment) {
    selectedPayment.value = payment;
    form.action = 'reject';
    form.rejection_reason = '';
    rejectionModalOpen.value = true;
}

function submitReject() {
    form.post(route('admin.payments.verify', selectedPayment.value.id), {
        onSuccess: () => {
            rejectionModalOpen.value = false;
            proofModalOpen.value = false;
        }
    });
}

function filterStatus(status) {
    router.get(route('admin.payments.index'), { status }, { preserveState: true });
}

function isPdf(path) {
    return path && path.toLowerCase().endsWith('.pdf');
}
</script>

<template>
    <Head title="Payment Verification - Admin" />
    <AdminLayout>
        <!-- Header Row -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Payment Verification</h1>
                <p class="text-xs text-slate-500 mt-0.5">Inspect proof files and verify participant registration payments.</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-5 flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="s in ['pending', 'verified', 'rejected']"
                :key="s"
                @click="filterStatus(s)"
                :class="[
                    'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition cursor-pointer',
                    currentFilter === s
                        ? 'bg-gold text-slate-950 shadow-xs'
                        : 'bg-white border border-slate-200 text-slate-600 hover:bg-amber-100 hover:text-slate-950'
                ]"
            >
                {{ s }}
            </button>
        </div>

        <!-- Minimalist Payments Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50 uppercase text-[11px] font-bold text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Invoice / Participant</th>
                            <th scope="col" class="px-5 py-3">Amount Paid</th>
                            <th scope="col" class="px-5 py-3">Proof Document</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="payments.data.length === 0">
                            <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                No payment records found in the "{{ currentFilter }}" queue.
                            </td>
                        </tr>
                        <tr
                            v-for="p in payments.data"
                            :key="p.id"
                            class="transition hover:bg-slate-50/50"
                        >
                            <!-- Invoice / User -->
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-purple-900 text-xs">{{ p.registration?.invoice_number }}</p>
                                <p class="font-bold text-slate-800 text-xs mt-0.5">{{ p.registration?.user?.name }}</p>
                                <p class="text-[11px] text-slate-400">{{ p.registration?.registration_fee?.name || p.registration?.registration_type?.name }}</p>
                            </td>

                            <!-- Amount -->
                            <td class="px-5 py-3.5 font-bold text-slate-900 text-sm">
                                <span v-if="p.currency === 'USD'">${{ Number(p.amount).toLocaleString() }}</span>
                                <span v-else>{{ formatRupiah(p.amount) }}</span>
                            </td>

                            <!-- Proof File Button -->
                            <td class="px-5 py-3.5">
                                <button
                                    @click="openProofModal(p)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                >
                                    View Proof
                                </button>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                <span :class="[
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold capitalize border',
                                    p.status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                    p.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                ]">
                                    {{ p.status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <template v-if="p.status === 'pending'">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="approve(p)" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 text-xs font-bold transition cursor-pointer">
                                            Approve
                                        </button>
                                        <button @click="openRejectModal(p)" class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 text-xs font-bold transition cursor-pointer">
                                            Reject
                                        </button>
                                    </div>
                                </template>
                                <span v-else class="text-xs font-semibold text-slate-400">
                                    {{ p.verifier ? 'Verified by ' + p.verifier.name : 'Processed' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Minimalist Payment Proof Modal -->
        <div v-if="proofModalOpen && selectedPayment" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-lg overflow-hidden border border-slate-200 my-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Payment Proof Inspection</h3>
                            <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-800">
                                {{ selectedPayment.registration?.invoice_number }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Participant: {{ selectedPayment.registration?.user?.name }} ({{ selectedPayment.registration?.user?.email }})</p>
                    </div>
                    <button
                        @click="proofModalOpen = false"
                        class="rounded-lg p-1 text-slate-400 hover:text-slate-700 transition cursor-pointer text-sm"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5">
                    <!-- Left: Document Preview (2 cols) -->
                    <div class="md:col-span-2 flex flex-col items-center justify-center bg-slate-100 rounded-xl border border-slate-200 p-2 min-h-[300px] max-h-[460px] overflow-hidden">
                        <template v-if="isPdf(selectedPayment.proof_file)">
                            <iframe :src="formatStorageUrl(selectedPayment.proof_file)" class="w-full h-[380px] rounded-lg border-0"></iframe>
                        </template>
                        <template v-else>
                            <img
                                :src="formatStorageUrl(selectedPayment.proof_file)"
                                alt="Payment Proof"
                                class="max-h-[400px] w-auto max-w-full object-contain rounded-lg"
                            />
                        </template>
                    </div>

                    <!-- Right: Info & Actions (1 col) -->
                    <div class="md:col-span-1 flex flex-col justify-between space-y-4 text-xs">
                        <div class="space-y-3">
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Amount</p>
                                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ selectedPayment.currency }} {{ Number(selectedPayment.amount).toLocaleString() }}</p>
                            </div>

                            <div class="space-y-2">
                                <div>
                                    <span class="text-slate-400 font-medium block">Registration Package</span>
                                    <span class="font-bold text-slate-800">{{ selectedPayment.registration?.registration_fee?.name || selectedPayment.registration?.registration_type?.name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Payment Method</span>
                                    <span class="font-bold text-slate-800 uppercase">{{ selectedPayment.payment_method || 'Bank Transfer' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Status</span>
                                    <span :class="[
                                        'inline-flex items-center rounded px-2 py-0.5 text-xs font-bold capitalize mt-0.5 border',
                                        selectedPayment.status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        selectedPayment.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        {{ selectedPayment.status }}
                                    </span>
                                </div>

                                <div v-if="selectedPayment.rejection_reason" class="rounded-lg bg-red-50 border border-red-200 p-2.5 text-xs">
                                    <span class="font-bold text-red-700 block">Rejection Reason:</span>
                                    <p class="text-red-800 mt-0.5">{{ selectedPayment.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            <template v-if="selectedPayment.status === 'pending'">
                                <button
                                    @click="approve(selectedPayment)"
                                    class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 text-xs transition cursor-pointer"
                                >
                                    Approve Payment
                                </button>
                                <button
                                    @click="openRejectModal(selectedPayment)"
                                    class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold py-2 text-xs transition cursor-pointer"
                                >
                                    Reject Payment
                                </button>
                            </template>
                            <button
                                type="button"
                                @click="proofModalOpen = false"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold py-2 text-xs transition cursor-pointer"
                            >
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Reason Modal -->
        <div v-if="rejectionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-lg border border-slate-200 text-xs">
                <h3 class="text-base font-bold text-slate-900 mb-1">Reject Payment Proof</h3>
                <p class="text-slate-500 mb-3">Please specify the reason for rejecting this payment proof file.</p>

                <form @submit.prevent="submitReject" class="space-y-3">
                    <div>
                        <label class="mb-1 block font-bold text-slate-700">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea v-model="form.rejection_reason" rows="3" class="admin-input" placeholder="e.g. Image is illegible or amount does not match invoice" required></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="rejectionModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2 font-bold cursor-pointer">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
