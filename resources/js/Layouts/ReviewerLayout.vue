<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const isMobileOpen = ref(false);
const page = usePage();

const navigationGroups = [
    {
        name: 'PROFILE',
        items: [
            { name: 'My Profile', routeName: 'profile.edit', hash: '#profile-info', icon: 'person' },
            { name: 'Change Password', routeName: 'profile.edit', hash: '#change-password', icon: 'key' },
        ]
    },
    {
        name: 'SUBMISSION',
        items: [
            { name: 'Review', routeName: 'reviewer.dashboard', icon: 'folder' },
        ]
    }
];

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col md:flex-row">
        <!-- Sidebar Desktop (Rounded & Purple/Gold Theme) -->
        <aside class="hidden w-80 shrink-0 bg-sidebar text-white md:flex flex-col md:rounded-r-[2rem] shadow-2xl border-r border-purple-900/50 sticky top-0 h-screen overflow-hidden">
            <!-- Brand Header -->
            <div class="flex h-18 items-center px-6 border-b border-purple-800/50 gap-3">
                <Link href="/" class="flex items-center gap-3 transition hover:opacity-95">
                    <div class="flex items-center gap-1.5 p-1 rounded-md shrink-0">
                        <img
                            src="/assets/logo/logo-pipmarsi.png"
                            alt="PIP MARSI"
                            class="h-8 w-auto object-contain"
                        />
                        <img
                            src="/assets/logo/logo-umsura.png"
                            alt="UMSURA"
                            class="h-8 w-auto object-contain"
                        />
                    </div>
                    <div>
                        <span class="font-extrabold text-sm tracking-tight text-white block leading-tight">Reviewer Portal</span>
                        <span class="text-[11px] text-gold font-semibold block mt-0.5">ICHA 2026</span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 space-y-6 px-4 py-6 overflow-y-auto">
                <div v-for="group in navigationGroups" :key="group.name" class="space-y-3">
                    <h3 class="px-2 text-[10px] font-black text-purple-200/50 uppercase tracking-widest">{{ group.name }}</h3>
                    <div class="space-y-1">
                        <Link
                            v-for="item in group.items"
                            :key="item.name"
                            :href="route(item.routeName) + (item.hash || '')"
                            class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                            :class="route().current(item.routeName) && (!item.hash || $page.url.includes(item.hash)) ? 'bg-gold text-slate-950 font-bold shadow-md' : 'text-purple-100/90 hover:bg-purple-800/60 hover:text-gold'"
                        >
                            <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User Footer -->
            <div class="p-5 border-t border-purple-800/50 flex flex-col gap-3 bg-purple-950/40">
                <!-- If reviewer is also an admin, they can switch back to participant/admin from layout potentially, but keep it simple -->
                <div class="flex items-center justify-between">
                    <div class="truncate mr-3">
                        <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-purple-200 truncate">{{ $page.props.auth.user.email }}</p>
                    </div>
                    <button @click="logout" class="text-sm font-bold text-gold hover:text-yellow-300 transition shrink-0 cursor-pointer">Logout</button>
                </div>
            </div>
        </aside>

        <!-- Mobile Header Bar -->
        <div class="md:hidden flex items-center justify-between bg-sidebar px-4 py-3 text-white sticky top-0 z-40 shadow-md">
            <Link href="/" class="font-bold tracking-tight text-gold">Reviewer Portal</Link>
            <button @click="isMobileOpen = !isMobileOpen" class="text-white hover:text-gold transition">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="!isMobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Overlay -->
        <div v-if="isMobileOpen" class="md:hidden fixed inset-0 z-30 bg-sidebar/95 backdrop-blur-sm flex flex-col pt-16">
            <nav class="flex-1 px-4 py-6 flex flex-col gap-2 overflow-y-auto">
                    <div v-for="group in navigationGroups" :key="group.name" class="space-y-3">
                        <h3 class="px-2 text-[10px] font-black text-purple-200/50 uppercase tracking-widest">{{ group.name }}</h3>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items"
                                :key="item.name"
                                :href="route(item.routeName) + (item.hash || '')"
                                @click="isMobileOpen = false"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200"
                                :class="route().current(item.routeName) && (!item.hash || $page.url.includes(item.hash)) ? 'bg-gold text-slate-950 font-bold shadow-md' : 'text-purple-100/90 hover:bg-purple-800/60 hover:text-gold'"
                            >
                                <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                                <span>{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                <button @click="logout" class="block rounded-xl px-4 py-3 font-semibold text-left text-red-400 hover:bg-red-500/10">
                    Logout
                </button>
            </nav>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-h-screen overflow-x-hidden bg-slate-50 relative">
            <!-- Background pattern for main area -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" :style="{ backgroundImage: 'url(\'data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Ccircle cx=\'2\' cy=\'2\' r=\'2\' fill=\'%234c1d95\'/%3E%3C/svg%3E\')' }"></div>
            
            <div class="relative z-10 flex-1 p-4 md:p-8 lg:p-12">
                <slot />
            </div>
        </main>
    </div>
</template>
