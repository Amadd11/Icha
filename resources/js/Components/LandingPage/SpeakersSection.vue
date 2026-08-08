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
});
</script>

<template>
    <section id="speakers" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div
                class="fade-in mx-auto mt-10 max-w-3xl rounded-3xl border-2 border-dashed border-slate-300 bg-[#f5f7fa] p-16 text-center"
            >
                <div class="mb-4 text-5xl">🎤</div>
                <strong class="mb-2 block text-xl text-[#2d0f4f]"
                    >Speakers to be announced</strong
                >
                <p class="text-sm leading-relaxed text-[#6b7280]">
                    We are confirming keynote and invited speakers. The full
                    speaker lineup will be published in the second announcement.
                </p>
            </div>
        </div>
    </section>
</template>
