<script setup>
import { computed, watch, onUnmounted } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    editingFee: {
        type: Object,
        default: null,
    },
    conferences: {
        type: Array,
        default: () => [],
    },
    defaultConferenceId: {
        type: [Number, String],
        default: "",
    },
});

const emit = defineEmits(["close"]);

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

watch(
    () => props.show,
    (val) => {
        if (typeof document !== "undefined") {
            document.body.classList.toggle("overflow-hidden", val);
        }

        if (val) {
            form.clearErrors();
            if (props.editingFee) {
                form.conference_id =
                    props.editingFee.conference_id ||
                    props.defaultConferenceId ||
                    props.conferences?.[0]?.id ||
                    "";
                form.name = props.editingFee.name || "";
                form.type = props.editingFee.type || "presenter";
                form.mode = props.editingFee.mode || "offline";
                form.category = props.editingFee.category || "national";
                form.price = props.editingFee.price ?? 0;
                form.is_active =
                    props.editingFee.is_active !== undefined
                        ? Boolean(props.editingFee.is_active)
                        : true;
            } else {
                form.reset();
                form.conference_id =
                    props.defaultConferenceId ||
                    props.conferences?.[0]?.id ||
                    "";
                form.name = "";
                form.type = "presenter";
                form.mode = "offline";
                form.category = "national";
                form.price = 0;
                form.is_active = true;
            }
        }
    },
);

onUnmounted(() => {
    if (typeof document !== "undefined") {
        document.body.classList.remove("overflow-hidden");
    }
});

function handleClose() {
    emit("close");
}

function submit() {
    if (props.editingFee) {
        form.put(route("admin.registration-fees.update", props.editingFee.id), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
                form.reset();
            },
        });
    } else {
        form.post(route("admin.registration-fees.store"), {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
                form.reset();
            },
        });
    }
}
</script>

<template>
    <div
        v-if="show"
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
                        Atur peran peserta (Presenter / Non-Presenter), format
                        kehadiran, kategori, dan nominal biaya.
                    </p>
                </div>
                <button
                    type="button"
                    @click="handleClose"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm transition cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- 1. Target Conference -->
                <div>
                    <label class="mb-1 block font-bold text-slate-700">
                        Target Konferensi
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.conference_id"
                        class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-semibold"
                        required
                    >
                        <option value="" disabled>Pilih konferensi...</option>
                        <option
                            v-for="c in conferences"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.title }}
                        </option>
                    </select>
                    <span
                        v-if="form.errors.conference_id"
                        class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                    >
                        {{ form.errors.conference_id }}
                    </span>
                </div>

                <!-- 2. Participation Type (Presenter vs Non-Presenter) -->
                <div>
                    <label class="mb-1.5 block font-bold text-slate-700">
                        Tipe Partisipasi Peserta
                        <span class="text-red-500">*</span>
                    </label>
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
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-extrabold text-xs text-purple-950 flex items-center gap-1.5"
                                >
                                    <span>🎙️</span>
                                    <span>Presenter</span>
                                </span>
                                <span
                                    v-if="form.type === 'presenter'"
                                    class="w-4 h-4 rounded-full bg-purple-700 text-white text-[10px] flex items-center justify-center font-bold"
                                >
                                    ✓
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1 leading-snug">
                                Untuk pemakalah yang mengirim abstrak & presentasi ilmiah.
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
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-extrabold text-xs text-emerald-950 flex items-center gap-1.5"
                                >
                                    <span>🎟️</span>
                                    <span>Non-Presenter</span>
                                </span>
                                <span
                                    v-if="form.type === 'non_presenter'"
                                    class="w-4 h-4 rounded-full bg-emerald-700 text-white text-[10px] flex items-center justify-center font-bold"
                                >
                                    ✓
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1 leading-snug">
                                Untuk peserta umum, delegasi, atau webinar pendengar.
                            </p>
                        </label>
                    </div>
                    <span
                        v-if="form.errors.type"
                        class="text-[10px] text-rose-500 font-bold mt-0.5 block"
                    >
                        {{ form.errors.type }}
                    </span>
                </div>

                <!-- 3. Attendance Mode -->
                <div>
                    <label class="mb-1.5 block font-bold text-slate-700">
                        Mode Kehadiran
                        <span class="text-red-500">*</span>
                    </label>
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
                    >
                        {{ form.errors.mode }}
                    </span>
                </div>

                <!-- 4. Package Name -->
                <div>
                    <label class="mb-1 block font-bold text-slate-700">
                        Nama Paket Biaya
                        <span class="text-red-500">*</span>
                    </label>
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
                    >
                        {{ form.errors.name }}
                    </span>
                </div>

                <!-- 5. Category Tier -->
                <div>
                    <label class="mb-1 block font-bold text-slate-700">
                        Kategori Sasaran (Opsional)
                    </label>
                    <select
                        v-model="form.category"
                        class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-1 focus:ring-purple-700 font-medium"
                    >
                        <option value="">Tanpa Kategori Khusus (Umum)</option>
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
                    >
                        {{ form.errors.category }}
                    </span>
                </div>

                <!-- 6. Price (Nominal Tarif Biaya) -->
                <div>
                    <label class="mb-1 block font-bold text-slate-700">
                        Nominal Tarif Biaya (Rp)
                        <span class="text-red-500">*</span>
                    </label>
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
                    >
                        {{ form.errors.price }}
                    </span>
                </div>

                <!-- 7. Active Status Toggle -->
                <div
                    class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between"
                >
                    <div>
                        <span class="font-bold text-xs text-slate-800 block">
                            Status Keaktifan Paket
                        </span>
                        <span class="text-[11px] text-slate-500 block">
                            Paket aktif akan langsung tampil di portal pendaftaran peserta.
                        </span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
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
                        @click="handleClose"
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
</template>
