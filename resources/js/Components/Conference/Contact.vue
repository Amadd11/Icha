<script setup>
import SectionHeading from "@/Components/UI/SectionHeading.vue";
</script>

<template>
    <section id="contact" class="bg-slate-50 px-5 py-20 md:px-10 md:py-28">
        <div class="mx-auto max-w-6xl space-y-12">
            <SectionHeading
                eyebrow="Get In Touch"
                title="Contact & Support Center"
                description="Have questions regarding registration, abstract submission, or partnership opportunities? Reach out to our committee."
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Email Support -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-7 shadow-xs hover:shadow-md transition text-center space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-primary flex items-center justify-center mx-auto font-bold">
                        <span class="material-symbols-outlined text-[24px]">mail</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Email Secretariat</h3>
                        <p class="text-xs text-slate-500 mt-1">Official conference email inquiries</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100 space-y-1 text-xs font-bold text-primary">
                        <a href="mailto:conference.icha10@gmail.com" class="block hover:underline">conference.icha10@gmail.com</a>
                    </div>
                </div>

                <!-- Card 2: WhatsApp Hotline -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-7 shadow-xs hover:shadow-md transition text-center space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto font-bold">
                        <span class="material-symbols-outlined text-[24px]">chat</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">WhatsApp Helpdesk</h3>
                        <p class="text-xs text-slate-500 mt-1">Fast response for participant assistance</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-xs font-bold text-slate-800">
                        <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-700 hover:underline">
                            <span>+62 812-3456-7890</span>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-extrabold">Online</span>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Organizer & Host -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-7 shadow-xs hover:shadow-md transition text-center space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto font-bold">
                        <span class="material-symbols-outlined text-[24px]">account_balance</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Host Institutions</h3>
                        <p class="text-xs text-slate-500 mt-1">Organizing committee & partners</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-xs font-semibold text-slate-700 leading-relaxed">
                        Organized by <strong>PIPMARSI</strong> in collaboration with <strong>UMSURA</strong> & <strong>Universitas Brawijaya</strong>.
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
