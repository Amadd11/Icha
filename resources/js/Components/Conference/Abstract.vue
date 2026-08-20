<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <section id="abstract" class="relative overflow-hidden bg-gradient-to-br from-sidebar via-purple-950 to-slate-950 px-5 py-20 text-white md:px-10 md:py-24">
        <!-- Background Ambient Glow -->
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-purple-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-4xl text-center space-y-8">
            <!-- Heading -->
            <div>
                <span class="inline-block rounded-full bg-purple-400/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-purple-300 border border-purple-400/20 mb-4">
                    Call For Abstracts
                </span>
                <h2 class="text-3xl font-black text-white sm:text-4xl md:text-5xl tracking-tight mb-6">
                    Submit Your Scientific Work Today
                </h2>
                <p class="text-slate-300 text-base sm:text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
                    Join leading scholars, healthcare professionals, and researchers at ICHA 2026. Submit your abstract and present your innovations.
                </p>
                <Link
                    :href="route('participant.submission.index')"
                    class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-extrabold text-xs px-8 py-3.5 shadow-lg shadow-amber-500/20 transition cursor-pointer"
                >
                    Submit Abstract Now
                </Link>
                <a
                    href="#timeline"
                    class="rounded-xl border border-white/20 bg-white/5 hover:bg-white/10 text-white font-bold text-xs px-8 py-3.5 transition cursor-pointer"
                >
                    View Guidelines
                </a>
            </div>
        </div>
    </section>
</template>
