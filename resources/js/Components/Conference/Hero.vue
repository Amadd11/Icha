<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
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

const carouselImages = computed(() => {
    if (props.conference?.hero_image) {
        const path = props.conference.hero_image;
        const formatted = (path.startsWith('http://') || path.startsWith('https://'))
            ? path
            : (path.startsWith('/storage/') ? path : (path.startsWith('storage/') ? '/' + path : '/storage/' + path));
        return [formatted];
    }
    return [
        '/assets/images/umsura.png',
    ];
});

const currentIndex = ref(0);
let intervalId = null;

onMounted(() => {
    if (carouselImages.value.length > 1) {
        intervalId = setInterval(() => {
            currentIndex.value = (currentIndex.value + 1) % carouselImages.value.length;
        }, 5000);
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <section id="hero" class="relative flex min-h-screen flex-col lg:flex-row bg-[#1a1133] overflow-hidden pt-12 lg:pt-16 pb-12 border-b border-purple-900/30">
        
        <!-- Clean Layered Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#160c2d] via-[#241747] to-[#160c2d] pointer-events-none"></div>

        <!-- Premium Subtle Micro-Dot Matrix with Radial Vignette Fade -->
        <div 
            class="absolute inset-0 pointer-events-none opacity-20"
            style="background-image: radial-gradient(rgba(255, 255, 255, 0.25) 1px, transparent 1px); background-size: 28px 28px; mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, #000 30%, transparent 80%); -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, #000 30%, transparent 80%);"
        ></div>

        <!-- Refined Soft Atmospheric Lightings -->
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 h-[350px] w-[700px] rounded-full bg-purple-500/15 blur-[100px]"></div>
        <div class="pointer-events-none absolute top-1/3 left-10 h-[300px] w-[300px] rounded-full bg-amber-400/10 blur-[90px]"></div>
        <div class="pointer-events-none absolute bottom-10 right-10 h-[350px] w-[350px] rounded-full bg-indigo-500/10 blur-[100px]"></div>

        <!-- Left Column (Content) -->
        <div class="relative z-10 flex w-full lg:w-1/2 flex-col justify-center px-6 py-6 md:px-12 lg:px-16 xl:px-24">
            
            <!-- Badge & Subtitle -->
            <div class="mb-4 flex flex-col items-start gap-2">
                <p class="text-xs md:text-sm font-bold uppercase tracking-[0.2em] text-gold">
                    {{ conference?.title ? '' : 'International Conference on Healthcare Administration' }}
                </p>
            </div>

            <!-- Main Title -->
            <h1 class="mb-4 font-sans text-5xl font-extrabold leading-[1.1] tracking-tight md:text-6xl lg:text-7xl drop-shadow-xs">
                <span class="text-gold">
                    {{ conference?.title || 'ICHA 2026' }}
                </span>
            </h1>

            <!-- Description -->
            <p class="mb-6 text-base md:text-lg leading-relaxed text-purple-100/90 max-w-xl font-medium">
                <strong class="font-bold text-white">{{ conference?.tagline || 'Musyawarah Nasional PIPMARSI' }}</strong><br />
                <em class="not-italic opacity-90">{{ conference?.theme || 'Leading the Future of Healthcare Administration' }}</em>
            </p>

            <!-- Call To Actions -->
            <div class="mb-8 flex flex-wrap items-center gap-4">
                <a
                    href="#abstract"
                    class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gold hover:bg-amber-400 px-7 py-3.5 text-sm font-bold text-slate-950 shadow-md shadow-amber-500/15 transition-all hover:scale-103 cursor-pointer"
                >
                    <span class="relative z-10">Submit Your Abstract</span>
                    <svg class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <a
                    href="#about"
                    class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-7 py-3.5 text-sm font-bold text-white transition-all hover:bg-white/20 hover:border-white/40 cursor-pointer"
                >
                    Learn More
                </a>
            </div>

            <!-- Info & Countdown Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 border-y border-purple-800/50 py-5 mb-6">
                <!-- Quick Info -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-800/40 text-purple-200 border border-purple-500/30 shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="block text-sm font-bold text-white leading-tight">UMSURA, Surabaya</strong>
                            <span class="text-xs font-semibold text-purple-300">10–11 Nov 2026</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/20 text-gold border border-gold/30 shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="block text-sm font-bold text-white leading-tight">Abstract Open</strong>
                            <span class="text-xs font-semibold text-gold">11 Aug – 3 Oct</span>
                        </div>
                    </div>
                </div>

                <!-- Countdown -->
                <div class="flex flex-col justify-center sm:border-l border-t sm:border-t-0 border-purple-800/50 pt-4 sm:pt-0 sm:pl-6">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-300 mb-1.5">Time Remaining</span>
                    <div class="flex gap-4">
                        <div v-for="unit in countdownUnits" :key="unit.key" class="flex flex-col items-center">
                            <span class="text-2xl font-black tabular-nums tracking-tight text-white drop-shadow-md">{{ countdown[unit.key] }}</span>
                            <span class="text-[0.6rem] font-bold uppercase tracking-widest text-purple-400">{{ unit.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hosted By -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 mt-2">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-300/80">Hosted by</span>
                <div class="flex flex-wrap items-center gap-5 sm:gap-6">
                    <img src="/assets/logo/logo-pipmarsi.png" alt="PIPMARSI" class="h-10 md:h-12 lg:h-14 w-auto object-contain drop-shadow-md transition-transform hover:scale-105" />
                    <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-10 md:h-12 lg:h-14 w-auto object-contain drop-shadow-md transition-transform hover:scale-105" />
                    <img src="/assets/logo/logo-ub.png" alt="Universitas Brawijaya" class="h-10 md:h-12 lg:h-14 w-auto object-contain drop-shadow-md transition-transform hover:scale-105" />
                </div>
            </div>

        </div>

        <!-- Right Column (Image Carousel) -->
        <div class="relative w-full lg:w-1/2 p-6 md:p-8 lg:p-10 flex items-center justify-center">
            
            <!-- Modern Large Rounded Carousel Container -->
            <div class="relative w-full max-w-xl lg:max-w-2xl xl:max-w-3xl h-[380px] sm:h-[460px] md:h-[540px] lg:h-[600px] xl:h-[650px] overflow-hidden rounded-[2.5rem] md:rounded-[3rem] shadow-2xl border border-purple-500/30 bg-slate-900">
                
                <!-- Images Loop -->
                <transition-group name="carousel-fade" tag="div" class="absolute inset-0">
                    <div 
                        v-for="(img, idx) in carouselImages" 
                        :key="img" 
                        v-show="currentIndex === idx"
                        class="absolute inset-0 w-full h-full"
                    >
                        <img :src="img" alt="Hero Image" class="w-full h-full object-cover object-center scale-105 animate-slow-pan" />
                        
                    </div>
                </transition-group>

                <!-- Carousel Indicators -->
                <div v-if="carouselImages.length > 1" class="absolute top-6 right-6 z-20 flex gap-2">
                    <button 
                        v-for="(_, idx) in carouselImages" 
                        :key="idx" 
                        @click="currentIndex = idx"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="currentIndex === idx ? 'w-8 bg-white' : 'w-4 bg-white/40 hover:bg-white/60'"
                        aria-label="Switch slide"
                    ></button>
                </div>
            </div>
        </div>

    </section>
</template>

<style scoped>
/* Smooth fade for carousel images */
.carousel-fade-enter-active,
.carousel-fade-leave-active {
    transition: opacity 1.5s ease-in-out;
}
.carousel-fade-enter-from,
.carousel-fade-leave-to {
    opacity: 0;
}
.carousel-fade-enter-to,
.carousel-fade-leave-from {
    opacity: 1;
}

/* Very slow pan animation for the background image inside the carousel to make it dynamic */
@keyframes slow-pan {
    0% { transform: scale(1.05) translate(0, 0); }
    50% { transform: scale(1.1) translate(-1%, -1%); }
    100% { transform: scale(1.05) translate(0, 0); }
}
.animate-slow-pan {
    animation: slow-pan 20s ease-in-out infinite;
}
</style>
