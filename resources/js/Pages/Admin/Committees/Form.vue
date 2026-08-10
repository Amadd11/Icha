<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    committee: Object,
    conferences: Array,
});

const isEditing = Boolean(props.committee);

const form = useForm({
    conference_id: props.committee?.conference_id || (props.conferences?.[0]?.id || ''),
    name: props.committee?.name || '',
    role: props.committee?.role || '',
    institution: props.committee?.institution || '',
    group: props.committee?.group || 'organizing',
    order: props.committee?.order ?? 0,
});

function submit() {
    if (isEditing) {
        form.put(route('admin.committees.update', props.committee.id));
    } else {
        form.post(route('admin.committees.store'));
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Committee Member' : 'Add Committee Member'" />

    <AdminLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ isEditing ? 'Edit Committee Member' : 'Add New Committee Member' }}</h1>
                    <p class="text-xs text-slate-500">Configure committee roles and institutional affiliations.</p>
                </div>
                <Link
                    :href="route('admin.committees.index')"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition"
                >
                    &larr; Back to List
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] p-6 lg:p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Conference</label>
                        <select v-model="form.conference_id" class="admin-input" required>
                            <option value="" disabled>Select conference...</option>
                            <option v-for="conf in props.conferences" :key="conf.id" :value="conf.id">
                                {{ conf.title }}
                            </option>
                        </select>
                        <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.conference_id }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name (with Academic Titles)</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Prof. Dr. John Doe, M.P.H."
                                class="admin-input"
                                required
                            />
                            <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.name }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Committee Role / Position</label>
                            <input
                                v-model="form.role"
                                type="text"
                                placeholder="e.g. Committee Chairman or Member"
                                class="admin-input"
                                required
                            />
                            <span v-if="form.errors.role" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.role }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Committee Group</label>
                            <select v-model="form.group" class="admin-input" required>
                                <option value="steering">Steering Committee</option>
                                <option value="organizing">Organizing Committee</option>
                                <option value="scientific">Scientific Committee</option>
                            </select>
                            <span v-if="form.errors.group" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.group }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Institution / Affiliation</label>
                            <input
                                v-model="form.institution"
                                type="text"
                                placeholder="e.g. Universitas Muhammadiyah Surakarta"
                                class="admin-input"
                            />
                            <span v-if="form.errors.institution" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.institution }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Order Index</label>
                            <input
                                v-model="form.order"
                                type="number"
                                min="0"
                                class="admin-input"
                            />
                            <span v-if="form.errors.order" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.order }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <Link
                            :href="route('admin.committees.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark transition shadow-md disabled:opacity-50 cursor-pointer"
                        >
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update Committee' : 'Create Committee') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AdminLayout>
</template>
