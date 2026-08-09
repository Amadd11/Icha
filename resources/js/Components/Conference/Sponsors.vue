<script setup>
import SectionHeading from "@/Components/UI/SectionHeading.vue";

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
</script>

<template>
    <section id="sponsors" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div v-if="props.sponsors && props.sponsors.length" class="mt-12 flex flex-wrap items-center justify-center gap-8">
                <div
                    v-for="sponsor in props.sponsors"
                    :key="sponsor.id"
                    class="flex h-20 w-40 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:shadow-md"
                >
                    <img
                        v-if="sponsor.logo"
                        :src="'/storage/' + sponsor.logo"
                        :alt="sponsor.name"
                        class="max-h-full max-w-full object-contain"
                    />
                    <span v-else class="text-sm font-semibold text-slate-600">{{ sponsor.name }}</span>
                </div>
            </div>

            <div v-else class="mt-12 rounded-2xl bg-slate-50 p-8 text-center border border-slate-100">
                <p class="text-slate-500">Interested in becoming a sponsor? Contact our organizing committee.</p>
            </div>
        </div>
    </section>
</template>
