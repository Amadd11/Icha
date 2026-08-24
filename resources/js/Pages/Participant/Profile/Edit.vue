<script setup>
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    user: Object,
    profile: Object,
    activeConference: Object,
});

// Profile Form
const form = useForm({
    name:                 props.user?.name                 ?? '',
    phone:                props.profile?.phone                ?? '',
    institution:          props.profile?.institution          ?? '',
    country:              props.profile?.country              ?? 'Indonesia',
    city:                 props.profile?.city                 ?? '',
    participant_category: props.profile?.participant_category ?? 'non_student',
    gender:               props.profile?.gender               ?? 'male',
});

function submitProfile() {
    form.put(route('participant.profile.update'), {
        preserveScroll: true,
    });
}

// Password Form
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updatePassword() {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
}

// Compute initials for avatar
const userInitials = computed(() => {
    const name = props.user?.name || '';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(n => n[0].toUpperCase())
        .join('');
});

// Participant code
const participantCode = computed(() => {
    return 'ICHA-' + String(props.user?.id || 1).padStart(4, '0');
});

const inputClass = 'block w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 px-3.5 transition focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 text-slate-800 placeholder:text-slate-400';
</script>

<template>
    <Head title="Profile Settings - Participant" />

    <ParticipantLayout>
        <div class="space-y-6 max-w-4xl mx-auto pb-12">
            
            <!-- Minimalist Page Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Profile Settings</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage your personal information, institutional affiliation, and account security.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200/80">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>{{ participantCode }}</span>
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-50 text-purple-900 text-xs font-bold border border-purple-100">
                        {{ form.participant_category === 'student' ? 'Student' : 'General' }}
                    </span>
                </div>
            </div>

            <!-- Profile Summary Card -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Clean Avatar Circle -->
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary text-gold text-lg font-bold shadow-xs">
                        {{ userInitials || 'IC' }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 leading-tight">
                            {{ form.name || props.user?.name || 'Participant Name' }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                            <span>{{ props.user?.email }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="text-emerald-700 font-semibold">Active Account</span>
                        </p>
                    </div>
                </div>

                <div v-if="props.activeConference" class="text-left sm:text-right text-xs">
                    <span class="text-slate-400 block font-medium text-[11px]">Conference</span>
                    <span class="font-bold text-slate-800">{{ props.activeConference.title }}</span>
                </div>
            </div>

            <!-- Card 1: Personal & Affiliation Details -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-2xs">
                <form @submit.prevent="submitProfile" class="space-y-6">
                    
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Personal & Academic Details</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Your official name and institution will appear on certificates and conference materials.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4.5 pt-2 border-t border-slate-100">
                        
                        <!-- Full Name -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">
                                Full Name with Degree / Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                :class="inputClass"
                                placeholder="e.g. Dr. Andi Sutomo, Sp.PK"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            <p class="mt-1 text-[11px] text-slate-400">This exact spelling will be used for your official E-Certificate.</p>
                        </div>

                        <!-- Email (Disabled) -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">
                                Email Address
                            </label>
                            <input
                                :value="props.user?.email"
                                type="email"
                                disabled
                                class="block w-full text-xs rounded-xl border border-slate-200 bg-slate-100/70 py-2.5 px-3.5 text-slate-500 cursor-not-allowed"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">
                                Phone / WhatsApp Number
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                :class="inputClass"
                                placeholder="+62 812-3456-7890"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Gender Selector -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Gender</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="form.gender = 'male'"
                                    :class="[
                                        'py-2 px-3 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-1.5',
                                        form.gender === 'male'
                                            ? 'bg-primary text-white border-primary shadow-2xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                    ]"
                                >
                                    Male
                                </button>
                                <button
                                    type="button"
                                    @click="form.gender = 'female'"
                                    :class="[
                                        'py-2 px-3 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-1.5',
                                        form.gender === 'female'
                                            ? 'bg-primary text-white border-primary shadow-2xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                    ]"
                                >
                                    Female
                                </button>
                            </div>
                        </div>

                        <!-- Participant Category -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Category</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="form.participant_category = 'non_student'"
                                    :class="[
                                        'py-2 px-3 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-center text-center',
                                        form.participant_category === 'non_student'
                                            ? 'bg-primary text-white border-primary shadow-2xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                    ]"
                                >
                                    General / Doctor
                                </button>
                                <button
                                    type="button"
                                    @click="form.participant_category = 'student'"
                                    :class="[
                                        'py-2 px-3 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-center text-center',
                                        form.participant_category === 'student'
                                            ? 'bg-primary text-white border-primary shadow-2xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                    ]"
                                >
                                    Student / Resident
                                </button>
                            </div>
                        </div>

                        <!-- Institution -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">
                                Institution / University / Hospital
                            </label>
                            <input
                                v-model="form.institution"
                                type="text"
                                :class="inputClass"
                                placeholder="e.g. Universitas Airlangga / RSUD Dr. Soetomo"
                            />
                            <p v-if="form.errors.institution" class="mt-1 text-xs text-red-500">{{ form.errors.institution }}</p>
                        </div>

                        <!-- City -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">City / Regency</label>
                            <input
                                v-model="form.city"
                                type="text"
                                :class="inputClass"
                                placeholder="e.g. Surabaya"
                            />
                            <p v-if="form.errors.city" class="mt-1 text-xs text-red-500">{{ form.errors.city }}</p>
                        </div>

                        <!-- Country -->
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Country <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.country"
                                type="text"
                                :class="inputClass"
                                placeholder="Indonesia"
                                required
                            />
                            <p v-if="form.errors.country" class="mt-1 text-xs text-red-500">{{ form.errors.country }}</p>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <span v-if="form.recentlySuccessful" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                            <span>✓</span>
                            <span>Profile updated successfully.</span>
                        </span>
                        <span v-else class="text-[11px] text-slate-400">All changes save instantly to your participant record.</span>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-gold hover:bg-gold-dark text-slate-950 font-bold text-xs px-6 py-2.5 transition cursor-pointer disabled:opacity-50 shadow-2xs"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Profile Changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Password & Security -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-2xs space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Account Password & Security</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ensure your account uses a secure password to protect your conference submissions.</p>
                </div>

                <form @submit.prevent="updatePassword" class="space-y-4 pt-2 border-t border-slate-100">
                    
                    <!-- Current Password -->
                    <div>
                        <InputLabel for="current_password" value="Current Password" class="text-xs font-bold text-slate-700 mb-1" />
                        <div class="relative">
                            <TextInput
                                id="current_password"
                                ref="currentPasswordInput"
                                v-model="passwordForm.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="block w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-3.5 pr-12 transition focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10"
                                autocomplete="current-password"
                                placeholder="Enter current password"
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 text-xs font-semibold cursor-pointer"
                            >
                                {{ showCurrentPassword ? 'Hide' : 'Show' }}
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- New Password -->
                        <div>
                            <InputLabel for="password" value="New Password" class="text-xs font-bold text-slate-700 mb-1" />
                            <div class="relative">
                                <TextInput
                                    id="password"
                                    ref="passwordInput"
                                    v-model="passwordForm.password"
                                    :type="showNewPassword ? 'text' : 'password'"
                                    class="block w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-3.5 pr-12 transition focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    autocomplete="new-password"
                                    placeholder="Enter new password"
                                />
                                <button
                                    type="button"
                                    @click="showNewPassword = !showNewPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 text-xs font-semibold cursor-pointer"
                                >
                                    {{ showNewPassword ? 'Hide' : 'Show' }}
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm New Password" class="text-xs font-bold text-slate-700 mb-1" />
                            <div class="relative">
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="block w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-3.5 pr-12 transition focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    autocomplete="new-password"
                                    placeholder="Confirm new password"
                                />
                                <button
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 text-xs font-semibold cursor-pointer"
                                >
                                    {{ showConfirmPassword ? 'Hide' : 'Show' }}
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password_confirmation }}</p>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <span v-if="passwordForm.recentlySuccessful" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                            <span>✓</span>
                            <span>Password updated successfully.</span>
                        </span>
                        <span v-else></span>

                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-6 py-2.5 transition cursor-pointer disabled:opacity-50 shadow-2xs"
                        >
                            {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </ParticipantLayout>
</template>
