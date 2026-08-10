<script setup>
import { computed } from 'vue';
import SectionHeading from '@/Components/UI/SectionHeading.vue';

const props = defineProps({
    registrationTypes: Array,
});

const defaultPricingData = [
    {
        title: 'Student',
        colorClass: 'from-sidebar to-primary',
        items: [
            { category: 'International Presenter', earlyBird: 'IDR 250K / USD 17', regular: 'IDR 350K / USD 24' },
            { category: 'Local Presenter', earlyBird: 'IDR 200K', regular: 'IDR 300K' },
            { category: 'General Attendee', earlyBird: 'IDR 150K / USD 12', regular: 'IDR 200K / USD 15' },
        ]
    },
    {
        title: 'Non-Student',
        colorClass: 'from-primary-dark to-primary',
        items: [
            { category: 'International Presenter', earlyBird: 'IDR 500K / USD 33', regular: 'IDR 600K / USD 38' },
            { category: 'Local Presenter', earlyBird: 'IDR 300K', regular: 'IDR 400K' },
            { category: 'General Attendee', earlyBird: 'IDR 200K / USD 15', regular: 'IDR 300K / USD 21' },
        ]
    }
];

const pricingData = computed(() => {
    if (props.registrationTypes && props.registrationTypes.length > 0) {
        const formatPrice = (idr, usd) => {
            let str = `IDR ${Number(idr).toLocaleString()}`;
            if (Number(usd) > 0) {
                str += ` / USD $${usd}`;
            }
            return str;
        };

        const studentItems = props.registrationTypes
            .filter(t => t.category === 'student')
            .map(t => ({
                category: t.name.replace('Student - ', ''),
                earlyBird: formatPrice(t.early_bird_price_idr, t.early_bird_price_usd),
                regular: formatPrice(t.regular_price_idr, t.regular_price_usd),
            }));

        const nonStudentItems = props.registrationTypes
            .filter(t => t.category === 'non_student')
            .map(t => ({
                category: t.name.replace('Non-Student - ', ''),
                earlyBird: formatPrice(t.early_bird_price_idr, t.early_bird_price_usd),
                regular: formatPrice(t.regular_price_idr, t.regular_price_usd),
            }));

        return [
            { title: 'Student', colorClass: 'from-sidebar to-primary', items: studentItems },
            { title: 'Non-Student', colorClass: 'from-primary-dark to-primary', items: nonStudentItems },
        ];
    }
    return defaultPricingData;
});
</script>

<template>
    <section id="registration" class="relative bg-slate-50 py-10 lg:py-10">
        <div class="container mx-auto px-6 md:px-12 xl:px-24">
            <SectionHeading
                eyebrow="Pricing"
                title="Registration Fees"
                description="Secure your spot at the ICHA National & International Conference. Take advantage of early bird rates."
                align="center"
            />

            <div class="mt-16 grid gap-8 lg:grid-cols-2 lg:gap-12">
                <div 
                    v-for="(group, index) in pricingData" 
                    :key="index"
                    class="fade-in overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-200/50 border border-slate-100"
                >
                    <!-- Card Header -->
                    <div :class="['bg-gradient-to-r p-6 text-center text-white', group.colorClass]">
                        <h3 class="text-2xl font-extrabold tracking-tight">{{ group.title }}</h3>
                    </div>

                    <!-- Table Container -->
                    <div class="p-6 sm:p-8">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b-2 border-slate-100">
                                        <th class="py-4 font-semibold text-slate-500">Category</th>
                                        <th class="py-4 font-semibold text-primary">Early Bird</th>
                                        <th class="py-4 font-semibold text-slate-500">Regular</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="(item, itemIndex) in group.items" 
                                        :key="itemIndex"
                                        class="group border-b border-slate-50 transition-colors hover:bg-slate-50/50 last:border-0"
                                    >
                                        <td class="py-5 pr-4 font-medium text-slate-800 transition-colors group-hover:text-primary">
                                            {{ item.category }}
                                        </td>
                                        <td class="py-5 pr-4 font-bold text-primary">
                                            {{ item.earlyBird }}
                                        </td>
                                        <td class="py-5 font-semibold text-slate-600">
                                            {{ item.regular }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="fade-in mt-16 text-center">
                <a
                    href="/register"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-gold to-gold-dark px-8 py-4 text-sm font-bold uppercase tracking-widest text-sidebar shadow-lg shadow-gold/30 transition-transform hover:scale-105"
                >
                    Register Now
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </section>
</template>
