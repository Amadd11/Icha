<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import ActivityList from '@/Components/Dashboard/ActivityList.vue';
import ConferenceProgress from '@/Components/Dashboard/ConferenceProgress.vue';
import DeadlineList from '@/Components/Dashboard/DeadlineList.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';

const props = defineProps({
    selectedConference: Object,
    availableConferences: Array,
    stats: Object,
    recentRegistrations: Array,
    deadlines: Array,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout
        :selected-conference="props.selectedConference"
        :available-conferences="props.availableConferences"
    >
        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                    <p class="text-xs text-slate-500">
                        Overview metrics for {{ props.selectedConference?.title || 'Active Conference' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        System Operational
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <StatCard
                    title="Participants"
                    :value="props.stats?.total_participants || 0"
                    subtitle="Registered users"
                    icon="users"
                />
                <StatCard
                    title="Registrations"
                    :value="props.stats?.total_registrations || 0"
                    subtitle="Selected category"
                    icon="document-text"
                />
                <StatCard
                    title="Paid"
                    :value="props.stats?.paid_registrations || 0"
                    subtitle="Verified payment"
                    icon="check-circle"
                    color="green"
                />
                <StatCard
                    title="Abstracts"
                    :value="props.stats?.total_abstracts || 0"
                    subtitle="Phase 3"
                    icon="document-text"
                />
                <StatCard
                    title="Full Papers"
                    :value="props.stats?.total_full_papers || 0"
                    subtitle="Phase 4"
                    icon="document-text"
                />
                <StatCard
                    title="Presentations"
                    :value="props.stats?.total_presentations || 0"
                    subtitle="Phase 5"
                    icon="chart-bar"
                />
            </div>

            <!-- Quick Actions -->
            <QuickActions />

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Activity List -->
                    <ActivityList :items="props.recentRegistrations" />
                </div>

                <div class="space-y-6">
                    <!-- Conference Progress -->
                    <ConferenceProgress :conference="props.selectedConference" />

                    <!-- Deadlines List -->
                    <DeadlineList :deadlines="props.deadlines" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
