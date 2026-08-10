<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Quick Actions</h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <Link
                :href="route('admin.conferences.create')"
                class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50/50 hover:text-indigo-600"
            >
                <svg class="h-6 w-6 text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs font-bold text-slate-700">New Conference</span>
            </Link>

            <Link
                :href="route('admin.registrations.index')"
                class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50/50 hover:text-indigo-600"
            >
                <svg class="h-6 w-6 text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-xs font-bold text-slate-700">Registrations</span>
            </Link>

            <Link
                :href="route('admin.payments.index')"
                class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50/50 hover:text-indigo-600"
            >
                <svg class="h-6 w-6 text-emerald-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-bold text-slate-700">Verify Payments</span>
            </Link>

            <Link
                :href="route('admin.speakers.index')"
                class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50/50 hover:text-indigo-600"
            >
                <svg class="h-6 w-6 text-amber-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span class="text-xs font-bold text-slate-700">Manage Speakers</span>
            </Link>
        </div>
    </div>
</template>
