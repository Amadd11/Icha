<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const isMobileOpen = ref(false);

const navigation = [
    { name: 'Dashboard', routeName: 'dashboard' },
    { name: 'Registration & Payment', routeName: 'participant.registration.create' },
    { name: 'Submission', routeName: 'participant.submission.index' },
    { name: 'Certificate', routeName: 'participant.certificate.index' },
    { name: 'Profile', routeName: 'participant.profile.edit' },
];

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col md:flex-row">
        <!-- Sidebar Desktop (Rounded & Purple/Gold Theme) -->
        <aside class="hidden w-80 shrink-0 bg-sidebar text-white md:flex flex-col md:rounded-r-4xl shadow-2xl border-r border-purple-900/50 sticky top-0 h-screen overflow-hidden">
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
                        <span class="font-extrabold text-sm tracking-tight text-white block leading-tight">Participant Portal</span>
                        <span class="text-[11px] text-gold font-semibold block mt-0.5">ICHA 2026</span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 space-y-2 px-4 py-6 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.routeName)"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200"
                    :class="route().current(item.routeName) ? 'bg-gold text-slate-950 font-bold shadow-md' : 'text-purple-100/90 hover:bg-purple-800/60 hover:text-gold'"
                >
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <!-- User Footer -->
            <div class="p-5 border-t border-purple-800/50 flex items-center justify-between bg-purple-950/40">
                <div class="truncate mr-3">
                    <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                    <p class="text-xs text-purple-200 truncate">{{ $page.props.auth.user.email }}</p>
                </div>
                <button @click="logout" class="text-sm font-bold text-gold hover:text-yellow-300 transition shrink-0 cursor-pointer">Logout</button>
            </div>
        </aside>

        <!-- Mobile Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 px-5 flex items-center justify-between md:hidden sticky top-0 z-30 shadow-xs">
            <Link href="/" class="font-bold text-slate-900 text-sm">Participant Portal</Link>
            <button @click="isMobileOpen = !isMobileOpen" class="text-slate-700 font-bold text-lg">
                ☰
            </button>
        </header>

        <!-- Mobile Navigation Dropdown -->
        <div v-if="isMobileOpen" class="bg-sidebar text-white p-4 md:hidden border-b border-purple-800">
            <nav class="space-y-1.5">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.routeName)"
                    @click="isMobileOpen = false"
                    class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold"
                    :class="route().current(item.routeName) ? 'bg-gold text-slate-950 font-bold' : 'text-purple-100 hover:bg-purple-800/50 hover:text-gold'"
                >
                    {{ item.name }}
                </Link>

                <button @click="logout" class="block w-full text-left px-3.5 py-2.5 text-xs text-gold font-bold">
                    Logout
                </button>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Bar -->
            <header class="hidden md:flex h-16 bg-white border-b border-slate-200 px-6 items-center justify-between sticky top-0 z-30 shadow-xs">
                <div>
                    <span class="text-md font-bold text-black uppercase">Participant Workspace</span>
                </div>
            </header>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
