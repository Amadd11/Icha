<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    speakers: Array,
});

const typeColor = (type) => ({
    keynote: 'bg-primary/10 text-primary',
    plenary: 'bg-gold/20 text-gold-dark',
    invited: 'bg-slate-100 text-slate-600',
}[type] ?? 'bg-slate-100 text-slate-600');

function destroy(id) {
    if (confirm('Delete this speaker?')) {
        router.delete(route('admin.speakers.destroy', id));
    }
}
</script>

<template>
    <Head title="Speakers - Admin" />
    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Speakers</h1>
                <p class="text-xs text-slate-500">{{ speakers.length }} speaker(s) found</p>
            </div>
            <Link
                :href="route('admin.speakers.create')"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700"
            >
                + New Speaker
            </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div v-if="speakers.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 p-10 text-center text-slate-400">
                No speakers yet.
            </div>
            <div
                v-for="s in speakers"
                :key="s.id"
                class="flex gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md"
            >
                <!-- Avatar -->
                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-slate-100">
                    <img v-if="s.photo" :src="'/storage/' + s.photo" :alt="s.name" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full items-center justify-center text-xl font-bold text-slate-400">
                        {{ s.name.charAt(0) }}
                    </div>
                </div>
                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-slate-800">{{ s.name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ s.title }} · {{ s.institution }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold uppercase', typeColor(s.type)]">{{ s.type }}</span>
                        <span class="text-[10px] text-slate-400">{{ s.conference?.title }}</span>
                    </div>
                    <div class="mt-3 flex gap-3">
                        <Link :href="route('admin.speakers.edit', s.id)" class="text-xs font-semibold text-slate-500 hover:text-primary">Edit</Link>
                        <button @click="destroy(s.id)" class="text-xs font-semibold text-red-400 hover:text-red-600">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
