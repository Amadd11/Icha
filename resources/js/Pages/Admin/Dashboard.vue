<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';
import { formatRupiah } from '@/Composables/useFormatRupiah';

const props = defineProps({
    selectedConference: Object,
    availableConferences: Array,
    stats: Object,
    trackCategories: Array,
    recentRegistrations: Array,
    deadlines: Array,
});

function changeConference(e) {
    const id = e.target.value;
    router.get(route('admin.dashboard'), { conference_id: id }, { preserveState: true });
}
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout
        :selected-conference="props.selectedConference"
    >
        <div class="space-y-6">
            <!-- Header Row with Conference Switcher -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Executive Dashboard & Financial Recap</h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Financial metrics and submission recap for {{ props.selectedConference?.title || 'Active Conference' }}
                    </p>
                </div>
            </div>

            <!-- 💰 TOTAL FINANCIAL RECAP & REGISTRATION REVENUE CARDS 💰 -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-4 shadow-sm animate-fade-in-scale">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Participant Financial Recap</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Total registered participant invoices vs verified received funds.</p>
                    </div>
                    <Link
                        :href="route('admin.payments.index')"
                        class="px-4 py-2 rounded-xl bg-gold hover:bg-gold-dark text-slate-950 font-bold text-xs transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 cursor-pointer"
                    >
                        Verify Payments Queue
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                    <!-- Total Invoiced Amount (Seluruh Peserta Mendaftar) -->
                    <div class="rounded-2xl border border-purple-200/80 bg-purple-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-purple-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Total Tagihan Peserta Mendaftar</span>
                            <span class="text-[10px] font-bold text-purple-800 bg-purple-100 px-2.5 py-0.5 rounded-full">All Regs</span>
                        </div>
                        <p class="text-2xl font-black text-purple-950 mt-2">
                            {{ formatRupiah(props.stats?.total_invoiced_idr || 0) }}
                        </p>
                        <span class="text-[11px] font-semibold text-slate-500 mt-2 block">
                            Dari {{ props.stats?.total_registrations || 0 }} peserta mendaftar
                        </span>
                    </div>

                    <!-- Verified Received Money (Sudah Lunas) -->
                    <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Uang Masuk (Lunas & Terverifikasi)</span>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">Verified</span>
                        </div>
                        <p class="text-2xl font-black text-emerald-950 mt-2">
                            {{ formatRupiah(props.stats?.verified_revenue_idr) }}
                        </p>
                        <span class="text-[11px] font-semibold text-emerald-700 mt-2 block">
                            {{ props.stats?.verified_payments || 0 }} transaksi telah lunas
                        </span>
                    </div>

                    <!-- Unpaid / Pending Balance (Belum Dibayar / Verifikasi) -->
                    <div class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-amber-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Sisa Tagihan (Belum Lunas)</span>
                            <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full">Pending</span>
                        </div>
                        <p class="text-2xl font-black text-amber-950 mt-2">
                            {{ formatRupiah(props.stats?.unpaid_revenue_idr) }}
                        </p>
                        <span class="text-[11px] font-semibold text-amber-800 mt-1.5 block">
                            {{ props.stats?.pending_payments || 0 }} invoice belum diverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submissions & Participants Metrics Grid (4 Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Participants -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Participants</span>
                        <span class="rounded bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700 border border-purple-100">Users</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-bold text-slate-900">{{ props.stats?.total_participants || 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">{{ props.stats?.total_registrations || 0 }} Regs</span>
                    </div>
                </div>

                <!-- Card 2: Abstract Submissions -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Abstracts</span>
                        <span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-100">Phase 1</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-bold text-slate-900">{{ props.stats?.total_abstracts || 0 }}</span>
                        <span class="text-xs font-bold text-emerald-700">{{ props.stats?.accepted_abstracts || 0 }} Accepted</span>
                    </div>
                </div>

                <!-- Card 3: Full Papers -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Full Papers</span>
                        <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-100">Phase 2</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-bold text-slate-900">{{ props.stats?.total_full_papers || 0 }}</span>
                        <span class="text-xs font-bold text-emerald-700">{{ props.stats?.accepted_papers || 0 }} Accepted</span>
                    </div>
                </div>

                <!-- Card 4: Active Scientific Tracks -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Scientific Tracks</span>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">Topics</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-bold text-slate-900">{{ props.trackCategories ? props.trackCategories.length : 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">Active Categories</span>
                    </div>
                </div>
            </div>

            <!-- Side-by-Side Tables (Recent Payments & Tracks Recap) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Registrations & Payments Table -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Recent Invoices & Payments Queue</h3>
                        <Link :href="route('admin.payments.index')" class="text-xs font-bold text-purple-700 hover:underline">
                            Manage All Payments →
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Invoice</th>
                                    <th scope="col" class="px-5 py-3">Participant</th>
                                    <th scope="col" class="px-5 py-3">Category</th>
                                    <th scope="col" class="px-5 py-3">Amount</th>
                                    <th scope="col" class="px-5 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="!props.recentRegistrations || props.recentRegistrations.length === 0">
                                    <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                        No recent registration invoices found.
                                    </td>
                                </tr>
                                <tr v-for="reg in props.recentRegistrations" :key="reg.id" class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-purple-900 text-xs">{{ reg.invoice_number }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-slate-800 text-xs">{{ reg.user?.name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ reg.user?.email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">
                                        {{ reg.registration_type?.name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-slate-900">
                                        {{ reg.currency }} {{ Number(reg.amount).toLocaleString() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span :class="[
                                            'inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border',
                                            reg.payment?.status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            reg.payment?.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' :
                                            'bg-amber-50 text-amber-700 border-amber-200'
                                        ]">
                                            {{ reg.payment?.status || 'Unpaid' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Scientific Tracks Submissions Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Track Submissions Breakdown</h3>
                        <Link :href="route('admin.categories.index')" class="text-xs font-bold text-purple-700 hover:underline">
                            Tracks →
                        </Link>
                    </div>

                    <div class="p-4 flex-1 space-y-2.5">
                        <div v-if="!props.trackCategories || props.trackCategories.length === 0" class="text-center text-xs text-slate-400 py-6">
                            No track categories registered.
                        </div>

                        <div
                            v-for="track in props.trackCategories"
                            :key="track.id"
                            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition"
                        >
                            <div>
                                <span class="inline-block rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-bold text-purple-800 mb-0.5">
                                    {{ track.badge || 'TRACK' }}
                                </span>
                                <p class="font-bold text-slate-900 text-xs truncate max-w-[160px]">{{ track.name }}</p>
                            </div>

                            <div class="text-right">
                                <span class="text-sm font-bold text-purple-950">{{ track.abstracts_count }}</span>
                                <span class="block text-[10px] text-slate-400">Abstracts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Direct Recap Quick Navigation Bar -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Direct Recap Quick Navigation</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Jump directly to management tables to export or inspect detailed records.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.registrations.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Registrations
                    </Link>
                    <Link :href="route('admin.payments.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Payments Queue
                    </Link>
                    <Link :href="route('admin.abstracts.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Abstract Submissions
                    </Link>
                    <Link :href="route('admin.papers.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Full Papers
                    </Link>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
