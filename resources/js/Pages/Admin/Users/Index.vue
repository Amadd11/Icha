<script setup>
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { useDeleteConfirm } from '@/Composables/useDeleteConfirm';
import { useTableFilter } from '@/Composables/useTableFilter';
import { getRoleBadgeClass as roleColor } from '@/Utils/badges';

const props = defineProps({
    users: Object, // Paginated 20 per page
    filters: Object,
    roleCounts: Object,
});

const { filters, applyFilter, resetFilter, isFiltered } = useTableFilter('admin.users.index', {
    search: props.filters?.search || '',
    role: props.filters?.role || 'all',
});

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilter();
    }, 350);
}

function setRole(role) {
    filters.role = role;
    applyFilter();
}

function clearSearch() {
    filters.search = '';
    applyFilter();
}

const isModalOpen = ref(false);
const editingUser = ref(null);

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: userToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'participant',
    institution: '',
    phone: '',
});

function openCreateModal() {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.role = 'participant';
    isModalOpen.value = true;
}

function openEditModal(user) {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    form.institution = user.profile?.institution || '';
    form.phone = user.profile?.phone || '';
    isModalOpen.value = true;
}

function submit() {
    if (editingUser.value) {
        form.put(route('admin.users.update', editingUser.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

function destroy(user) {
    openDeleteModal({
        item: user,
        title: 'Permanently Delete User',
        message: `Are you sure you want to permanently delete user "${user.name}" (${user.email})? All associated data will be removed and this action cannot be undone.`,
        url: route('admin.users.destroy', user.id),
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>

<template>
    <Head title="Users Management - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Users Management</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage all registered accounts, roles, and affiliations across the conference portal.</p>
                </div>

                <div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        + Add New User
                    </button>
                </div>
            </div>

            <!-- Role Filter Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-thin">
                <button
                    v-for="tab in [
                        { key: 'all', label: 'All Users', count: props.roleCounts?.all ?? 0 },
                        { key: 'super_admin', label: 'Super Admin', count: props.roleCounts?.super_admin ?? 0 },
                        { key: 'admin', label: 'Admin', count: props.roleCounts?.admin ?? 0 },
                        { key: 'reviewer', label: 'Reviewer', count: props.roleCounts?.reviewer ?? 0 },
                        { key: 'participant', label: 'Participant', count: props.roleCounts?.participant ?? 0 },
                    ]"
                    :key="tab.key"
                    @click="setRole(tab.key)"
                    :class="[
                        'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition cursor-pointer shrink-0',
                        (filters.role === tab.key || (!filters.role && tab.key === 'all'))
                            ? 'bg-primary text-white shadow-xs'
                            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                    ]"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        :class="[
                            'px-1.5 py-0.5 rounded-full text-[10px] font-extrabold',
                            (filters.role === tab.key || (!filters.role && tab.key === 'all'))
                                ? 'bg-white/20 text-white'
                                : 'bg-slate-100 text-slate-600'
                        ]"
                    >
                        {{ tab.count }}
                    </span>
                </button>
            </div>

            <!-- Search and Filter Control Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200">
                <div class="relative w-full sm:w-96 flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px] pointer-events-none leading-none select-none">
                        search
                    </span>
                    <input
                        v-model="filters.search"
                        @input="onSearchInput"
                        @keyup.enter="applyFilter()"
                        type="text"
                        placeholder="Search by name, email, institution, or phone..."
                        class="admin-input text-xs !pl-10 !pr-8 w-full"
                    />
                    <button
                        v-if="filters.search"
                        @click="clearSearch"
                        type="button"
                        class="absolute right-3 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer"
                        title="Clear search"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <select
                        v-model="filters.role"
                        @change="applyFilter()"
                        class="admin-input py-2 text-xs font-bold w-full sm:w-auto"
                    >
                        <option value="all">All Roles ({{ props.roleCounts?.all ?? 0 }})</option>
                        <option value="super_admin">Super Admin ({{ props.roleCounts?.super_admin ?? 0 }})</option>
                        <option value="admin">Admin ({{ props.roleCounts?.admin ?? 0 }})</option>
                        <option value="reviewer">Reviewer ({{ props.roleCounts?.reviewer ?? 0 }})</option>
                        <option value="participant">Participant ({{ props.roleCounts?.participant ?? 0 }})</option>
                    </select>

                    <button
                        v-if="isFiltered"
                        @click="resetFilter"
                        type="button"
                        class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1"
                        title="Reset all filters"
                    >
                        <span>↺</span>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Minimalist Table Card Container -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Registered Users List</h3>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ props.users?.total || 0 }} Users</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-100 uppercase text-[11px] font-bold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">User Details</th>
                                <th scope="col" class="px-5 py-3">Institution & Phone</th>
                                <th scope="col" class="px-5 py-3">Role</th>
                                <th scope="col" class="px-5 py-3">Joined Date</th>
                                <th scope="col" class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!props.users?.data || props.users.data.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-xs text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <p class="font-bold text-slate-600 text-sm">No users found</p>
                                        <p class="text-slate-400 text-xs">
                                            {{ isFiltered ? 'No user accounts match your search query or role filter.' : 'Click "+ Add New User" to register a new account.' }}
                                        </p>
                                        <button
                                            v-if="isFiltered"
                                            @click="resetFilter"
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline pt-1 cursor-pointer"
                                        >
                                            Reset Filters
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="u in props.users?.data"
                                :key="u.id"
                                class="hover:bg-slate-50/50 transition"
                            >
                                <!-- Name & Email -->
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-slate-900 text-xs">{{ u.name }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ u.email }}</p>
                                </td>

                                <!-- Institution & Phone -->
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800 text-xs">{{ u.profile?.institution || '—' }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ u.profile?.phone || '—' }}</p>
                                </td>

                                <!-- Role Pill -->
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase', roleColor(u.role)]">
                                        {{ u.role.replace('_', ' ') }}
                                    </span>
                                </td>

                                <!-- Joined Date -->
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ formatDate(u.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(u)"
                                            class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="destroy(u)"
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

                <!-- Pagination Footer (20 per page) -->
                <Pagination
                    :links="props.users?.links"
                    :from="props.users?.from"
                    :to="props.users?.to"
                    :total="props.users?.total"
                />
            </div>

            <!-- Simple Modal for Create & Edit -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
                <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl overflow-hidden border border-slate-200 my-8">
                    
                    <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/70">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">
                                {{ editingUser ? 'Edit User Account' : 'Add New User Account' }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ editingUser ? `Update credentials for ${editingUser.name}` : 'Fill in the form to register a new user' }}
                            </p>
                        </div>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer">✕</button>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. John Doe"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700"
                                required
                            />
                            <span v-if="form.errors.name" class="text-red-500 font-bold text-[10px] block mt-1">{{ form.errors.name }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="user@example.com"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700"
                                required
                            />
                            <span v-if="form.errors.email" class="text-red-500 font-bold text-[10px] block mt-1">{{ form.errors.email }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                Password
                                <span v-if="!editingUser" class="text-red-500">*</span>
                                <span v-else class="text-slate-400 font-normal">(Leave blank to keep unchanged)</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Min. 8 characters..."
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700"
                                :required="!editingUser"
                            />
                            <span v-if="form.errors.password" class="text-red-500 font-bold text-[10px] block mt-1">{{ form.errors.password }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.role"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-bold"
                                required
                            >
                                <option value="participant">Participant</option>
                                <option value="reviewer">Reviewer</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                            <span v-if="form.errors.role" class="text-red-500 font-bold text-[10px] block mt-1">{{ form.errors.role }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Institution</label>
                                <input
                                    v-model="form.institution"
                                    type="text"
                                    placeholder="e.g. Universitas..."
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700"
                                />
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="e.g. 08123456789"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2 px-3 focus:ring-1 focus:ring-purple-700"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="isModalOpen = false"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold px-5 py-2 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{ form.processing ? 'Saving...' : (editingUser ? 'Update User' : 'Save User') }}
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
                :item-name="userToDelete ? `${userToDelete.name} (${userToDelete.email})` : ''"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />

        </div>
    </AdminLayout>
</template>
