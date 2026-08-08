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
});
</script>

<template>
    <section id="venue" class="bg-[#f5f7fa] px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                :eyebrow="props.eyebrow"
                :title="props.title"
                :description="props.description"
            />

            <div
                class="mt-10 grid grid-cols-1 overflow-hidden rounded-3xl bg-white shadow-xl md:grid-cols-2"
            >
                <div class="p-10 lg:p-14">
                    <h3 class="mb-2 font-serif text-2xl text-sidebar">
                        Universitas Muhammadiyah Surabaya
                    </h3>
                    <p class="mb-6 font-semibold text-primary">
                        UMSURA · Surabaya, Indonesia
                    </p>
                    <div class="mb-4 flex items-start gap-3">
                        <span class="mt-0.5 text-lg text-primary-dark">📍</span>
                        <p class="text-sm leading-relaxed text-slate-600">
                            Surabaya, East Java, Indonesia<br />One of
                            Indonesia’s leading universities.
                        </p>
                    </div>
                    <div class="mb-4 flex items-start gap-3">
                        <span class="mt-0.5 text-lg text-primary-dark">📅</span>
                        <p class="text-sm leading-relaxed text-slate-600">
                            10 November 2026 – MUNAS PIPMARSI<br />11 November
                            2026 – ICHA 2026
                        </p>
                    </div>
                    <div class="mb-4 flex items-start gap-3">
                        <span class="mt-0.5 text-lg text-primary-dark">🏛️</span>
                        <p class="text-sm leading-relaxed text-slate-600">
                            Hosted by PIPMARSI, UMSURA & Universitas Brawijaya.
                        </p>
                    </div>
                </div>

                <div
                    class="relative flex min-h-[320px] items-center justify-center bg-gradient-to-br from-[#c8e6f7] to-[#e8f4fd] p-10"
                >
                    <img
                        src="/assets/logo/logo-umsura.png"
                        alt="UMSURA"
                        class="absolute inset-0 z-0 h-80 w-full object-contain object-center"
                    />
                    <a
                        href="https://maps.google.com/?q=Universitas+Muhammadiyah+Surabaya"
                        target="_blank"
                        class="absolute bottom-4 right-4 z-20 rounded-lg bg-primary px-5 py-2.5 text-[0.82rem] font-semibold text-white shadow-lg transition-colors hover:bg-sidebar"
                    >
                        📍 Open in Maps
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
