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

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="item in props.items"
                    :key="item.title"
                    :class="[item.cardClass, 'fade-in flex flex-col justify-between rounded-2xl p-6 shadow-md']"
                >
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">
                                {{ item.badge }}
                            </span>
                            <span class="text-2xl">{{ item.icon }}</span>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-white">
                            {{ item.title }}
                        </h3>
                        <p class="text-sm leading-relaxed text-white/80">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
