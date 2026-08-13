<script setup>
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
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
    participant_category: props.profile?.participant_category ?? 'non_student',
    gender:               props.profile?.gender               ?? 'male',
});

function submit() {
    form.put(route('participant.profile.update'));
}
</script>

<template>
    <Head title="Participant Profile" />
    <ParticipantLayout>
        <div class="space-y-6 max-w-4xl">
            
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-xl font-bold text-slate-900">Participant Profile</h1>
                <p class="text-xs text-slate-500">Update your personal and professional information</p>
            </div>

            <!-- Form Card -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Section 1: Personal Details -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary">
                            Personal Information
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Full Name -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Full Name (with degree) <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="admin-input rounded-xl text-xs py-2.5" placeholder="E.g. Dr. Andi Sutomo" required />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Phone / WhatsApp</label>
                                <input v-model="form.phone" type="text" class="admin-input rounded-xl text-xs py-2.5" placeholder="+62 812-3456-7890" />
                                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                            </div>

                            <!-- Gender Buttons -->
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Gender</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="form.gender = 'male'"
                                        :class="[
                                            'py-2 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                            form.gender === 'male'
                                                ? 'bg-blue-50 border-blue-500 text-blue-700'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                        ]"
                                    >
                                        Male
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.gender = 'female'"
                                        :class="[
                                            'py-2 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                            form.gender === 'female'
                                                ? 'bg-pink-50 border-pink-500 text-pink-700'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                        ]"
                                    >
                                        Female
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <!-- Section 2: Affiliation & Category -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-black uppercase tracking-wider text-primary">
                            Affiliation & Category
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Category Buttons -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Participant Category</label>
                                <div class="grid grid-cols-2 gap-3 max-w-md">
                                    <button
                                        type="button"
                                        @click="form.participant_category = 'non_student'"
                                        :class="[
                                            'py-2.5 px-4 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                            form.participant_category === 'non_student'
                                                ? 'bg-purple-50 border-primary text-primary shadow-xs'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                        ]"
                                    >
                                        General
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.participant_category = 'student'"
                                        :class="[
                                            'py-2.5 px-4 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                            form.participant_category === 'student'
                                                ? 'bg-purple-50 border-primary text-primary shadow-xs'
                                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                        ]"
                                    >
                                        Student
                                    </button>
                                </div>
                            </div>

                            <!-- Institution -->
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Institution / University / Hospital</label>
                                <input v-model="form.institution" type="text" class="admin-input rounded-xl text-xs py-2.5" placeholder="e.g. Universitas Muhammadiyah Surabaya" />
                                <p v-if="form.errors.institution" class="mt-1 text-xs text-red-500">{{ form.errors.institution }}</p>
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Country <span class="text-red-500">*</span></label>
                                <input v-model="form.country" type="text" class="admin-input rounded-xl text-xs py-2.5" required />
                                <p v-if="form.errors.country" class="mt-1 text-xs text-red-500">{{ form.errors.country }}</p>
                            </div>

                            <!-- City -->
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">City</label>
                                <input v-model="form.city" type="text" class="admin-input rounded-xl text-xs py-2.5" placeholder="e.g. Surabaya" />
                                <p v-if="form.errors.city" class="mt-1 text-xs text-red-500">{{ form.errors.city }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-black text-xs px-7 py-3 shadow-md shadow-amber-500/20 transition cursor-pointer disabled:opacity-50 flex items-center justify-center"
                        >
                            <span>{{ form.processing ? 'Saving Profile...' : 'Save Profile Settings' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ParticipantLayout>
</template>
