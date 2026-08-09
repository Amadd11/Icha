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
    speakers: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section id="speakers" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div v-if="props.speakers && props.speakers.length" class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="speaker in props.speakers"
                    :key="speaker.id"
                    class="fade-in group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:shadow-md"
                >
                    <div class="aspect-square w-full overflow-hidden bg-slate-100">
                        <img
                            v-if="speaker.photo"
                            :src="'/storage/' + speaker.photo"
                            :alt="speaker.name"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-4xl text-slate-300">
                            👤
                        </div>
                    </div>
                    <div class="p-5">
                        <span v-if="speaker.type" class="mb-1 block text-xs font-semibold text-primary">
                            {{ speaker.type }}
                        </span>
                        <h4 class="text-base font-bold text-slate-900">
                            {{ speaker.name }}
                        </h4>
                        <p v-if="speaker.institution" class="mt-1 text-xs text-slate-500">
                            {{ speaker.institution }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="mt-12 rounded-2xl bg-slate-50 p-8 text-center border border-slate-100">
                <p class="text-slate-500">Speaker lineup will be announced soon.</p>
            </div>
        </div>
    </section>
</template>
