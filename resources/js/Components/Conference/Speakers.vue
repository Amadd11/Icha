<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import SectionHeading from "@/Components/UI/SectionHeading.vue";

const props = defineProps({
    eyebrow: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
    speakers: {
        type: Array,
        default: () => [],
    },
});

const selectedSpeaker = ref(null);
const isModalOpen = ref(false);

function openSpeakerModal(speaker) {
    selectedSpeaker.value = speaker;
    isModalOpen.value = true;
    document.body.style.overflow = "hidden";
}

function closeSpeakerModal() {
    isModalOpen.value = false;
    document.body.style.overflow = "";
    setTimeout(() => {
        selectedSpeaker.value = null;
    }, 200);
}

function handleKeydown(e) {
    if (e.key === "Escape" && isModalOpen.value) {
        closeSpeakerModal();
    }
}

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    document.body.style.overflow = "";
});

/**
 * Get flag image URL from country_code (ISO 3166-1 alpha-2).
 * Uses flagcdn.com for reliable, lightweight flag images.
 */
function getFlagUrl(speaker) {
    if (speaker.country_code) {
        return `https://flagcdn.com/w40/${speaker.country_code.toLowerCase()}.png`;
    }
    return null;
}

const typeLabels = {
    keynote: 'Keynote Speaker',
    plenary: 'Plenary Speaker',
    invited: 'Invited Speaker',
    speaker: 'Invited Speaker',
};

const displaySpeakers = computed(() => {
    if (!props.speakers?.length) return [];
    return [...props.speakers];
});

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}
</script>

<template>
    <section id="speakers" class="bg-slate-50 px-5 py-16 md:px-10 md:py-24 relative border-t border-slate-200/60">
        <div class="mx-auto max-w-7xl relative z-10">
            <!-- Section Heading with ample bottom margin -->
            <div class="mb-10 text-center max-w-3xl mx-auto">
                <SectionHeading
                    :eyebrow="props.eyebrow"
                    :title="props.title"
                    :description="props.description"
                />
            </div>

            <!-- Speaker Cards Grid (Unified List, Latest Added First) -->
            <template v-if="displaySpeakers && displaySpeakers.length">
                <div class="flex flex-wrap justify-center gap-x-8 gap-y-12 lg:gap-x-12 mt-6">
                    <div
                        v-for="speaker in displaySpeakers"
                        :key="speaker.id"
                        @click="openSpeakerModal(speaker)"
                        class="group cursor-pointer flex w-56 sm:w-60 flex-col items-center text-center p-5 rounded-3xl transition-all duration-300 hover:bg-white hover:shadow-xl hover:shadow-purple-950/10 hover:-translate-y-2 border border-transparent hover:border-purple-100 bg-white/50"
                    >
                        <!-- Circular Photo with Ring -->
                        <div class="relative mb-7">
                            <div class="h-40 w-40 sm:h-44 sm:w-44 rounded-full border-[3px] border-amber-300/80 bg-white p-1.5 shadow-md group-hover:border-gold group-hover:shadow-amber-400/20 transition-all duration-300">
                                <div class="h-full w-full overflow-hidden rounded-full bg-slate-100 flex items-center justify-center relative">
                                    <img
                                        v-if="speaker.photo"
                                        :src="formatStorageUrl(speaker.photo)"
                                        :alt="speaker.name"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-108"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200"
                                    >
                                        <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </div>

                                    <!-- Hover Overlay Pill -->
                                    <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="text-[11px] font-bold text-white bg-slate-950/90 px-3 py-1 rounded-full shadow-sm">
                                            View Bio &rarr;
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Country Flag Badge -->
                            <div
                                v-if="getFlagUrl(speaker)"
                                class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 flex items-center justify-center z-10"
                            >
                                <div class="rounded-full bg-white p-1 shadow-md border border-slate-100">
                                    <img
                                        :src="getFlagUrl(speaker)"
                                        :alt="speaker.country || speaker.country_code"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-6 w-6 rounded-full object-cover"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Speaker Info -->
                        <div class="w-full flex-1 flex flex-col items-center justify-between">
                            <div>
                                <h4 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug group-hover:text-primary transition-colors">
                                    {{ speaker.name }}
                                </h4>
                                <p v-if="speaker.institution" class="mt-1.5 text-xs font-semibold text-slate-600 leading-snug">
                                    {{ speaker.institution }}
                                </p>
                                <p v-if="speaker.country" class="mt-0.5 text-xs text-slate-400 font-medium">
                                    {{ speaker.country }}
                                </p>
                            </div>

                            <!-- Action button link -->
                            <div class="mt-4 pt-2 border-t border-slate-100 w-full flex items-center justify-center">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-primary group-hover:text-gold transition-colors">
                                    <span>Profile &amp; Topic</span>
                                    <span>&rarr;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else class="mt-12 rounded-2xl bg-white p-8 text-center border border-slate-200">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <p class="text-sm text-slate-500">Speaker lineup will be announced soon.</p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- INTERACTIVE SPEAKER PROFILE MODAL                            -->
        <!-- ============================================================ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isModalOpen && selectedSpeaker"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 md:p-10 overflow-y-auto"
                >
                    <!-- Backdrop Overlay -->
                    <div
                        class="fixed inset-0 bg-black/60 transition-opacity"
                        @click="closeSpeakerModal"
                    ></div>

                    <!-- Modal Box -->
                    <div
                        class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden transform transition-all z-10 max-h-[90vh] flex flex-col"
                    >
                        <!-- Modal Top Header Banner -->
                        <div class="relative bg-gradient-to-r from-[#2a1b4e] via-[#3b0764] to-[#1e1b4b] p-6 sm:p-8 text-white shrink-0">
                            <!-- Close Button -->
                            <button
                                @click="closeSpeakerModal"
                                class="absolute top-4 right-4 h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/20"
                                aria-label="Close modal"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Speaker Type Badge -->
                            <span class="inline-block rounded-full bg-amber-400/20 px-3 py-1 text-xs font-black uppercase tracking-wider text-amber-300 border border-amber-400/40 mb-4">
                                {{ typeLabels[selectedSpeaker.type] || selectedSpeaker.type || 'Speaker' }}
                            </span>

                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                                <!-- Big Avatar with Flag -->
                                <div class="relative shrink-0">
                                    <div class="h-28 w-28 sm:h-32 sm:w-32 rounded-full border-4 border-amber-300 bg-white p-1 shadow-xl">
                                        <img
                                            v-if="selectedSpeaker.photo"
                                            :src="formatStorageUrl(selectedSpeaker.photo)"
                                            :alt="selectedSpeaker.name"
                                            class="h-full w-full rounded-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center rounded-full bg-slate-100 text-slate-400 font-black text-2xl"
                                        >
                                            {{ selectedSpeaker.name?.charAt(0) || 'S' }}
                                        </div>
                                    </div>

                                    <!-- Country Flag -->
                                    <div v-if="getFlagUrl(selectedSpeaker)" class="absolute -bottom-1 right-1 rounded-full bg-white p-1 shadow-md border border-slate-100">
                                        <img :src="getFlagUrl(selectedSpeaker)" :alt="selectedSpeaker.country" class="h-6 w-6 rounded-full object-cover" />
                                    </div>
                                </div>

                                <!-- Header Text Info -->
                                <div class="text-center sm:text-left flex-1">
                                    <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
                                        {{ selectedSpeaker.name }}
                                    </h3>
                                    <p v-if="selectedSpeaker.title" class="text-xs sm:text-sm font-semibold text-gold mt-1">
                                        {{ selectedSpeaker.title }}
                                    </p>
                                    <p v-if="selectedSpeaker.institution" class="text-xs sm:text-sm text-purple-200/90 mt-1 font-medium">
                                        🏛️ {{ selectedSpeaker.institution }}
                                    </p>
                                    <p v-if="selectedSpeaker.country" class="text-xs text-purple-300/80 mt-0.5">
                                        📍 {{ selectedSpeaker.country }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Scrollable Content -->
                        <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1 text-slate-700">
                            <!-- Presentation / Keynote Topic Box -->
                            <div class="rounded-2xl bg-purple-50/80 border border-purple-100 p-5">
                                <div class="flex items-center gap-2 text-primary text-xs font-black uppercase tracking-wider mb-2">
                                    <svg class="h-4 w-4 text-gold" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                                    </svg>
                                    <span>Presentation Topic &amp; Keynote</span>
                                </div>
                                <p class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
                                    "{{ selectedSpeaker.topic || selectedSpeaker.title || 'Digital Transformation, Quality Governance & Sustainable Healthcare Leadership' }}"
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500 pt-3 border-t border-purple-200/60">
                                    <span class="inline-flex items-center gap-1 text-primary font-bold">
                                        📅 10–11 Nov 2026
                                    </span>
                                    <span>•</span>
                                    <span>📍 Auditorium UMSURA &amp; Zoom</span>
                                </div>
                            </div>

                            <!-- Biography Section -->
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">
                                    Biography &amp; Academic Background
                                </h4>
                                <div class="text-sm text-slate-600 leading-relaxed space-y-3 font-normal">
                                    <p v-if="selectedSpeaker.bio">
                                        {{ selectedSpeaker.bio }}
                                    </p>
                                    <p v-else>
                                        {{ selectedSpeaker.name }} is an esteemed academic and healthcare leader representing {{ selectedSpeaker.institution || 'leading international health institutions' }}. With extensive experience in health administration, hospital management systems, and clinical quality advancement, their presentation at ICHA 2026 brings invaluable global insights for delegates.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                            <span class="text-xs text-slate-400 font-medium">
                                ICHA 2026 Official Speaker Profile
                            </span>
                            <div class="flex items-center gap-2">
                                <a
                                    href="/#timeline"
                                    @click="closeSpeakerModal"
                                    class="px-4 py-2 text-xs font-bold text-primary hover:bg-primary/10 rounded-xl transition"
                                >
                                    View Timeline &rarr;
                                </a>
                                <button
                                    @click="closeSpeakerModal"
                                    class="px-5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
