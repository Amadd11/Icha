<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    activeConference: Object,
    availableConferences: Array,
    journalPartners: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref("");

const filteredJournals = computed(() => {
    let list = props.journalPartners || [];

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(
            (j) =>
                (j.journal_name || "").toLowerCase().includes(q) ||
                (j.university || "").toLowerCase().includes(q) ||
                (j.submission_requirements || "").toLowerCase().includes(q) ||
                (j.loa_information || "").toLowerCase().includes(q)
        );
    }

    return list;
});
</script>

<template>
    <Head>
        <title>Journal Partners - ICHA 2026</title>
        <meta
            name="description"
            content="Daftar mitra jurnal resmi publikasi International Conference on Healthcare Administration (ICHA 2026)."
        />
    </Head>

    <PublicLayout
        :conference="props.activeConference"
        :available-conferences="props.availableConferences"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <!-- Page Header -->
        <header class="border-b border-purple-100 bg-linear-to-b from-purple-50/50 to-white py-10 sm:py-12">
            <div class="w-full px-4 sm:px-6 lg:px-10">
                <nav class="mb-3 flex items-center gap-2 text-xs text-purple-700/80 font-medium">
                    <Link href="/" class="hover:text-primary transition">Home</Link>
                    <span>/</span>
                    <span class="text-purple-950 font-bold">Journal Partners</span>
                </nav>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                            Official Journal Partners
                        </h1>
                        <p class="mt-1.5 text-xs sm:text-sm text-slate-600 max-w-3xl leading-relaxed">
                            Daftar jurnal mitra resmi publikasi konferensi ICHA 2026 beserta kebijakan penerbitan LoA dan ketentuan submission naskah.
                        </p>
                    </div>

                    <!-- Highlight Badge -->
                    <div class="inline-flex items-center gap-2 self-start sm:self-center px-3.5 py-1.5 rounded-full bg-purple-100/80 border border-purple-200 text-purple-900 font-bold text-xs shadow-2xs">
                        <span class="material-symbols-outlined text-[16px] text-primary">auto_stories</span>
                        <span>{{ props.journalPartners?.length || 0 }} Jurnal Terdaftar</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content: Simple Clean List Table -->
        <main class="bg-slate-50/70 py-8 sm:py-10 min-h-[60vh]">
            <div class="w-full px-4 sm:px-6 lg:px-10 space-y-4">
                <!-- Search & Counter Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="relative w-full sm:w-80">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-purple-400 text-[18px]">
                            search
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama jurnal atau universitas..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary transition"
                        />
                    </div>
                    <span class="text-xs font-semibold text-slate-500">
                        Menampilkan <strong class="text-primary">{{ filteredJournals.length }}</strong> jurnal mitra
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredJournals.length === 0"
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-xs text-slate-500"
                >
                    Tidak ada jurnal yang sesuai dengan kata kunci pencarian Anda.
                </div>

                <!-- 📊 Simple List Table -->
                <div v-else class="rounded-2xl border border-purple-100 shadow-md shadow-purple-950/5 overflow-hidden bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <!-- Table Head -->
                            <thead class="bg-linear-to-r from-purple-950 via-primary to-purple-900 text-white text-[11px] uppercase font-bold tracking-wider">
                                <tr>
                                    <th scope="col" class="py-3.5 px-4 text-center w-12 text-purple-200">#</th>
                                    <th scope="col" class="py-3.5 px-5 w-[20%] min-w-[200px]">Nama Jurnal</th>
                                    <th scope="col" class="py-3.5 px-5 w-[16%] min-w-[160px]">Universitas</th>
                                    <th scope="col" class="py-3.5 px-4 w-[8%] min-w-[90px]">Website</th>
                                    <th scope="col" class="py-3.5 px-5 w-[22%] min-w-[200px]">Kebijakan LoA</th>
                                    <th scope="col" class="py-3.5 px-5 w-[34%] min-w-[300px]">Ketentuan Khusus Submission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr
                                    v-for="(j, idx) in filteredJournals"
                                    :key="j.id"
                                    class="hover:bg-purple-50/20 transition-colors"
                                >
                                    <!-- Number -->
                                    <td class="py-4 px-4 text-center text-slate-400 font-medium text-xs align-top">
                                        {{ idx + 1 }}
                                    </td>

                                    <!-- Journal Name -->
                                    <td class="py-4 px-5 align-top">
                                        <span class="font-bold text-slate-900 text-sm leading-snug">
                                            {{ j.journal_name }}
                                        </span>
                                    </td>

                                    <!-- University (Simple: No BG, No Icon) -->
                                    <td class="py-4 px-5 text-slate-700 text-xs leading-relaxed align-top">
                                        {{ j.university || '-' }}
                                    </td>

                                    <!-- Website (Simple: No BG, No Icon) -->
                                    <td class="py-4 px-4 whitespace-nowrap align-top">
                                        <a
                                            v-if="j.website"
                                            :href="j.website"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary hover:underline font-semibold text-xs inline-block"
                                        >
                                            Buka Web
                                        </a>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>

                                    <!-- LoA Policy (Simple: No BG, No Icon) -->
                                    <td class="py-4 px-5 text-slate-700 text-xs leading-relaxed align-top">
                                        {{ j.loa_information || '-' }}
                                    </td>

                                    <!-- Submission Requirements (Simple: Full text, No "Baca Selengkapnya") -->
                                    <td class="py-4 px-5 text-slate-600 leading-relaxed whitespace-pre-line text-xs align-top">
                                        {{ j.submission_requirements || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
