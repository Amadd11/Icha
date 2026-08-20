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
    <section id="timeline" class="bg-slate-50/80 text-slate-800 px-5 py-20 md:px-10 md:py-28 relative overflow-hidden border-y border-slate-200/60">
        <div class="mx-auto max-w-7xl relative z-10">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <!-- Horizontal Continuous Timeline -->
            <div class="mt-20 relative overflow-x-auto pb-8 scrollbar-thin scrollbar-thumb-primary/30">
                <div class="min-w-237.5elative pt-8">
                    <!-- Horizontal Connecting Line -->
                    <div class="absolute top-12 left-10 right-10 h-2 bg-linear-to-r from-primary via-gold to-primary rounded-full opacity-70 shadow-sm"></div>

                    <!-- Steps Grid -->
                    <div class="grid grid-cols-3 gap-10 relative z-10">
                        <div
                            v-for="(item, index) in props.items"
                            :key="item.title || index"
                            class="relative flex flex-col group"
                        >
                            <!-- Timeline Node Badge -->
                            <div class="flex items-center justify-center mb-8">
                                <div class="w-13 h-13 rounded-2xl bg-primary text-white font-black text-base flex items-center justify-center shadow-lg border-2 border-gold group-hover:scale-115 transition-all duration-300">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </div>
                            </div>

                            <!-- Card Content Below Node (Official ICHA 2026 Style) -->
                            <div 
                                class="rounded-4xl p-7 sm:p-9 shadow-xl border-2 transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col justify-between relative overflow-hidden"
                                :class="[
                                    index % 2 === 0 
                                        ? 'bg-primary text-white border-primary-dark shadow-primary/20' 
                                        : 'bg-white text-slate-900 border-primary/20 hover:border-primary shadow-slate-200/60'
                                ]"
                            >
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <span 
                                            class="px-4 py-1.5 rounded-full text-xs font-black tracking-wider uppercase shadow-xs"
                                            :class="[
                                                index % 2 === 0 
                                                    ? 'bg-gold/20 text-white border border-gold/40' 
                                                    : 'bg-primary/10 text-primary border border-primary/20'
                                            ]"
                                        >
                                            {{ item.period }}
                                        </span>
                                    </div>
                                    <h3 
                                        class="text-lg sm:text-xl font-black mb-4 tracking-tight"
                                        :class="index % 2 === 0 ? 'text-white' : 'text-primary'"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <ul class="space-y-3 text-xs sm:text-sm font-medium" :class="index % 2 === 0 ? 'text-indigo-100' : 'text-slate-600'">
                                        <li v-for="point in item.points" :key="point" class="flex items-start gap-3">
                                            <span 
                                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full shadow-xs"
                                                :class="index % 2 === 0 ? 'bg-gold' : 'bg-gold'"
                                            ></span>
                                            <span>{{ point }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
