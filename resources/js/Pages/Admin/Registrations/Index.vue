<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { formatRupiah } from '@/Composables/useFormatRupiah';
import { useTableFilter } from '@/Composables/useTableFilter';
import { useStatusBadge } from '@/Composables/useStatusBadge';
import { useModal } from '@/Composables/useModal';

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
    if (confirm(`Send invoice email to ${email}?`)) {
        sendingInvoice.value = true;
        router.post(route('admin.registrations.send-invoice', selectedRegistration.value.id), {}, {
            preserveScroll: true,
            onFinish: () => {
                sendingInvoice.value = false;
            }
        });
    }
}

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}
</script>

<template>
    <Head title="Registrations - Admin" />
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registrations</h1>
                <p class="text-xs text-slate-500">{{ registrations.total ?? registrations.data?.length ?? 0 }} registration(s) found</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
            <button
                v-for="s in [null, 'unpaid', 'waiting_verification', 'paid', 'cancelled']"
                :key="s"
                @click="filterStatus(s)"
                :class="[
                    'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition cursor-pointer',
                    (currentFilter === s || (!currentFilter && !s)) ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                ]"
            >
                {{ s ? s.replace('_', ' ') : 'All' }}
            </button>
        </div>

        <!-- Registrations Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Invoice #</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Participant</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Category</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Amount</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Status</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="registrations.data.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">No registrations found.</td>
                    </tr>
                    <tr
                        v-for="r in registrations.data"
                        :key="r.id"
                        class="border-b border-slate-50 transition hover:bg-slate-50/50 last:border-0"
                    >
                        <td class="px-5 py-4 font-bold text-primary">{{ r.invoice_number }}</td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-800">{{ r.user?.name }}</p>
                            <p class="text-xs text-slate-400">{{ r.user?.email }} · {{ r.user?.profile?.phone }}</p>
                        </td>
                        <td class="px-5 py-4 text-xs font-medium text-slate-700">
                            {{ r.registration_fee?.name }}
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-800">
                            {{ formatRupiah(r.amount) }}
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border', getBadgeClass(r.status)]">
                                {{ getStatusLabel(r.status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button
                                @click="openInvoiceModal(r)"
                                class="text-xs font-bold text-primary hover:underline cursor-pointer"
                            >
                                View Invoice
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <Pagination
            :links="props.registrations?.links"
            :from="props.registrations?.from"
            :to="props.registrations?.to"
            :total="props.registrations?.total"
        />

        <!-- 📄 Invoice Detail Modal -->
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
                        <p class="text-purple-800"><strong>Rate:</strong> {{ selectedRegistration.is_early_bird ? 'Early Bird' : 'Regular' }}</p>
                        <div class="pt-2 border-t border-purple-100/80">
                            <span class="text-[10px] font-bold uppercase text-purple-700 block">Total Amount</span>
                            <span class="text-xl font-black text-purple-950">
                                {{ formatRupiah(selectedRegistration.amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Receipt (If Uploaded) -->
                <div v-if="selectedRegistration.payment" class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Payment Receipt</span>
                        <p class="font-bold text-slate-800">Method: {{ selectedRegistration.payment.payment_method || 'Bank Transfer' }}</p>
                        <p class="text-slate-500 text-[11px]">Submitted at: {{ selectedRegistration.payment.created_at ? new Date(selectedRegistration.payment.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}</p>
                    </div>
                    <a
                        v-if="selectedRegistration.payment.proof_file"
                        :href="formatStorageUrl(selectedRegistration.payment.proof_file)"
                        target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-800 font-bold transition inline-flex items-center gap-1 shadow-xs"
                    >
                        <span>🔍</span> View Receipt
                    </a>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="sendInvoiceEmail"
                        :disabled="sendingInvoice"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-950 text-gold text-xs font-bold shadow-xs transition cursor-pointer disabled:opacity-50"
                    >
                        <span>📧</span>
                        <span>{{ sendingInvoice ? 'Sending Email...' : 'Send / Resend Invoice Email' }}</span>
                    </button>

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
