<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    registration: Object,
});
</script>

<template>
    <Head title="Invoice Detail" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.registrations.index')" class="text-sm text-slate-400 hover:text-primary">Registrations</Link>
                <span class="text-slate-300">/</span>
                <h1 class="text-lg font-bold text-slate-800">Invoice {{ registration.invoice_number }}</h1>
            </div>
        </template>

        <div class="max-w-3xl space-y-6">
            <!-- Invoice Header Card -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">INVOICE DETAILS</span>
                        <h2 class="text-2xl font-extrabold text-primary">{{ registration.invoice_number }}</h2>
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

                <div class="mt-6 grid gap-6 sm:grid-cols-2 text-sm">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Participant Info</h3>
                        <p class="font-bold text-slate-800">{{ registration.user?.name }}</p>
                        <p class="text-slate-500">{{ registration.user?.email }}</p>
                        <p class="text-slate-500">Phone: {{ registration.user?.profile?.phone ?? '—' }}</p>
                        <p class="text-slate-500">Institution: {{ registration.user?.profile?.institution ?? '—' }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Registration Type</h3>
                        <p class="font-bold text-slate-800">{{ registration.registration_type?.name }}</p>
                        <p class="text-slate-500">Rate: {{ registration.is_early_bird ? 'Early Bird' : 'Regular' }}</p>
                        <p class="mt-2 text-lg font-extrabold text-primary">{{ registration.currency }} {{ Number(registration.amount).toLocaleString() }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Proof Info -->
            <div v-if="registration.payment" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Payment Proof</h3>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Method: {{ registration.payment.payment_method }}</p>
                        <p class="text-xs text-slate-400">Paid at: {{ registration.payment.paid_at ?? '—' }}</p>
                    </div>
                    <a :href="'/storage/' + registration.payment.proof_file" target="_blank" class="rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary-dark">
                        View Proof File ↗
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
