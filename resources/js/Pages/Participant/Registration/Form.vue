<script setup>
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeConference: Object,
    existingRegistration: Object,
    registrationTypes: Array,
    userProfile: Object,
});

const form = useForm({
    registration_type_id: '',
    currency:             'IDR',
    notes:                '',
});

const selectedType = computed(() => {
    return props.registrationTypes?.find(t => t.id === form.registration_type_id);
});

function submit() {
    form.post(route('participant.registration.store'));
}
</script>

<template>
    <Head title="Conference Ticket Registration" />
    <ParticipantLayout>
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">Conference Registration</h1>
            <p class="text-xs text-slate-500">Select your registration category for {{ activeConference?.title || 'the conference' }}</p>
        </div>

        <!-- Profile Warning -->
        <div v-if="!userProfile" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <p class="font-bold">Profile Incomplete</p>
                    <p class="text-xs text-amber-700">Please complete your profile details before registering.</p>
                </div>
                <Link :href="route('participant.profile.edit')" class="ml-auto rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">
                    Complete Profile
                </Link>
            </div>
        </div>

        <!-- Existing Registration Status Card -->
        <div v-if="existingRegistration" class="mb-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Invoice Number</span>
                    <h3 class="text-xl font-extrabold text-primary">{{ existingRegistration.invoice_number }}</h3>
                </div>
                <span :class="[
                    'rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-wider',
                    existingRegistration.status === 'paid' ? 'bg-green-100 text-green-700' :
                    existingRegistration.status === 'waiting_verification' ? 'bg-amber-100 text-amber-700' :
                    existingRegistration.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'
                ]">
                    {{ existingRegistration.status.replace('_', ' ') }}
                </span>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400">Selected Category</span>
                    <p class="font-semibold text-slate-800">{{ existingRegistration.registration_type?.name }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400">Total Amount</span>
                    <p class="font-extrabold text-slate-800">
                        {{ existingRegistration.currency }} {{ Number(existingRegistration.amount).toLocaleString() }}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-slate-400">Early Bird Rate</span>
                    <p class="font-semibold text-slate-800">{{ existingRegistration.is_early_bird ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <Link :href="route('participant.payment.index')" class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark">
                    View Payment / Upload Proof →
                </Link>
            </div>
        </div>

        <!-- New Registration Form -->
        <div v-else class="max-w-3xl">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 text-base font-bold text-slate-800">{{ activeConference?.title }}</h2>
                    <p class="mb-6 text-xs text-slate-500">{{ activeConference?.theme }}</p>

                    <!-- Select Registration Type -->
                    <div class="mb-6">
                        <label class="mb-3 block text-sm font-bold text-slate-700">Select Registration Category <span class="text-red-400">*</span></label>
                        <div class="grid gap-3">
                            <label
                                v-for="type in registrationTypes"
                                :key="type.id"
                                :class="[
                                    'flex cursor-pointer items-center justify-between rounded-xl border p-4 transition-all',
                                    form.registration_type_id === type.id ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 hover:border-slate-300'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" v-model="form.registration_type_id" :value="type.id" class="text-primary focus:ring-primary" required />
                                    <div>
                                        <p class="font-bold text-slate-800">{{ type.name }}</p>
                                        <p class="text-xs text-slate-500 capitalize">{{ type.category.replace('_', ' ') }} · {{ type.role_type }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-extrabold text-primary">IDR {{ Number(type.early_bird_price_idr).toLocaleString() }}</p>
                                    <p v-if="type.early_bird_price_usd > 0" class="text-xs font-semibold text-slate-400">USD ${{ type.early_bird_price_usd }}</p>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.registration_type_id" class="mt-1 text-xs text-red-500">{{ form.errors.registration_type_id }}</p>
                    </div>

                    <!-- Currency Selection -->
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Payment Currency</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="radio" v-model="form.currency" value="IDR" class="text-primary focus:ring-primary" />
                                IDR (Rupiah)
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="radio" v-model="form.currency" value="USD" class="text-primary focus:ring-primary" />
                                USD (US Dollar)
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Special Notes (Optional)</label>
                        <textarea v-model="form.notes" rows="2" class="admin-input" placeholder="Dietary restrictions, accessibility requests, etc."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing || !userProfile"
                        class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50"
                    >
                        {{ form.processing ? 'Submitting...' : 'Proceed to Payment →' }}
                    </button>
                </div>
            </form>
        </div>
    </ParticipantLayout>
</template>
