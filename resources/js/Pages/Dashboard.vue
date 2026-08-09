<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    profile: Object,
    registration: Object,
    activeConference: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isProfileDone = computed(() => !!props.profile);
const isRegistered = computed(() => !!props.registration);
const isPaid = computed(() => props.registration?.status === 'paid');
const isWaitingPayment = computed(() => props.registration?.status === 'waiting_verification');

const journeySteps = computed(() => [
    {
        num: '01',
        title: 'Participant Profile',
        subtitle: isProfileDone.value ? `${props.profile?.institution}` : 'Personal & contact information',
        status: isProfileDone.value ? 'complete' : 'action_required',
        actionText: isProfileDone.value ? 'Edit Profile' : 'Complete Profile',
        actionHref: '/my/profile',
    },
    {
        num: '02',
        title: 'Conference Ticket',
        subtitle: isRegistered.value ? `${props.registration?.registration_type?.name}` : 'Select ticket category',
        status: isRegistered.value ? 'complete' : (isProfileDone.value ? 'action_required' : 'locked'),
        actionText: isRegistered.value ? 'View Ticket' : 'Select Ticket',
        actionHref: '/my/registration',
    },
    {
        num: '03',
        title: 'Payment & Verification',
        subtitle: isPaid.value ? 'Payment Verified' : (isWaitingPayment.value ? 'Under Review' : 'Upload payment receipt'),
        status: isPaid.value ? 'complete' : (isWaitingPayment.value ? 'waiting' : (isRegistered.value ? 'action_required' : 'locked')),
        actionText: isPaid.value ? 'Invoice Details' : 'Upload Receipt',
        actionHref: '/my/payment',
    },
    {
        num: '04',
        title: 'Abstract Submission',
        subtitle: isPaid.value ? 'Submit research paper abstract' : 'Requires verified payment',
        status: 'locked',
        actionText: 'Submit Abstract',
        actionHref: '/my/abstract',
    },
    {
        num: '05',
        title: 'Full Paper Submission',
        subtitle: 'Requires accepted abstract',
        status: 'locked',
        actionText: 'Upload Paper',
        actionHref: '/my/paper',
    },
    {
        num: '06',
        title: 'Conference Certificate',
        subtitle: 'Available post-event',
        status: 'locked',
        actionText: 'Download',
        actionHref: '/my/certificate',
    },
]);

const statusBadgeClass = (status) => ({
    complete:        'bg-sidebar text-white',
    action_required: 'bg-primary/10 text-primary border border-primary/20',
    waiting:         'bg-amber-500/10 text-amber-700 border border-amber-500/20',
    locked:          'bg-slate-100 text-slate-400',
}[status] ?? 'bg-slate-100 text-slate-400');
</script>

<template>
    <Head title="Participant Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-bold tracking-tight text-slate-900">Participant Workspace</h1>
                    <p class="text-xs text-slate-500">Track your conference registration and submission milestones.</p>
                </div>
            </div>
        </template>

        <!-- Welcome Card -->
        <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="rounded-md bg-gold/20 px-2 py-0.5 text-[10px] font-bold tracking-wider text-gold-dark uppercase">
                            Participant
                        </span>
                        <span class="text-xs text-slate-400">·</span>
                        <span class="text-xs font-medium text-slate-500">{{ activeConference?.title ?? 'ICHA 2026' }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-primary-dark">Hello, {{ user?.name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ activeConference?.theme ?? 'Healthcare Administration for a Sustainable Future' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="route('participant.profile.edit')"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Edit Profile
                    </Link>
                    <Link
                        v-if="!isRegistered"
                        :href="route('participant.registration.create')"
                        class="rounded-xl bg-gold px-5 py-2 text-xs font-semibold text-sidebar transition hover:bg-gold-dark shadow-sm"
                    >
                        Register Ticket →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Journey Milestones Grid -->
        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase">Milestone Progress</h3>
                <span class="text-xs text-slate-500">6 Stages</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="step in journeySteps"
                    :key="step.num"
                    :class="[
                        'group flex flex-col justify-between rounded-2xl border p-5 transition-all bg-white shadow-sm',
                        step.status === 'complete' ? 'border-primary/20' :
                        step.status === 'action_required' ? 'border-primary/40 ring-1 ring-primary/20' :
                        step.status === 'waiting' ? 'border-amber-300' : 'border-slate-200/70 opacity-60'
                    ]"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black tracking-wider text-primary/40 font-mono">{{ step.num }}</span>
                            <span :class="['rounded-full px-2.5 py-0.5 text-[9px] font-bold tracking-wider uppercase', statusBadgeClass(step.status)]">
                                {{ step.status.replace('_', ' ') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 tracking-tight">{{ step.title }}</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ step.subtitle }}</p>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end">
                        <Link
                            v-if="step.status !== 'locked'"
                            :href="step.actionHref"
                            class="text-xs font-semibold text-primary transition hover:underline"
                        >
                            {{ step.actionText }} →
                        </Link>
                        <span v-else class="text-[10px] font-medium text-slate-400">Locked</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Summary -->
        <div v-if="registration" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-xs font-bold tracking-widest text-slate-400 uppercase">Registration Summary</h3>
            <div class="grid gap-4 sm:grid-cols-4 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">Invoice Number</span>
                    <p class="font-bold text-primary text-sm font-mono mt-0.5">{{ registration.invoice_number }}</p>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Category</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ registration.registration_type?.name }}</p>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Total Amount</span>
                    <p class="font-bold text-slate-900 mt-0.5">{{ registration.currency }} {{ Number(registration.amount).toLocaleString() }}</p>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Status</span>
                    <p :class="['font-bold uppercase tracking-wider text-[11px] mt-0.5', registration.status === 'paid' ? 'text-green-600' : 'text-amber-600']">
                        {{ registration.status.replace('_', ' ') }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
