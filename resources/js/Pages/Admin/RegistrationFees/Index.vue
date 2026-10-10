<script setup>
import { ref, computed, watch, onUnmounted } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DeleteConfirmModal from "@/Components/DeleteConfirmModal.vue";
import { useDeleteConfirm } from "@/Composables/useDeleteConfirm";

const props = defineProps({
    registrationFees: Array,
    conferences: Array,
    selectedConfId: Number,
});

// Modal state
const isModalOpen = ref(false);
const editingFee = ref(null);

watch(isModalOpen, (val) => {
    if (typeof document !== "undefined") {
        document.body.classList.toggle("overflow-hidden", val);
    }
});

onUnmounted(() => {
    if (typeof document !== "undefined") {
        document.body.classList.remove("overflow-hidden");
    }
});

// Form state with all schema fields
const form = useForm({
    conference_id: "",
    name: "",
    type: "presenter",
    mode: "offline",
    category: "national",
    price: 0,
    is_active: true,
});

const categoryOptions = [
    { value: "national", label: "Nasional (Dosen / Peneliti / Umum)" },
    { value: "student", label: "Mahasiswa (Undergraduate / Postgrad)" },
    { value: "international", label: "Internasional (Foreign Participant)" },
    { value: "general", label: "Umum / Delegasi Institusi" },
];

function getCategoryLabel(val) {
    const found = categoryOptions.find((c) => c.value === val);
    if (found) return found.label;
    if (!val) return "Umum";
    return val.charAt(0).toUpperCase() + val.slice(1);
}

const formattedPrice = computed({
    get() {
        if (
            form.price === null ||
            form.price === undefined ||
            form.price === "" ||
            form.price === 0
        ) {
            return form.price === 0 ? "0" : "";
        }
        return Number(form.price).toLocaleString("id-ID");
    },
    set(val) {
        const clean = String(val).replace(/\D/g, "");
        form.price = clean ? parseInt(clean, 10) : 0;
    },
});

function openCreateModal() {
    editingFee.value = null;
    form.reset();
    form.clearErrors();
    form.conference_id =
        props.selectedConfId || props.conferences?.[0]?.id || "";
    form.name = "";
    form.type = "presenter";
    form.mode = "offline";
    form.category = "national";
    form.price = 0;
    form.is_active = true;
    isModalOpen.value = true;
}

function openEditModal(item) {
    editingFee.value = item;
    form.clearErrors();
    form.conference_id = item.conference_id || props.conferences?.[0]?.id || "";
    form.name = item.name || "";
    form.type = item.type || "presenter";
    form.mode = item.mode || "offline";
    form.category = item.category || "national";
    form.price = item.price ?? 0;
    form.is_active =
        item.is_active !== undefined ? Boolean(item.is_active) : true;
    isModalOpen.value = true;
}

function submit() {
    if (editingFee.value) {
        form.put(route("admin.registration-fees.update", editingFee.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route("admin.registration-fees.store"), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
}

const {
    isModalOpen: isDeleteModalOpen,
    itemToDelete: feeToDelete,
    deleteTitle,
    deleteMessage,
    isDeleting,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useDeleteConfirm();

function deleteFee(item) {
    openDeleteModal({
        item: item,
        title: "Hapus Paket Registrasi",
        message: `Apakah Anda yakin ingin menghapus paket "${item.name}"? Paket ini tidak akan bisa dipilih lagi oleh peserta baru.`,
        url: route("admin.registration-fees.destroy", item.id),
    });
}

function formatPrice(val) {
    if (!val) return "Rp 0";
    return "Rp " + Number(val).toLocaleString("id-ID");
}

// Client Filters
const filterType = ref("all"); // 'all', 'presenter', 'non_presenter'
const filterMode = ref("all"); // 'all', 'offline', 'online'
const searchQuery = ref("");

const filteredFees = computed(() => {
    let list = props.registrationFees || [];
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

const stats = computed(() => {
    const list = props.registrationFees || [];
    return {
        total: list.length,
        presenter: list.filter((f) => f.type === "presenter").length,
        nonPresenter: list.filter((f) => f.type === "non_presenter").length,
        offline: list.filter((f) => f.mode === "offline").length,
        online: list.filter((f) => f.mode === "online").length,
    };
});

function handleConferenceChange(event) {
    const confId = event.target.value;
    router.get(
        route("admin.registration-fees.index"),
        { conference_id: confId },
        {
            preserveState: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="Registration Fee Packages - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        Kelola Paket & Biaya Registrasi
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Konfigurasi paket biaya pendaftaran untuk pemakalah
                        (Presenter) dan peserta umum (Non-Presenter).
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Conference Selector -->
                    <select
                        v-if="props.conferences && props.conferences.length > 1"
                        :value="
                            props.selectedConfId || props.conferences[0]?.id
                        "
                        @change="handleConferenceChange"
                        class="text-xs rounded-xl border border-slate-200 bg-white py-2 px-3 font-semibold text-slate-700 shadow-2xs focus:ring-1 focus:ring-purple-700"
                    >
                        <option
                            v-for="c in props.conferences"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.title }}
                        </option>
                    </select>

                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs px-4 py-2.5 transition shadow-xs cursor-pointer"
                    >
                        <span>+</span>
                        <span>Tambah Paket Biaya</span>
                    </button>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div
                    class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs"
                >
                    <span
                        class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block"
                        >Total Paket</span
                    >
                    <span
                        class="text-xl font-black text-slate-900 mt-1 block"
                        >{{ stats.total }}</span
                    >
                    <span class="text-[10px] text-slate-400"
                        >Tersedia di sistem</span
                    >
                </div>

                <div
                    class="bg-white p-4 rounded-2xl border border-purple-100 shadow-2xs bg-linear-to-br from-purple-50/40 to-white"
                >
                    <span
                        class="text-[11px] font-bold uppercase tracking-wider text-purple-700 block"
                        >Paket Presenter</span
                    >
                    <span
                        class="text-xl font-black text-purple-900 mt-1 block"
                        >{{ stats.presenter }}</span
                    >
                    <span class="text-[10px] text-purple-600"
                        >Khusus pemakalah abstrak</span
                    >
                </div>

                <div
                    class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-2xs bg-linear-to-br from-emerald-50/40 to-white"
                >
                    <span
                        class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block"
                        >Paket Non-Presenter</span
                    >
                    <span
                        class="text-xl font-black text-emerald-900 mt-1 block"
                        >{{ stats.nonPresenter }}</span
                    >
                    <span class="text-[10px] text-emerald-600"
                        >Peserta umum & webinar</span
                    >
                </div>

                <div
                    class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs bg-linear-to-br from-sky-50/40 to-white"
                >
                    <span
                        class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block"
                        >Format Kehadiran</span
                    >
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-slate-800"
                            >{{ stats.offline }} On-site</span
                        >
                        <span class="text-slate-300">•</span>
                        <span class="text-xs font-bold text-sky-800"
                            >{{ stats.online }} Virtual</span
                        >
                    </div>
                    <span class="text-[10px] text-slate-400"
                        >Offline & Online</span
                    >
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs"
            >
                <!-- Type Filter Tabs -->
                <div
                    class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl"
                >
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
                            >🔍</span
                        >
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
                                            Silakan sesuaikan filter pencarian
                                            atau klik Tambah Paket Biaya.
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
                                        Kategori:
                                        {{ getCategoryLabel(item.category) }}
                                    </span>
                                </td>

                                <!-- Participation Type Badge -->
                                <td
                                    class="px-4 py-3.5 align-middle whitespace-nowrap"
                                >
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
                                <td
                                    class="px-4 py-3.5 align-middle whitespace-nowrap"
                                >
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
                                <td
                                    class="px-4 py-3.5 align-middle text-xs font-medium text-slate-600 max-w-xs truncate"
                                >
                                    {{
                                        item.conference?.title ||
                                        "Default Conference"
                                    }}
                                </td>

                                <!-- Price -->
                                <td
                                    class="px-4 py-3.5 align-middle font-bold text-xs text-slate-900 whitespace-nowrap"
                                >
                                    {{ formatPrice(item.price) }}
                                </td>

                                <!-- Active Status -->
                                <td
                                    class="px-3 py-3.5 align-middle text-center whitespace-nowrap"
                                >
                                    <span
                                        v-if="item.is_active !== false"
                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-black bg-emerald-100 text-emerald-800"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-emerald-500"
                                        ></span>
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
                                <td
                                    class="px-5 py-3.5 align-middle text-right whitespace-nowrap"
                                >
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:text-purple-700 hover:border-purple-200 font-semibold text-xs hover:bg-purple-50/50 transition cursor-pointer shadow-2xs"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteFee(item)"
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

            <!-- Create / Edit Registration Fee Modal -->
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 overflow-y-auto backdrop-blur-2xs"
            >
                <div
                    class="w-full max-w-xl rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200 text-xs my-auto animate-fade-in space-y-5"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-4"
                    >
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                {{
                                    editingFee
                                        ? "Edit Paket Biaya Registrasi"
                                        : "Tambah Paket Biaya Registrasi"
                                }}
                            </h3>
                            <p class="text-slate-500 text-[11px] mt-0.5">
                                Atur peran peserta (Presenter / Non-Presenter),
                                format kehadiran, kategori, dan nominal biaya.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- 1. Target Conference -->
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Target Konferensi
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="form.conference_id"
                                class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-semibold"
                                required
                            >
                                <option value="" disabled>
                                    Pilih konferensi...
                                </option>
                                <option
                                    v-for="c in props.conferences"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.title }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.conference_id"
                                class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                                >{{ form.errors.conference_id }}</span
                            >
                        </div>

                        <!-- 2. Participation Type (Presenter vs Non-Presenter) -->
                        <div>
                            <label class="mb-1.5 block font-bold text-slate-700"
                                >Tipe Partisipasi Peserta
                                <span class="text-red-500">*</span></label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    :class="[
                                        'flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition select-none',
                                        form.type === 'presenter'
                                            ? 'border-purple-600 bg-purple-50/70 shadow-xs ring-1 ring-purple-600/30'
                                            : 'border-slate-200 hover:border-slate-300 bg-white',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        value="presenter"
                                        v-model="form.type"
                                        class="sr-only"
                                    />
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="font-extrabold text-xs text-purple-950 flex items-center gap-1.5"
                                        >
                                            <span>🎙️</span>
                                            <span>Presenter</span>
                                        </span>
                                        <span
                                            v-if="form.type === 'presenter'"
                                            class="w-4 h-4 rounded-full bg-purple-700 text-white text-[10px] flex items-center justify-center font-bold"
                                            >✓</span
                                        >
                                    </div>
                                    <p
                                        class="text-[10px] text-slate-500 mt-1 leading-snug"
                                    >
                                        Untuk pemakalah yang mengirim abstrak &
                                        presentasi ilmiah.
                                    </p>
                                </label>

                                <label
                                    :class="[
                                        'flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition select-none',
                                        form.type === 'non_presenter'
                                            ? 'border-emerald-600 bg-emerald-50/70 shadow-xs ring-1 ring-emerald-600/30'
                                            : 'border-slate-200 hover:border-slate-300 bg-white',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        value="non_presenter"
                                        v-model="form.type"
                                        class="sr-only"
                                    />
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="font-extrabold text-xs text-emerald-950 flex items-center gap-1.5"
                                        >
                                            <span>🎟️</span>
                                            <span>Non-Presenter</span>
                                        </span>
                                        <span
                                            v-if="form.type === 'non_presenter'"
                                            class="w-4 h-4 rounded-full bg-emerald-700 text-white text-[10px] flex items-center justify-center font-bold"
                                            >✓</span
                                        >
                                    </div>
                                    <p
                                        class="text-[10px] text-slate-500 mt-1 leading-snug"
                                    >
                                        Untuk peserta umum, delegasi, atau
                                        webinar pendengar.
                                    </p>
                                </label>
                            </div>
                            <span
                                v-if="form.errors.type"
                                class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                                >{{ form.errors.type }}</span
                            >
                        </div>

                        <!-- 3. Attendance Mode -->
                        <div>
                            <label class="mb-1.5 block font-bold text-slate-700"
                                >Mode Kehadiran
                                <span class="text-red-500">*</span></label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    @click="form.mode = 'offline'"
                                    :class="[
                                        'p-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer',
                                        form.mode === 'offline'
                                            ? 'bg-slate-900 border-slate-900 text-white shadow-xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100',
                                    ]"
                                >
                                    <span>🏢</span>
                                    <span>Offline (On-Site)</span>
                                </button>
                                <button
                                    type="button"
                                    @click="form.mode = 'online'"
                                    :class="[
                                        'p-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer',
                                        form.mode === 'online'
                                            ? 'bg-sky-600 border-sky-600 text-white shadow-xs'
                                            : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100',
                                    ]"
                                >
                                    <span>💻</span>
                                    <span>Online (Virtual)</span>
                                </button>
                            </div>
                            <span
                                v-if="form.errors.mode"
                                class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                                >{{ form.errors.mode }}</span
                            >
                        </div>

                        <!-- 4. Package Name -->
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Nama Paket Biaya
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                :placeholder="
                                    form.type === 'presenter'
                                        ? 'e.g. Presenter Nasional Offline / Mahasiswa Online'
                                        : 'e.g. Peserta Umum Onsite / Webinar Only'
                                "
                                class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-medium"
                                required
                            />
                            <span
                                v-if="form.errors.name"
                                class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                                >{{ form.errors.name }}</span
                            >
                        </div>

                        <!-- 5. Category Tier -->
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Kategori Sasaran (Opsional)</label
                            >
                            <select
                                v-model="form.category"
                                class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-medium"
                            >
                                <option value="">
                                    Tanpa Kategori Khusus (Umum)
                                </option>
                                <option
                                    v-for="cat in categoryOptions"
                                    :key="cat.value"
                                    :value="cat.value"
                                >
                                    {{ cat.label }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.category"
                                class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                                >{{ form.errors.category }}</span
                            >
                        </div>

                        <!-- 6. Price (Nominal Tarif Biaya) -->
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Nominal Tarif Biaya (Rp)
                                <span class="text-red-500">*</span></label
                            >
                            <div class="relative">
                                <span
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none"
                                >
                                    Rp
                                </span>
                                <input
                                    v-model="formattedPrice"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="500.000"
                                    class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 focus:ring-1 focus:ring-purple-700 font-bold text-slate-900"
                                    required
                                />
                            </div>
                            <span
                                v-if="form.errors.price"
                                class="text-[10px] text-rose-500 font-bold mt-1 block"
                                >{{ form.errors.price }}</span
                            >
                        </div>

                        <!-- 7. Active Status Toggle -->
                        <div
                            class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between"
                        >
                            <div>
                                <span
                                    class="font-bold text-xs text-slate-800 block"
                                    >Status Keaktifan Paket</span
                                >
                                <span class="text-[11px] text-slate-500 block"
                                    >Paket aktif akan langsung tampil di portal
                                    pendaftaran peserta.</span
                                >
                            </div>
                            <label
                                class="relative inline-flex items-center cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="sr-only peer"
                                />
                                <div
                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-700"
                                ></div>
                            </label>
                        </div>

                        <!-- Modal Actions -->
                        <div
                            class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100"
                        >
                            <button
                                type="button"
                                @click="isModalOpen = false"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold px-6 py-2.5 transition disabled:opacity-50 cursor-pointer shadow-xs"
                            >
                                {{
                                    form.processing
                                        ? "Menyimpan..."
                                        : editingFee
                                          ? "Perbarui Paket"
                                          : "Simpan Paket"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reusable Delete Confirmation Modal -->
            <DeleteConfirmModal
                :show="isDeleteModalOpen"
                :title="deleteTitle"
                :message="deleteMessage"
                :item-name="feeToDelete?.name"
                :loading="isDeleting"
                @close="closeDeleteModal"
                @confirm="confirmDelete"
            />
        </div>
    </AdminLayout>
</template>
