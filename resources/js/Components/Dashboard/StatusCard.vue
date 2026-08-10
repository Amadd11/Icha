<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'default', // success, warning, pending, default
    },
});
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ title }}</span>
            <span
                class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                :class="{
                    'bg-emerald-100 text-emerald-800': variant === 'success' || status === 'verified' || status === 'completed',
                    'bg-amber-100 text-amber-800': variant === 'warning' || status === 'pending' || status === 'unpaid',
                    'bg-slate-100 text-slate-600': variant === 'default' || status === 'not_submitted' || status === 'not_available'
                }"
            >
                {{ status.replace('_', ' ').toUpperCase() }}
            </span>
        </div>
        <p v-if="description" class="mt-2 text-sm text-slate-600">{{ description }}</p>
    </div>
</template>
