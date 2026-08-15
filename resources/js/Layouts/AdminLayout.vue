<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    selectedConference: {
        type: Object,
        default: null,
    },
    availableConferences: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const isMobileMenuOpen = ref(false);
const isDesktopSidebarOpen = ref(true);

const activeConf = computed(
    () => props.selectedConference || page.props.activeConference,
);
const confList = computed(() =>
    props.availableConferences.length
        ? props.availableConferences
        : page.props.availableConferences || [],
);

const navigationGroups = [
    {
        name: "Overview",
        items: [
            { name: "Dashboard", routeName: "admin.dashboard" },
            { name: "Conferences", routeName: "admin.conferences.index" },
        ]
    },
    {
        name: "Registration & Finance",
        items: [
            { name: "Participants", routeName: "admin.registrations.index" },
            { name: "Payment Verification", routeName: "admin.payments.index" },
            { name: "Fee Packages", routeName: "admin.registration-fees.index" },
        ]
    },
    {
        name: "Peer Review System",
        items: [
            { name: "Abstracts", routeName: "admin.abstracts.index" },
            { name: "Full Papers", routeName: "admin.papers.index" },
            { name: "Scientific Tracks", routeName: "admin.categories.index" },
            { name: "Reviewers", routeName: "admin.reviewers.index" },
        ]
    },
    {
        name: "Website & Content",
        items: [
            { name: "Speakers", routeName: "admin.speakers.index" },
            { name: "Timeline & Schedule", routeName: "admin.timelines.index" },
            { name: "Sponsors & Partners", routeName: "admin.sponsors.index" },
        ]
    },
    {
        items: [
            { name: "User Management", routeName: "admin.users.index", role: "super_admin" },
        ]
    }
];

function switchConference(e) {
    const confId = e.target.value;
    router.get(
        window.location.pathname,
        { conference_id: confId },
        { preserveState: false },
    );
}

function logout() {
    router.post(route("logout"));
}
</script>

<template>
    <div
        class="min-h-screen bg-slate-100 text-slate-800 flex flex-col md:flex-row font-sans"
    >
        <!-- Sidebar Desktop (Rounded & Purple/Gold Theme) -->
        <transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="-ml-80 opacity-0"
            enter-to-class="ml-0 opacity-100"
            leave-active-class="transition-all duration-300 ease-in-out"
            leave-from-class="ml-0 opacity-100"
            leave-to-class="-ml-80 opacity-0"
        >
            <aside
                v-show="isDesktopSidebarOpen"
                class="hidden w-80 shrink-0 bg-sidebar text-white md:flex flex-col md:rounded-r-4xl shadow-2xl border-r border-purple-900/50 sticky top-0 h-screen overflow-hidden"
            >
                <!-- Brand Header -->
                <div class="flex h-18 items-center px-6 border-b border-purple-800/50 gap-3">
                    <Link href="/" class="flex items-center gap-3 transition hover:opacity-95">
                        <div class="flex items-center gap-1.5 p-1 rounded-md shrink-0">
                            <img
                                src="/assets/logo/logo-pipmarsi.png"
                                alt="PIP MARSI"
                                class="h-10 w-auto object-contain"
                            />
                            <img
                                src="/assets/logo/logo-umsura.png"
                                alt="UMSURA"
                                class="h-10 w-auto object-contain"
                            />
                        </div>
                        <div>
                            <span class="font-extrabold text-xl tracking-tight text-white block leading-tight">Admin Portal</span>
                            <span class="text-xs text-gold font-semibold block mt-0.5">ICHA 2026</span>
                        </div>
                    </Link>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 px-4 py-6 overflow-y-auto [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <div v-for="(group, idx) in navigationGroups" :key="idx" class="mb-5 last:mb-0">
                        <p v-if="group.name" class="px-3 mb-2 text-[11px] font-black uppercase tracking-wider text-purple-300/75">
                            {{ group.name }}
                        </p>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items.filter(i => !i.role || i.role === $page.props.auth.user?.role)"
                                :key="item.name"
                                :href="item.routeName ? route(item.routeName) : '#'"
                                class="flex items-center rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200"
                                :class="
                                    item.routeName && route().current(item.routeName)
                                        ? 'bg-gold text-slate-950 font-bold shadow-md'
                                        : 'text-purple-100/90 hover:bg-purple-800/60 hover:text-gold'
                                "
                            >
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- User Footer -->
                <div
                    class="p-5 border-t border-purple-800/50 flex items-center justify-between bg-purple-950/40"
                >
                    <div class="truncate mr-3">
                        <p class="text-sm font-bold text-white truncate">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-purple-200 capitalize truncate">
                            {{ $page.props.auth.user.role.replace('_', ' ') }}
                        </p>
                    </div>
                    <button
                        @click="logout"
                        class="text-sm font-bold text-gold hover:text-yellow-300 transition shrink-0 cursor-pointer"
                    >
                        Logout
                    </button>
                </div>
            </aside>
        </transition>

        <!-- Mobile Drawer -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-50 flex md:hidden bg-slate-950/80"
        >
            <div
                class="w-72 bg-sidebar text-white flex flex-col h-full p-4 border-r border-purple-800"
            >
                <div
                    class="flex items-center justify-between mb-5 pb-3 border-b border-purple-800"
                >
                    <span class="font-bold text-sm text-gold"
                        >ICHA Admin Menu</span
                    >
                    <button
                        @click="isMobileMenuOpen = false"
                        class="text-purple-200 hover:text-white"
                    >
                        ✕
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <div v-for="(group, idx) in navigationGroups" :key="idx" class="mb-5 last:mb-0">
                        <p v-if="group.name" class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-purple-300/70">
                            {{ group.name }}
                        </p>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items.filter(i => !i.role || i.role === $page.props.auth.user?.role)"
                                :key="item.name"
                                :href="item.routeName ? route(item.routeName) : '#'"
                                @click="isMobileMenuOpen = false"
                                class="block px-3 py-2 rounded-xl text-xs font-semibold"
                                :class="
                                    item.routeName && route().current(item.routeName)
                                        ? 'bg-gold text-slate-950 font-bold'
                                        : 'text-purple-100 hover:bg-purple-800/50 hover:text-gold'
                                "
                            >
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- Header Bar -->
            <header
                class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs"
            >
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger -->
                    <button
                        @click="isMobileMenuOpen = true"
                        class="md:hidden text-slate-700 font-bold text-lg hover:text-purple-600 transition"
                    >
                        ☰
                    </button>
                    <!-- Desktop Sidebar Toggle -->
                    <button
                        @click="isDesktopSidebarOpen = !isDesktopSidebarOpen"
                        class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-purple-600 transition"
                        title="Toggle Sidebar"
                    >
                        <svg v-if="isDesktopSidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Current Conference Selector -->
                    <div v-if="confList.length" class="flex items-center gap-2">
                        <span
                            class="text-xs font-bold uppercase text-slate-400 hidden sm:inline"
                            >Active Edition:</span
                        >
                        <select
                            :value="activeConf?.id"
                            @change="switchConference"
                            class="rounded-xl border-slate-300 py-1.5 text-xs font-bold text-slate-800 shadow-xs focus:border-purple-600 focus:ring-purple-600"
                        >
                            <option
                                v-for="conf in confList"
                                :key="conf.id"
                                :value="conf.id"
                            >
                                {{ conf.title }} ({{ conf.year || "Default" }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Right side: User info -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-xs font-bold text-slate-800 leading-tight">{{ $page.props.auth.user.name }}</span>
                        <span class="text-[10px] text-slate-400 capitalize leading-tight">{{ $page.props.auth.user.role?.replace('_', ' ') }}</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-sidebar flex items-center justify-center text-white text-xs font-black shadow-sm">
                        {{ $page.props.auth.user.name?.charAt(0).toUpperCase() }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
