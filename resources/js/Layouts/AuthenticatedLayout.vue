<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value && ['super_admin', 'admin'].includes(user.value.role));

const sidebarOpen = ref(false);

const participantNav = [
    { label: 'Dashboard',    href: '/dashboard',       icon: '🏠' },
    { label: 'My Profile',   href: '/profile',         icon: '👤' },
    { label: 'Registration', href: '/my/registration', icon: '📋' },
    { label: 'Payment',      href: '/my/payment',      icon: '💳' },
    { label: 'Abstract',     href: '/my/abstract',     icon: '📄' },
    { label: 'Full Paper',   href: '/my/paper',        icon: '📝' },
    { label: 'Presentation', href: '/my/presentation', icon: '📊' },
    { label: 'Certificate',  href: '/my/certificate',  icon: '🏆' },
];

const adminNav = [
    { label: 'Dashboard',     href: '/admin/dashboard',     icon: '🏠' },
    { label: 'Conferences',   href: '/admin/conferences',   icon: '🎓' },
    { label: 'Participants',  href: '/admin/participants',   icon: '👥' },
    { label: 'Registrations', href: '/admin/registrations', icon: '📋' },
    { label: 'Payments',      href: '/admin/payments',      icon: '💳' },
    { label: 'Abstracts',     href: '/admin/abstracts',     icon: '📄' },
    { label: 'Full Papers',   href: '/admin/papers',        icon: '📝' },
    { label: 'Speakers',      href: '/admin/speakers',      icon: '🎤' },
    { label: 'Sponsors',      href: '/admin/sponsors',      icon: '🏢' },
    { label: 'Certificates',  href: '/admin/certificates',  icon: '🏆' },
];

const navItems = computed(() => isAdmin.value ? adminNav : participantNav);
</script>

<template>
    <div class="flex min-h-screen bg-slate-100 font-sans">

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-sidebar shadow-2xl transition-transform duration-300 lg:translate-x-0 lg:static lg:shadow-none',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold text-sidebar shadow-md">
                    <span class="text-sm font-black">IC</span>
                </div>
                <div>
                    <span class="block text-sm font-extrabold leading-none text-white">ICHA 2026</span>
                    <span class="block text-[10px] font-medium uppercase tracking-widest text-white/50">Surabaya</span>
                </div>
            </div>

            <!-- Role Badge -->
            <div class="px-5 pt-4 pb-2">
                <span class="inline-block rounded-full bg-gold/20 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-gold">
                    {{ user?.role?.replace('_', ' ') ?? 'Guest' }}
                </span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 pb-6">
                <ul class="space-y-0.5">
                    <li v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/70 transition-all hover:bg-white/10 hover:text-white"
                            :class="{ 'bg-white/15 !text-white': $page.url.startsWith(item.href) }"
                        >
                            <span class="w-5 text-center text-base leading-none">{{ item.icon }}</span>
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- User Info + Logout -->
            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
                        <p class="truncate text-xs text-white/50">{{ user?.email }}</p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-3 py-2 text-xs font-semibold text-white/80 transition hover:bg-white/20 hover:text-white"
                >
                    <span>→</span> Log Out
                </Link>
            </div>
        </aside>

        <!-- Sidebar Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main Content Area -->
        <div class="flex flex-1 min-w-0 flex-col">

            <!-- Top Bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-6 shadow-sm">
                <!-- Mobile hamburger -->
                <button
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Page Heading -->
                <div class="flex-1 min-w-0">
                    <slot name="header" />
                </div>

                <!-- Back to website -->
                <Link href="/" class="hidden items-center gap-1.5 text-xs font-medium text-slate-500 transition hover:text-primary sm:flex">
                    ← Back to Website
                </Link>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
