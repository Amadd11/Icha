<script setup>
import SectionHeading from "@/Components/LandingPage/SectionHeading.vue";

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
    <section id="timeline" class="bg-[#f5f7fa] px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div
                class="mb-14 mt-10 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="item in props.items"
                    :key="item.title"
                    class="fade-in rounded-xl border-l-4 bg-white p-6 shadow-sm"
                    :class="item.borderClass"
                >
                    <div
                        class="mb-1 text-xs font-bold uppercase tracking-[0.2em] text-[#7c3aed]"
                    >
                        {{ item.period }}
                    </div>
                    <h4 class="mb-2 text-[0.95rem] font-bold text-[#2d0f4f]">
                        {{ item.title }}
                    </h4>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <li v-for="point in item.points" :key="point">
                            • {{ point }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
