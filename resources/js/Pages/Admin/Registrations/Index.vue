<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { formatRupiah } from '@/Composables/useFormatRupiah';
import { useTableFilter } from '@/Composables/useTableFilter';
import { useStatusBadge } from '@/Composables/useStatusBadge';
import { useModal } from '@/Composables/useModal';
import { formatDateTime } from '@/Utils/formatters';

const props = defineProps({
    registrations: Object,
    currentFilter: String,
});

const { filters, applyFilter } = useTableFilter('admin.registrations.index', {
    status: props.currentFilter || null,
});

const { getBadgeClass, getStatusLabel } = useStatusBadge();
const { isOpen: isInvoiceModalOpen, activeItem: selectedRegistration, open: openInvoiceModal, close: closeInvoiceModal } = useModal();

function filterStatus(status) {
    applyFilter({ status });
}

const sendingInvoice = ref(false);

function sendInvoiceEmail() {
    if (!selectedRegistration.value) return;
    const email = selectedRegistration.value.user?.email;
    if (confirm(`Send official invoice email to ${email}?`)) {
        sendingInvoice.value = true;
        router.post(route('admin.registrations.send-invoice', selectedRegistration.value.id), {}, {
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
    <Head title="Participants & Registrations - Admin" />
    <AdminLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Participant Registrations</h1>
                <p class="text-xs text-slate-500">{{ registrations.total ?? registrations.data?.length ?? 0 }} participant registration(s) found</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
            <button
                v-for="s in [
                    { key: null, label: 'All' },
                    { key: 'unpaid', label: 'Unpaid' },
                    { key: 'waiting_verification', label: 'Waiting Verification' },
                    { key: 'paid', label: 'Paid' },
                    { key: 'rejected', label: 'Rejected' },
                ]"
                :key="s.key"
                @click="filterStatus(s.key)"
                :class="[
                    'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition cursor-pointer',
                    (currentFilter === s.key || (!currentFilter && !s.key)) ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                ]"
            >
                {{ s.label }}
            </button>
        </div>

        <!-- Single Clean Registrations Table with Submitted At & Approved At Columns -->
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b border-slate-100 bg-slate-50 uppercase text-[11px] font-bold text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Invoice #</th>
                            <th scope="col" class="px-5 py-3">Participant</th>
                            <th scope="col" class="px-5 py-3">Category</th>
                            <th scope="col" class="px-5 py-3">Amount</th>
                            <th scope="col" class="px-5 py-3">Submitted At</th>
                            <th scope="col" class="px-5 py-3">Approved At</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="registrations.data.length === 0">
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400 text-xs">
                                No registrations found in the "{{ currentFilter || 'all' }}" queue.
                            </td>
                        </tr>
                        <tr
                            v-for="r in registrations.data"
                            :key="r.id"
                            class="transition hover:bg-slate-50/50"
                        >
                            <!-- Invoice Number -->
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-purple-900 text-xs font-mono">{{ r.invoice_number }}</p>
                            </td>

                            <!-- Participant Info -->
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-slate-900 text-xs">{{ r.user?.name }}</p>
                                <p class="text-[11px] text-slate-400">{{ r.user?.email }}</p>
                                <p v-if="r.user?.profile?.institution" class="text-[10px] text-slate-500 truncate max-w-[180px]">
                                    🏛️ {{ r.user?.profile?.institution }}
                                </p>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">
                                {{ r.registration_fee?.name || 'Standard Registration' }}
                            </td>

                            <!-- Amount -->
                            <td class="px-5 py-3.5 font-bold text-slate-900 text-xs">
                                {{ formatRupiah(r.amount) }}
                            </td>

                            <!-- Submitted At Column -->
                            <td class="px-5 py-3.5 text-xs font-medium text-slate-800">
                                <span v-if="r.payment?.paid_at || r.payment?.created_at || r.created_at">
                                    {{ formatDateTime(r.payment?.paid_at || r.payment?.created_at || r.created_at) }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </td>

                            <!-- Approved At Column -->
                            <td class="px-5 py-3.5 text-xs font-bold text-emerald-700">
                                <span v-if="r.payment?.verified_at">
                                    {{ formatDateTime(r.payment.verified_at) }}
                                </span>
                                <span v-else class="text-slate-400 font-normal">-</span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                <span :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border', getBadgeClass(r.status)]">
                                    {{ getStatusLabel(r.status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <button
                                    @click="openInvoiceModal(r)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-purple-50 hover:bg-purple-100 text-primary border border-purple-200 px-3 py-1.5 text-xs font-bold transition cursor-pointer shadow-2xs"
                                >
                                    <span>📄</span>
                                    <span>View Invoice</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <Pagination
            :links="props.registrations?.links"
            :from="props.registrations?.from"
            :to="props.registrations?.to"
            :total="props.registrations?.total"
        />

        <!-- 📄 Invoice Detail Modal (Original Compact Design) -->
        <div
            v-if="isInvoiceModalOpen && selectedRegistration"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
        >
            <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 space-y-6 animate-fade-in-scale">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">INVOICE DETAILS</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <h2 class="text-lg font-black text-primary">{{ selectedRegistration.invoice_number }}</h2>
                            <span :class="[
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border',
                                getBadgeClass(selectedRegistration.status)
                            ]">
                                {{ getStatusLabel(selectedRegistration.status) }}
                            </span>
                        </div>
                    </div>
                    <button
                        @click="closeInvoiceModal"
                        class="text-slate-400 hover:text-slate-700 text-lg font-bold p-1 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Rejection Reason Alert (If Rejected) -->
                <div
                    v-if="selectedRegistration.status === 'rejected' || selectedRegistration.payment?.status === 'rejected'"
                    class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs space-y-1"
                >
                    <div class="flex items-center gap-1.5 font-bold text-rose-900">
                        <span>⚠️</span>
                        <span>Registration / Payment Rejected</span>
                    </div>
                    <p v-if="selectedRegistration.payment?.rejection_reason" class="text-rose-700">
                        <strong>Reason:</strong> "{{ selectedRegistration.payment.rejection_reason }}"
                    </p>
                    <p v-if="selectedRegistration.payment?.verified_at" class="text-rose-600 text-[11px]">
                        Rejected at: {{ formatDateTime(selectedRegistration.payment.verified_at) }}
                    </p>
                </div>

                <!-- Invoice Body Content -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <!-- Participant Details -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Participant Information</span>
                        <p class="text-sm font-bold text-slate-900">{{ selectedRegistration.user?.name }}</p>
                        <p class="text-slate-600"><strong>Email:</strong> {{ selectedRegistration.user?.email }}</p>
                        <p class="text-slate-600"><strong>Phone:</strong> {{ selectedRegistration.user?.profile?.phone || '-' }}</p>
                        <p class="text-slate-600"><strong>Institution:</strong> {{ selectedRegistration.user?.profile?.institution || '-' }}</p>
                    </div>

                    <!-- Package & Payment Details -->
                    <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100 space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 block">Package & Pricing</span>
                        <p class="text-sm font-bold text-purple-950">{{ selectedRegistration.registration_fee?.name || 'Standard Registration' }}</p>
                        <div class="pt-2 border-t border-purple-100/80">
                            <span class="text-[10px] font-bold uppercase text-purple-700 block">Total Amount</span>
                            <span class="text-xl font-black text-purple-950">
                                {{ formatRupiah(selectedRegistration.amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Receipt (If Uploaded) -->
                <div v-if="selectedRegistration.payment" class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Payment Receipt</span>
                        <p class="font-bold text-slate-800">Method: {{ selectedRegistration.payment.payment_method || 'Bank Transfer' }}</p>
                        <p class="text-slate-600 text-[11px]">Submitted at: <span class="font-mono font-bold">{{ selectedRegistration.payment.paid_at || selectedRegistration.payment.created_at ? formatDateTime(selectedRegistration.payment.paid_at || selectedRegistration.payment.created_at) : '-' }}</span></p>
                        <p v-if="selectedRegistration.payment.verified_at" class="text-emerald-700 font-bold text-[11px]">
                            Approved at: <span class="font-mono">{{ formatDateTime(selectedRegistration.payment.verified_at) }}</span>
                        </p>
                        <p v-if="selectedRegistration.payment.verifier" class="text-emerald-800 text-[10px]">
                            Verified by: <strong>{{ selectedRegistration.payment.verifier.name }}</strong>
                        </p>
                    </div>
                    <a
                        v-if="selectedRegistration.payment.proof_file"
                        :href="selectedRegistration.payment.proof_url || formatStorageUrl(selectedRegistration.payment.proof_file)"
                        target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-800 font-bold transition inline-flex items-center gap-1 shadow-xs self-start sm:self-auto"
                    >
                        <span>🔍</span> View Receipt
                    </a>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Print Button -->
                        <button
                            type="button"
                            @click="printInvoice"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition cursor-pointer"
                        >
                            <span>🖨️</span>
                            <span>Print Invoice</span>
                        </button>

                        <!-- Send Email Button -->
                        <button
                            type="button"
                            @click="sendInvoiceEmail"
                            :disabled="sendingInvoice"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-gold text-xs font-bold shadow-xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span>📧</span>
                            <span>{{ sendingInvoice ? 'Sending...' : 'Send / Resend Invoice Email' }}</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="closeInvoiceModal"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
