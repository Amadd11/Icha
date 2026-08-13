<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    sponsors: Array,
    conferences: Array,
});

const tierColor = (tier) => ({
    title:    'bg-purple-50 text-purple-700 border-purple-200',
    platinum: 'bg-slate-100 text-slate-700 border-slate-300',
    gold:     'bg-amber-50 text-amber-700 border-amber-200',
    silver:   'bg-slate-50 text-slate-600 border-slate-200',
    bronze:   'bg-orange-50 text-orange-700 border-orange-200',
    exhibitor:'bg-indigo-50 text-indigo-700 border-indigo-200',
}[tier] ?? 'bg-slate-100 text-slate-600 border-slate-200');

function destroy(id) {
    if (confirm('Delete this sponsor?')) {
        router.delete(route('admin.sponsors.destroy', id));
    }
}

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}
</script>

<template>
    <Head title="Sponsors - Admin" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Sponsors & Co-Hosts</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage event partners, corporate sponsors, and co-hosts.</p>
                </div>
                <Link
                    :href="route('admin.sponsors.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                >
                    + Add New Sponsor
                </Link>
            </div>

            <!-- Minimalist Sponsors Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Sponsors List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.sponsors ? props.sponsors.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Sponsor / Logo</th>
                                <th scope="col" class="px-5 py-3">Tier</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.sponsors || props.sponsors.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No sponsors found. Click "+ Add New Sponsor" to add one.
                                </td>
                            </tr>
                            <tr v-for="s in props.sponsors" :key="s.id" class="hover:bg-slate-50/50 transition">
                                <!-- Logo & Name -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center p-1">
                                            <img v-if="s.logo" :src="formatStorageUrl(s.logo)" :alt="s.name" class="max-h-full max-w-full object-contain" />
                                            <span v-else class="text-[10px] text-slate-300 font-bold">LOGO</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ s.name }}</p>
                                            <a v-if="s.website" :href="s.website" target="_blank" class="text-[11px] text-purple-700 hover:underline font-medium">{{ s.website }}</a>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tier -->
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-block rounded-md px-2.5 py-0.5 text-[11px] font-bold uppercase border', tierColor(s.tier)]">
                                        {{ s.tier }}
                                    </span>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                                    {{ s.conference?.title || 'Default Conference' }}
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold capitalize border',
                                        s.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'
                                    ]">
                                        {{ s.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.sponsors.edit', s.id)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="destroy(s.id)"
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
