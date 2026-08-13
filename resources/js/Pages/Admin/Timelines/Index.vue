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
    <Head title="Timeline & Schedule - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Timeline & Important Dates</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage key milestone dates and schedules for conference events.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.timelines.create')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add Timeline Item
                    </Link>
                </div>
            </div>

            <!-- Minimalist Timelines Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Conference Schedule</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.timelines ? props.timelines.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Order</th>
                                <th scope="col" class="px-5 py-3">Event Title</th>
                                <th scope="col" class="px-5 py-3">Period / Date</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.timelines || props.timelines.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No timeline items found. Click "+ Add Timeline Item" to create one.
                                </td>
                            </tr>
                            <tr v-for="t in props.timelines" :key="t.id" class="hover:bg-slate-50/50 transition">
                                <!-- Order -->
                                <td class="px-5 py-3.5 font-bold text-xs text-slate-400">
                                    #{{ t.order }}
                                </td>

                                <!-- Title & Description -->
                                <td class="px-5 py-3.5 max-w-xs">
                                    <p class="font-bold text-slate-900 text-xs">{{ t.title }}</p>
                                    <p v-if="t.description" class="text-[11px] text-slate-400 mt-0.5 truncate">{{ t.description }}</p>
                                </td>

                                <!-- Period / Date -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-xs text-purple-900">{{ t.period || t.date || 'TBA' }}</span>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                                    {{ t.conference?.title || 'Default Conference' }}
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase border',
                                        t.is_completed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        {{ t.is_completed ? 'Completed' : 'Upcoming' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.timelines.edit', t.id)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteTimeline(t.id)"
                                            class="px-2.5 py-1 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-bold text-xs transition cursor-pointer"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
