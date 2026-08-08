<script setup>
defineProps({
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
    <div class="mx-auto max-w-3xl">
        <p
            v-if="eyebrow"
            class="mb-2 text-center text-xs font-bold uppercase tracking-[0.3em] text-[#7c3aed]"
        >
            {{ eyebrow }}
        </p>
        <h2
            class="mb-4 text-center font-serif text-3xl text-[#2d0f4f] md:text-4xl"
        >
            {{ title }}
        </h2>
        <p v-if="description" class="text-center text-[#6b7280]">
            {{ description }}
        </p>
    </div>
</template>
