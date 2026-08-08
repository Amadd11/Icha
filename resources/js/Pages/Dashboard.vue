<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value && ['super_admin', 'admin'].includes(user.value.role));

const journey = [
    { label: 'Registration', icon: '📋', status: 'pending',   href: '/my/registration' },
    { label: 'Payment',      icon: '💳', status: 'pending',   href: '/my/payment' },
    { label: 'Abstract',     icon: '📄', status: 'locked',    href: '/my/abstract' },
    { label: 'Full Paper',   icon: '📝', status: 'locked',    href: '/my/paper' },
    { label: 'Certificate',  icon: '🏆', status: 'locked',    href: '/my/certificate' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-slate-800">Dashboard</h1>
        </template>

        <!-- Welcome Banner -->
        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-sidebar to-primary p-6 text-white shadow-lg">
            <p class="text-sm font-medium text-white/70">Welcome back,</p>
            <h2 class="mt-1 text-2xl font-extrabold">{{ user?.name }}</h2>
            <p class="mt-2 text-sm text-white/70">
                <span v-if="isAdmin">You are logged in as <strong class="text-gold">{{ user?.role?.replace('_', ' ') }}</strong>.</span>
                <span v-else>Track your ICHA 2026 conference journey below.</span>
            </p>
            <a
                v-if="isAdmin"
                href="/admin/dashboard"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gold px-5 py-2 text-sm font-bold text-sidebar transition hover:bg-gold-dark"
            >
                Go to Admin Panel →
            </a>
        </div>

        <!-- Participant Journey (only for participants) -->
        <template v-if="!isAdmin">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-500">Your Conference Journey</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <a
                    v-for="step in journey"
                    :key="step.label"
                    :href="step.href"
                    class="group flex flex-col items-center rounded-2xl border bg-white p-5 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md"
                    :class="{
                        'border-primary/30 hover:border-primary': step.status === 'pending',
                        'border-green-200 bg-green-50':            step.status === 'done',
                        'border-slate-100 opacity-60 cursor-default pointer-events-none': step.status === 'locked',
                    }"
                >
                    <span class="mb-2 text-3xl">{{ step.icon }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ step.label }}</span>
                    <span
                        class="mt-2 inline-block rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest"
                        :class="{
                            'bg-amber-100 text-amber-700': step.status === 'pending',
                            'bg-green-100 text-green-700': step.status === 'done',
                            'bg-slate-100 text-slate-400': step.status === 'locked',
                        }"
                    >
                        {{ step.status }}
                    </span>
                </a>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
