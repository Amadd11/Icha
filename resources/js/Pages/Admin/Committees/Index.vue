<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    committees: Array,
    conferences: Array,
});

function deleteCommittee(id) {
    if (confirm('Are you sure you want to delete this committee member?')) {
        router.delete(route('admin.committees.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Committee Management - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Conference Committee</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage steering, organizing, and scientific committee members.</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.committees.create')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add Committee Member
                    </Link>
                </div>
            </div>

            <!-- Minimalist Committees Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Committee Members List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.committees ? props.committees.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Member Name</th>
                                <th scope="col" class="px-5 py-3">Committee Role</th>
                                <th scope="col" class="px-5 py-3">Group</th>
                                <th scope="col" class="px-5 py-3">Institution</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.committees || props.committees.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No committee members found. Click "+ Add Committee Member" to create one.
                                </td>
                            </tr>
                            <tr v-for="c in props.committees" :key="c.id" class="hover:bg-slate-50/50 transition">
                                <!-- Name -->
                                <td class="px-5 py-3.5 font-bold text-xs text-slate-900">
                                    {{ c.name }}
                                </td>

                                <!-- Role -->
                                <td class="px-5 py-3.5 text-xs text-slate-700">
                                    {{ c.role }}
                                </td>

                                <!-- Group -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-block rounded-md px-2.5 py-0.5 text-[11px] font-bold uppercase border',
                                        c.group === 'steering' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                                        c.group === 'scientific' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                                        'bg-blue-50 text-blue-700 border-blue-200'
                                    ]">
                                        {{ c.group }}
                                    </span>
                                </td>

                                <!-- Institution -->
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ c.institution || '-' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.committees.edit', c.id)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteCommittee(c.id)"
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
