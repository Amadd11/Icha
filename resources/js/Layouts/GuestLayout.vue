<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const activeConference = computed(() => page.props.activeConference);
</script>

<template>
    <div class="relative flex min-h-screen w-full flex-col justify-between bg-slate-950 bg-gradient-to-br from-sidebar via-purple-950 to-slate-950 font-sans text-white">
        
        <!-- Background Subtle Pattern & Ambient Orbs -->
        <div class="pointer-events-none fixed inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;80&quot; height=&quot;80&quot; viewBox=&quot;0 0 80 80&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M0 0h80v80H0z&quot; fill=&quot;none&quot;/%3E%3Cpath d=&quot;M40 0L0 40l40 40 40-40zM40 10L10 40l30 30 30-30z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="pointer-events-none fixed -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-purple-600/30 blur-3xl"></div>
        <div class="pointer-events-none fixed -bottom-40 -right-40 h-[500px] w-[500px] rounded-full bg-amber-500/20 blur-3xl"></div>

        <!-- Top Header Bar -->
        <header class="relative z-20 flex shrink-0 items-center justify-between px-6 py-4 sm:px-10">
            <Link
                href="/"
                class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-bold text-purple-100 border border-white/15 backdrop-blur-md transition duration-200 hover:bg-white/20 hover:text-gold"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to Portal
            </Link>
        </header>

        <!-- Center Content Slot -->
        <main class="relative z-20 flex flex-1 flex-col items-center justify-center px-4 py-6 sm:py-10">
            <div class="w-full max-w-2xl my-auto">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-20 shrink-0 py-4 text-center text-xs text-purple-200/60 border-t border-white/10 bg-slate-950/40">
            &copy; {{ new Date().getFullYear() }} PIPMARSI & Universitas Muhammadiyah Surabaya. All rights reserved.
        </footer>
    </div>
</template>
