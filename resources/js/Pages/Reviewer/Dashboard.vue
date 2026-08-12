<script setup>
import { Head } from '@inertiajs/vue3';
import ReviewerLayout from '@/Layouts/ReviewerLayout.vue';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Reviewer Dashboard" />

    <ReviewerLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reviewer Dashboard</h1>
            <p class="text-slate-500 mt-1">Welcome back, {{ $page.props.auth.user.name }}! Here's an overview of your review tasks.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Assigned -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Total Assigned</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.total_assigned }}</span>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-1 rounded-md mb-1">Abstracts</span>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Pending</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.pending_reviews }}</span>
                        <span class="text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-1 rounded-md mb-1">Requires Action</span>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Completed</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.completed_reviews }}</span>
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-100 px-2 py-1 rounded-md mb-1">Done</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/60 relative overflow-hidden group hover:shadow-md transition-all">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10 flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Deadlines</span>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-black text-slate-800">{{ stats.upcoming_deadlines }}</span>
                        <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-md mb-1">Next 7 days</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="font-bold text-lg text-slate-800">Recent Assignments</h3>
            </div>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">No pending reviews</h3>
                <p class="text-slate-500">You currently have no abstracts assigned to you for review.</p>
            </div>
        </div>

    </ReviewerLayout>
</template>
