<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
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
const userRole = computed(() => page.props.auth?.user?.role || 'participant');

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
        <div class="space-y-6 max-w-7xl mx-auto pb-12">
            
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-xl font-bold text-slate-900">Profile Settings</h1>
                <p class="text-xs text-slate-500">Manage your profile details and security settings</p>
            </div>

            <!-- Content Layout: Main Forms & Sidebar Guidelines -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column: Forms (Col Span 2) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Profile Info Card -->
                    <div id="profile-info" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm scroll-mt-6">
                        <div class="mb-6 pb-4 border-b border-slate-100">
                            <h2 class="text-base font-black text-slate-900">Personal & Profile Information</h2>
                            <p class="text-xs text-slate-500">Update your official conference details and contact info.</p>
                        </div>
                        
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>

                    <!-- Change Password Card -->
                    <div id="change-password" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm scroll-mt-6">
                        <div class="mb-6 pb-4 border-b border-slate-100">
                            <h2 class="text-base font-black text-slate-900">Security & Password</h2>
                            <p class="text-xs text-slate-500">Manage your password to maintain account security.</p>
                        </div>

                        <UpdatePasswordForm />
                    </div>

                </div>

                <!-- Right Column: Profile Guidelines / Notes -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-slate-50 rounded-3xl p-6 border border-slate-200/80 sticky top-6 shadow-2xs space-y-4">
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-200/80 pb-3">
                            Profile Guidelines
                        </h3>
                        
                        <ul class="space-y-4 text-xs text-slate-600 font-medium">
                            <li class="border-b border-slate-100 pb-3">
                                <strong class="block text-slate-900 mb-0.5">Accurate Name & Degree</strong>
                                Your full name and academic degree will be printed on official Conference Certificates and proceedings.
                            </li>
                            <li class="border-b border-slate-100 pb-3">
                                <strong class="block text-slate-900 mb-0.5">Institution Name</strong>
                                Write full university or organization name without abbreviations for official documentation.
                            </li>
                            <li>
                                <strong class="block text-slate-900 mb-0.5">Active Phone / WhatsApp</strong>
                                Used by committee members for urgent updates regarding review feedback or payment verification.
                            </li>
                        </ul>

                        <div class="pt-4 border-t border-slate-200/80 text-center">
                            <p class="text-[11px] text-slate-400 font-medium">Need help? Contact committee at info@ichaconference.com</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </component>
</template>
