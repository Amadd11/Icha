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
    <section id="timeline" class="bg-slate-50 px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="item in props.items"
                    :key="item.title"
                    :class="['fade-in rounded-2xl border-t-4 bg-white p-6 shadow-sm', item.borderClass]"
                >
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-primary">
                        {{ item.period }}
                    </span>
                    <h3 class="mb-4 text-lg font-bold text-slate-900">
                        {{ item.title }}
                    </h3>
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li v-for="point in item.points" :key="point" class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>
                            <span>{{ point }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
