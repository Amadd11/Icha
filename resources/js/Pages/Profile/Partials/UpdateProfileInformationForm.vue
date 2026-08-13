<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;
const profile = page.props.profile || {};

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: profile.phone || '',
    institution: profile.institution || '',
    participant_category: profile.participant_category || 'non_student',
    gender: profile.gender || 'male',
    city: profile.city || '',
    country: profile.country || 'Indonesia',
});

const inputClass = 'block w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 py-2.5 px-3.5 transition hover:border-slate-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20';
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-6"
        >
            <!-- 1. Personal & Contact Info Section -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary">
                    Personal & Contact Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div class="sm:col-span-2">
                        <InputLabel for="name" value="Full Name (with degree)" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="name"
                            type="text"
                            :class="inputClass"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="E.g. Dr. Andi Sutomo, M.Ked"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="email"
                            type="email"
                            :class="inputClass"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <!-- Phone / WhatsApp -->
                    <div>
                        <InputLabel for="phone" value="Phone / WhatsApp" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="phone"
                            type="text"
                            :class="inputClass"
                            v-model="form.phone"
                            placeholder="+62 812-3456-7890"
                        />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- 2. Institution & Location Section -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary">
                    Affiliation & Location
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Institution -->
                    <div class="sm:col-span-2">
                        <InputLabel for="institution" value="Institution / Organization / University" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="institution"
                            type="text"
                            :class="inputClass"
                            v-model="form.institution"
                            placeholder="E.g. Faculty of Medicine, Universitas Muhammadiyah Surabaya"
                        />
                        <InputError class="mt-1" :message="form.errors.institution" />
                    </div>

                    <!-- Country -->
                    <div>
                        <InputLabel for="country" value="Country" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="country"
                            type="text"
                            :class="inputClass"
                            v-model="form.country"
                            required
                            placeholder="Indonesia"
                        />
                        <InputError class="mt-1" :message="form.errors.country" />
                    </div>

                    <!-- City -->
                    <div>
                        <InputLabel for="city" value="City" class="text-xs font-bold text-slate-700 mb-1" />
                        <TextInput
                            id="city"
                            type="text"
                            :class="inputClass"
                            v-model="form.city"
                            placeholder="E.g. Surabaya"
                        />
                        <InputError class="mt-1" :message="form.errors.city" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- 3. Category & Gender Selection Chips -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary">
                    Category & Demographics
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Participant Category -->
                    <div>
                        <InputLabel value="Participant Category" class="text-xs font-bold text-slate-700 mb-2" />
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="form.participant_category = 'non_student'"
                                :class="[
                                    'py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.participant_category === 'non_student'
                                        ? 'bg-purple-50 border-primary text-primary shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400'
                                ]"
                            >
                                General
                            </button>
                            <button
                                type="button"
                                @click="form.participant_category = 'student'"
                                :class="[
                                    'py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.participant_category === 'student'
                                        ? 'bg-purple-50 border-primary text-primary shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400'
                                ]"
                            >
                                Student
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.participant_category" />
                    </div>

                    <!-- Gender Select -->
                    <div>
                        <InputLabel value="Gender" class="text-xs font-bold text-slate-700 mb-2" />
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="form.gender = 'male'"
                                :class="[
                                    'py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.gender === 'male'
                                        ? 'bg-blue-50 border-blue-500 text-blue-700 shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400'
                                ]"
                            >
                                Male
                            </button>
                            <button
                                type="button"
                                @click="form.gender = 'female'"
                                :class="[
                                    'py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.gender === 'female'
                                        ? 'bg-pink-50 border-pink-500 text-pink-700 shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400'
                                ]"
                            >
                                Female
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.gender" />
                    </div>
                </div>
            </div>

            <!-- Email verification hint -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-slate-600">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-xs text-primary font-bold underline hover:text-primary-dark ml-1"
                    >
                        Click here to re-send verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-medium text-emerald-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-black text-xs px-7 py-3 shadow-md shadow-amber-500/20 transition cursor-pointer disabled:opacity-50 flex items-center justify-center"
                >
                    <span>{{ form.processing ? 'Saving Changes...' : 'Save Profile Settings' }}</span>
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs text-emerald-700 font-extrabold bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200"
                    >
                        Saved successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
