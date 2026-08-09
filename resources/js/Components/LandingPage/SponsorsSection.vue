<script setup>
import SectionHeading from "@/Components/LandingPage/SectionHeading.vue";

const props = defineProps({
    eyebrow: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        required: true,
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

const tierBadge = (tier) => ({
    title:     'bg-sidebar text-white',
    platinum:  'bg-slate-200 text-slate-700',
    gold:      'bg-gold text-sidebar',
    silver:    'bg-slate-100 text-slate-500',
    bronze:    'bg-amber-100 text-amber-700',
    exhibitor: 'bg-primary/10 text-primary',
}[tier] ?? 'bg-slate-100 text-slate-600');
</script>

<template>
    <section id="sponsors" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <!-- Dynamic Sponsors Grid -->
            <div v-if="props.sponsors && props.sponsors.length > 0" class="mt-12 flex flex-wrap items-center justify-center gap-6">
                <a
                    v-for="s in props.sponsors"
                    :key="s.id"
                    :href="s.website ?? '#'"
                    :target="s.website ? '_blank' : '_self'"
                    class="fade-in flex flex-col items-center justify-center rounded-2xl border border-slate-100 bg-slate-50/50 p-6 shadow-sm transition-all hover:bg-white hover:shadow-md"
                >
                    <div class="mb-3 flex h-16 w-32 items-center justify-center">
                        <img v-if="s.logo" :src="'/storage/' + s.logo" :alt="s.name" class="max-h-full max-w-full object-contain" />
                        <span v-else class="text-base font-extrabold text-slate-800">{{ s.name }}</span>
                    </div>
                    <span :class="['rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest', tierBadge(s.tier)]">
                        {{ s.tier }} Sponsor
                    </span>
                </a>
            </div>

            <!-- Placeholder -->
            <div v-else class="mt-10 text-center text-sm text-slate-400">
                Sponsors will be updated soon.
            </div>
        </div>
    </section>
</template>
