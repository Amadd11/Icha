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
    <section id="sponsors" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div
                class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6"
            >
                <div
                    v-for="sponsor in props.items"
                    :key="sponsor.title"
                    class="fade-in rounded-xl border-2 p-5 text-center"
                    :class="sponsor.className"
                >
                    <h4
                        class="mb-1.5 text-[0.8rem] font-bold uppercase tracking-[0.2em]"
                        :class="sponsor.className"
                    >
                        {{ sponsor.title }}
                    </h4>
                    <div class="text-2xl font-extrabold text-[#2d0f4f]">
                        {{ sponsor.value }}
                    </div>
                    <div class="mt-0.5 text-[0.72rem] text-[#6b7280]">
                        {{ sponsor.note }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
