<script setup>
import { computed } from 'vue';
import SectionHeading from '@/Components/UI/SectionHeading.vue';

const props = defineProps({
    registrationFees: Array,
    registrationTypes: Array,
});

function formatPrice(val) {
    if (!val) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

const pricingData = computed(() => {
    const types = props.registrationFees || props.registrationTypes || [];
    
    const offlineItems = types
        .filter(t => t.mode === 'offline')
        .map(t => ({
            name: t.name,
            price: formatPrice(t.price),
        }));

    const onlineItems = types
        .filter(t => t.mode === 'online')
        .map(t => ({
            name: t.name,
            price: formatPrice(t.price),
        }));

    return [
        {
            title: '1. Registrasi Peserta Offline (Onsite)',
            badge: 'Offline / On-Site',
            colorClass: 'from-sidebar to-primary',
            items: offlineItems.length > 0 ? offlineItems : [
                { name: 'Presenter Nasional Offline', price: 'Rp 1.500.000' },
                { name: 'Presenter Mahasiswa Offline', price: 'Rp 500.000' },
                { name: 'Peserta Non-Presenter Offline', price: 'Rp 750.000' },
                { name: 'Presenter Internasional Offline', price: 'Rp 2.400.000' },
            ]
        },
        {
            title: '2. Registrasi Peserta Online (Virtual)',
            badge: 'Online / Virtual',
            colorClass: 'from-primary-dark to-purple-900',
            items: onlineItems.length > 0 ? onlineItems : [
                { name: 'Presenter Nasional Online', price: 'Rp 1.000.000' },
                { name: 'Presenter Mahasiswa Online', price: 'Rp 250.000' },
                { name: 'Peserta Non-Presenter Online (Webinar Only)', price: 'Rp 250.000' },
                { name: 'Presenter Internasional Online', price: 'Rp 1.500.000' },
            ]
        }
    ];
});
</script>

<template>
    <section id="registration" class="relative bg-slate-50 py-10 lg:py-16">
        <div class="container mx-auto px-6 md:px-12 xl:px-24">
            <SectionHeading
                eyebrow="Pricing & Packages"
                title="Registration Fees"
                description="Choose the registration package that fits your participation mode (Offline / Online)."
                align="center"
            />

            <div class="mt-12 grid gap-8 lg:grid-cols-2 lg:gap-12">
                <div 
                    v-for="(group, index) in pricingData" 
                    :key="index"
                    class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col justify-between"
                >
                    <!-- Card Header -->
                    <div :class="['bg-gradient-to-r p-6 text-white flex items-center justify-between', group.colorClass]">
                        <h3 class="text-lg sm:text-xl font-extrabold tracking-tight">{{ group.title }}</h3>
                        <span class="rounded-full bg-white/20 text-white border border-white/30 px-3 py-0.5 text-xs font-bold">
                            {{ group.badge }}
                        </span>
                    </div>

                    <!-- Table Container -->
                    <div class="p-6 sm:p-8 flex-1">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b-2 border-slate-100 text-xs uppercase text-slate-400 font-bold">
                                        <th class="py-3 font-semibold text-slate-500">Paket Peserta</th>
                                        <th class="py-3 text-right font-bold text-primary">Biaya (Price)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr 
                                        v-for="(item, itemIndex) in group.items" 
                                        :key="itemIndex"
                                        class="hover:bg-slate-50/60 transition-colors"
                                    >
                                        <td class="py-4 pr-4 font-bold text-slate-800">
                                            {{ item.name }}
                                        </td>
                                        <td class="py-4 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                            {{ item.price }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="mt-14 text-center">
                <a
                    href="/register"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-gold to-gold-dark px-8 py-4 text-sm font-bold uppercase tracking-widest text-sidebar shadow-lg shadow-gold/30 transition-transform hover:scale-105"
                >
                    Register Account Now
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </section>
</template>
