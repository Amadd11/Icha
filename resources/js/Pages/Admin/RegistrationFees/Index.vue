<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    registrationFees: Array,
    conferences: Array,
    selectedConfId: Number,
});

function deleteFee(id) {
    if (confirm('Are you sure you want to delete this registration fee package?')) {
        router.delete(route('admin.registration-fees.destroy', id), {
            preserveScroll: true,
        });
    }
}

function formatPrice(val) {
    if (!val) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Registration Fees - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Registration Fees & Packages</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage registration fees, packages, and attendance modes (Offline / Online).</p>
                </div>

                <div>
                    <Link
                        :href="route('admin.registration-fees.create')"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add Registration Fee
                    </Link>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Registration Fees List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.registrationFees ? props.registrationFees.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Package Name</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Attendance Mode</th>
                                <th scope="col" class="px-5 py-3">Registration Fee (Price)</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="item in props.registrationFees"
                                :key="item.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="px-5 py-4 font-bold text-slate-900">
                                    {{ item.name }}
                                </td>

                                <td class="px-5 py-4 text-xs font-semibold text-slate-600">
                                    {{ item.conference?.title || 'Default Conference' }}
                                </td>

                                <td class="px-5 py-4 text-xs font-semibold">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                                            item.mode === 'offline'
                                                ? 'bg-purple-50 text-purple-700 border-purple-200'
                                                : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        ]"
                                    >
                                        {{ item.mode }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm font-black text-slate-900">
                                    {{ formatPrice(item.price) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('admin.registration-fees.edit', item.id)"
                                            class="text-xs font-bold text-primary hover:text-purple-900 transition px-2 py-1 rounded-lg hover:bg-purple-50"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteFee(item.id)"
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 transition px-2 py-1 rounded-lg hover:bg-rose-50 cursor-pointer"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!props.registrationFees || props.registrationFees.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-slate-400 text-xs">
                                    No registration fees added yet. Click "+ Add Registration Fee" above to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
