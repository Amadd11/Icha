<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const errorInfo = computed(() => {
    switch (props.status) {
        case 404:
            return {
                code: '404',
                badge: 'Halaman Tidak Ditemukan',
                title: 'Halaman Tidak Ditemukan',
                description: 'Halaman yang Anda cari tidak tersedia, tautan mungkin rusak, atau berkas telah dipindahkan.',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
                dotClass: 'bg-amber-500',
            };
        case 403:
            return {
                code: '403',
                badge: 'Akses Ditolak',
                title: 'Akses Tidak Diizinkan',
                description: 'Anda tidak memiliki hak akses atau izin yang sesuai untuk membuka halaman ini.',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
                dotClass: 'bg-rose-500',
            };
        case 500:
            return {
                code: '500',
                badge: 'Kesalahan Server',
                title: 'Terjadi Kendala Sistem',
                description: 'Server sedang mengalami kendala internal saat memproses data. Silakan coba kembali sesaat lagi.',
                badgeClass: 'bg-red-50 text-red-700 border-red-200',
                dotClass: 'bg-red-500',
            };
        case 503:
            return {
                code: '503',
                badge: 'Layanan Pemeliharaan',
                title: 'Sistem Dalam Pemeliharaan',
                description: 'Sistem sedang menjalani pemeliharaan berkala untuk peningkatan layanan. Kami akan segera kembali.',
                badgeClass: 'bg-sky-50 text-sky-700 border-sky-200',
                dotClass: 'bg-sky-500',
            };
        case 419:
            return {
                code: '419',
                badge: 'Sesi Berakhir',
                title: 'Sesi Halaman Kedaluwarsa',
                description: 'Sesi keamanan formulir Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman.',
                badgeClass: 'bg-purple-50 text-purple-700 border-purple-200',
                dotClass: 'bg-purple-500',
            };
        default:
            return {
                code: String(props.status || 'Error'),
                badge: 'Terjadi Kesalahan',
                title: 'Terjadi Kendala',
                description: 'Permintaan Anda belum dapat diproses. Silakan kembali ke beranda atau muat ulang halaman.',
                badgeClass: 'bg-slate-100 text-slate-700 border-slate-200',
                dotClass: 'bg-slate-500',
            };
    }
});

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}

function reloadPage() {
    window.location.reload();
}
</script>

<template>
    <div class="min-h-screen w-full flex flex-col justify-between bg-slate-50/80 font-sans text-slate-800 antialiased relative">
        <Head :title="`${errorInfo.code} - ${errorInfo.title}`" />

        <!-- Subtle Background Dot Matrix Pattern -->
        <div 
            class="pointer-events-none fixed inset-0 opacity-[0.35]" 
            style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px;"
        ></div>

        <!-- Main Content (Minimalist Light Card) -->
        <main class="relative z-10 flex flex-1 items-center justify-center px-4 py-8 sm:py-12">
            <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/40 p-8 sm:p-12 text-center relative overflow-hidden">
                
                <!-- Huge Subtle Watermark Number in Background -->
                <div class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 select-none text-[120px] sm:text-[150px] font-black tracking-tighter text-slate-100 leading-none -z-0">
                    {{ errorInfo.code }}
                </div>

                <div class="relative z-10">
                    <!-- Status Badge -->
                    <div 
                        class="inline-flex items-center gap-2 rounded-full px-3.5 py-1 text-xs font-semibold border mb-5"
                        :class="errorInfo.badgeClass"
                    >
                        <span class="inline-block w-1.5 h-1.5 rounded-full" :class="errorInfo.dotClass"></span>
                        Error {{ errorInfo.code }} &bull; {{ errorInfo.badge }}
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
                        {{ errorInfo.title }}
                    </h1>

                    <!-- Description -->
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-8 max-w-sm mx-auto">
                        {{ errorInfo.description }}
                    </p>

                    <!-- Minimalist Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link
                            href="/"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold px-5 py-2.5 text-xs sm:text-sm shadow-xs transition duration-150"
                        >
                            <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Ke Beranda
                        </Link>

                        <Link
                            v-if="isAuthenticated"
                            href="/dashboard"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold px-5 py-2.5 text-xs sm:text-sm border border-slate-200 transition duration-150"
                        >
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </Link>

                        <button
                            type="button"
                            @click="goBack"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 font-semibold px-4 py-2.5 text-xs sm:text-sm border border-slate-200 transition duration-150"
                        >
                            Sebelumnya
                        </button>

                        <button
                            v-if="status === 419 || status >= 500"
                            type="button"
                            @click="reloadPage"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 text-xs sm:text-sm transition duration-150"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Muat Ulang
                        </button>
                    </div>

                    <!-- Subtle Assistance Line -->
                    <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-500">
                        Memerlukan bantuan? Silakan hubungi panitia melalui sekretariat konferensi.
                    </div>
                </div>
            </div>
        </main>

        <!-- Minimalist Light Footer -->
        <footer class="relative z-10 w-full py-4 text-center text-xs text-slate-500 border-t border-slate-200/60 bg-white/40">
            &copy; {{ new Date().getFullYear() }} ICHA Conference. All rights reserved.
        </footer>
    </div>
</template>
