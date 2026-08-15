<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    from: {
        type: Number,
        default: null,
    },
    to: {
        type: Number,
        default: null,
    },
    total: {
        type: Number,
        default: null,
    },
});
</script>

<template>
    <div
        v-if="props.links && props.links.length > 3"
        class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-200 bg-white"
    >
        <!-- Results Counter -->
        <p v-if="props.total !== null" class="text-xs text-slate-500">
            Showing
            <span class="font-bold text-slate-700">{{ props.from || 0 }}</span>
            to
            <span class="font-bold text-slate-700">{{ props.to || 0 }}</span>
            of
            <span class="font-bold text-slate-900">{{ props.total }}</span>
            results
        </p>
        <div v-else></div>

        <!-- Navigation Links -->
        <div class="flex flex-wrap items-center gap-1">
            <Link
                v-for="(link, index) in props.links"
                :key="index"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                    'px-3 py-1.5 text-xs rounded-lg transition font-medium',
                    link.active
                        ? 'bg-gold hover:bg-amber-400 text-slate-950 font-bold shadow-xs'
                        : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50',
                    !link.url ? 'opacity-40 pointer-events-none cursor-not-allowed' : 'cursor-pointer'
                ]"
                preserve-scroll
            />
        </div>
    </div>
</template>
