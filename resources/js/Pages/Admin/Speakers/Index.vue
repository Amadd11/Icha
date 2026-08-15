<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';
import { formatStorageUrl } from '@/Utils/formatters';

const props = defineProps({
    speakers: Array,
    conferences: Array,
});

const isModalOpen = ref(false);
const editingSpeaker = ref(null);
const photoPreview = ref(null);

const typeColor = (type) => ({
    keynote: 'bg-purple-50 text-purple-700 border-purple-200',
    plenary: 'bg-amber-50 text-amber-700 border-amber-200',
    invited: 'bg-indigo-50 text-indigo-700 border-indigo-200',
}[type] ?? 'bg-slate-100 text-slate-600 border-slate-200');

const countries = [
    { code: 'ID', name: 'Indonesia' },
    { code: 'MY', name: 'Malaysia' },
    { code: 'SG', name: 'Singapore' },
    { code: 'TH', name: 'Thailand' },
    { code: 'PH', name: 'Philippines' },
    { code: 'VN', name: 'Vietnam' },
    { code: 'BN', name: 'Brunei' },
    { code: 'JP', name: 'Japan' },
    { code: 'KR', name: 'South Korea' },
    { code: 'CN', name: 'China' },
    { code: 'IN', name: 'India' },
    { code: 'AU', name: 'Australia' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'US', name: 'United States' },
    { code: 'NL', name: 'Netherlands' },
    { code: 'DE', name: 'Germany' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'TR', name: 'Turkey' },
    { code: 'EG', name: 'Egypt' },
];

const form = useForm({
    conference_id: '',
    name: '',
    title: '',
    institution: '',
    country: '',
    country_code: '',
    bio: '',
    email: '',
    type: 'keynote',
    order: 0,
    photo: null,
});

function onCountryChange() {
    const selected = countries.find(c => c.name === form.country);
    if (selected) {
        form.country_code = selected.code;
    }
}

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
}

function openCreateModal() {
    editingSpeaker.value = null;
    photoPreview.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id = props.conferences?.[0]?.id || '';
    form.type = 'keynote';
    form.country = 'Indonesia';
    form.country_code = 'ID';
    form.order = (props.speakers?.length || 0) + 1;
    isModalOpen.value = true;
}

function openEditModal(s) {
    editingSpeaker.value = s;
    photoPreview.value = formatStorageUrl(s.photo);
    form.clearErrors();
    form.conference_id = s.conference_id || (props.conferences?.[0]?.id || '');
    form.name = s.name || '';
    form.title = s.title || '';
    form.institution = s.institution || '';
    form.country = s.country || '';
    form.country_code = s.country_code || '';
    form.bio = s.bio || '';
    form.email = s.email || '';
    form.type = s.type || 'keynote';
    form.order = s.order ?? 0;
    form.photo = null;
    isModalOpen.value = true;
}

function submit() {
    if (editingSpeaker.value) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('admin.speakers.update', editingSpeaker.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.speakers.store'), {
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
    itemToDelete: speakerToDelete,
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
        title: 'Delete Speaker',
        message: `Are you sure you want to delete speaker "${s.name}"?`,
        url: route('admin.speakers.destroy', s.id),
    });
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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                >
                    + Add New Speaker
                </button>
            </div>

            <!-- Minimalist Speakers Table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
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

            <!-- Create / Edit Speaker Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 overflow-y-auto">
                <div class="w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100 text-xs my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{ editingSpeaker ? 'Edit Speaker Details' : 'Add New Conference Speaker' }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">Fill in speaker biography, academic institution, and profile photo.</p>
                        </div>
                        <button
                            @click="isModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Photo Upload Row -->
                        <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="h-18 w-18 shrink-0 overflow-hidden rounded-2xl bg-white border border-slate-200 flex items-center justify-center shadow-xs">
                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                <span v-else class="text-2xl text-slate-300">👤</span>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <label class="block font-bold text-slate-800 mb-1">Speaker Profile Photo</label>
                                <p class="text-[11px] text-slate-500 mb-2">Upload professional square headshot (JPG/PNG, Max 2MB)</p>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onPhotoChange"
                                    class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer"
                                />
                                <span v-if="form.errors.photo" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.photo }}</span>
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
                                <label class="mb-1 block font-bold text-slate-700">Speaker Category <span class="text-red-500">*</span></label>
                                <select v-model="form.type" class="admin-input" required>
                                    <option value="keynote">Keynote Speaker</option>
                                    <option value="plenary">Plenary Speaker</option>
                                    <option value="invited">Invited Speaker</option>
                                </select>
                                <span v-if="form.errors.type" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.type }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" placeholder="e.g. Prof. Dr. Jane Doe, Ph.D" class="admin-input" required />
                                <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Title / Subtitle</label>
                                <input v-model="form.title" type="text" placeholder="e.g. Healthcare Policy Advisor" class="admin-input" />
                                <span v-if="form.errors.title" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.title }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Institution / Organization <span class="text-red-500">*</span></label>
                                <input v-model="form.institution" type="text" placeholder="e.g. Harvard School of Public Health" class="admin-input" required />
                                <span v-if="form.errors.institution" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.institution }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Country</label>
                                <select v-model="form.country" @change="onCountryChange" class="admin-input">
                                    <option value="">Select country...</option>
                                    <option v-for="cnt in countries" :key="cnt.code" :value="cnt.name">{{ cnt.name }}</option>
                                </select>
                                <span v-if="form.errors.country" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.country }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Contact Email (Optional)</label>
                                <input v-model="form.email" type="email" placeholder="speaker@university.edu" class="admin-input" />
                                <span v-if="form.errors.email" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.email }}</span>
                            </div>

                            <div>
                                <label class="mb-1 block font-bold text-slate-700">Display Order</label>
                                <input v-model="form.order" type="number" min="0" class="admin-input" />
                                <span v-if="form.errors.order" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.order }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block font-bold text-slate-700">Biography / Overview</label>
                            <textarea
                                v-model="form.bio"
                                rows="3"
                                placeholder="Brief academic career and research interests..."
                                class="admin-input"
                            ></textarea>
                            <span v-if="form.errors.bio" class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ form.errors.bio }}</span>
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
                                {{ form.processing ? 'Saving...' : (editingSpeaker ? 'Update Speaker' : 'Save Speaker') }}
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
                :item-name="speakerToDelete?.name"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />
        </div>
    </AdminLayout>
</template>
