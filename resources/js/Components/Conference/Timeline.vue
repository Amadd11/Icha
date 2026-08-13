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
                            <!-- Timeline Node Badge (Larger Node with Gold Border) -->
                            <div class="flex items-center justify-center mb-8">
                                <div class="w-13 h-13 rounded-2xl bg-linear-to-br from-primary-dark via-primary to-purple-900 text-gold font-black text-base flex items-center justify-center shadow-lg border-2 border-gold group-hover:scale-115 group-hover:border-white transition-all duration-300">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </div>
                            </div>

                            <!-- Card Content Below Node (Colored Cards: Purple & Gold) -->
                            <div 
                                class="rounded-4xl p-7 sm:p-9 shadow-xl border-2 transition-all duration-300 hover:-translate-y-2 flex-1 flex flex-col justify-between relative overflow-hidden"
                                :class="[
                                    index % 2 === 0 
                                        ? 'bg-linear-to-br from-slate-900 via-purple-950 to-primary-dark text-white border-purple-800/60 hover:border-gold shadow-purple-900/20' 
                                        : 'bg-linear-to-br from-amber-400 via-amber-300 to-gold text-slate-950 border-amber-300 hover:border-purple-900 shadow-amber-500/20'
                                ]"
                            >
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <span 
                                            class="px-4 py-1.5 rounded-full text-xs font-black tracking-wider uppercase shadow-xs"
                                            :class="[
                                                index % 2 === 0 
                                                    ? 'bg-gold/20 text-gold border border-gold/40' 
                                                    : 'bg-purple-950 text-gold border border-purple-900'
                                            ]"
                                        >
                                            {{ item.period }}
                                        </span>
                                    </div>
                                    <h3 
                                        class="text-lg sm:text-xl font-black mb-4 tracking-tight"
                                        :class="index % 2 === 0 ? 'text-white' : 'text-slate-950'"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <ul class="space-y-3 text-xs sm:text-sm font-medium" :class="index % 2 === 0 ? 'text-purple-100' : 'text-slate-900'">
                                        <li v-for="point in item.points" :key="point" class="flex items-start gap-3">
                                            <span 
                                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full shadow-xs"
                                                :class="index % 2 === 0 ? 'bg-gold' : 'bg-purple-900'"
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
