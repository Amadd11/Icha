<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import ParticipantProgress from '@/Components/Dashboard/ParticipantProgress.vue';
import StatusCard from '@/Components/Dashboard/StatusCard.vue';

const props = defineProps({
    user: Object,
    activeConference: Object,
    activeRegistration: Object,
    payment: Object,
    paymentStatus: String,
    abstract: Object,
    fullPaper: Object,
    hasCertificate: Boolean,
    stages: Array,
    nextAction: Object,
    nearestDeadline: Object,
});
</script>

<template>
    <Head title="Participant Workspace & Progress" />

    <ParticipantLayout>
        <div class="space-y-6">
            <!-- Welcome Header Banner -->
            <div class="rounded-2xl bg-gradient-to-r from-sidebar via-purple-900 to-purple-950 p-6 md:p-8 text-white shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4 border border-purple-800/40">
                <div>
                    <span class="inline-block rounded-md bg-gold/20 px-2.5 py-0.5 text-[11px] font-bold text-gold border border-gold/30 mb-2 uppercase tracking-wider">
                        {{ props.activeConference?.title || 'ICHA 2026' }} Participant Portal
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                        Welcome back, {{ props.user?.name || 'Participant' }}!
                    </h1>
                    <p class="mt-1 text-xs md:text-sm text-purple-100/90 max-w-xl">
                        {{ props.activeConference?.theme || 'Healthcare Administration for a Sustainable Future' }}
                    </p>
                </div>

                <div v-if="props.nearestDeadline" class="shrink-0 rounded-xl bg-white/10 p-4 border border-white/10 text-center md:text-right backdrop-blur-sm">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-purple-200">Nearest Deadline</span>
                    <span class="block text-sm font-bold text-white mt-0.5">{{ props.nearestDeadline.title }}</span>
                    <span class="block text-xs text-gold font-bold mt-0.5">{{ props.nearestDeadline.date }}</span>
                </div>
            </div>

            <!-- Next Action Required Banner -->
            <div v-if="props.nextAction" class="rounded-2xl border border-purple-200 bg-purple-50/70 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sidebar text-gold font-black text-sm shadow-2xs">
                        ➜
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-purple-950">Next Action: {{ props.nextAction.title }}</h3>
                        <p class="text-xs text-purple-800 mt-0.5">{{ props.nextAction.description }}</p>
                    </div>
                </div>

                <Link
                    v-if="props.nextAction.url"
                    :href="props.nextAction.url"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gold hover:bg-amber-400 px-5 py-2.5 text-xs font-bold text-slate-950 transition cursor-pointer"
                >
                    {{ props.nextAction.button_label }} →
                </Link>
            </div>

            <!-- Sequential Journey Stage Tracker -->
            <ParticipantProgress :stages="props.stages" />

            <!-- Detailed Status Grid -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Detailed Submission & Registration Status</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <StatusCard
                        title="Registration Category"
                        :status="props.activeRegistration ? 'Registered' : 'Not Registered'"
                        :description="props.activeRegistration ? ('Category: ' + (props.activeRegistration.registration_type?.name || 'Standard')) : 'Please complete conference registration.'"
                        :variant="props.activeRegistration ? 'success' : 'default'"
                    />
                    <StatusCard
                        title="Payment Receipt"
                        :status="props.paymentStatus || 'unpaid'"
                        :description="props.paymentStatus === 'verified' ? 'Payment verified & confirmed by Admin' : (props.paymentStatus === 'pending' ? 'Verification in progress by Admin' : 'Upload payment receipt to verify')"
                        :variant="props.paymentStatus === 'verified' ? 'success' : (props.paymentStatus === 'pending' ? 'warning' : 'default')"
                    />
                    <StatusCard
                        title="Abstract Submission"
                        :status="props.abstract ? props.abstract.status.replace('_', ' ') : 'not submitted'"
                        :description="props.abstract ? ('Code: ' + props.abstract.abstract_code) : 'Call for Abstract is open'"
                        :variant="props.abstract?.status === 'accepted' ? 'success' : (props.abstract ? 'warning' : 'default')"
                    />
                    <StatusCard
                        title="Full Paper"
                        :status="props.fullPaper ? props.fullPaper.status.replace('_', ' ') : 'not submitted'"
                        :description="props.fullPaper ? ('Code: ' + props.fullPaper.paper_code) : (props.abstract?.status === 'accepted' ? 'Ready to submit full paper' : 'Requires accepted abstract')"
                        :variant="props.fullPaper?.status === 'accepted' ? 'success' : (props.fullPaper ? 'warning' : 'default')"
                    />
                    <StatusCard
                        title="Presentation Status"
                        :status="props.abstract?.status === 'accepted' ? 'eligible' : 'pending'"
                        :description="props.abstract?.status === 'accepted' ? 'Author Presentation Eligible' : 'Schedule to be announced post abstract acceptance'"
                        :variant="props.abstract?.status === 'accepted' ? 'success' : 'default'"
                    />
                    <StatusCard
                        title="E-Certificate"
                        :status="props.hasCertificate ? 'issued' : 'locked'"
                        :description="props.hasCertificate ? 'Verified E-Certificate Ready to Download' : 'Available post-payment or presentation'"
                        :variant="props.hasCertificate ? 'success' : 'default'"
                    />
                </div>
            </div>
        </div>
    </ParticipantLayout>
</template>
