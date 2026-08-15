<script setup>
import SectionHeading from "@/Components/UI/SectionHeading.vue";
</script>

<template>
    <section id="venue" class="bg-slate-50 px-5 py-16 md:px-10 md:py-24">
        <div class="mx-auto max-w-7xl">
            <SectionHeading
                eyebrow="Location"
                title="Conference Venue"
                description="Universitas Muhammadiyah Surabaya (UMSURA), Surabaya, Indonesia"
            />

            <div class="mt-12 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm grid grid-cols-1 md:grid-cols-2">
                <div class="p-8 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Surabaya, Indonesia</h3>
                    <p class="text-slate-600 mb-6 leading-relaxed">
                        Surabaya is known as the City of Heroes and is the second-largest city in Indonesia.
                        A vibrant hub for healthcare, education, and commerce in East Java.
                    </p>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="text-primary font-bold">Venue:</span> Auditorium UMSURA
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-primary font-bold">Address:</span> Jl. Sutorejo No.59, Surabaya
                        </div>
                    </div>
                </div>
                <div class="h-64 md:h-auto bg-slate-200">
                    <img src="/assets/images/umsura.png" alt="Venue" loading="lazy" decoding="async" class="h-full w-full object-cover" />
                </div>
            </div>
        </div>
    </section>
</template>
