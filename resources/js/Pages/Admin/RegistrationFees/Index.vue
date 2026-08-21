<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';
import { formatRupiah } from '@/Composables/useFormatRupiah';

const props = defineProps({
    registrationFees: Array,
    conferences: Array,
    selectedConfId: Number,
});

const isModalOpen = ref(false);
const editingFee = ref(null);

const form = useForm({
    conference_id: '',
    name: '',
    mode: 'offline',
    price: 0,
});

const formattedPrice = computed({
    get() {
        if (form.price === null || form.price === undefined || form.price === '' || form.price === 0) {
            return form.price === 0 ? '0' : '';
        }
        return Number(form.price).toLocaleString('id-ID');
    },
    set(val) {
        const clean = String(val).replace(/\D/g, '');
        form.price = clean ? parseInt(clean, 10) : 0;
    }
});

function openCreateModal() {
    editingFee.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.selectedConfId || props.conferences?.[0]?.id || '';
    form.mode = 'offline';
    form.price = 0;
    isModalOpen.value = true;
}

function openEditModal(item) {
    editingFee.value = item;
    form.clearErrors();
    form.conference_id = item.conference_id || (props.conferences?.[0]?.id || '');
    form.name = item.name || '';
    form.mode = item.mode || 'offline';
    form.price = item.price ?? 0;
    isModalOpen.value = true;
}

function submit() {
    if (editingFee.value) {
        form.put(route('admin.registration-fees.update', editingFee.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.registration-fees.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: feeToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

function deleteFee(item) {
    openDeleteModal({
        item: item,
        title: 'Delete Fee Package',
        message: `Are you sure you want to delete "${item.name}" package? This will soft delete the tier.`,
        url: route('admin.registration-fees.destroy', item.id),
    });
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
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add Registration Fee
                    </button>
                </div>
            </div>

            <!-- Minimalist Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Fee Packages List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.registrationFees ? props.registrationFees.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Package / Category Name</th>
                                <th scope="col" class="px-5 py-3">Conference Event</th>
                                <th scope="col" class="px-5 py-3">Attendance Mode</th>
                                <th scope="col" class="px-5 py-3">Price Rate</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.registrationFees || props.registrationFees.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No registration fees defined yet. Click "+ Add Registration Fee" to create one.
                                </td>
                            </tr>
                            <tr v-for="item in props.registrationFees" :key="item.id" class="hover:bg-slate-50/50 transition">
                                <!-- Name -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-900 text-xs">{{ item.name }}</p>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                                    {{ item.conference?.title || 'Default Event' }}
                                </td>

                                <!-- Mode -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase border',
                                        item.mode === 'offline' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-sky-50 text-sky-700 border-sky-200'
                                    ]">
                                        {{ item.mode }}
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="px-5 py-3.5 font-bold text-xs text-slate-900">
                                    {{ formatPrice(item.price) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteFee(item)"
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

            <!-- Create / Edit Registration Fee Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
                <div class="w-full max-w-lg rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100 text-xs my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{ editingFee ? 'Edit Fee Package' : 'Add New Fee Package' }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">Configure participant tier rate and attendance format.</p>
                        </div>
                        <button
                            @click="isModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Target Conference <span class="text-red-500">*</span></label>
                            <select v-model="form.conference_id" class="admin-input" required>
                                <option value="" disabled>Select conference...</option>
                                <option v-for="c in props.conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                            <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.conference_id }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Package / Participant Tier Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="e.g. Mahasiswa / Umum (Offline)" class="admin-input" required />
                            <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.name }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Attendance Mode <span class="text-red-500">*</span></label>
                                <select v-model="form.mode" class="admin-input" required>
                                    <option value="offline">Offline (On-Site)</option>
                                    <option value="online">Online (Virtual)</option>
                                </select>
                                <span v-if="form.errors.mode" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.mode }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Price (IDR) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                    <input
                                        v-model="formattedPrice"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="500.000"
                                        class="admin-input !pl-10 font-bold text-slate-900"
                                        required
                                    />
                                </div>
                                <span v-if="form.errors.price" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.price }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                @click="isModalOpen = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold px-6 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ form.processing ? 'Saving...' : (editingFee ? 'Update Package' : 'Save Package') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reusable Delete Confirmation Modal -->
            <DeleteConfirmModal
                :show="isDeleteModalOpen"
                :title="deleteTitle"
                :message="deleteMessage"
                :item-name="feeToDelete?.name"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
