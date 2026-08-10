<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    sponsors: Array,
    conferences: Array,
});

const tierColor = (tier) => ({
    title:    'bg-sidebar text-white',
    platinum: 'bg-slate-200 text-slate-700',
    gold:     'bg-gold/30 text-gold-dark',
    silver:   'bg-slate-100 text-slate-500',
    bronze:   'bg-amber-100 text-amber-700',
    exhibitor:'bg-primary/10 text-primary',
}[tier] ?? 'bg-slate-100 text-slate-600');

function destroy(id) {
    if (confirm('Delete this sponsor?')) {
        router.delete(route('admin.sponsors.destroy', id));
    }
}
</script>

<template>
    <Head title="Sponsors - Admin" />
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Sponsors</h1>
                <p class="text-xs text-slate-500">{{ sponsors.length }} sponsor(s) found</p>
            </div>
            <Link
                :href="route('admin.sponsors.create')"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700"
            >
                + New Sponsor
            </Link>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Sponsor</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Tier</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Conference</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Active</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="sponsors.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">No sponsors yet.</td>
                    </tr>
                    <tr
                        v-for="s in sponsors"
                        :key="s.id"
                        class="border-b border-slate-50 transition hover:bg-slate-50/50 last:border-0"
                    >
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-14 overflow-hidden rounded-lg border border-slate-100 bg-slate-50 flex items-center justify-center">
                                    <img v-if="s.logo" :src="'/storage/' + s.logo" :alt="s.name" class="max-h-8 max-w-full object-contain" />
                                    <span v-else class="text-xs text-slate-300">No logo</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ s.name }}</p>
                                    <a v-if="s.website" :href="s.website" target="_blank" class="text-xs text-primary hover:underline">{{ s.website }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-2.5 py-1 text-xs font-bold capitalize', tierColor(s.tier)]">{{ s.tier }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500">{{ s.conference?.title ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span :class="s.is_active ? 'text-green-600' : 'text-slate-400'" class="text-xs font-semibold">
                                {{ s.is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link :href="route('admin.sponsors.edit', s.id)" class="mr-2 text-xs font-semibold text-slate-600 hover:underline">Edit</Link>
                            <button @click="destroy(s.id)" class="text-xs font-semibold text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
