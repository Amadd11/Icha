<script setup>
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: Object, // Paginated 20 per page
    filters: Object,
    roleCounts: Object,
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'participant',
    institution: '',
    phone: '',
});

const roleColor = (role) => ({
    super_admin: 'bg-purple-50 text-purple-700 border-purple-200',
    admin:       'bg-blue-50 text-blue-700 border-blue-200',
    reviewer:    'bg-amber-50 text-amber-700 border-amber-200',
    participant: 'bg-emerald-50 text-emerald-700 border-emerald-200',
}[role] ?? 'bg-slate-50 text-slate-600 border-slate-200');

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
    if (confirm(`Are you sure you want to delete user "${user.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
        });
    }
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
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No users found. Click "+ Add New User" to create one.
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
                <div v-if="props.users?.links && props.users.total > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-5 py-4 border-t border-slate-100 bg-slate-50/50 gap-3">
                    <p class="text-xs text-slate-500">
                        Showing <strong class="text-slate-700">{{ props.users.from || 0 }}</strong> to <strong class="text-slate-700">{{ props.users.to || 0 }}</strong> of <strong class="text-slate-700">{{ props.users.total }}</strong> users (20 per page)
                    </p>

                    <div class="flex items-center gap-1 flex-wrap">
                        <Link
                            v-for="(link, i) in props.users.links"
                            :key="i"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1 text-xs rounded-lg transition font-medium',
                                link.active ? 'bg-purple-900 text-gold font-bold shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50',
                                !link.url ? 'opacity-40 pointer-events-none cursor-not-allowed' : 'cursor-pointer'
                            ]"
                            preserve-scroll
                        />
                    </div>
                </div>
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

        </div>
    </AdminLayout>
</template>
