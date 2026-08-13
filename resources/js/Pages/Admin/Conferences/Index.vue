<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    conferences: Array,
});

const statusColor = (status) => ({
    draft:    'bg-slate-100 text-slate-600 border-slate-200',
    active:   'bg-emerald-50 text-emerald-700 border-emerald-200',
    archived: 'bg-amber-50 text-amber-700 border-amber-200',
}[status] ?? 'bg-slate-100 text-slate-600 border-slate-200');

function destroy(id) {
    if (confirm('Are you sure you want to delete this conference? This action cannot be undone.')) {
        router.delete(route('admin.conferences.destroy', id));
    }
}

function formatStorageUrl(path) {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>

<template>
    <Head title="Conferences Management - Admin" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Conference Editions</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage annual conference editions, themes, venue settings, and active portals.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.conferences.create')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add New Conference
                    </Link>
                </div>
            </div>

            <!-- Minimalist Table Card Container -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Annual Conferences List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.conferences ? props.conferences.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Conference Details</th>
                                <th scope="col" class="px-5 py-3">Schedule & Location</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.conferences || props.conferences.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No conferences found. Click "+ Add New Conference" to create one.
                                </td>
                            </tr>
                            <tr
                                v-for="c in props.conferences"
                                :key="c.id"
                                class="hover:bg-slate-50/50 transition"
                            >
                                <!-- Title & Banner -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-14 shrink-0 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center p-0.5">
                                            <img 
                                                v-if="c.hero_image || c.logo" 
                                                :src="formatStorageUrl(c.hero_image || c.logo)" 
                                                alt="Banner" 
                                                class="h-full w-full object-cover rounded" 
                                            />
                                            <span v-else class="text-[10px] font-bold text-slate-400">ICHA</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-slate-900 text-xs truncate max-w-sm">{{ c.title }}</p>
                                                <span v-if="c.is_active" class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                                    Active Portal
                                                </span>
                                            </div>
                                            <p v-if="c.theme" class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">
                                                “{{ c.theme }}”
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Schedule & Location -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-800 text-xs">
                                        {{ formatDate(c.start_date) }} - {{ formatDate(c.end_date) }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ c.venue || 'Venue TBD' }}, {{ c.city || 'Surabaya' }}
                                    </p>
                                </td>

                                <!-- Status Pill -->
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase', statusColor(c.status)]">
                                        {{ c.status }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.conferences.edit', c.id)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="destroy(c.id)"
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
