<script setup>
import { Link } from "@inertiajs/vue3";

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
}
</script>

<template>
    <footer class="relative bg-sidebar text-white pt-16 pb-8 border-t border-purple-900/60 overflow-hidden font-sans">
        <!-- Ambient background lighting -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-gold/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-5 md:px-10 relative z-10">
            <!-- Main Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-purple-800/50">
                
                <!-- Col 1: Brand & Logo -->
                <div class="space-y-5">
                    <div class="flex items-center gap-3 bg-white/10 p-3 rounded-2xl border border-white/10 w-fit">
                        <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-10 w-auto object-contain" />
                        <div class="w-px h-8 bg-white/20"></div>
                        <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-10 w-auto object-contain" />
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-white tracking-tight">ICHA 2026</h3>
                        <p class="text-xs font-semibold text-gold mt-0.5">International Conference on Healthcare Administration</p>
                    </div>

                    <p class="text-xs text-purple-200/80 leading-relaxed">
                        Advancing healthcare quality, leadership, and digital innovation through international academic collaboration and research excellence.
                    </p>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-4">
                    <h4 class="text-xs font-extrabold uppercase tracking-widest text-gold">Quick Links</h4>
                    <ul class="space-y-2.5 text-xs text-purple-100/90 font-medium">
                        <li>
                            <a href="/#about" class="hover:text-gold transition-colors flex items-center gap-1.5">
                                <span class="text-gold">›</span> About Conference
                            </a>
                        </li>
                        <li>
                            <a href="/#tracks" class="hover:text-gold transition-colors flex items-center gap-1.5">
                                <span class="text-gold">›</span> Scientific Tracks
                            </a>
                        </li>
                        <li>
                            <a href="/#timeline" class="hover:text-gold transition-colors flex items-center gap-1.5">
                                <span class="text-gold">›</span> Schedule & Timeline
                            </a>
                        </li>
                        <li>
                            <a href="/#speakers" class="hover:text-gold transition-colors flex items-center gap-1.5">
                                <span class="text-gold">›</span> Keynote Speakers
                            </a>
                        </li>
                        <li>
                            <Link :href="route('login')" class="hover:text-gold transition-colors flex items-center gap-1.5">
                                <span class="text-gold">›</span> Author Portal & Login
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Secretariat & Contact -->
                <div class="space-y-4">
                    <h4 class="text-xs font-extrabold uppercase tracking-widest text-gold">Conference Secretariat</h4>
                    <div class="space-y-3 text-xs text-purple-200/90">
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-gold shrink-0 mt-0.5">location_on</span>
                            <span>Universitas Muhammadiyah Surabaya<br />Jl. Sutorejo No.59, Surabaya, Jawa Timur</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-gold shrink-0">mail</span>
                            <a href="mailto:[EMAIL_ADDRESS]" class="hover:text-gold transition-colors font-semibold">conference.icha10@gmail.com</a>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-gold shrink-0">call</span>
                            <span class="font-semibold">+62 812-3456-7890 (Secretariat)</span>
                        </div>
                    </div>
                </div>

                <!-- Col 4: Host Organizations -->
                <div class="space-y-4">
                    <h4 class="text-xs font-extrabold uppercase tracking-widest text-gold">Organized By</h4>
                    <p class="text-xs text-purple-200/80 leading-relaxed">
                        Organized by <strong>PIPMARSI</strong> in joint collaboration with <strong>Universitas Muhammadiyah Surabaya</strong>.
                    </p>

                    <div class="pt-2">
                        <a
                            href="/#registration"
                            class="inline-flex items-center gap-2 bg-gold hover:bg-amber-400 text-slate-950 font-bold text-xs py-3 px-5 rounded-xl shadow-md transition-all"
                        >
                            Register as Participant &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-purple-300/70">
                <p>&copy; {{ new Date().getFullYear() }} International Conference on Healthcare Administration (ICHA). All rights reserved.</p>

                <button
                    @click="scrollToTop"
                    class="inline-flex items-center gap-1.5 text-purple-200 hover:text-gold transition-colors font-bold cursor-pointer"
                >
                    Back to top
                    <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                </button>
            </div>
        </div>
    </footer>
</template>
