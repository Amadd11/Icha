<script setup>
defineProps({
    deadlines: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Upcoming Deadlines</h3>

        <div v-if="deadlines && deadlines.length" class="space-y-3">
            <div v-for="(item, idx) in deadlines" :key="idx" class="flex items-center justify-between border-l-2 border-indigo-500 pl-3 py-1">
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ item.label }}</p>
                    <p class="text-xs text-slate-500">{{ item.date }}</p>
                </div>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                    {{ item.status }}
                </span>
            </div>
        </div>

        <div v-else class="py-4 text-center text-sm text-slate-400">
            No upcoming deadlines scheduled.
        </div>
    </div>
</template>
