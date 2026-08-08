<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
    },
    columnsClass: {
        type: String,
        default: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-4",
    },
});
</script>

<template>
    <div :class="['grid gap-6', columnsClass]">
        <div
            v-for="item in items"
            :key="item.title"
            class="fade-in group relative overflow-hidden rounded-2xl p-8 text-white transition-all hover:-translate-y-2 hover:shadow-2xl"
            :class="item.cardClass || ''"
        >
            <div
                class="mb-3 text-[0.7rem] font-bold uppercase tracking-[0.3em] text-white/65"
            >
                {{ item.badge }}
            </div>
            <div class="mb-4 text-4xl">{{ item.icon }}</div>
            <h3 class="mb-3 text-lg font-bold leading-snug">
                {{ item.title }}
            </h3>
            <p class="text-sm leading-relaxed text-white/80">
                {{ item.description }}
            </p>
            <div
                class="absolute -bottom-8 -right-8 h-24 w-24 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-150"
            ></div>
        </div>
    </div>
</template>
