<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    identity_number: profile.identity_number || '',
    participant_category: profile.participant_category || 'non_student',
    gender: profile.gender || '',
    city: profile.city || '',
    country: profile.country || 'Indonesia',
    address: profile.address || '',
});
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Full Name" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email Address" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- Phone -->
                <div>
                    <InputLabel for="phone" value="Phone / WhatsApp Number" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.phone"
                        placeholder="+62 812-3456-7890"
                    />
                    <InputError class="mt-1" :message="form.errors.phone" />
                </div>

                <!-- Institution -->
                <div>
                    <InputLabel for="institution" value="Institution / Organization / University" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="institution"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.institution"
                        placeholder="e.g. Universitas Indonesia"
                    />
                    <InputError class="mt-1" :message="form.errors.institution" />
                </div>

                <!-- Identity Number -->
                <div>
                    <InputLabel for="identity_number" value="ID Number (KTP / NIM / Passport)" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="identity_number"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.identity_number"
                        placeholder="3271000000000000"
                    />
                    <InputError class="mt-1" :message="form.errors.identity_number" />
                </div>

                <!-- Category -->
                <div>
                    <InputLabel for="participant_category" value="Participant Category" class="text-xs font-bold text-slate-700" />
                    <select
                        id="participant_category"
                        v-model="form.participant_category"
                        class="mt-1 block w-full text-xs rounded-xl border-slate-300 focus:border-primary focus:ring-primary"
                    >
                        <option value="non_student">General / Non-Student / Professional</option>
                        <option value="student">Student (D3/S1/S2/S3)</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.participant_category" />
                </div>

                <!-- Gender -->
                <div>
                    <InputLabel for="gender" value="Gender" class="text-xs font-bold text-slate-700" />
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="mt-1 block w-full text-xs rounded-xl border-slate-300 focus:border-primary focus:ring-primary"
                    >
                        <option value="" disabled>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.gender" />
                </div>

                <!-- City -->
                <div>
                    <InputLabel for="city" value="City" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="city"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.city"
                        placeholder="e.g. Jakarta"
                    />
                    <InputError class="mt-1" :message="form.errors.city" />
                </div>

                <!-- Country -->
                <div class="sm:col-span-2">
                    <InputLabel for="country" value="Country" class="text-xs font-bold text-slate-700" />
                    <TextInput
                        id="country"
                        type="text"
                        class="mt-1 block w-full text-xs rounded-xl"
                        v-model="form.country"
                        placeholder="Indonesia"
                    />
                    <InputError class="mt-1" :message="form.errors.country" />
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <InputLabel for="address" value="Full Address" class="text-xs font-bold text-slate-700" />
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="2"
                        class="mt-1 block w-full text-xs rounded-xl border-slate-300 focus:border-primary focus:ring-primary resize-none"
                        placeholder="Street address..."
                    ></textarea>
                    <InputError class="mt-1" :message="form.errors.address" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-slate-600">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-xs text-primary underline hover:text-primary-dark"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-medium text-emerald-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-3 border-t border-slate-100">
                <PrimaryButton :disabled="form.processing" class="rounded-xl px-5 py-2 text-xs">Save Profile</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs text-emerald-600 font-bold flex items-center gap-1"
                    >
                        ✓ Saved successfully.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
