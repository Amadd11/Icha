<script setup>
defineProps({
    conference: {
        type: Object,
        default: null,
    },
    countdown: {
        type: Object,
        required: true,
    },
    countdownUnits: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section
        id="hero"
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-5 pb-24 pt-16 md:px-10 lg:pb-12"
    >
        <div class="absolute inset-0 z-0 overflow-hidden">
            <!-- Background Image -->
            <img
                v-if="conference?.hero_image"
                :src="'/storage/' + conference.hero_image"
                :alt="conference?.title"
                class="absolute inset-0 h-full w-full object-cover object-center"
            />
            <img
                v-else
                src="/assets/images/umsura.png"
                alt="UMSURA Background"
                class="absolute inset-0 h-full w-full object-cover object-center"
            />

            <!-- Main Dark Overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-indigo-850/65 via-slate-950/55 to-slate-950/85"
            ></div>

            <!-- Soft Blue/Indigo Tint -->
            <div
                class="absolute inset-0 bg-indigo-850/30 mix-blend-multiply"
            ></div>
        </div>

        <!-- Subtle Grid Pattern -->
        <div
            class="absolute inset-0 z-0 pointer-events-none opacity-10"
            :style="{
                backgroundImage:
                    'url(\'data:image/svg+xml,%3Csvg width=\'40\' height=\'40\' viewBox=\'0 0 40 40\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M0 0h40v40H0V0zm1 1h38v38H1V1z\' fill=\'%23ffffff\' fill-opacity=\'0.1\' fill-rule=\'evenodd\'/%3E%3C/svg%3E\')',
            }"
        ></div>

        <!-- Main Content (Centered) -->
        <div
            class="relative z-10 mx-auto flex w-full max-w-5xl flex-col items-center text-center"
        >
            <!-- Badge -->
            <div
                class="mb-6 inline-flex items-center gap-3 rounded-full border border-purple-500/30 bg-purple-950/70 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-purple-200 shadow-[0_0_15px_rgba(168,85,247,0.15)] transition-all hover:bg-purple-900/70"
            >
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold opacity-75"
                    ></span>
                    <span
                        class="relative inline-flex rounded-full h-2 w-2 bg-gold-dark"
                    ></span>
                </span>
                <span>10th International Conference</span>
            </div>

            <!-- Subtitle -->
            <p
                class="mb-4 text-2xl font-bold uppercase tracking-[0.25em] text-gold"
            >
                {{ conference?.title ? '' : 'International Conference on Healthcare Administration' }}
            </p>

            <!-- Main Title -->
            <h1
                class="mb-6 font-sans text-5xl font-extrabold leading-[1.1] tracking-tight text-white md:text-6xl lg:text-7xl"
            >
                <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-white to-gold-dark drop-shadow-sm"
                    >{{ conference?.title || 'ICHA 2026' }}</span
                >
            </h1>

            <!-- Description -->
            <p class="mb-10 text-lg leading-relaxed text-slate-300 max-w-2xl">
                <strong class="font-semibold text-white"
                    >{{ conference?.tagline || 'Musyawarah Nasional PIPMARSI' }}</strong
                ><br />
                <em class="not-italic text-white"
                    >{{ conference?.theme || 'Leading the Future of Healthcare Administration' }}</em
                >
            </p>

            <!-- Call To Actions -->
            <div class="mb-14 flex flex-wrap items-center justify-center gap-5">
                <a
                    href="#abstract"
                    class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-primary to-primary-dark px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-dark/30 transition-all hover:scale-105 hover:shadow-primary-dark/50"
                >
                    <span class="relative z-10">Submit Your Abstract</span>
                    <svg
                        class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-r from-primary-dark to-primary opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </a>
                <a
                    href="#about"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-600 bg-slate-900/80 px-8 py-3.5 text-sm font-semibold text-white transition-all hover:bg-slate-800 hover:border-slate-400"
                >
                    Learn More
                </a>
            </div>

            <!-- Bottom Content Group (Countdown + Cards) -->
            <div
                class="mb-16 md:mb-24 flex w-full max-w-6xl flex-col items-center gap-8 lg:flex-row lg:items-stretch lg:justify-center"
            >
                <!-- Glass Countdown Tracker -->
                <div
                    class="relative z-20 flex w-full lg:w-auto flex-col items-center justify-center gap-4 rounded-3xl border border-white/10 bg-slate-900/85 p-6 shadow-2xl"
                >
                    <div class="flex flex-wrap justify-center gap-3 xl:gap-4">
                        <div
                            v-for="unit in countdownUnits"
                            :key="unit.key"
                            class="flex min-w-[70px] xl:min-w-[80px] flex-col items-center justify-center rounded-2xl bg-slate-950/70 px-3 py-3 xl:px-4 xl:py-4 shadow-inner border border-white/5"
                        >
                            <div
                                class="text-3xl xl:text-4xl font-black tabular-nums tracking-tight text-white drop-shadow-md"
                            >
                                {{ countdown[unit.key] }}
                            </div>
                            <div
                                class="mt-2 text-[0.65rem] xl:text-[0.7rem] font-semibold uppercase tracking-widest text-slate-400"
                            >
                                {{ unit.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards Grid -->
                <div
                    class="grid w-full lg:w-auto flex-1 grid-cols-1 gap-4 sm:grid-cols-3 lg:gap-5"
                >
                    <div
                        class="flex flex-col items-center justify-center text-center gap-3 rounded-3xl border border-white/10 bg-slate-900/75 p-5 xl:p-6 transition-colors hover:bg-slate-900/90"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/20 text-primary"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div>
                            <strong
                                class="block text-sm xl:text-base font-bold text-white"
                                >10–11 Nov 2026</strong
                            >
                            <span class="text-xs xl:text-sm text-slate-400"
                                >Conference Date</span
                            >
                        </div>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center text-center gap-3 rounded-3xl border border-white/10 bg-slate-900/75 p-5 xl:p-6 transition-colors hover:bg-slate-900/90"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/20 text-gold"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <strong
                                class="block text-sm xl:text-base font-bold text-white"
                                >UMSURA</strong
                            >
                            <span class="text-xs xl:text-sm text-slate-400"
                                >Surabaya</span
                            >
                        </div>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center text-center gap-3 rounded-3xl border border-white/10 bg-slate-900/75 p-5 xl:p-6 transition-colors hover:bg-slate-900/90"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-accent-red/20 text-accent-red"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </div>
                        <div>
                            <strong
                                class="block text-sm xl:text-base font-bold text-white"
                                >Abstract Open</strong
                            >
                            <span class="text-xs xl:text-sm text-slate-400"
                                >11 Aug – 3 Oct</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hosted By Footer -->
        <div
            class="absolute bottom-0 left-0 right-0 z-20 border-t border-white/10 bg-slate-950/60 px-5 py-4 backdrop-blur-md md:px-10"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center gap-4 md:flex-row md:justify-between"
            >
                <span
                    class="whitespace-nowrap text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400"
                >
                    Hosted by
                </span>
                <div class="flex flex-wrap items-center justify-center gap-6">
                    <!-- PIPMARSI Logo -->
                    <div
                        class="flex items-center justify-center rounded-lg border border-white/20 bg-white/10 px-4 py-2 transition-colors hover:bg-white/20"
                    >
                        <img
                            src="/assets/logo/logo-pipmarsi.png"
                            alt="PIPMARSI"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <!-- UMSURA Logo -->
                    <div
                        class="flex items-center justify-center rounded-lg border border-white/20 bg-white/10 px-4 py-2 transition-colors hover:bg-white/20"
                    >
                        <img
                            src="/assets/logo/logo-umsura.png"
                            alt="Universitas Muhammadiyah Surabaya"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <!-- Universitas Brawijaya Logo -->
                    <div
                        class="flex items-center justify-center rounded-lg border border-white/20 bg-white/10 px-4 py-2 transition-colors hover:bg-white/20"
                    >
                        <img
                            src="/assets/logo/logo-ub.png"
                            alt="Universitas Brawijaya"
                            class="h-14 w-auto object-contain"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
