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

            <!-- Grid of Tracks (Alternating White and Royal Indigo Cards) -->
            <div v-if="props.items && props.items.length > 0" class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(item, index) in props.items"
                    :key="item.title"
                    class="fade-in rounded-3xl p-7 border-2 shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 flex flex-col justify-between group relative overflow-hidden"
                    :class="[
                        index % 2 === 0
                            ? 'bg-white text-slate-900 border-slate-100 hover:border-primary/40'
                            : 'bg-primary text-white border-primary-dark shadow-purple-950/20 hover:border-gold'
                    ]"
                >
                    <div class="relative z-10">
                        <div class="flex items-center justify-start mb-5">
                            <div 
                                class="w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs border-2"
                                :class="index % 2 === 0 ? 'bg-primary text-gold border-gold/40' : 'bg-white text-primary border-white'"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </div>
                        </div>

                        <h3 
                            class="mb-3 text-lg font-black tracking-tight transition-colors"
                            :class="index % 2 === 0 ? 'text-slate-900 group-hover:text-primary' : 'text-white'"
                        >
                            {{ item.title }}
                        </h3>

                        <p 
                            class="text-xs sm:text-sm leading-relaxed font-medium"
                            :class="index % 2 === 0 ? 'text-slate-600' : 'text-indigo-100'"
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
