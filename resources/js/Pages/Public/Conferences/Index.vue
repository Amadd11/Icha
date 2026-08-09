<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    conferences: Array,
});
</script>

<template>
    <Head title="Conference Archive" />

    <PublicLayout :available-conferences="props.conferences">
        <div class="bg-slate-50 py-16 px-5 md:px-10">
            <div class="mx-auto max-w-7xl">
                <div class="mb-12 text-center">
                    <h1 class="text-3xl font-extrabold text-slate-900 md:text-4xl">Conference Archive</h1>
                    <p class="mt-2 text-slate-600">Browse current, upcoming, and past editions of the International Conference on Healthcare Administration.</p>
                </div>

                <div v-if="props.conferences && props.conferences.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="conf in props.conferences"
                        :key="conf.id"
                        class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                    {{ conf.year || 'Edition' }}
                                </span>
                                <span
                                    class="text-xs font-semibold uppercase tracking-wider"
                                    :class="conf.is_active ? 'text-green-600' : 'text-slate-400'"
                                >
                                    {{ conf.is_active ? 'Active' : conf.status }}
                                </span>
                            </div>

                            <h2 class="text-xl font-bold text-slate-900 mb-2">{{ conf.title }}</h2>
                            <p v-if="conf.tagline" class="text-sm text-slate-600 mb-4 line-clamp-2">{{ conf.tagline }}</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-500">{{ conf.city || 'Indonesia' }}</span>
                            <Link
                                :href="route('conferences.show', conf.slug)"
                                class="inline-flex items-center text-sm font-bold text-primary hover:underline"
                            >
                                View Details &rarr;
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl bg-white p-12 text-center border border-slate-200">
                    <p class="text-slate-500">No conferences found.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
