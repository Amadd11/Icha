<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';
import { formatStorageUrl } from '@/Utils/formatters';

const props = defineProps({
    sponsors: Array,
    conferences: Array,
});

const isModalOpen = ref(false);
const editingSponsor = ref(null);
const logoPreview = ref(null);

const tierColor = (tier) => ({
    title:    'bg-purple-50 text-purple-700 border-purple-200',
    platinum: 'bg-slate-100 text-slate-700 border-slate-300',
    gold:     'bg-amber-50 text-amber-700 border-amber-200',
    silver:   'bg-slate-50 text-slate-600 border-slate-200',
    bronze:   'bg-orange-50 text-orange-700 border-orange-200',
    exhibitor:'bg-indigo-50 text-indigo-700 border-indigo-200',
}[tier] ?? 'bg-slate-100 text-slate-600 border-slate-200');

const form = useForm({
    conference_id: '',
    name: '',
    website: '',
    tier: 'gold',
    description: '',
    is_active: true,
    order: 0,
    logo: null,
});

function onLogoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function openCreateModal() {
    editingSponsor.value = null;
    logoPreview.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.conferences?.[0]?.id || '';
    form.tier = 'gold';
    form.is_active = true;
    form.order = (props.sponsors?.length || 0) + 1;
    isModalOpen.value = true;
}

function openEditModal(s) {
    editingSponsor.value = s;
    logoPreview.value = formatStorageUrl(s.logo);
    form.clearErrors();
    form.conference_id = s.conference_id || (props.conferences?.[0]?.id || '');
    form.name = s.name || '';
    form.website = s.website || '';
    form.tier = s.tier || 'gold';
    form.description = s.description || '';
    form.is_active = Boolean(s.is_active);
    form.order = s.order ?? 0;
    form.logo = null;
    isModalOpen.value = true;
}

function submit() {
    if (editingSponsor.value) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('admin.sponsors.update', editingSponsor.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.sponsors.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: sponsorToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

function destroy(s) {
    openDeleteModal({
        item: s,
        title: 'Delete Sponsor',
        message: `Are you sure you want to delete sponsor "${s.name}"?`,
        url: route('admin.sponsors.destroy', s.id),
    });
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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                >
                    + Add New Sponsor
                </button>
            </div>

            <!-- Minimalist Sponsors Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Sponsors List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.sponsors ? props.sponsors.length : 0 }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Sponsor / Partner</th>
                                <th scope="col" class="px-5 py-3">Tier</th>
                                <th scope="col" class="px-5 py-3">Website</th>
                                <th scope="col" class="px-5 py-3">Conference</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.sponsors || props.sponsors.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No sponsors found. Click "+ Add New Sponsor" to create one.
                                </td>
                            </tr>
                            <tr v-for="s in props.sponsors" :key="s.id" class="hover:bg-slate-50/50 transition">
                                <!-- Logo & Name -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center p-1">
                                            <img v-if="s.logo" :src="formatStorageUrl(s.logo)" :alt="s.name" class="h-full w-full object-contain" />
                                            <span v-else class="text-xs font-bold text-slate-400">LOGO</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ s.name }}</p>
                                            <p v-if="s.description" class="text-[11px] text-slate-400 max-w-xs truncate">{{ s.description }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tier -->
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase border', tierColor(s.tier)]">
                                        {{ s.tier }}
                                    </span>
                                </td>

                                <!-- Website -->
                                <td class="px-5 py-3.5">
                                    <a
                                        v-if="s.website"
                                        :href="s.website"
                                        target="_blank"
                                        class="text-purple-700 hover:text-purple-900 font-semibold text-xs inline-flex items-center gap-1"
                                    >
                                        Visit Link &nearr;
                                    </a>
                                    <span v-else class="text-slate-400 text-xs">—</span>
                                </td>

                                <!-- Conference -->
                                <td class="px-5 py-3.5 text-xs text-slate-600 font-medium max-w-xs truncate" :title="s.conference?.title">
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
                                        <button
                                            @click="openEditModal(s)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="destroy(s)"
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

            <!-- Create / Edit Sponsor Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
                <div class="w-full max-w-xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100 text-xs my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{ editingSponsor ? 'Edit Sponsor Details' : 'Add New Event Sponsor' }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">Manage partner branding, tier rank, and official website URL.</p>
                        </div>
                        <button
                            @click="isModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Logo Upload Row -->
                        <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="h-16 w-24 shrink-0 overflow-hidden rounded-xl bg-white border border-slate-200 flex items-center justify-center p-1.5 shadow-xs">
                                <img v-if="logoPreview" :src="logoPreview" class="h-full w-full object-contain" />
                                <span v-else class="text-xs font-bold text-slate-300">LOGO</span>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <label class="block font-bold text-slate-800 mb-1">Company / Partner Logo</label>
                                <p class="text-[11px] text-slate-500 mb-2">Upload PNG with transparent background (Max 2MB)</p>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onLogoChange"
                                    class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                                />
                                <span v-if="form.errors.logo" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.logo }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Target Conference <span class="text-red-500">*</span></label>
                                <select v-model="form.conference_id" class="admin-input" required>
                                    <option value="" disabled>Select conference...</option>
                                    <option v-for="c in props.conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                                </select>
                                <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.conference_id }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Sponsorship Tier <span class="text-red-500">*</span></label>
                                <select v-model="form.tier" class="admin-input" required>
                                    <option value="title">Title Partner</option>
                                    <option value="platinum">Platinum Sponsor</option>
                                    <option value="gold">Gold Sponsor</option>
                                    <option value="silver">Silver Sponsor</option>
                                    <option value="bronze">Bronze Sponsor</option>
                                    <option value="exhibitor">Co-Host / Exhibitor</option>
                                </select>
                                <span v-if="form.errors.tier" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.tier }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Sponsor / Company Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" placeholder="e.g. PT Medika Solusindo" class="admin-input" required />
                                <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Official Website URL</label>
                                <input v-model="form.website" type="url" placeholder="https://example.com" class="admin-input" />
                                <span v-if="form.errors.website" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.website }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Display Order</label>
                                <input v-model="form.order" type="number" min="0" class="admin-input" />
                                <span v-if="form.errors.order" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.order }}</span>
                            </div>

                            <div class="flex items-center pt-5">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                                    <input type="checkbox" v-model="form.is_active" class="rounded border-slate-300 text-purple-700 focus:ring-purple-700" />
                                    <span>Active (Show on Website)</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Notes / Description (Optional)</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Additional details or package notes..."
                                class="admin-input"
                            ></textarea>
                            <span v-if="form.errors.description" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.description }}</span>
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
                                {{ form.processing ? 'Saving...' : (editingSponsor ? 'Update Sponsor' : 'Save Sponsor') }}
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
                :item-name="sponsorToDelete?.name"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />
        </div>
    </AdminLayout>
</template>
