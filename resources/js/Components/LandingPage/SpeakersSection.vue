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
    speakers: {
        type: Array,
        default: () => [],
    },
});

const typeColor = (type) => ({
    keynote: 'bg-primary text-white',
    plenary: 'bg-gold text-sidebar',
    invited: 'bg-slate-200 text-slate-700',
}[type] ?? 'bg-slate-200 text-slate-700');
</script>

<template>
    <section id="speakers" class="bg-white px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <!-- Dynamic Speakers Grid -->
            <div v-if="props.speakers && props.speakers.length > 0" class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="s in props.speakers"
                    :key="s.id"
                    class="fade-in flex flex-col items-center rounded-3xl border border-slate-100 bg-white p-6 text-center shadow-lg shadow-slate-200/50 transition-all hover:-translate-y-2"
                >
                    <div class="mb-4 h-28 w-28 overflow-hidden rounded-full border-4 border-primary/20 bg-slate-100 shadow-md">
                        <img v-if="s.photo" :src="'/storage/' + s.photo" :alt="s.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-3xl font-extrabold text-primary">
                            {{ s.name.charAt(0) }}
                        </div>
                    </div>
                    <span :class="['mb-2 rounded-full px-3 py-0.5 text-[10px] font-black uppercase tracking-widest', typeColor(s.type)]">
                        {{ s.type }} Speaker
                    </span>
                    <h4 class="text-lg font-bold text-slate-800">{{ s.name }}</h4>
                    <p class="text-xs font-semibold text-primary">{{ s.title }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ s.institution }} · {{ s.country }}</p>
                    <p v-if="s.bio" class="mt-3 text-xs text-slate-400 line-clamp-3">{{ s.bio }}</p>
                </div>
            </div>

            <!-- Placeholder if empty -->
            <div
                v-else
                class="fade-in mx-auto mt-10 max-w-3xl rounded-3xl border-2 border-dashed border-slate-300 bg-[#f5f7fa] p-16 text-center"
            >
                <div class="mb-4 text-5xl">🎤</div>
                <strong class="mb-2 block text-xl text-[#2d0f4f]">Speakers to be announced</strong>
                <p class="text-sm leading-relaxed text-[#6b7280]">
                    We are confirming keynote and invited speakers. The full speaker lineup will be published in the second announcement.
                </p>
            </div>
        </div>
    </section>
</template>
