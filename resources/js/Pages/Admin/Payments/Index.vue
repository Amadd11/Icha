<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payments: Object,
    currentFilter: String,
});

const selectedPayment = ref(null);
const rejectionModalOpen = ref(false);

const form = useForm({
    action:           'approve',
    rejection_reason: '',
});

function approve(payment) {
    if (confirm(`Approve payment for ${payment.registration?.invoice_number}?`)) {
        form.action = 'approve';
        form.post(route('admin.payments.verify', payment.id));
    }
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
        }
    });
}

function filterStatus(status) {
    router.get(route('admin.payments.index'), { status }, { preserveState: true });
}
</script>

<template>
    <Head title="Payment Verification - Admin" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-slate-800">Payment Verification Queue</h1>
        </template>

        <!-- Filter Tabs -->
        <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
            <button
                v-for="s in ['pending', 'verified', 'rejected']"
                :key="s"
                @click="filterStatus(s)"
                :class="[
                    'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition',
                    currentFilter === s ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                ]"
            >
                {{ s }}
            </button>
        </div>

        <!-- Payments Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Invoice / Participant</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Amount</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Proof File</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Status</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">No payments in this queue.</td>
                    </tr>
                    <tr
                        v-for="p in payments.data"
                        :key="p.id"
                        class="border-b border-slate-50 transition hover:bg-slate-50/50 last:border-0"
                    >
                        <td class="px-5 py-4">
                            <p class="font-bold text-primary">{{ p.registration?.invoice_number }}</p>
                            <p class="font-semibold text-slate-800">{{ p.registration?.user?.name }}</p>
                            <p class="text-xs text-slate-400">{{ p.registration?.registration_type?.name }}</p>
                        </td>
                        <td class="px-5 py-4 font-bold text-slate-800">
                            {{ p.currency }} {{ Number(p.amount).toLocaleString() }}
                        </td>
                        <td class="px-5 py-4">
                            <a :href="'/storage/' + p.proof_file" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
                                📄 View Proof
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="[
                                'rounded-full px-2.5 py-1 text-xs font-bold uppercase',
                                p.status === 'verified' ? 'bg-green-100 text-green-700' :
                                p.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'
                            ]">
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <template v-if="p.status === 'pending'">
                                <button @click="approve(p)" class="mr-2 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700">
                                    Approve
                                </button>
                                <button @click="openRejectModal(p)" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700">
                                    Reject
                                </button>
                            </template>
                            <span v-else class="text-xs text-slate-400">
                                {{ p.verifier ? 'by ' + p.verifier.name : 'Processed' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Reject Modal -->
        <div v-if="rejectionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-800 mb-2">Reject Payment Proof</h3>
                <p class="text-xs text-slate-500 mb-4">Please specify the reason for rejecting this payment proof so the participant can re-upload.</p>

                <form @submit.prevent="submitReject" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Rejection Reason</label>
                        <textarea v-model="form.rejection_reason" rows="3" class="admin-input" placeholder="e.g. Proof image is blurry / Amount does not match" required></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="rejectionModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
