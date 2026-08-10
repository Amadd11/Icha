<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    timelines: Array,
    conferences: Array,
});

function deleteTimeline(id) {
    if (confirm('Are you sure you want to delete this timeline item?')) {
        router.delete(route('admin.timelines.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Timeline Management - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Timeline & Schedule Management</h1>
                    <p class="text-xs text-slate-500">Manage key milestone dates and schedules for conference events.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.timelines.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        Add Timeline Item
                    </Link>
                </div>
            </div>

            <!-- Timelines Table (Sipanda Style) -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-amber-600 text-[18px]" style="font-variation-settings: 'FILL' 1">calendar_today</span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-sm tracking-wide">Conference Schedule Items</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-400">Total: {{ props.timelines.length }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Order</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Conference</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Period / Date</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Title & Description</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs">Status</th>
                                <th class="px-5 py-3.5 font-semibold text-gray-500 text-xs text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!props.timelines || props.timelines.length === 0">
                                <td colspan="6" class="px-5 py-10 text-center text-xs text-gray-400">No timeline items found. Click "Add Timeline Item" to create one.</td>
                            </tr>
                            <tr v-for="t in props.timelines" :key="t.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-slate-500">#{{ t.order }}</td>
                                <td class="px-5 py-4 font-bold text-xs text-purple-700">{{ t.conference?.title || 'Default' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-extrabold text-xs text-slate-900">{{ t.period || t.date || 'TBA' }}</p>
                                </td>
                                <td class="px-5 py-4 max-w-sm">
                                    <p class="font-bold text-gray-900 text-xs">{{ t.title }}</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5 whitespace-pre-line truncate max-w-xs">{{ t.description }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                        t.is_completed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                    ]">
                                        {{ t.is_completed ? 'Completed' : 'Upcoming' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right space-x-2">
                                    <Link
                                        :href="route('admin.timelines.edit', t.id)"
                                        class="px-3 py-1.5 rounded-xl bg-purple-50 text-primary hover:bg-primary hover:text-white font-bold text-xs transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteTimeline(t.id)"
                                        class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs transition-colors cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
