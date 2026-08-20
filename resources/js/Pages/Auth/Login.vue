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

        <div class="login-card rounded-3xl bg-white p-7 sm:p-10 shadow-2xl shadow-purple-950/30 border border-slate-100 relative overflow-hidden transition-all duration-500 hover:shadow-purple-900/20">
            <!-- Decorative Top Gradient Line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-gold to-primary"></div>

            <!-- Prominent Large Logo Strip with Hover Micro-Animations -->
            <div class="mb-8 flex items-center justify-center gap-4 sm:gap-6 rounded-2xl bg-slate-50/70 p-4 border border-slate-100 shadow-xs">
                <img 
                    src="/assets/logo/logo-pipmarsi.png" 
                    alt="PIP MARSI" 
                    class="h-10 sm:h-14 w-auto object-contain transition-all duration-300 hover:scale-108 filter drop-shadow-xs" 
                />
                <div class="h-8 w-px bg-slate-200"></div>
                <img 
                    src="/assets/logo/logo-umsura.png" 
                    alt="UMSURA" 
                    class="h-10 sm:h-14 w-auto object-contain transition-all duration-300 hover:scale-108 filter drop-shadow-xs" 
                />
                <div class="h-8 w-px bg-slate-200"></div>
                <img 
                    src="/assets/logo/logo-ub.png" 
                    alt="Universitas Brawijaya" 
                    class="h-10 sm:h-14 w-auto object-contain transition-all duration-300 hover:scale-108 filter drop-shadow-xs" 
                />
            </div>

            <!-- Heading -->
            <div class="mb-7 text-center">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-1">Welcome Back</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Sign in to access your ICHA 2026 conference portal.</p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="mb-6 flex items-center gap-3 rounded-2xl bg-purple-50 border border-purple-200 p-4 text-sm font-medium text-purple-900 animate-bounce-short"
            >
                <svg class="w-5 h-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ status }}
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email Input with Icon -->
                <div class="space-y-1.5">
                    <InputLabel for="email" value="Email Address" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/50 py-3.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition-all duration-300 hover:border-slate-300 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15 shadow-xs"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="your.email@institution.ac.id"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- Password Input with Icon & Show/Hide Toggle -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <InputLabel for="password" value="Password" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-bold text-primary hover:text-primary-dark transition-colors hover:underline"
                        >
                            Forgot password?
                        </Link>
                    </div>
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50/50 py-3.5 pl-10 pr-12 text-sm text-slate-900 placeholder-slate-400 transition-all duration-300 hover:border-slate-300 focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15 shadow-xs"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />
                        <!-- Toggle Button -->
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-2 right-2 flex items-center justify-center w-8 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer"
                            title="Toggle password visibility"
                        >
                            <svg v-if="!showPassword" class="w-4 h-4 transition-transform hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else class="w-4 h-4 transition-transform hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded-lg border-slate-300 text-primary focus:ring-primary/20 transition-colors" />
                        <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-900 transition-colors">Remember me on this device</span>
                    </label>
                </div>

                <!-- Submit Button with Shimmer & Animated Spinner -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full relative group overflow-hidden rounded-2xl bg-gradient-to-r from-primary via-purple-900 to-primary-dark py-3.5 px-6 text-sm font-black tracking-wide text-white shadow-lg shadow-primary/25 transition-all duration-300 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                    >
                        <!-- Button Shimmer Highlight -->
                        <span class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                        
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin text-gold" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>

                        <span>{{ form.processing ? 'Signing In to Portal...' : 'Sign In to Portal' }}</span>

                        <svg v-if="!form.processing" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>

                <!-- Divider -->
                <div class="relative py-2">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-slate-400 font-bold uppercase tracking-wider">or</span>
                    </div>
                </div>

                <!-- Register Link Button -->
                <div class="text-center">
                    <p class="text-xs text-slate-500 font-medium mb-3">Don't have an ICHA 2026 account yet?</p>
                    <Link 
                        :href="route('register')" 
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white py-3 px-4 text-xs font-bold text-slate-800 shadow-2xs transition-all duration-300 hover:bg-slate-50 hover:border-slate-300 hover:text-primary"
                    >
                        Create New Participant Account &rarr;
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(24px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-card {
    animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes bounceShort {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.animate-bounce-short {
    animation: bounceShort 2s ease-in-out infinite;
}
</style>
