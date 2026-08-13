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
    <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
            <span
                class="rounded-md px-2.5 py-0.5 text-[10px] font-bold uppercase border"
                :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-200': variant === 'success' || status === 'verified' || status === 'completed' || status === 'accepted' || status === 'issued',
                    'bg-amber-50 text-amber-700 border-amber-200': variant === 'warning' || status === 'pending' || status === 'unpaid' || status === 'under_review',
                    'bg-slate-100 text-slate-600 border-slate-200': variant === 'default' || status === 'not_submitted' || status === 'not_available' || status === 'locked'
                }"
            >
                {{ status.replace('_', ' ').toUpperCase() }}
            </span>
        </div>
        <p v-if="description" class="text-xs text-slate-600 font-medium leading-relaxed">{{ description }}</p>
    </div>
</template>
