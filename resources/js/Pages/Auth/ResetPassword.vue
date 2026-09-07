<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password - ICHA 10th" />

        <div class="login-card rounded-3xl bg-white p-7 sm:p-10 shadow-2xl shadow-purple-950/30 border border-slate-100 relative overflow-hidden transition-all duration-500 hover:shadow-purple-900/20 max-w-lg mx-auto">
            <!-- Decorative Top Gradient Line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-gold to-primary"></div>

            <!-- Prominent Logo & Badge -->
            <div class="mb-6 flex flex-col items-center justify-center text-center">
                <div class="h-20 w-20 rounded-2xl bg-white p-2 shadow-lg shadow-purple-900/10 border border-slate-100 flex items-center justify-center hover:scale-105 transition-transform duration-300 mb-3">
                    <img 
                        src="/assets/logo/logo-icha.png" 
                        alt="ICHA 10th Logo" 
                        class="h-full w-full object-contain" 
                    />
                </div>
                <span class="inline-block px-3.5 py-1 rounded-full bg-purple-50 text-primary text-[11px] font-black uppercase tracking-wider border border-purple-100">
                    Security Update
                </span>
            </div>

            <!-- Heading -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-1">Set New Password</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed">
                    Choose a strong, secure password to protect your conference account.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email (Readonly or verified) -->
                <div class="space-y-1.5">
                    <InputLabel for="email" value="Account Email" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-100/70 py-3.5 pl-10 pr-4 text-sm text-slate-700 cursor-not-allowed shadow-xs"
                            v-model="form.email"
                            required
                            readonly
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- New Password -->
                <div class="space-y-1.5">
                    <InputLabel for="password" value="New Password" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/50 py-3.5 pl-10 pr-12 text-sm text-slate-900 placeholder-slate-400 transition-all duration-300 hover:border-slate-300 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15 shadow-xs"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="At least 8 characters"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-2 right-2 flex items-center justify-center w-8 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer"
                            title="Toggle visibility"
                        >
                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                    <InputLabel for="password_confirmation" value="Confirm New Password" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/50 py-3.5 pl-10 pr-12 text-sm text-slate-900 placeholder-slate-400 transition-all duration-300 hover:border-slate-300 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15 shadow-xs"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter new password"
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-2 right-2 flex items-center justify-center w-8 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer"
                            title="Toggle visibility"
                        >
                            <svg v-if="!showPasswordConfirmation" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                </div>

                <div class="pt-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full relative group overflow-hidden rounded-2xl bg-gradient-to-r from-primary via-purple-900 to-primary-dark py-3.5 px-6 text-sm font-black tracking-wide text-white shadow-lg shadow-primary/25 transition-all duration-300 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                    >
                        <span v-if="!form.processing" class="flex items-center gap-2">
                            <span>Update Password</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span>Updating Password...</span>
                        </span>
                    </button>
                </div>

                <!-- Back to Sign In Link -->
                <div class="pt-3 text-center border-t border-slate-100">
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-slate-600 hover:text-primary transition-colors duration-150"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        <span>Back to Sign In</span>
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
