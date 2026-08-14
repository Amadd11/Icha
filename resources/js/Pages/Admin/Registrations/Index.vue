<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    registrations: Object,
    currentFilter: String,
});

const statusColor = (status) => ({
    unpaid:               'bg-slate-100 text-slate-600',
    waiting_verification: 'bg-amber-100 text-amber-700',
    paid:                 'bg-green-100 text-green-700',
    cancelled:            'bg-red-100 text-red-700',
}[status] ?? 'bg-slate-100 text-slate-600');

function filterStatus(status) {
    router.get(route('admin.registrations.index'), { status }, { preserveState: true });
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
                    'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition',
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
                            {{ r.currency }} {{ Number(r.amount).toLocaleString() }}
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-2.5 py-1 text-xs font-bold uppercase', statusColor(r.status)]">
                                {{ r.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link :href="route('admin.registrations.show', r.id)" class="text-xs font-semibold text-primary hover:underline">
                                View Invoice
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
