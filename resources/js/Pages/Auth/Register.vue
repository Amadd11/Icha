<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-navy tracking-tight">Create your account</h1>
            <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">
                Join ICHA and start your conference journey
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div>
                <InputLabel for="name" value="Full Name" />
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="block w-full !pl-10"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Enter your full name"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email Address" />
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full !pl-10"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="you@example.com"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password Fields (Side by Side on Desktop) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="password" value="Password" />
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full !pl-10"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Min. 8 characters"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full !pl-10"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter password"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- Terms hint -->
            <p class="text-xs text-slate-400 leading-relaxed">
                By creating an account, you agree to our
                <a href="#" class="text-primary hover:text-primary-dark transition-colors duration-150">Terms of Service</a>
                and
                <a href="#" class="text-primary hover:text-primary-dark transition-colors duration-150">Privacy Policy</a>.
            </p>

            <!-- Submit -->
            <div class="pt-1">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Create Account</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Creating account...
                    </span>
                </PrimaryButton>
            </div>

            <!-- Divider -->
            <div class="relative py-1">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-slate-400">or</span>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-slate-500">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="font-semibold text-primary transition-colors duration-150 hover:text-primary-dark"
                    >
                        Sign in
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
