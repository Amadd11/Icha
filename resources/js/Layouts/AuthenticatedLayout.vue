<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value && ['super_admin', 'admin'].includes(user.value.role));

const sidebarOpen = ref(false);

const participantNav = [
    { label: 'Dashboard',    href: '/dashboard' },
    { label: 'My Profile',   href: '/my/profile' },
    { label: 'Registration', href: '/my/registration' },
    { label: 'Payment',      href: '/my/payment' },
    { label: 'Abstract',     href: '/my/abstract' },
    { label: 'Full Paper',   href: '/my/paper' },
    { label: 'Presentation', href: '/my/presentation' },
    { label: 'Certificate',  href: '/my/certificate' },
];

const adminNav = [
    { label: 'Dashboard',     href: '/admin/dashboard' },
    { label: 'Conferences',   href: '/admin/conferences' },
    { label: 'Participants',  href: '/admin/participants' },
    { label: 'Registrations', href: '/admin/registrations' },
    { label: 'Payments',      href: '/admin/payments' },
    { label: 'Abstracts',     href: '/admin/abstracts' },
    { label: 'Full Papers',   href: '/admin/papers' },
    { label: 'Speakers',      href: '/admin/speakers' },
    { label: 'Sponsors',      href: '/admin/sponsors' },
    { label: 'Certificates',  href: '/admin/certificates' },
];

const navItems = computed(() => isAdmin.value ? adminNav : participantNav);
</script>

<template>
    <div class="flex min-h-screen bg-slate-50/60 font-sans text-slate-800 antialiased">

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-sidebar shadow-2xl transition-transform duration-300 lg:translate-x-0 lg:static lg:shadow-none',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo Header -->
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-6">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 p-1 rounded-md">
                        <img src="/assets/logo/logo-pipmarsi.png" alt="PIP MARSI" class="h-6 w-auto object-contain" />
                        <img src="/assets/logo/logo-umsura.png" alt="UMSURA" class="h-6 w-auto object-contain" />
                    </div>
                    <div>
                        <span class="block text-sm font-bold tracking-tight text-white">ICHA 2026</span>
                        <span class="block text-[10px] font-medium tracking-widest text-white/50 uppercase">Management</span>
                    </div>
                </div>
            </div>

            <!-- User Role Tag -->
            <div class="px-6 pt-5 pb-3">
                <span class="inline-block rounded-md bg-white/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-gold border border-white/5">
                    {{ user?.role?.replace('_', ' ') ?? 'User' }}
                </span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 pb-6">
                <ul class="space-y-1">
                    <li v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold tracking-wide transition-all"
                            :class="[
                                $page.url.startsWith(item.href)
                                    ? 'bg-gold text-sidebar shadow-sm'
                                    : 'text-white/60 hover:bg-white/10 hover:text-white'
                            ]"
                        >
                            <span
                                :class="[
                                    'h-1.5 w-1.5 rounded-full transition-colors',
                                    $page.url.startsWith(item.href) ? 'bg-sidebar' : 'bg-white/30 group-hover:bg-white/80'
                                ]"
                            />
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- User Footer & Logout -->
            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3 px-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-bold text-white border border-white/10">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-bold text-white">{{ user?.name }}</p>
                        <p class="truncate text-[10px] text-white/50">{{ user?.email }}</p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/10 bg-white/5 py-2 text-xs font-medium text-white/70 transition hover:bg-red-500/20 hover:border-red-500/40 hover:text-white"
                >
                    Sign Out
                </Link>
            </div>
        </aside>

        <!-- Sidebar Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Main Content Container -->
        <div class="flex flex-1 min-w-0 flex-col">

            <!-- Top Header -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200/80 bg-white px-6">
                <button
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 min-w-0">
                    <slot name="header" />
                </div>

                <Link href="/" class="hidden items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-primary sm:flex">
                    Public Website →
                </Link>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <ToastNotification />
    </div>
</template>
