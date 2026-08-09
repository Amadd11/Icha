<script setup>
import SectionHeading from "@/Components/UI/SectionHeading.vue";
</script>

<template>
    <section id="abstract" class="bg-slate-900 px-5 py-16 text-white md:px-10 md:py-24">
        <div class="mx-auto max-w-4xl text-center">
            <SectionHeading
                eyebrow="Call for Papers"
                title="Submit Your Abstract"
                description="Share your research, case studies, or innovative projects with global healthcare administration leaders."
            />

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a
                    href="#abstract"
                    class="rounded-xl bg-gold px-8 py-3.5 text-sm font-bold text-sidebar transition hover:bg-gold-dark"
                >
                    Submit Abstract Now
                </a>
                <a
                    href="#tracks"
                    class="rounded-xl border border-slate-700 bg-slate-800 px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                >
                    View Guidelines
                </a>
            </div>
        </div>
    </section>
</template>
