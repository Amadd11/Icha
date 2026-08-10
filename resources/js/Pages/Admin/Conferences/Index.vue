<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    conferences: Array,
});

const statusColor = (status) => ({
    draft:    'bg-slate-100 text-slate-600',
    active:   'bg-green-100 text-green-700',
    archived: 'bg-amber-100 text-amber-700',
}[status] ?? 'bg-slate-100 text-slate-600');

function destroy(id) {
    if (confirm('Delete this conference?')) {
        router.delete(route('admin.conferences.destroy', id));
    }
}
</script>

<template>
    <Head title="Conferences - Admin" />
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Conferences</h1>
                <p class="text-xs text-slate-500">{{ conferences.length }} conference(s) found</p>
            </div>
            <Link
                :href="route('admin.conferences.create')"
                class="inline-flex items-center gap-2 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition px-4 py-2"
            >
                + New Conference
            </Link>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Title</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Date</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-500">Active</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="conferences.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">No conferences yet.</td>
                    </tr>
                    <tr
                        v-for="c in conferences"
                        :key="c.id"
                        class="border-b border-slate-50 transition hover:bg-slate-50/50 last:border-0"
                    >
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-800">{{ c.title }}</p>
                            <p class="text-xs text-slate-400">{{ c.city }}, {{ c.country }}</p>
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ c.start_date ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-2.5 py-1 text-xs font-bold capitalize', statusColor(c.status)]">
                                {{ c.status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="c.is_active ? 'text-green-600' : 'text-slate-400'" class="text-xs font-semibold">
                                {{ c.is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link
                                :href="route('admin.conferences.show', c.id)"
                                class="mr-2 text-xs font-semibold text-primary hover:underline"
                            >View</Link>
                            <Link
                                :href="route('admin.conferences.edit', c.id)"
                                class="mr-2 text-xs font-semibold text-slate-600 hover:underline"
                            >Edit</Link>
                            <button
                                @click="destroy(c.id)"
                                class="text-xs font-semibold text-red-500 hover:underline"
                            >Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
