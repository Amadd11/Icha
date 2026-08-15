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
                <span class="inline-block rounded-full bg-amber-400/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-amber-300 border border-amber-400/20 mb-4">
                    Call for Papers
                </span>
                <h2 class="text-3xl font-black tracking-tight text-white md:text-5xl">
                    Submit Your Abstract
                </h2>
                <p class="mt-4 max-w-2xl mx-auto text-sm leading-relaxed text-purple-200/80 md:text-base">
                    Share your research, case studies, or innovative projects with global healthcare administration leaders.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
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
