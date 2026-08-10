<script setup>
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

/**
 * Group speakers by type for sectioned display.
 */
const typeOrder = ['keynote', 'plenary', 'invited'];
const typeLabels = {
    keynote: 'Keynote Speakers',
    plenary: 'Plenary Speakers',
    invited: 'Invited Speakers',
};

function groupedSpeakers() {
    if (!props.speakers?.length) return [];

    const groups = {};
    props.speakers.forEach((s) => {
        const type = s.type || 'invited';
        if (!groups[type]) groups[type] = [];
        groups[type].push(s);
    });

    return typeOrder
        .filter((t) => groups[t]?.length)
        .map((t) => ({
            type: t,
            label: typeLabels[t] || t,
            items: groups[t],
        }));
}
</script>

<template>
    <section id="speakers" class="bg-slate-50 px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <template v-if="props.speakers && props.speakers.length">
                <div
                    v-for="group in groupedSpeakers()"
                    :key="group.type"
                    class="mt-14 first:mt-12"
                >
                    <!-- Group Label -->
                    <h3 class="mb-10 text-center text-lg font-bold italic text-slate-800">
                        {{ group.label }}
                        <span class="block mx-auto mt-2 w-16 h-0.5 rounded-full bg-primary/30"></span>
                    </h3>

                    <!-- Speaker Cards Grid -->
                    <div class="flex flex-wrap justify-center gap-x-10 gap-y-12 lg:gap-x-16">
                        <div
                            v-for="speaker in group.items"
                            :key="speaker.id"
                            class="fade-in flex w-48 flex-col items-center text-center"
                        >
                            <!-- Circular Photo with Ring -->
                            <div class="relative mb-5">
                                <div class="h-44 w-44 rounded-full border-[3px] border-sky-300 bg-white p-1.5 shadow-sm">
                                    <div class="h-full w-full overflow-hidden rounded-full bg-slate-100">
                                        <img
                                            v-if="speaker.photo"
                                            :src="'/storage/' + speaker.photo"
                                            :alt="speaker.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200"
                                        >
                                            <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Country Flag Badge -->
                                <div
                                    v-if="getFlagUrl(speaker)"
                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 flex items-center justify-center"
                                >
                                    <div class="rounded-full bg-white p-1 shadow-md border border-slate-100">
                                        <img
                                            :src="getFlagUrl(speaker)"
                                            :alt="speaker.country || speaker.country_code"
                                            class="h-6 w-6 rounded-full object-cover"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Speaker Info -->
                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ speaker.name }}
                            </h4>
                            <p v-if="speaker.institution" class="mt-1 text-xs font-medium text-slate-600 leading-snug">
                                {{ speaker.institution }}
                            </p>
                            <p v-if="speaker.country" class="mt-0.5 text-xs text-slate-400">
                                {{ speaker.country }}
                            </p>
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
    </section>
</template>
