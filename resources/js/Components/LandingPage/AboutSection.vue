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
    stats: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section id="about" class="bg-[#f5f7fa] px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div
                class="mt-12 grid grid-cols-1 items-center gap-12 md:grid-cols-2"
            >
                <div>
                    <h3 class="mb-4 font-serif text-2xl text-[#2d0f4f]">
                        Why ICHA 2026?
                    </h3>
                    <p class="mb-4 leading-relaxed text-[#6b7280]">
                        The International Conference on Healthcare
                        Administration 2026 brings together researchers,
                        academics, practitioners, students, and policymakers to
                        share ideas, innovations, and best practices that will
                        shape the future of healthcare systems worldwide.
                    </p>
                    <p class="mb-6 leading-relaxed text-[#6b7280]">
                        Held alongside Musyawarah Nasional PIPMARSI, this event
                        offers a unique opportunity to engage with the
                        Indonesian healthcare administration community and
                        international stakeholders in one high-impact gathering.
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-5 sm:grid-cols-3">
                        <div
                            v-for="item in props.stats"
                            :key="item.label"
                            class="fade-in rounded-xl bg-white p-5 text-center shadow-sm"
                        >
                            <div class="text-3xl font-extrabold text-[#6d28d9]">
                                {{ item.value }}
                            </div>
                            <div class="mt-1 text-xs text-[#6b7280]">
                                {{ item.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div
                        class="rounded-2xl bg-gradient-to-br from-[#2d0f4f] to-[#6d28d9] p-8 text-lg italic leading-relaxed text-white shadow-lg"
                    >
                        <strong
                            class="mb-2 block text-xs uppercase tracking-[0.3em] text-[#e9d5ff] not-italic"
                            >Conference Theme</strong
                        >
                        “Leading the Future of Healthcare Administration”
                        <br /><br />
                        <span
                            class="mt-2 block text-sm text-white/80 not-italic"
                        >
                            Transforming Education, Leadership and Innovation
                            for Sustainable Health Systems
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
