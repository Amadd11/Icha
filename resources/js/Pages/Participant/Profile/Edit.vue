<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    profile: Object,
});

const form = useForm({
    name:                 props.user?.name                 ?? '',
    phone:                props.profile?.phone                ?? '',
    institution:          props.profile?.institution          ?? '',
    country:              props.profile?.country              ?? 'Indonesia',
    city:                 props.profile?.city                 ?? '',
    address:              props.profile?.address              ?? '',
    participant_category: props.profile?.participant_category ?? 'non_student',
    identity_number:      props.profile?.identity_number      ?? '',
    gender:               props.profile?.gender               ?? 'male',
});

function submit() {
    form.put(route('participant.profile.update'));
}
</script>

<template>
    <Head title="My Profile" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-slate-800">My Profile</h1>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Personal Information</h2>
                    <div class="grid gap-4 sm:grid-cols-2">

                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Full Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" class="admin-input" required />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Phone / WhatsApp <span class="text-red-400">*</span></label>
                            <input v-model="form.phone" type="text" class="admin-input" placeholder="+628123456789" required />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Identity Number -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">KTP / NIM / Passport <span class="text-red-400">*</span></label>
                            <input v-model="form.identity_number" type="text" class="admin-input" placeholder="35150..." required />
                            <p v-if="form.errors.identity_number" class="mt-1 text-xs text-red-500">{{ form.errors.identity_number }}</p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Gender <span class="text-red-400">*</span></label>
                            <select v-model="form.gender" class="admin-input" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Participant Category -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Category <span class="text-red-400">*</span></label>
                            <select v-model="form.participant_category" class="admin-input" required>
                                <option value="student">Student</option>
                                <option value="non_student">Non-Student / Professional</option>
                            </select>
                        </div>

                        <!-- Institution -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Institution / University / Hospital <span class="text-red-400">*</span></label>
                            <input v-model="form.institution" type="text" class="admin-input" placeholder="e.g. Universitas Muhammadiyah Surabaya" required />
                            <p v-if="form.errors.institution" class="mt-1 text-xs text-red-500">{{ form.errors.institution }}</p>
                        </div>

                        <!-- Country -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Country <span class="text-red-400">*</span></label>
                            <input v-model="form.country" type="text" class="admin-input" required />
                        </div>

                        <!-- City -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">City <span class="text-red-400">*</span></label>
                            <input v-model="form.city" type="text" class="admin-input" required />
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Full Address</label>
                            <textarea v-model="form.address" rows="3" class="admin-input"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
