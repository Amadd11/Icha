<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
    center: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div :class="[center ? 'text-center' : '', 'max-w-3xl mx-auto']">
        <h2 class="mb-4 text-3xl font-extrabold text-slate-900 md:text-4xl">
            {{ title }}
        </h2>
        <p v-if="description" class="text-base leading-relaxed text-slate-600">
            {{ description }}
        </p>
    </div>
</template>
