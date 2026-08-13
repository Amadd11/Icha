<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-xl shadow-purple-950/25 border border-white/60">
            <!-- Prominent Large Logo Strip -->
            <div class="mb-6 flex items-center justify-center gap-4 sm:gap-6 rounded-2xl px-6 py-4">
                <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-12 sm:h-16 w-auto object-contain transition-transform hover:scale-105" />
                <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-12 sm:h-16 w-auto object-contain transition-transform hover:scale-105" />
            </div>

            <!-- Heading -->
            <div class="mb-6 text-center sm:text-left">
                <p class="mt-1 text-sm text-slate-500">Sign in to your ICHA portal account.</p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="mb-5 flex items-center gap-2 rounded-xl bg-purple-50 border border-purple-200 px-4 py-3 text-sm font-medium text-purple-800"
            >
                <svg class="w-4 h-4 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ status }}
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email Address" class="text-xs font-semibold text-slate-600 mb-1" />
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 transition hover:border-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                    />
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- Password with Show/Hide Toggle -->
                <div>
    <div class="flex items-center justify-between mb-1">
        <InputLabel for="password" value="Password" class="text-xs font-semibold text-slate-600" />
        <Link
            v-if="canResetPassword"
            :href="route('password.request')"
            class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors"
        >
            Forgot password?
        </Link>
    </div>
    <div class="relative">
        <TextInput
            id="password"
            :type="showPassword ? 'text' : 'password'"
            class="block w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 pl-4 pr-12 text-sm text-slate-900 placeholder-slate-400 transition hover:border-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
            v-model="form.password"
            required
            autocomplete="current-password"
            placeholder="Enter your password"
        />
        <!-- Divider -->
        <span class="absolute inset-y-2 right-10 w-px bg-slate-200"></span>
        <!-- Toggle button -->
        <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute inset-y-1.5 right-1.5 flex items-center justify-center w-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
            title="Toggle password visibility"
        >
            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        </button>
    </div>
    <InputError class="mt-1.5" :message="form.errors.password" />
</div>

                <!-- Remember Me -->
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" class="border-slate-300 text-primary focus:ring-primary" />
                    <span class="text-sm text-slate-600">Remember me on this device</span>
                </label>

                <!-- Submit Button -->
                <div class="pt-1">
                    <PrimaryButton
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Signing in...' : 'Sign In' }}
                    </PrimaryButton>
                </div>

                <!-- Divider -->
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-slate-400 font-medium">or</span>
                    </div>
                </div>

                <!-- Register Link -->
                <p class="text-center text-sm text-slate-500">
                    Don't have an account?
                    <Link :href="route('register')" class="ml-1 font-semibold text-primary hover:text-primary-dark transition-colors">
                        Register
                    </Link>
                </p>
            </form>
        </div>
    </GuestLayout>
</template>
