<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatStorageUrl } from '@/Utils/formatters';
import { formatRupiah } from '@/Composables/useFormatRupiah';
import AnimatedCounter from '@/Components/UI/AnimatedCounter.vue';

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

function getRegistrationPresenterStatus(reg) {
    const feeType = reg.registration_fee?.type || reg.registrationFee?.type;
    if (feeType === 'non_presenter') {
        return { label: 'Peserta Umum', class: 'bg-slate-100 text-slate-700 border-slate-200' };
    }

    const abstracts = reg.user?.abstract_submissions || reg.user?.abstractSubmissions || reg.user?.abstracts || [];
    const hasAccepted = abstracts.some(a => a.status === 'accepted');
    const hasPending = abstracts.some(a => ['pending', 'under_review'].includes(a.status));
    const hasRevision = abstracts.some(a => a.status === 'revision_required');
    const hasRejected = abstracts.some(a => a.status === 'rejected');

    if (hasAccepted) {
        return { label: 'Pemakalah (Lolos Review)', class: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
    }
    if (hasRevision) {
        return { label: 'Pemakalah (Perlu Revisi)', class: 'bg-amber-50 text-amber-800 border-amber-200' };
    }
    if (hasPending) {
        return { label: 'Pemakalah (Menunggu Telaah)', class: 'bg-blue-50 text-blue-800 border-blue-200' };
    }
    if (hasRejected) {
        return { label: 'Pemakalah (Ditolak)', class: 'bg-rose-50 text-rose-800 border-rose-200' };
    }
    return { label: 'Pemakalah', class: 'bg-purple-50 text-purple-800 border-purple-200' };
}

function getRegistrationPaymentBadge(reg) {
    if (reg.payment?.status === 'verified') {
        return {
            label: 'Lunas',
            class: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            hasPing: false,
        };
    }
    if (reg.payment?.status === 'pending') {
        return {
            label: 'Menunggu Verifikasi',
            class: 'bg-amber-50 text-amber-800 border-amber-300',
            hasPing: true,
        };
    }
    if (reg.payment?.status === 'rejected') {
        return {
            label: 'Bukti Ditolak',
            class: 'bg-rose-50 text-rose-700 border-rose-200',
            hasPing: false,
        };
    }

    // No payment record yet (Unpaid)
    const abstracts = reg.user?.abstract_submissions || reg.user?.abstractSubmissions || reg.user?.abstracts || [];
    const hasPendingReview = abstracts.some(a => ['pending', 'under_review'].includes(a.status));

    if (hasPendingReview) {
        return {
            label: 'Belum Bayar (Menunggu Review)',
            class: 'bg-slate-50 text-slate-700 border-slate-200',
            hasPing: false,
        };
    }

    return {
        label: 'Belum Bayar',
        class: 'bg-amber-50 text-amber-700 border-amber-200',
        hasPing: false,
    };
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
                        Ringkasan metrik keuangan dan progres pendaftaran untuk <span class="font-semibold text-slate-700">{{ props.selectedConference?.title || 'Active Conference' }}</span>
                    </p>
                </div>
                <div v-if="props.availableConferences && props.availableConferences.length > 1" class="flex items-center gap-2">
                    <label for="conf-switcher" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Konferensi:</label>
                    <select
                        id="conf-switcher"
                        :value="props.selectedConference?.id"
                        @change="changeConference"
                        class="text-xs rounded-xl border-slate-200 bg-white shadow-2xs font-semibold text-slate-700 focus:ring-purple-500 focus:border-purple-500 py-1.5 px-3"
                    >
                        <option v-for="c in props.availableConferences" :key="c.id" :value="c.id">
                            {{ c.title }} ({{ c.year }}) {{ c.is_active ? '★ Aktif' : '' }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- 💰 TOTAL FINANCIAL RECAP & REGISTRATION REVENUE CARDS 💰 -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-4 shadow-sm animate-fade-in-scale">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Rekapitulasi Keuangan Pendaftaran</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Total tagihan terbit, dana terverifikasi lunas, dan sisa tagihan yang belum dibayar.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('admin.payments.index')"
                            class="px-4 py-2 rounded-xl bg-gold hover:bg-gold-dark text-slate-950 font-bold text-xs transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 cursor-pointer flex items-center gap-1.5"
                        >
                            <span v-if="(props.stats?.pending_payments || 0) > 0" class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-600"></span>
                            </span>
                            <span>Verifikasi Pembayaran</span>
                            <span v-if="(props.stats?.pending_payments || 0) > 0" class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-950 text-gold text-[10px] font-black">
                                {{ props.stats?.pending_payments }}
                            </span>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                    <!-- Total Invoiced Amount (Seluruh Peserta Mendaftar) -->
                    <div class="animate-fade-in-up animation-delay-100 rounded-2xl border border-purple-200/80 bg-purple-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-purple-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Total Tagihan Terbit</span>
                            <span class="text-[10px] font-bold text-purple-800 bg-purple-100 px-2.5 py-0.5 rounded-full">All Invoices</span>
                        </div>
                        <p class="text-2xl font-black text-purple-950 mt-2">
                            <AnimatedCounter :value="props.stats?.total_invoiced_idr || 0" prefix="Rp " />
                        </p>
                        <span class="text-[11px] font-semibold text-slate-500 mt-2 block">
                            Dari <strong class="text-slate-700">{{ props.stats?.total_registrations || 0 }}</strong> invoice pendaftaran
                        </span>
                    </div>

                    <!-- Verified Received Money (Sudah Lunas) -->
                    <div class="animate-fade-in-up animation-delay-200 rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Uang Masuk (Lunas & Terverifikasi)</span>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">Verified</span>
                        </div>
                        <p class="text-2xl font-black text-emerald-950 mt-2">
                            <AnimatedCounter :value="props.stats?.verified_revenue_idr || 0" prefix="Rp " />
                        </p>
                        <span class="text-[11px] font-semibold text-emerald-700 mt-2 block">
                            <strong class="text-emerald-800">{{ props.stats?.verified_payments || 0 }}</strong> transaksi telah lunas
                        </span>
                    </div>

                    <!-- Unpaid / Pending Balance (Belum Dibayar) -->
                    <div class="animate-fade-in-up animation-delay-300 rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-amber-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Sisa Tagihan (Belum Lunas)</span>
                            <span class="relative flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full">
                                <span v-if="(props.stats?.pending_payments || 0) > 0" class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                <span>Pending ({{ props.stats?.unpaid_invoices_count || 0 }})</span>
                            </span>
                        </div>
                        <p class="text-2xl font-black text-amber-950 mt-2">
                            <AnimatedCounter :value="props.stats?.unpaid_revenue_idr || 0" prefix="Rp " />
                        </p>
                        <span class="text-[11px] font-semibold text-amber-800 mt-1.5 block">
                            <template v-if="(props.stats?.pending_payments || 0) > 0">
                                <strong class="text-amber-900">{{ props.stats?.pending_payments }}</strong> bukti transfer menunggu verifikasi
                            </template>
                            <template v-else>
                                <strong class="text-amber-900">{{ props.stats?.unpaid_invoices_count || 0 }}</strong> invoice belum melakukan pembayaran
                            </template>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submissions & Participants Metrics Grid (3 Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Card 1: Participants -->
                <div class="animate-fade-in-up animation-delay-100 rounded-2xl border border-slate-200 bg-white p-5 space-y-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-purple-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Akun Peserta</span>
                        <span class="rounded bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700 border border-purple-100">Participants</span>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-slate-900">
                            <AnimatedCounter :value="props.stats?.total_participants || 0" />
                        </span>
                        <span class="text-xs text-slate-400 ml-1">pengguna terdaftar</span>
                    </div>
                    <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100 text-[11px] font-bold">
                            {{ props.stats?.presenters_count || 0 }} Pemakalah
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-bold">
                            {{ props.stats?.non_presenters_count || 0 }} Peserta Umum
                        </span>
                    </div>
                </div>

                <!-- Card 2: Abstract Submissions Funnel -->
                <div class="animate-fade-in-up animation-delay-200 rounded-2xl border border-slate-200 bg-white p-5 space-y-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-indigo-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Progres Naskah Abstrak</span>
                        <Link :href="route('admin.abstracts.index')" class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-100 hover:bg-indigo-100 transition">
                            Kelola Naskah →
                        </Link>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-slate-900">
                            <AnimatedCounter :value="props.stats?.total_abstracts || 0" />
                        </span>
                        <span class="text-xs text-slate-400 ml-1">total naskah masuk</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-slate-100">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            ✓ {{ props.stats?.accepted_abstracts || 0 }} Diterima
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold">
                            ⌛ {{ props.stats?.under_review_abstracts || 0 }} Ditelaah
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                            ✎ {{ props.stats?.revision_abstracts || 0 }} Revisi
                        </span>
                        <span v-if="(props.stats?.rejected_abstracts || 0) > 0" class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">
                            ✕ {{ props.stats?.rejected_abstracts }} Ditolak
                        </span>
                    </div>
                </div>

                <!-- Card 3: Active Scientific Tracks -->
                <div class="animate-fade-in-up animation-delay-300 rounded-2xl border border-slate-200 bg-white p-5 space-y-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-amber-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Bidang Ilmu & Topik</span>
                        <Link :href="route('admin.categories.index')" class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200 hover:bg-slate-200 transition">
                            Tracks →
                        </Link>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-slate-900">
                            <AnimatedCounter :value="props.trackCategories ? props.trackCategories.length : 0" />
                        </span>
                        <span class="text-xs text-slate-400 ml-1">kategori peminatan aktif</span>
                    </div>
                    <div class="pt-1 border-t border-slate-100 text-xs font-semibold text-slate-500">
                        Distribusi naskah ke reviewer sesuai bidang ilmu
                    </div>
                </div>
            </div>

            <!-- Side-by-Side Tables (Recent Payments & Tracks Recap) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Registrations & Payments Table -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Antrean Transaksi & Tagihan Terbaru</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">8 transaksi pendaftaran terakhir yang tercatat di sistem.</p>
                        </div>
                        <Link :href="route('admin.payments.index')" class="text-xs font-bold text-purple-700 hover:underline">
                            Semua Pembayaran →
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Invoice</th>
                                    <th scope="col" class="px-5 py-3">Pendaftar</th>
                                    <th scope="col" class="px-5 py-3">Kategori & Alur</th>
                                    <th scope="col" class="px-5 py-3">Nominal</th>
                                    <th scope="col" class="px-5 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="!props.recentRegistrations || props.recentRegistrations.length === 0">
                                    <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                        Belum ada data pendaftaran yang tercatat.
                                    </td>
                                </tr>
                                <tr
                                    v-for="(reg, index) in props.recentRegistrations"
                                    :key="reg.id"
                                    class="table-row-stagger hover:bg-slate-50/50 transition"
                                    :style="{ animationDelay: `${Math.min(index * 90 + 200, 900)}ms` }"
                                >
                                    <td class="px-5 py-3.5">
                                        <Link :href="route('admin.registrations.show', reg.id)" class="font-bold text-purple-900 text-xs hover:underline">
                                            {{ reg.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-slate-800 text-xs">{{ reg.user?.name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ reg.user?.email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-xs font-semibold text-slate-800">
                                            {{ reg.registration_fee?.name || reg.registrationFee?.name || 'Pendaftaran Konferensi' }}
                                        </p>
                                        <span
                                            :class="[
                                                'inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md border mt-1',
                                                getRegistrationPresenterStatus(reg).class
                                            ]"
                                        >
                                            {{ getRegistrationPresenterStatus(reg).label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-slate-900 whitespace-nowrap">
                                        {{ reg.currency }} {{ Number(reg.amount).toLocaleString('id-ID') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <span :class="[
                                             'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border',
                                             getRegistrationPaymentBadge(reg).class
                                         ]">
                                             <span v-if="getRegistrationPaymentBadge(reg).hasPing" class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                             <span>{{ getRegistrationPaymentBadge(reg).label }}</span>
                                         </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Scientific Tracks Submissions Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col shadow-2xs">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Sebaran Naskah per Topik</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Statistik naskah per bidang ilmu.</p>
                        </div>
                        <Link :href="route('admin.categories.index')" class="text-xs font-bold text-purple-700 hover:underline">
                            Kelola →
                        </Link>
                    </div>

                    <div class="p-4 flex-1 space-y-2.5">
                        <div v-if="!props.trackCategories || props.trackCategories.length === 0" class="text-center text-xs text-slate-400 py-6">
                            Belum ada kategori bidang ilmu yang didaftarkan.
                        </div>

                        <div
                            v-for="(track, index) in props.trackCategories"
                            :key="track.id"
                            class="table-row-stagger p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition hover:-translate-y-0.5 shadow-2xs"
                            :style="{ animationDelay: `${Math.min(index * 110 + 250, 900)}ms` }"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-bold text-purple-800 mb-1">
                                        {{ track.badge || 'TRACK' }}
                                    </span>
                                    <p class="font-bold text-slate-900 text-xs line-clamp-1">{{ track.name }}</p>
                                </div>

                                <div class="text-right whitespace-nowrap">
                                    <span class="text-sm font-bold text-purple-950">{{ track.abstracts_count }}</span>
                                    <span class="block text-[10px] text-slate-400">Total Naskah</span>
                                </div>
                            </div>

                            <!-- Accepted & Under Review sub-counters -->
                            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-200/60 text-[10px]">
                                <span class="text-emerald-700 font-bold">
                                    ✓ {{ track.accepted_count || 0 }} Diterima
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-indigo-700 font-bold">
                                    ⌛ {{ track.under_review_count || 0 }} Ditelaah
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Direct Recap Quick Navigation Bar -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-2xs">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Akses Cepat Pengelolaan</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Navigasi langsung ke modul pendaftaran, pembayaran, atau naskah ilmiah.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.registrations.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Data Registrasi
                    </Link>
                    <Link :href="route('admin.payments.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Antrean Pembayaran
                    </Link>
                    <Link :href="route('admin.abstracts.index')" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 transition shadow-2xs">
                        Daftar Abstrak
                    </Link>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
