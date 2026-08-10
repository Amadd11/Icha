<script setup>
defineProps({
    stages: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-6">Conference Journey Progress</h3>

        <!-- Horizontal Step Tracker for Desktop, Stacked for Mobile -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div
                v-for="(stage, index) in stages"
                :key="stage.key"
                class="relative flex flex-col items-center rounded-xl border p-4 text-center transition-all duration-200"
                :class="{
                    'border-emerald-500 bg-emerald-50/40 text-emerald-950': stage.status === 'completed',
                    'border-purple-600 bg-purple-50 text-purple-950 ring-2 ring-purple-600/20': stage.status === 'current',
                    'border-slate-200 bg-slate-50/50 text-slate-400': stage.status === 'pending'
                }"
            >
                <div
                    class="mb-2 flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold shadow-xs"
                    :class="{
                        'bg-emerald-600 text-white': stage.status === 'completed',
                        'bg-purple-700 text-gold': stage.status === 'current',
                        'bg-slate-200 text-slate-600': stage.status === 'pending'
                    }"
                >
                    <svg v-if="stage.status === 'completed'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else>{{ index + 1 }}</span>
                </div>

                <span class="text-xs font-bold text-slate-900">{{ stage.label }}</span>
                <span class="mt-1 text-[11px] font-medium opacity-80">{{ stage.desc }}</span>
            </div>
        </div>
    </div>
</template>
