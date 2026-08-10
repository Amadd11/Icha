<script setup>
defineProps({
    conference: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Conference Readiness</h3>
            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">Phase 1 Active</span>
        </div>

        <p class="text-sm font-bold text-slate-800">{{ conference?.title || 'Active Conference' }}</p>
        <p class="text-xs text-slate-500 mb-4">{{ conference?.theme || 'Healthcare Administration for a Sustainable Future' }}</p>

        <!-- Lightweight CSS Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2 overflow-hidden">
            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 40%"></div>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Setup & Call for Abstract</span>
            <span class="font-bold text-indigo-600">40% Complete</span>
        </div>
    </div>
</template>
