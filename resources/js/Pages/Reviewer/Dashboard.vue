<script setup>
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

onMounted(() => {
    router.replace(route('reviewer.abstracts.index'));
});
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 text-slate-400 text-xs">
        Memuat Reviewer Portal...
    </div>
</template>
