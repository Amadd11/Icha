<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role);

const currentLayout = computed(() => {
    if (userRole.value === 'reviewer') {
        return ReviewerLayout;
    } else if (userRole.value === 'admin' || userRole.value === 'super_admin') {
        return AdminLayout;
    }
    return ParticipantLayout;
});
</script>

<template>
    <Head title="Profile Settings" />

    <component :is="currentLayout">
        <div class="space-y-6 max-w-5xl mx-auto pb-12">

            <!-- Profile Info Card -->
            <div id="profile-info" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm scroll-mt-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-primary flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Personal Information</h2>
                        <p class="text-xs text-slate-500">Update your account profile details and email address.</p>
                    </div>
                </div>
                
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <!-- Change Password Card -->
            <div id="change-password" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm scroll-mt-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">key</span>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Security & Password</h2>
                        <p class="text-xs text-slate-500">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                </div>

                <UpdatePasswordForm />
            </div>

            <!-- Danger Zone / Delete Account -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-rose-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-rose-100">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Danger Zone</h2>
                        <p class="text-xs text-slate-500">Permanently delete your account and all associated data.</p>
                    </div>
                </div>

                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </component>
</template>
