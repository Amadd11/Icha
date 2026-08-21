<script setup>
import { ref, computed } from "vue";
import SectionHeading from "@/Components/UI/SectionHeading.vue";

const props = defineProps({
    conference: {
        type: Object,
        default: null,
    },
    eyebrow: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
    stats: {
        type: Array,
        default: null,
    },
});

const isPosterModalOpen = ref(false);

const displayStats = computed(() => {
    if (props.stats && props.stats.length > 0) {
        return props.stats;
    }

    const categoriesCount = props.conference?.categories?.length ?? 4;
    const speakersCount = props.conference?.speakers?.length ?? 0;
    const sponsorsCount = props.conference?.sponsors?.length ?? 0;

    const list = [
        { value: categoriesCount.toString(), label: "Scientific Tracks" },
    ];

    if (speakersCount > 0) {
        list.push({ value: speakersCount.toString(), label: "Keynote Speakers" });
    } else {
        list.push({ value: "2", label: "Conference Days" });
    }

    if (sponsorsCount > 0) {
        list.push({ value: sponsorsCount.toString(), label: "Partners & Sponsors" });
    } else {
        list.push({ value: "3", label: "Host Institutions" });
    }

    return list;
});

function formatImageUrl(path) {
    if (!path) return '/assets/images/poster.jpeg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    if (path.startsWith('/assets/')) return path;
    return '/storage/' + path;
}

const posterUrl = computed(() => {
    const customPoster = props.conference?.poster || props.conference?.poster_image;
    return formatImageUrl(customPoster);
});
</script>

<template>
    <section id="about" class="bg-slate-50 px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12">
                <!-- Left Column: Background Description & Stats -->
                <div class="lg:col-span-7 space-y-6 fade-in">
                    <h3 class="text-2xl font-bold text-slate-900 md:text-3xl">
                        Why {{ props.conference?.title || 'ICHA 2026' }}?
                    </h3>
                    <p class="leading-relaxed text-slate-600 text-sm md:text-base">
                        {{ props.conference?.description || ('The ' + (props.conference?.title || 'International Conference on Healthcare Administration 2026') + ' brings together researchers, academics, practitioners, students, and policymakers to share ideas, innovations, and best practices that will shape the future of healthcare systems worldwide.') }}
                    </p>

                    <!-- Theme Callout Card (Yellow Gold Gradient) -->
                    <div
                        v-if="props.conference?.theme"
                        class="fade-in rounded-2xl bg-gradient-to-br from-gold via-yellow-400 to-amber-500 p-6 text-slate-950 shadow-lg border border-gold/40 space-y-2"
                    >
                        <strong class="block text-xs font-extrabold uppercase tracking-[0.2em] text-slate-950/80">
                            Conference Theme
                        </strong>
                        <div class="text-lg font-black leading-snug text-slate-950">
                            “{{ props.conference.theme }}”
                        </div>
                        <p v-if="props.conference?.tagline" class="text-xs font-semibold leading-relaxed text-slate-900/90 pt-1 border-t border-slate-950/10">
                            {{ props.conference.tagline }}
                        </p>
                    </div>

                    <!-- Display Stats Grid -->
                    <div v-if="displayStats && displayStats.length > 0" class="pt-2 grid grid-cols-2 gap-5 sm:grid-cols-3">
                        <div
                            v-for="item in displayStats"
                            :key="item.label"
                            class="fade-in rounded-2xl bg-white p-5 text-center shadow-xs border border-slate-200/80"
                        >
                            <div class="text-3xl font-black text-primary">
                                {{ item.value }}
                            </div>
                            <div class="mt-1 text-xs font-semibold text-slate-500">
                                {{ item.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Larger Borderless Portrait Poster Image -->
                <div class="lg:col-span-5 flex flex-col items-center lg:items-end fade-in">
                    <div
                        @click="isPosterModalOpen = true"
                        class="group cursor-pointer overflow-hidden rounded-3xl shadow-2xl transition-all duration-300 hover:shadow-purple-950/20 hover:scale-[1.02] max-w-sm sm:max-w-md w-full border border-slate-200/80 bg-white relative"
                    >
                        <img
                            :src="posterUrl"
                            :alt="props.conference?.title ? `${props.conference.title} Official Poster` : 'Conference Poster'"
                            class="w-full h-auto aspect-[3/4] object-cover rounded-3xl transition-transform duration-500 group-hover:scale-103"
                        />

                        <!-- Hover Overlay with Zoom Icon & Hint -->
                        <div class="absolute inset-0 bg-slate-950/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-950/80 px-4 py-2 text-xs font-bold text-white shadow-lg backdrop-blur-xs">
                                <svg class="h-4 w-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                                <span>Klik untuk memperbesar</span>
                            </span>
                        </div>
                    </div>
                    <span class="mt-2.5 text-[11px] font-semibold text-slate-400">
                        🔍 Klik poster untuk melihat resolusi penuh
                    </span>
                </div>
            </div>
        </div>

        <!-- POSTER LIGHTBOX MODAL DIALOG -->
        <div
            v-if="isPosterModalOpen"
            @click.self="isPosterModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 sm:p-6"
        >
            <div class="relative max-w-4xl max-h-[92vh] overflow-hidden rounded-3xl bg-slate-950 p-2 shadow-2xl border border-white/15">
                <button
                    @click="isPosterModalOpen = false"
                    class="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-slate-950/80 text-white font-bold text-base hover:bg-slate-800 transition cursor-pointer border border-white/20"
                >
                    ✕
                </button>
                <img
                    :src="posterUrl"
                    :alt="props.conference?.title ? `${props.conference.title} Official Poster Full` : 'Conference Official Poster Full'"
                    class="max-h-[88vh] w-auto rounded-2xl object-contain mx-auto"
                />
            </div>
        </div>
    </section>
</template>
