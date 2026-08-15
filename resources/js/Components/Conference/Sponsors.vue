<script setup>
import SectionHeading from "@/Components/UI/SectionHeading.vue";
import { formatStorageUrl } from "@/Utils/formatters";

const props = defineProps({
    eyebrow: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        default: "Partners & Sponsors",
    },
    description: {
        type: String,
        default: "",
    },
    sponsors: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section id="sponsors" class="bg-slate-50/50 px-5 py-16 md:px-10 md:py-24 border-t border-slate-100">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <!-- Prominent Sponsors Grid -->
            <div v-if="props.sponsors && props.sponsors.length" class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <component
                    :is="sponsor.website ? 'a' : 'div'"
                    v-for="sponsor in props.sponsors"
                    :key="sponsor.id"
                    :href="sponsor.website || undefined"
                    :target="sponsor.website ? '_blank' : undefined"
                    :rel="sponsor.website ? 'noopener noreferrer' : undefined"
                    class="group flex items-center gap-5 rounded-3xl border border-slate-200/90 bg-white p-5 md:p-6 shadow-xs transition duration-200 hover:-translate-y-1 hover:border-gold hover:shadow-xl"
                >
                    <!-- Large Logo Box -->
                    <div class="h-20 w-28 shrink-0 overflow-hidden rounded-2xl bg-slate-50 border border-slate-100 p-2.5 flex items-center justify-center shadow-2xs">
                        <img
                            v-if="sponsor.logo"
                            :src="formatStorageUrl(sponsor.logo)"
                            :alt="sponsor.name"
                            loading="lazy"
                            decoding="async"
                            class="max-h-full max-w-full object-contain transition duration-300 group-hover:scale-110"
                        />
                        <span v-else class="text-sm font-black text-slate-400">
                            {{ sponsor.name ? sponsor.name.charAt(0) : 'S' }}
                        </span>
                    </div>

                    <!-- Sponsor Name -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="font-extrabold text-slate-900 text-base md:text-lg leading-snug group-hover:text-purple-900 transition">
                                {{ sponsor.name }}
                            </h4>
                            <span v-if="sponsor.website" class="text-slate-400 font-bold text-base group-hover:text-purple-700 shrink-0 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                                &nearr;
                            </span>
                        </div>
                    </div>
                </component>
            </div>

            <!-- Empty State -->
            <div v-else class="mt-12 rounded-3xl bg-white p-10 text-center border border-slate-200/80 shadow-xs max-w-xl mx-auto">
                <p class="text-sm text-slate-500 font-medium">Interested in becoming a sponsor or partner? Contact our organizing committee.</p>
            </div>
        </div>
    </section>
</template>
