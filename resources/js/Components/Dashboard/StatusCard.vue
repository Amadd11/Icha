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
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 space-y-2.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-purple-200">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ title }}</span>
            <span
                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider border shadow-2xs"
                :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-200': variant === 'success' || status === 'verified' || status === 'paid' || status === 'completed' || status === 'accepted' || status === 'issued',
                    'bg-blue-50 text-blue-700 border-blue-200': status === 'waiting_verification' || status === 'waiting verification',
                    'bg-amber-50 text-amber-700 border-amber-200': variant === 'warning' || status === 'pending' || status === 'unpaid' || status === 'under_review',
                    'bg-red-50 text-red-700 border-red-200': status === 'rejected',
                    'bg-slate-100 text-slate-600 border-slate-200': variant === 'default' || status === 'not_submitted' || status === 'not_available' || status === 'locked'
                }"
            >
                <!-- Animated Pulse Dot on Action Required / Warning / Pending -->
                <span
                    v-if="variant === 'warning' || status === 'pending' || status === 'unpaid' || status === 'under_review' || status === 'waiting_verification' || status === 'waiting verification'"
                    class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-ping"
                ></span>
                <span>{{ String(status).replace(/_/g, ' ') }}</span>
            </span>
        </div>
        <p v-if="description" class="text-xs text-slate-600 font-medium leading-relaxed">{{ description }}</p>
    </div>
</template>
