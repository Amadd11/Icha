<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    country: 'Indonesia',
    institution: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const inputClass = 'block w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 transition hover:border-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20';
const inputPasswordClass = 'block w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 pl-4 pr-12 text-sm text-slate-900 placeholder-slate-400 transition hover:border-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20';
const labelClass = 'text-xs font-semibold text-slate-600 mb-1';
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-xl shadow-purple-950/25 border border-white/60">
            <!-- Logo strip -->
             <div class="mb-6 flex items-center justify-center gap-4 sm:gap-6 rounded-2xl px-6 py-4">
                <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-12 sm:h-16 w-auto object-contain transition-transform hover:scale-105" />
                <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-12 sm:h-16 w-auto object-contain transition-transform hover:scale-105" />
            </div>

            <!-- Heading -->
            <div class="mb-6 text-center sm:text-left">
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Conference Registration</h1>
                <p class="mt-1 text-sm text-slate-500">Join ICHA and start your conference journey</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section: Personal Information -->
                <div class="space-y-4">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-primary">Personal Information</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="first_name" value="First Name" :class="labelClass" />
                            <TextInput
                                id="first_name"
                                type="text"
                                :class="inputClass"
                                v-model="form.first_name"
                                required
                                autofocus
                                placeholder="E.g. Andi"
                            />
                            <InputError class="mt-1.5" :message="form.errors.first_name" />
                        </div>
                        <div>
                            <InputLabel for="last_name" value="Last Name" :class="labelClass" />
                            <TextInput
                                id="last_name"
                                type="text"
                                :class="inputClass"
                                v-model="form.last_name"
                                required
                                placeholder="E.g. Sutomo"
                            />
                            <InputError class="mt-1.5" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="country" value="Country" :class="labelClass" />
                            <TextInput
                                id="country"
                                type="text"
                                :class="inputClass"
                                v-model="form.country"
                                required
                                placeholder="E.g. Indonesia"
                            />
                            <InputError class="mt-1.5" :message="form.errors.country" />
                        </div>
                        <div>
                            <InputLabel for="phone" value="Mobile Phone / WhatsApp" :class="labelClass" />
                            <TextInput
                                id="phone"
                                type="text"
                                :class="inputClass"
                                v-model="form.phone"
                                placeholder="E.g. 08123456789"
                            />
                            <InputError class="mt-1.5" :message="form.errors.phone" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="institution" value="Institution / Affiliation" :class="labelClass" />
                        <TextInput
                            id="institution"
                            type="text"
                            :class="inputClass"
                            v-model="form.institution"
                            required
                            placeholder="E.g. Faculty of Medicine, Universitas Muhammadiyah Surabaya"
                        />
                        <InputError class="mt-1.5" :message="form.errors.institution" />
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <!-- Section: Account Security -->
                <div class="space-y-4">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-primary">Account Security</h2>

                    <div>
                        <InputLabel for="email" value="Email Address" :class="labelClass" />
                        <TextInput
                            id="email"
                            type="email"
                            :class="inputClass"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="you@example.com"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Password with Toggle & Divider -->
                        <div>
                            <InputLabel for="password" value="Password" :class="labelClass" />
                            <div class="relative">
                                <TextInput
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    :class="inputPasswordClass"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Min. 8 characters"
                                />
                                <!-- Vertical Divider -->
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

                        <!-- Confirm Password with Toggle & Divider -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" :class="labelClass" />
                            <div class="relative">
                                <TextInput
                                    id="password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    :class="inputPasswordClass"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Re-enter password"
                                />
                                <!-- Vertical Divider -->
                                <span class="absolute inset-y-2 right-10 w-px bg-slate-200"></span>
                                <!-- Toggle button -->
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute inset-y-1.5 right-1.5 flex items-center justify-center w-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
                                    title="Toggle password visibility"
                                >
                                    <svg v-if="!showPasswordConfirmation" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>

                <!-- Terms hint -->
                <p class="text-xs text-slate-400 leading-relaxed">
                    By registering, you agree to our
                    <a href="#" class="text-primary hover:text-primary-dark font-medium transition-colors">Terms of Service</a>
                    and
                    <a href="#" class="text-primary hover:text-primary-dark font-medium transition-colors">Privacy Policy</a>.
                </p>

                <!-- Submit -->
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ form.processing ? 'Registering Account...' : 'Register Account' }}
                </PrimaryButton>

                <!-- Divider -->
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-slate-400 font-medium">or</span>
                    </div>
                </div>

                <!-- Login link -->
                <p class="text-center text-sm text-slate-500">
                    Already have an account?
                    <Link :href="route('login')" class="ml-1 font-semibold text-primary hover:text-primary-dark transition-colors">
                        Sign In
                    </Link>
                </p>
            </form>
        </div>
    </GuestLayout>
</template>