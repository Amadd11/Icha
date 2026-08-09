<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 p-6 font-sans text-slate-800">
        
        <!-- Minimalist Logo -->
        <div class="mb-8">
            <Link href="/" class="group flex flex-col items-center gap-4">
                <div class="flex items-center gap-3 px-4 py-2 transition-transform group-hover:scale-105">
                    <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-12 w-auto object-contain" />
                    <div class="w-px h-8 bg-slate-200"></div>
                    <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-12 w-auto object-contain" />
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-900 transition-colors group-hover:text-primary">
                    ICHA 2026
                </span>
            </Link>
        </div>

        <!-- Auth Card -->
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white p-8 shadow-xl shadow-slate-200/50 sm:p-10 border border-slate-100">
            <slot />
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; 2026 PIPMARSI & Universitas Muhammadiyah Surabaya.
        </div>
        
    </div>
</template>
