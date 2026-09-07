<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const page = usePage();
const isMobileOpen = ref(false);
const isLogoutModalOpen = ref(false);

const navigation = computed(() => {
    const isPresenter = page.props.auth?.user?.is_presenter ?? true;

    return [
        { name: 'Dashboard', routeName: 'dashboard' },
        { name: 'Registration & Payment', routeName: 'participant.registration.create' },
        ...(isPresenter ? [{ name: 'Submission', routeName: 'participant.submission.index' }] : []),
        { name: 'Certificate', routeName: 'participant.certificate.index' },
        { name: 'Profile', routeName: 'participant.profile.edit' },
    ];
});

function confirmLogout() {
    isLogoutModalOpen.value = true;
}

function performLogout() {
    isLogoutModalOpen.value = false;
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col md:flex-row">
        <!-- Sidebar Desktop (Rounded & Purple/Gold Theme) -->
        <aside class="hidden w-80 shrink-0 bg-sidebar text-white md:flex flex-col md:rounded-r-4xl shadow-2xl border-r border-purple-900/50 sticky top-0 h-screen overflow-hidden">
            <!-- Brand Header -->
            <div class="flex h-18 items-center px-6 border-b border-purple-800/50 gap-3">
                <Link href="/" class="flex items-center gap-3 transition hover:opacity-95 group">
                    <div class="h-11 w-11 rounded-xl bg-white/10 p-1.5 shrink-0 flex items-center justify-center border border-white/10 group-hover:scale-105 transition-transform">
                        <img
                            src="/assets/logo/logo-icha.png"
                            alt="ICHA"
                            class="h-full w-full object-contain drop-shadow-sm"
                        />
                    </div>
                    <div>
                        <span class="font-extrabold text-base tracking-tight text-white block leading-tight group-hover:text-gold transition-colors">ICHA 2026</span>
                        <span class="text-[11px] text-purple-200/80 font-medium block mt-0.5">Participant Portal</span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 space-y-2 px-4 py-6 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.routeName)"
                    class="group flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-300 hover:translate-x-1"
                    :class="route().current(item.routeName) ? 'bg-gold text-slate-950 font-bold shadow-md' : 'text-purple-100/90 hover:bg-purple-800/60 hover:text-gold'"
                >
                    <span class="transition-transform duration-200 group-hover:translate-x-0.5">{{ item.name }}</span>
                    <span v-if="route().current(item.routeName)" class="text-xs text-slate-950 font-black shrink-0">●</span>
                </Link>
            </nav>

            <!-- User Footer -->
            <div class="p-5 border-t border-purple-800/50 flex items-center justify-between bg-purple-950/40">
                <div class="truncate mr-3">
                    <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                    <p class="text-xs text-purple-200 truncate">{{ $page.props.auth.user.email }}</p>
                </div>
                <button @click="confirmLogout" class="text-sm font-bold text-gold hover:text-yellow-300 transition shrink-0 cursor-pointer">Logout</button>
            </div>
        </aside>

        <!-- Mobile Header Bar (Fixed at top) -->
        <header class="h-16 bg-white border-b border-slate-200 px-5 flex items-center justify-between md:hidden fixed top-0 left-0 right-0 z-40 shadow-xs">
            <Link href="/" class="flex items-center gap-2.5">
                <div class="h-9 w-9 rounded-lg bg-sidebar p-1 shrink-0 flex items-center justify-center">
                    <img src="/assets/logo/logo-icha.png" alt="ICHA" class="h-full w-full object-contain" />
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-sm block leading-tight">ICHA 2026</span>
                    <span class="text-[10px] text-slate-500 font-medium block">Participant Portal</span>
                </div>
            </Link>
            <button
                @click="isMobileOpen = !isMobileOpen"
                class="flex flex-col justify-center items-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer gap-1"
                :aria-label="isMobileOpen ? 'Close menu' : 'Open menu'"
            >
                <span :class="['block h-0.5 w-5 bg-slate-700 transition-all duration-300', isMobileOpen ? 'rotate-45 translate-y-1.5' : '']"></span>
                <span :class="['block h-0.5 w-5 bg-slate-700 transition-all duration-300', isMobileOpen ? 'opacity-0' : '']"></span>
                <span :class="['block h-0.5 w-5 bg-slate-700 transition-all duration-300', isMobileOpen ? '-rotate-45 -translate-y-1.5' : '']"></span>
            </button>
        </header>

        <!-- Mobile Navigation Drawer Overlay -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="isMobileOpen" class="fixed top-16 inset-x-0 bottom-0 z-40 md:hidden flex flex-col">
                <!-- Backdrop -->
                <div class="fixed inset-0 top-16 bg-slate-950/70 backdrop-blur-xs" @click="isMobileOpen = false"></div>

                <!-- Menu Panel -->
                <div class="relative bg-sidebar text-white p-5 border-b border-purple-800 shadow-2xl max-h-[calc(100vh-4rem)] overflow-y-auto z-10">
                    <nav class="space-y-2">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="route(item.routeName)"
                            @click="isMobileOpen = false"
                            class="block px-4 py-3 rounded-xl text-xs font-semibold transition"
                            :class="route().current(item.routeName) ? 'bg-gold text-slate-950 font-bold shadow-sm' : 'text-purple-100 hover:bg-purple-800/50 hover:text-gold'"
                        >
                            {{ item.name }}
                        </Link>

                        <div class="pt-3 mt-2 border-t border-purple-800/60 flex items-center justify-between">
                            <div class="truncate mr-3">
                                <p class="text-xs font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                                <p class="text-[10px] text-purple-200 truncate">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <button @click="confirmLogout" class="px-3 py-1.5 rounded-lg bg-gold/20 text-gold text-xs font-bold hover:bg-gold/30 transition shrink-0 cursor-pointer">
                                Logout
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </transition>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 pt-16 md:pt-0">
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

        <!-- Minimalist Logout Confirmation Modal -->
        <div v-if="isLogoutModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 transition-all">
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 space-y-4 animate-fade-in-scale">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 border border-amber-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Sign Out Confirmation</h3>
                        <p class="text-xs text-slate-500">Are you sure you want to log out?</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    You will be signed out of your conference portal session. Any unsaved form progress may be lost.
                </p>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="isLogoutModalOpen = false"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="performLogout"
                        class="rounded-xl bg-primary hover:bg-purple-900 text-white px-4 py-2 text-xs font-bold transition cursor-pointer shadow-xs"
                    >
                        Yes, Log Out
                    </button>
                </div>
            </div>
        </div>

        <ToastNotification />
    </div>
</template>
