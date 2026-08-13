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
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section id="tracks" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <!-- Grid of Tracks (If items exist) -->
            <div v-if="props.items && props.items.length > 0" class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(item, index) in props.items"
                    :key="item.title"
                    :class="[
                        index % 2 === 0
                            ? 'bg-gradient-to-br from-sidebar to-primary text-white shadow-purple-500/20'
                            : 'bg-gradient-to-br from-gold to-amber-400 text-slate-950 shadow-amber-500/20',
                        'fade-in flex flex-col justify-between rounded-2xl p-6 shadow-md transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl'
                    ]"
                >
                    <div>
                        <div v-if="item.badge" class="mb-4">
                            <span
                                :class="[
                                    index % 2 === 0 ? 'bg-white/20 text-white' : 'bg-amber-950/15 text-slate-950 font-bold',
                                    'inline-block rounded-full px-3 py-1 text-xs font-semibold backdrop-blur-md'
                                ]"
                            >
                                {{ item.badge }}
                            </span>
                        </div>
                        <h3
                            :class="[
                                index % 2 === 0 ? 'text-white' : 'text-slate-950',
                                'mb-3 text-lg font-bold'
                            ]"
                        >
                            {{ item.title }}
                        </h3>
                        <p
                            :class="[
                                index % 2 === 0 ? 'text-white/80' : 'text-slate-800',
                                'text-sm leading-relaxed'
                            ]"
                        >
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alert Box Fallback (If no tracks exist in database) -->
            <div v-else class="mt-12 rounded-2xl border border-amber-200 bg-amber-50/70 p-6 text-center text-amber-900 max-w-2xl mx-auto shadow-xs space-y-2">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-lg font-bold">
                    ℹ️
                </div>
                <h4 class="text-sm font-bold text-slate-900">Scientific Tracks Information</h4>
                <p class="text-xs text-slate-600">Scientific tracks for this conference edition will be announced soon by the committee.</p>
            </div>
        </div>
    </section>
</template>
