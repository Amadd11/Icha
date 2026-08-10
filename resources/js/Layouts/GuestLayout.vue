<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen font-sans text-slate-800">

        <!-- Left Panel — Decorative (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-[45%] relative overflow-hidden bg-linear-to-br from-sidebar via-purple-950 to-slate-950">
            <!-- Subtle pattern overlay -->
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <!-- Gradient orbs for depth -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-gold/15 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-purple-400/10 rounded-full blur-2xl"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between w-full p-12">
                <!-- Top: Back button -->
                <div>
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 text-sm font-medium text-purple-200 transition-colors duration-150 hover:text-gold"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Back to Home
                    </Link>
                </div>

                <!-- Center: Branding -->
                <div class="flex flex-col items-start gap-6">
                    <div class="flex items-center gap-3 p-3 bg-white/10 rounded-xl backdrop-blur-sm border border-white/10">
                        <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-10 w-auto object-contain" />
                        <div class="w-px h-8 bg-white/20"></div>
                        <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-10 w-auto object-contain" />
                    </div>

                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight leading-tight">
                            ICHA 2026
                        </h1>
                        <p class="mt-2 text-lg text-purple-200 leading-relaxed max-w-sm">
                            International Conference on<br />
                            Healthcare Administration
                        </p>
                    </div>

                    <div class="w-12 h-0.5 bg-gold rounded-full"></div>

                    <p class="text-sm text-purple-200/80 max-w-xs leading-relaxed">
                        Advancing Healthcare Through Innovation and Collaboration
                    </p>
                </div>

                <!-- Bottom: Copyright -->
                <div class="text-xs text-purple-300/60">
                    &copy; {{ new Date().getFullYear() }} PIPMARSI & Universitas Muhammadiyah Surabaya
                </div>
            </div>
        </div>

        <!-- Right Panel — Form -->
        <div class="flex flex-1 flex-col bg-white">
            <!-- Mobile: Top bar with back button & logo -->
            <div class="flex items-center justify-between px-6 py-4 lg:hidden border-b border-slate-100">
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors duration-150 hover:text-primary"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </Link>
                <Link href="/" class="flex items-center gap-2">
                    <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-7 w-auto object-contain" />
                    <div class="w-px h-5 bg-slate-200"></div>
                    <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-7 w-auto object-contain" />
                </Link>
            </div>
            
            <!-- Form Content -->
            <div class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10">
                <div class="w-full max-w-md">
                    <slot />
                </div>
            </div>

            <!-- Bottom footer (mobile) -->
            <div class="py-4 text-center text-xs text-slate-400 lg:hidden">
                &copy; {{ new Date().getFullYear() }} PIPMARSI & Universitas Muhammadiyah Surabaya
            </div>
        </div>
    </div>
</template>
