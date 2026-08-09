<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    activeConference: Object,
    recentRegistrations: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isSuperAdmin = computed(() => user.value?.role === 'super_admin');

const statusColor = (status) => ({
    pending:              'bg-slate-100 text-slate-600',
    waiting_verification: 'bg-amber-50 text-amber-700 border border-amber-200/60',
    paid:                 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
    rejected:             'bg-rose-50 text-rose-700 border border-rose-200/60',
}[status] ?? 'bg-slate-100 text-slate-600');
</script>

<template>
    <Head :title="isSuperAdmin ? 'Super Admin Control' : 'Admin Dashboard'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-base font-bold tracking-tight text-slate-900">
                        {{ isSuperAdmin ? 'Super Admin Command Center' : 'Operations Dashboard' }}
                    </h1>
                    <span
                        :class="[
                            'rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                            isSuperAdmin ? 'bg-sidebar text-white' : 'bg-primary/10 text-primary border border-primary/20'
                        ]"
                    >
                        {{ user?.role?.replace('_', ' ') }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 font-mono">{{ activeConference?.title ?? 'ICHA 2026' }}</span>
            </div>
        </template>

        <!-- Main Banner -->
        <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        {{ isSuperAdmin ? 'System Governance' : 'Conference Operations' }}
                    </span>
                    <h2 class="text-xl font-bold tracking-tight text-primary-dark mt-0.5">
                        {{ activeConference?.title ?? 'ICHA 2026' }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Active account: <strong class="text-slate-800">{{ user?.name }}</strong> ({{ user?.email }})
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.payments.index')"
                        class="relative inline-flex items-center gap-2 rounded-xl bg-sidebar px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-primary-dark shadow-sm"
                    >
                        <span>Payment Queue</span>
                        <span
                            v-if="stats?.pending_payments > 0"
                            class="rounded-full bg-gold px-2 py-0.5 text-[10px] font-bold text-sidebar"
                        >
                            {{ stats.pending_payments }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Super Admin Control Panel (Only for Super Admin) -->
        <div v-if="isSuperAdmin" class="mb-8 rounded-2xl border border-sidebar bg-sidebar p-6 text-white shadow-md">
            <div class="mb-4 flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">Super Admin Privilege Panel</span>
                    <h3 class="text-sm font-bold text-white tracking-tight">System & Security Diagnostics</h3>
                </div>
                <span class="rounded-full bg-gold/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gold border border-gold/30">
                    Full Access Granted
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 text-xs">
                <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                    <span class="text-white/50 font-medium uppercase tracking-wider text-[10px]">User Accounts</span>
                    <p class="mt-2 text-sm font-bold text-white">
                        Participants: <span class="text-gold font-mono">{{ stats?.total_participants ?? 0 }}</span>
                    </p>
                    <p class="text-sm font-bold text-white">
                        Admins: <span class="text-gold font-mono">{{ stats?.total_admins ?? 0 }}</span>
                    </p>
                </div>

                <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                    <span class="text-white/50 font-medium uppercase tracking-wider text-[10px]">Host Environment</span>
                    <p class="mt-2 text-sm font-bold text-white">Architecture: Laravel + Inertia</p>
                    <p class="text-emerald-400 font-semibold">Deployment Target: Shared Hosting Sync</p>
                </div>

                <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                    <span class="text-white/50 font-medium uppercase tracking-wider text-[10px]">Queue Management</span>
                    <p class="mt-2 text-sm font-bold text-white">Mode: Synchronous (No Workers Needed)</p>
                    <p class="text-white/60">Status: Active</p>
                </div>
            </div>
        </div>

        <!-- Operational Metrics Grid -->
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase">Operational Metrics</h3>
        </div>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Participants</span>
                <p class="mt-2 text-3xl font-extrabold text-primary font-mono">{{ stats?.total_participants ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Registered user accounts</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Registrations</span>
                <p class="mt-2 text-3xl font-extrabold text-primary font-mono">{{ stats?.total_registrations ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Invoices issued</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending Verification</span>
                <p class="mt-2 text-3xl font-extrabold text-amber-600 font-mono">{{ stats?.pending_payments ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Requires admin action</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Verified Payments</span>
                <p class="mt-2 text-3xl font-extrabold text-emerald-600 font-mono">{{ stats?.verified_payments ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Confirmed paid tickets</p>
            </div>
        </div>

        <!-- Management Shortcuts -->
        <h3 class="mb-4 text-xs font-bold tracking-widest text-slate-400 uppercase">Management Shortcuts</h3>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link :href="route('admin.payments.index')" class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md">
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors">Payment Queue</h4>
                <p class="mt-1 text-xs text-slate-500">Review & verify proof of payments</p>
            </Link>

            <Link :href="route('admin.registrations.index')" class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md">
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors">Registrations</h4>
                <p class="mt-1 text-xs text-slate-500">View invoices & ticket categories</p>
            </Link>

            <Link :href="route('admin.speakers.index')" class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md">
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors">Speakers</h4>
                <p class="mt-1 text-xs text-slate-500">Manage keynote & plenary speakers</p>
            </Link>

            <Link :href="route('admin.sponsors.index')" class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md">
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors">Sponsors</h4>
                <p class="mt-1 text-xs text-slate-500">Manage title, gold, silver sponsors</p>
            </Link>
        </div>

        <!-- Recent Registrations Table -->
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase">Recent Registrations</h3>
            <Link :href="route('admin.registrations.index')" class="text-xs font-semibold text-primary hover:underline">View All →</Link>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Invoice #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Participant</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!recentRegistrations || recentRegistrations.length === 0">
                        <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">No registrations recorded yet.</td>
                    </tr>
                    <tr
                        v-for="r in recentRegistrations"
                        :key="r.id"
                        class="border-b border-slate-100/60 transition hover:bg-slate-50/50 last:border-0"
                    >
                        <td class="px-5 py-3.5 font-bold font-mono text-xs text-primary">{{ r.invoice_number }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-800 text-xs">{{ r.user?.name }}</p>
                            <p class="text-[11px] text-slate-400">{{ r.user?.email }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600">{{ r.registration_type?.name }}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-900 text-xs font-mono">{{ r.currency }} {{ Number(r.amount).toLocaleString() }}</td>
                        <td class="px-5 py-3.5">
                            <span :class="['rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider', statusColor(r.status)]">
                                {{ r.status.replace('_', ' ') }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
