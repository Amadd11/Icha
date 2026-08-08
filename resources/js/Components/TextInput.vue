<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm transition-colors focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        v-model="model"
        ref="input"
    />
</template>
