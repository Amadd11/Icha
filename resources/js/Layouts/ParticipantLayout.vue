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
                <Link href="/" class="flex items-center gap-3 transition hover:opacity-95">
                    <div class="flex items-center gap-1.5 p-1 rounded-md shrink-0">
                        <img
                                src="/assets/logo/logo-pipmarsi.png"
                                alt="PIP MARSI"
                                class="h-8 sm:h-9 w-auto object-contain"
                            />
                            <img
                                src="/assets/logo/logo-umsura.png"
                                alt="UMSURA"
                                class="h-8 sm:h-9 w-auto object-contain"
                            />
                            <img
                                src="/assets/logo/logo-ub.png"
                                alt="Universitas Brawijaya"
                                class="h-8 sm:h-9 w-auto object-contain"
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

                <button @click="confirmLogout" class="block w-full text-left px-3.5 py-2.5 text-xs text-gold font-bold">
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
