<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const stats = [
    { label: 'Participants', value: '—', icon: '👥', color: 'bg-primary/10 text-primary' },
    { label: 'Registrations', value: '—', icon: '📋', color: 'bg-gold/20 text-gold-dark' },
    { label: 'Abstracts', value: '—', icon: '📄', color: 'bg-green-100 text-green-700' },
    { label: 'Payments Pending', value: '—', icon: '💳', color: 'bg-amber-100 text-amber-700' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-slate-800">Admin Dashboard</h1>
        </template>

        <!-- Welcome Banner -->
        <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-sidebar to-primary p-6 text-white shadow-lg">
            <p class="text-sm font-medium text-white/70">Administration Panel</p>
            <h2 class="mt-1 text-2xl font-extrabold">ICHA 2026</h2>
            <p class="mt-1 text-sm text-white/70">
                Logged in as <strong class="text-gold">{{ user?.name }}</strong>
                <span class="ml-2 rounded-full bg-gold/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-gold">{{ user?.role?.replace('_', ' ') }}</span>
            </p>
        </div>

        <!-- Stats Overview -->
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-500">Overview</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm"
            >
                <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-2xl', stat.color]">
                    {{ stat.icon }}
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-800">{{ stat.value }}</p>
                    <p class="text-xs font-medium text-slate-500">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- Coming Soon Note -->
        <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-400">
            <p class="text-4xl">🚧</p>
            <p class="mt-2 text-sm font-semibold">More admin features coming in Phase 1 (Conference CMS)</p>
        </div>
    </AuthenticatedLayout>
</template>
