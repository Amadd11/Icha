<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    speakers: Array,
});

const typeColor = (type) => ({
    keynote: 'bg-purple-50 text-purple-700 border-purple-200',
    plenary: 'bg-amber-50 text-amber-700 border-amber-200',
    invited: 'bg-indigo-50 text-indigo-700 border-indigo-200',
}[type] ?? 'bg-slate-100 text-slate-600 border-slate-200');

function destroy(id) {
    if (confirm('Delete this speaker?')) {
        router.delete(route('admin.speakers.destroy', id));
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
    <Head title="Speakers - Admin" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Keynote & Invited Speakers</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage conference keynote, plenary, and invited speakers lineup.</p>
                </div>
                <Link
                    :href="route('admin.speakers.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs"
                >
                    + Add New Speaker
                </Link>
            </div>

            <!-- Minimalist Speakers Table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Speakers Lineup</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.speakers ? props.speakers.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Speaker</th>
                                <th scope="col" class="px-5 py-3">Category</th>
                                <th scope="col" class="px-5 py-3">Institution & Country</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Order</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.speakers || props.speakers.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No speakers added yet. Click "+ Add New Speaker" to add one.
                                </td>
                            </tr>
                            <tr v-for="s in props.speakers" :key="s.id" class="hover:bg-slate-50/50 transition">
                                <!-- Speaker Name & Avatar -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center">
                                            <img v-if="s.photo" :src="formatStorageUrl(s.photo)" :alt="s.name" class="h-full w-full object-cover" />
                                            <span v-else class="text-xs font-bold text-slate-400">
                                                {{ s.name ? s.name.charAt(0) : 'S' }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ s.name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ s.title || 'Keynote Speaker' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-block rounded-md px-2.5 py-0.5 text-[11px] font-bold uppercase border', typeColor(s.type)]">
                                        {{ s.type }}
                                    </span>
                                </td>

                                <!-- Institution & Country -->
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800 text-xs">{{ s.institution || 'Independent' }}</p>
                                    <div v-if="s.country" class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-0.5">
                                        <img
                                            v-if="s.country_code"
                                            :src="`https://flagcdn.com/w40/${s.country_code.toLowerCase()}.png`"
                                            :alt="s.country"
                                            class="h-3 w-4 rounded-xs object-cover"
                                        />
                                        <span>{{ s.country }}</span>
                                    </div>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs text-slate-600 font-medium max-w-xs truncate" :title="s.conference?.title">
                                    {{ s.conference?.title }}
                                </td>

                                <!-- Order -->
                                <td class="px-5 py-3.5 text-xs font-bold text-slate-500">
                                    #{{ s.order || 0 }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.speakers.edit', s.id)"
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
