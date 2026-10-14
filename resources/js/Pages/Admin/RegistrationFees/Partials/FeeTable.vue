<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    fees: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            presenter: 0,
            nonPresenter: 0,
        }),
    },
});

defineEmits(["edit", "delete"]);

const filterType = ref("all");
const filterMode = ref("all");
const searchQuery = ref("");

const categoryLabels = {
    national: "Nasional (Dosen / Peneliti / Umum)",
    student: "Mahasiswa (Undergraduate / Postgrad)",
    international: "Internasional (Foreign Participant)",
    general: "Umum / Delegasi Institusi",
};

function getCategoryLabel(val) {
    if (!val) return "Umum";
    if (categoryLabels[val]) return categoryLabels[val];
    return val.charAt(0).toUpperCase() + val.slice(1);
}

function formatPrice(val) {
    if (!val) return "Rp 0";
    return "Rp " + Number(val).toLocaleString("id-ID");
}

const filteredFees = computed(() => {
    let list = props.fees || [];
    if (filterType.value !== "all") {
        list = list.filter((f) => f.type === filterType.value);
    }
    if (filterMode.value !== "all") {
        list = list.filter((f) => f.mode === filterMode.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(
            (f) =>
                (f.name && f.name.toLowerCase().includes(q)) ||
                (f.category && f.category.toLowerCase().includes(q)) ||
                (f.conference?.title &&
                    f.conference.title.toLowerCase().includes(q)),
        );
    }
    return list;
});
</script>

<template>
    <div class="space-y-4">
        <!-- Filter & Search Bar -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs"
        >
            <!-- Type Filter Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl">
                <button
                    type="button"
                    @click="filterType = 'all'"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer',
                        filterType === 'all'
                            ? 'bg-white text-slate-900 shadow-2xs'
                            : 'text-slate-500 hover:text-slate-900',
                    ]"
                >
                    Semua ({{ stats.total }})
                </button>
                <button
                    type="button"
                    @click="filterType = 'presenter'"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer',
                        filterType === 'presenter'
                            ? 'bg-purple-700 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-purple-700',
                    ]"
                >
                    Presenter ({{ stats.presenter }})
                </button>
                <button
                    type="button"
                    @click="filterType = 'non_presenter'"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer',
                        filterType === 'non_presenter'
                            ? 'bg-emerald-700 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-emerald-700',
                    ]"
                >
                    Non-Presenter ({{ stats.nonPresenter }})
                </button>
            </div>

            <!-- Mode & Search -->
            <div class="flex items-center gap-2">
                <select
                    v-model="filterMode"
                    class="text-xs rounded-xl border border-slate-200 bg-slate-50/70 py-2 px-3 font-semibold text-slate-700 focus:bg-white focus:ring-1 focus:ring-purple-700"
                >
                    <option value="all">Semua Mode</option>
                    <option value="offline">Offline (On-Site)</option>
                    <option value="online">Online (Virtual)</option>
                </select>

                <div class="relative w-full sm:w-56">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama paket..."
                        class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-8 pr-3 focus:bg-white focus:ring-1 focus:ring-purple-700"
                    />
                    <span
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"
                    >🔍</span>
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        type="button"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead
                        class="bg-slate-50 border-b border-slate-200 uppercase text-[11px] font-bold text-slate-500"
                    >
                        <tr>
                            <th scope="col" class="px-5 py-3.5">
                                Nama Paket & Kategori
                            </th>
                            <th scope="col" class="px-4 py-3.5">
                                Tipe Partisipasi
                            </th>
                            <th scope="col" class="px-4 py-3.5">
                                Mode Kehadiran
                            </th>
                            <th scope="col" class="px-4 py-3.5">
                                Konferensi
                            </th>
                            <th scope="col" class="px-4 py-3.5">
                                Tarif Biaya
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-center">
                                Status
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-right">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="filteredFees.length === 0">
                            <td
                                colspan="7"
                                class="px-5 py-10 text-center text-xs text-slate-400"
                            >
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="text-3xl">📦</div>
                                    <p class="font-bold text-slate-700">
                                        Tidak ada paket yang sesuai filter
                                    </p>
                                    <p class="text-slate-400 text-[11px]">
                                        Silakan sesuaikan filter pencarian atau klik Tambah Paket Biaya.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="item in filteredFees"
                            :key="item.id"
                            class="hover:bg-slate-50/60 transition"
                        >
                            <!-- Name & Category -->
                            <td class="px-5 py-3.5 align-middle">
                                <p class="font-bold text-slate-900 text-xs">
                                    {{ item.name }}
                                </p>
                                <span
                                    v-if="item.category"
                                    class="inline-block mt-0.5 text-[10px] font-semibold text-slate-500"
                                >
                                    Kategori: {{ getCategoryLabel(item.category) }}
                                </span>
                            </td>

                            <!-- Participation Type Badge -->
                            <td class="px-4 py-3.5 align-middle whitespace-nowrap">
                                <span
                                    v-if="item.type === 'presenter'"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-2.5 py-1 text-[11px] font-bold text-purple-700 border border-purple-200"
                                >
                                    <span>Presenter</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200"
                                >
                                    <span>Non-Presenter</span>
                                </span>
                            </td>

                            <!-- Attendance Mode -->
                            <td class="px-4 py-3.5 align-middle whitespace-nowrap">
                                <span
                                    :class="
                                        item.mode === 'offline'
                                            ? 'bg-slate-100 text-slate-700 border-slate-200'
                                            : 'bg-sky-50 text-sky-700 border-sky-200'
                                    "
                                    class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase border"
                                >
                                    {{ item.mode }}
                                </span>
                            </td>

                            <!-- Conference -->
                            <td class="px-4 py-3.5 align-middle text-xs font-medium text-slate-600 max-w-xs truncate">
                                {{ item.conference?.title || "Default Conference" }}
                            </td>

                            <!-- Price -->
                            <td class="px-4 py-3.5 align-middle font-bold text-xs text-slate-900 whitespace-nowrap">
                                {{ formatPrice(item.price) }}
                            </td>

                            <!-- Active Status -->
                            <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap">
                                <span
                                    v-if="item.is_active !== false"
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-black bg-emerald-100 text-emerald-800"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-500"
                                >
                                    Nonaktif
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 align-middle text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        @click="$emit('edit', item)"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:text-purple-700 hover:border-purple-200 font-semibold text-xs hover:bg-purple-50/50 transition cursor-pointer shadow-2xs"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('delete', item)"
                                        class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 font-semibold text-xs transition cursor-pointer shadow-2xs"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
