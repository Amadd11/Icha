<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import { formatStorageUrl } from '@/Utils/formatters';
import { formatRupiah } from '@/Composables/useFormatRupiah';
import { useTableFilter } from '@/Composables/useTableFilter';
import { useStatusBadge } from '@/Composables/useStatusBadge';
import { useModal } from '@/Composables/useModal';

const props = defineProps({
    payments: Object,
    currentFilter: String,
});

const { filters, applyFilter } = useTableFilter('admin.payments.index', {
    status: props.currentFilter || 'pending',
});

const { getBadgeClass, getStatusLabel } = useStatusBadge();

const proofModal = useModal();
const rejectionModal = useModal();
const approveModal = useModal();

const form = useForm({
    action:           'approve',
    rejection_reason: '',
});

function openApproveModal(payment) {
    approveModal.open(payment);
    form.action = 'approve';
    form.rejection_reason = '';
}

function submitApprove() {
    form.action = 'approve';
    form.post(route('admin.payments.verify', approveModal.activeItem.value.id), {
        onSuccess: () => {
            approveModal.close();
            proofModal.close();
        }
    });
}

function openProofModal(payment) {
    proofModal.open(payment);
}

function openRejectModal(payment) {
    rejectionModal.open(payment);
    form.action = 'reject';
    form.rejection_reason = '';
}

function submitReject() {
    form.post(route('admin.payments.verify', rejectionModal.activeItem.value.id), {
        onSuccess: () => {
            rejectionModal.close();
            proofModal.close();
        }
    });
}

function filterStatus(status) {
    applyFilter({ status });
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
                                {{ formatRupiah(p.amount) }}
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
                                    getBadgeClass(p.status)
                                ]">
                                    {{ getStatusLabel(p.status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <template v-if="p.status === 'pending'">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openApproveModal(p)" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 text-xs font-bold transition cursor-pointer">
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

            <!-- Pagination Footer -->
            <Pagination
                :links="props.payments?.links"
                :from="props.payments?.from"
                :to="props.payments?.to"
                :total="props.payments?.total"
            />
        </div>

        <!-- Proof Image Modal (Fullscreen Viewer) -->
        <div v-if="proofModal.isOpen.value && proofModal.activeItem.value" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4">
            <div class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Payment Proof Preview</h3>
                        <p class="text-xs text-slate-500 font-mono">{{ proofModal.activeItem.value.registration?.invoice_number }} &bull; {{ proofModal.activeItem.value.registration?.user?.name }}</p>
                    </div>
                    <button
                        type="button"
                        @click="proofModal.close()"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Left: Proof Preview (2 cols) -->
                    <div class="md:col-span-2 flex items-center justify-center bg-slate-950 rounded-xl p-4 min-h-[360px] overflow-hidden border border-slate-800">
                        <template v-if="isPdf(proofModal.activeItem.value.proof_file)">
                            <div class="text-center p-8 text-white space-y-3">
                                <svg class="h-16 w-16 mx-auto text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-semibold">PDF Document Uploaded</p>
                                <a
                                    :href="formatStorageUrl(proofModal.activeItem.value.proof_file)"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-xs font-bold transition"
                                >
                                    Open PDF in New Tab
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </div>
                        </template>
                        <template v-else>
                            <img
                                :src="formatStorageUrl(proofModal.activeItem.value.proof_file)"
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
                                <p class="text-xl font-bold text-slate-900 mt-0.5">
                                    {{ formatRupiah(proofModal.activeItem.value.amount) }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div>
                                    <span class="text-slate-400 font-medium block">Registration Package</span>
                                    <span class="font-bold text-slate-800">{{ proofModal.activeItem.value.registration?.registration_fee?.name || proofModal.activeItem.value.registration?.registration_type?.name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Payment Method</span>
                                    <span class="font-bold text-slate-800 uppercase">{{ proofModal.activeItem.value.payment_method || 'Bank Transfer' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Status</span>
                                    <span :class="[
                                        'inline-flex items-center rounded px-2 py-0.5 text-xs font-bold capitalize mt-0.5 border',
                                        proofModal.activeItem.value.status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        proofModal.activeItem.value.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        {{ proofModal.activeItem.value.status }}
                                    </span>
                                </div>

                                <div v-if="proofModal.activeItem.value.rejection_reason" class="rounded-lg bg-red-50 border border-red-200 p-2.5 text-xs">
                                    <span class="font-bold text-red-700 block">Rejection Reason:</span>
                                    <p class="text-red-800 mt-0.5">{{ proofModal.activeItem.value.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            <template v-if="proofModal.activeItem.value.status === 'pending'">
                                <button
                                    @click="openApproveModal(proofModal.activeItem.value)"
                                    class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 text-xs transition cursor-pointer"
                                >
                                    Approve Payment
                                </button>
                                <button
                                    @click="openRejectModal(proofModal.activeItem.value)"
                                    class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold py-2 text-xs transition cursor-pointer"
                                >
                                    Reject Payment
                                </button>
                            </template>
                            <button
                                type="button"
                                @click="proofModal.close()"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold py-2 text-xs transition cursor-pointer"
                            >
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Confirmation Modal -->
        <div v-if="approveModal.isOpen.value && approveModal.activeItem.value" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-200 text-xs">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Approve Payment</h3>
                        <p class="text-xs text-slate-500 font-mono">{{ approveModal.activeItem.value.registration?.invoice_number }}</p>
                    </div>
                </div>

                <div class="my-4 space-y-2 rounded-xl bg-slate-50 border border-slate-200 p-3.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Participant:</span>
                        <strong class="text-slate-800">{{ approveModal.activeItem.value.registration?.user?.name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Package:</span>
                        <strong class="text-slate-800">{{ approveModal.activeItem.value.registration?.registration_fee?.name || 'Registration' }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Amount:</span>
                        <strong class="text-emerald-700 text-sm font-bold">
                            {{ formatRupiah(approveModal.activeItem.value.amount) }}
                        </strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Method:</span>
                        <span class="font-semibold text-slate-700 uppercase">{{ approveModal.activeItem.value.payment_method || 'Bank Transfer' }}</span>
                    </div>
                </div>

                <p class="text-slate-500 mb-5 leading-relaxed">
                    Are you sure you want to approve this transaction? Approving will mark the participant's invoice as <strong>Paid</strong> and verify their conference registration.
                </p>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="approveModal.close()"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submitApprove"
                        :disabled="form.processing"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 font-bold transition disabled:opacity-50 cursor-pointer shadow-xs"
                    >
                        {{ form.processing ? 'Approving...' : 'Yes, Approve Payment' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Reason Modal -->
        <div v-if="rejectionModal.isOpen.value && rejectionModal.activeItem.value" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-lg border border-slate-200 text-xs">
                <h3 class="text-base font-bold text-slate-900 mb-1">Reject Payment Proof</h3>
                <p class="text-slate-500 mb-3">Please specify the reason for rejecting this payment proof file.</p>

                <form @submit.prevent="submitReject" class="space-y-3">
                    <div>
                        <label class="mb-1 block font-bold text-slate-700">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea v-model="form.rejection_reason" rows="3" class="admin-input" placeholder="e.g. Image is illegible or amount does not match invoice" required></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="rejectionModal.close()" class="rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2 font-bold cursor-pointer">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
