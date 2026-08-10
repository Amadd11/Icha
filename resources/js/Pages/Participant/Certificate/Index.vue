<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';

const props = defineProps({
    certificates: Array,
    isEligible: Boolean,
    registrationStatus: String,
    activeConference: Object,
});
</script>

<template>
    <Head title="My E-Certificates - ICHA 2026" />

    <ParticipantLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span class="material-symbols-outlined text-gold">workspace_premium</span>
                        My E-Certificates
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">Download official verified electronic certificates for attendance and paper presentations.</p>
                </div>
                <div v-if="props.isEligible" class="px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    Certificates Issued
                </div>
            </div>

            <!-- Ineligible Notice -->
            <div v-if="!props.isEligible" class="bg-amber-50 rounded-3xl p-6 border border-amber-200 text-amber-950 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-amber-700 text-2xl">lock</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Certificate Download Locked</h3>
                        <p class="text-xs text-amber-800/80 mt-0.5">E-Certificates are unlocked upon completing paid conference registration or having an accepted presentation abstract.</p>
                    </div>
                </div>
                <Link
                    :href="route('participant.payment.index')"
                    class="px-5 py-2.5 rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition whitespace-nowrap"
                >
                    Check Payment Status &rarr;
                </Link>
            </div>

            <!-- Certificates List -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="cert in props.certificates" :key="cert.id" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-6 relative overflow-hidden group hover:shadow-xl transition-all">
                    
                    <div class="absolute -right-8 -top-8 w-28 h-28 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>

                    <div class="relative z-10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-[10px] font-extrabold uppercase tracking-wider">
                                {{ cert.type }}
                            </span>
                            <span class="font-mono text-[11px] font-bold text-slate-400">{{ cert.certificate_number }}</span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 leading-snug">{{ cert.role_title }}</h3>
                        <p class="text-xs text-slate-500">{{ cert.conference?.title || 'ICHA 2026 Conference' }}</p>
                    </div>

                    <div class="relative z-10 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-medium">Issued: {{ new Date(cert.issued_at).toLocaleDateString() }}</span>
                        <a
                            :href="route('certificate.download', cert.id)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 bg-primary hover:bg-primary-dark text-white font-bold text-xs py-2 px-4 rounded-xl shadow-sm transition cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-[16px]">print</span>
                            View & Print PDF
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </ParticipantLayout>
</template>
