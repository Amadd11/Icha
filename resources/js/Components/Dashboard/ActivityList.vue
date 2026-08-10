<script setup>
defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Recent Activity</h3>

        <div v-if="items && items.length" class="divide-y divide-slate-100">
            <div v-for="item in items" :key="item.id" class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-600 text-xs">
                        {{ item.user?.name ? item.user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ item.user?.name || 'Participant' }}</p>
                        <p class="text-xs text-slate-500">{{ item.registration_type?.name || 'Registration' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span
                        class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="item.payment?.status === 'verified' ? 'bg-emerald-50 text-emerald-700' : (item.payment?.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600')"
                    >
                        {{ item.payment?.status ? item.payment.status.toUpperCase() : 'UNPAID' }}
                    </span>
                </div>
            </div>
        </div>

        <div v-else class="py-8 text-center text-sm text-slate-400">
            No recent activity recorded.
        </div>
    </div>
</template>
