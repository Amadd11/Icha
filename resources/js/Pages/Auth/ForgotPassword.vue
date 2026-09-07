<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password - ICHA 10th" />

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
                    Account Recovery
                </span>
            </div>

            <!-- Heading -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-1">Forgot Password?</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed">
                    Enter the email address associated with your account, and we'll send you an official reset link.
                </p>
            </div>

            <!-- Status Message (Success) -->
            <div
                v-if="status"
                class="mb-6 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 border border-emerald-200"
            >
                <svg class="w-5 h-5 shrink-0 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div class="flex-1 leading-relaxed">
                    {{ status }}
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-1.5">
                    <InputLabel for="email" value="Registered Email Address" class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                    <div class="relative group">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 group-focus-within:text-primary transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
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
                            placeholder="you@example.com"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full relative group overflow-hidden rounded-2xl bg-gradient-to-r from-primary via-purple-900 to-primary-dark py-3.5 px-6 text-sm font-black tracking-wide text-white shadow-lg shadow-primary/25 transition-all duration-300 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                    >
                        <span v-if="!form.processing" class="flex items-center gap-2">
                            <span>Send Password Reset Link</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span>Sending Reset Link...</span>
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
