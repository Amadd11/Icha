<script setup>
import FeatureGrid from "@/Components/LandingPage/FeatureGrid.vue";
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
    <section id="tracks" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />
            <div class="mt-10">
                <FeatureGrid
                    :items="props.items"
                    columns-class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-4"
                />
            </div>
        </div>
    </section>
</template>
