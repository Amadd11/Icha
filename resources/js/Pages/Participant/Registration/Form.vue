<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ParticipantLayout from '@/Layouts/ParticipantLayout.vue';
import { useClipboard } from '@/Composables/useClipboard';
import { formatDateTime } from '@/Utils/formatters';

const props = defineProps({
    activeConference: Object,
    existingRegistration: Object,
    payment: Object,
    registrationFees: Array,
    userProfile: Object,
    abstract: Object,
    isAbstractAccepted: Boolean,
    abstractStatus: String,
});

const presenterFees = computed(() => {
    return (props.registrationFees || []).filter(f => f.type === 'presenter');
});

const nonPresenterFees = computed(() => {
    return (props.registrationFees || []).filter(f => f.type === 'non_presenter');
});

const isNonPresenterUser = computed(() => {
    return props.userProfile?.participation_type === 'non_presenter';
});

const invoiceAmount = computed(() => {
    const raw = props.existingRegistration?.amount 
        ?? props.existingRegistration?.total_amount 
        ?? props.existingRegistration?.registration_fee?.price 
        ?? props.existingRegistration?.registrationFee?.price 
        ?? 0;
    return Number(raw) || 0;
});

const selectedPackageName = computed(() => {
    return props.existingRegistration?.registration_fee?.name 
        ?? props.existingRegistration?.registrationFee?.name 
        ?? '-';
});

const selectedPackageMode = computed(() => {
    return props.existingRegistration?.registration_fee?.mode 
        ?? props.existingRegistration?.registrationFee?.mode 
        ?? 'General';
});

const { copyItem, copiedKey } = useClipboard();

// Modal state
const isProofModalOpen = ref(false);
const showReuploadForm = ref(false);

// Form 1: Registration Creation
const regForm = useForm({
    registration_fee_id: '',
    currency:            'IDR',
    notes:               '',
});

const selectedFee = computed(() => {
    return (props.registrationFees || []).find(f => f.id === regForm.registration_fee_id) || null;
});

// Auto-select package for smooth UX if not selected
if (!regForm.registration_fee_id) {
    if (isNonPresenterUser.value && nonPresenterFees.value.length > 0) {
        regForm.registration_fee_id = nonPresenterFees.value[0].id;
    } else if (props.isAbstractAccepted && presenterFees.value.length > 0) {
        regForm.registration_fee_id = presenterFees.value[0].id;
    }
}

function submitRegistration() {
    regForm.post(route('participant.registration.store'), {
        preserveScroll: true,
    });
}

// Form 2: Payment Receipt Upload
const proofPreview = ref(props.payment?.proof_url || (props.payment?.proof_file ? formatStorageUrl(props.payment.proof_file) : null));

const paymentForm = useForm({
    registration_id: props.existingRegistration?.id || null,
    payment_method:  props.payment?.payment_method ?? 'Bank Transfer (Bank JATIM)',
    proof_file:      null,
});

// Sync registration id & preview when props change
watch(() => props.existingRegistration, (newVal) => {
    if (newVal?.id) {
        paymentForm.registration_id = newVal.id;
    }
}, { immediate: true });

watch(() => props.payment, (newVal) => {
    if (newVal?.proof_file) {
        proofPreview.value = newVal.proof_url || formatStorageUrl(newVal.proof_file);
    }
    if (newVal?.payment_method) {
        paymentForm.payment_method = newVal.payment_method;
    }
}, { immediate: true });

function onFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        paymentForm.proof_file = file;
        proofPreview.value = URL.createObjectURL(file);
    }
}

function submitPayment() {
    paymentForm.registration_id = props.existingRegistration?.id;
    paymentForm.post(route('participant.payment.submit'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset('proof_file');
            showReuploadForm.value = false;
        },
    });
}

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

function isPdf(path) {
    return path && (path.toLowerCase().endsWith('.pdf') || path.includes('application/pdf') || (paymentForm.proof_file?.type === 'application/pdf'));
}
</script>

<template>
    <Head title="Registration & Payment - Participant" />
    <ParticipantLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registration & Payment Portal</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Select your registration fee package and submit payment receipt for {{ activeConference?.title || 'the conference' }}
                </p>
            </div>

            <!-- Profile Incomplete Warning -->
            <div v-if="!userProfile" class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-amber-800 text-xs">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold">⚠️ Profile Incomplete:</span>
                        <span>Please complete your participant profile details before registering.</span>
                    </div>
                    <Link :href="route('participant.profile.edit')" class="rounded-xl bg-amber-600 px-3.5 py-1.5 font-bold text-white hover:bg-amber-700 transition">
                        Complete Profile
                    </Link>
                </div>
            </div>

            <!-- Contextual Flow Advisory Banners -->
            <div v-if="!existingRegistration" class="space-y-4">
                <!-- Non-Presenter Hero Advisory Banner -->
                <div v-if="isNonPresenterUser" class="relative overflow-hidden rounded-3xl bg-linear-to-r from-purple-950 via-purple-900 to-indigo-950 p-6 sm:p-7 text-white shadow-md border border-purple-800/40">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5 max-w-2xl">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-gold backdrop-blur-xs border border-white/10">
                                <span>🎟️</span>
                                <span>Jalur Peserta Umum (Non-Presenter)</span>
                            </div>
                            <h2 class="text-lg sm:text-xl font-black text-white tracking-tight">Pilih Paket Kehadiran & Dapatkan Invoice</h2>
                            <p class="text-xs text-purple-200/90 leading-relaxed">
                                Sebagai peserta umum, Anda tidak perlu mengirimkan naskah abstrak. Cukup pilih paket kehadiran di bawah (Offline Onsite atau Online Webinar) untuk menerbitkan nomor invoice resmi dan tiket masuk seminar.
                            </p>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-400/30">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Direct Ticketing Aktif</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Presenter Track Banners -->
                <template v-else>
                    <!-- Abstract Accepted Banner -->
                    <div v-if="props.isAbstractAccepted" class="rounded-3xl border border-emerald-300 bg-emerald-50/90 p-5 text-emerald-950 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px] font-black">✓</span>
                            <span>Naskah Abstrak Resmi DITERIMA (Accepted)</span>
                        </div>
                        <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                            Selamat! Naskah Anda <strong>({{ props.abstract?.abstract_code }}: {{ props.abstract?.title }})</strong> telah resmi diterima. Silakan pilih salah satu paket <strong>Presenter</strong> di bawah untuk menyelesaikan pendaftaran dan membuka akses jadwal presentasi.
                        </p>
                    </div>

                    <!-- Abstract Under Review Banner -->
                    <div v-else-if="props.abstractStatus === 'under_review' || props.abstractStatus === 'pending'" class="rounded-3xl border border-blue-200 bg-blue-50/90 p-5 text-blue-950 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-black">⏳</span>
                            <span>Naskah Abstrak Sedang Ditelaah Reviewer</span>
                        </div>
                        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
                            Naskah Anda <strong>({{ props.abstract?.abstract_code }})</strong> saat ini sedang dievaluasi oleh tim peer reviewer. Tagihan paket Presenter akan otomatis terbuka setelah naskah Anda dinyatakan <strong>Accepted</strong>. Anda belum perlu membayar apapun saat ini.
                        </p>
                        <div class="mt-2.5">
                            <Link :href="route('participant.submission.index')" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-950 underline hover:text-blue-800">
                                Pantau Status Review Naskah →
                            </Link>
                        </div>
                    </div>

                    <!-- Abstract Revision Required Banner -->
                    <div v-else-if="props.abstractStatus === 'revision_required'" class="rounded-3xl border border-amber-300 bg-amber-50/90 p-5 text-amber-950 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-600 text-white text-[10px] font-black">⚠️</span>
                            <span>Naskah Abstrak Memerlukan Revisi</span>
                        </div>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Reviewer meminta revisi naskah. Silakan unggah perbaikan abstrak Anda. Tagihan paket Presenter akan aktif setelah naskah revisi disetujui.
                        </p>
                        <div class="mt-2.5">
                            <Link :href="route('participant.submission.index')" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-950 hover:text-amber-800">
                                Lihat Catatan Reviewer & Unggah Revisi 
                            </Link>
                        </div>
                    </div>

                    <!-- Abstract Rejected Banner -->
                    <div v-else-if="props.abstractStatus === 'rejected'" class="rounded-3xl border border-rose-200 bg-rose-50/90 p-5 text-rose-950 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-[10px] font-black">✕</span>
                            <span>Naskah Tidak Lolos untuk Presentasi</span>
                        </div>
                        <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                            Anda tidak perlu membayar biaya presenter. Namun, Anda tetap dipersilakan menghadiri seluruh sesi seminar dan webinar serta memperoleh sertifikat peserta dengan memilih <strong>Paket Non-Presenter</strong> di bawah.
                        </p>
                    </div>

                    <!-- No Abstract Yet Guidance Banner -->
                    <div v-else class="rounded-3xl border border-purple-200 bg-purple-50/80 p-5 text-purple-950 shadow-xs">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-700 text-gold text-[10px] font-black">ℹ️</span>
                            <span>Petunjuk Pemakalah (Presenter)</span>
                        </div>
                        <p class="text-xs text-purple-800 mt-1 leading-relaxed">
                            Sebagai Pemakalah, silakan submit naskah abstrak Anda terlebih dahulu melalui menu <strong>Submission</strong> tanpa dipungut biaya di awal. Paket tagihan presenter akan terbuka setelah naskah Anda dinyatakan <strong>Accepted</strong>.
                        </p>
                    </div>
                </template>
            </div>

            <!-- STEP 1: REGISTRATION & TICKET SELECTION (2-Column Checkout Layout) -->
            <div v-if="!existingRegistration" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left Column: Package Options (col-span-8) -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-xs space-y-6">
                        <!-- Clean Unified Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-5">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">
                                    {{ isNonPresenterUser ? 'Pilihan Paket Tiket Seminar & Webinar' : 'Pilihan Paket Biaya Registrasi' }}
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ activeConference?.title }} — {{ activeConference?.theme }}
                                </p>
                            </div>
                            <span v-if="isNonPresenterUser" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-center">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Pendaftaran Langsung</span>
                            </span>
                        </div>

                        <!-- Presenter Packages (If Presenter) -->
                        <div v-if="!isNonPresenterUser && presenterFees.length > 0" class="space-y-4">
                            <!-- Notice if abstract not yet accepted -->
                            <div v-if="!props.isAbstractAccepted" class="rounded-2xl bg-amber-50/80 border border-amber-200/90 p-4 text-xs text-amber-900 space-y-1.5">
                                <div class="flex items-center gap-2 font-bold">
                                    <span>🔒</span>
                                    <span>Paket Presenter Terkunci Sementara</span>
                                </div>
                                <p class="text-xs text-amber-800 leading-relaxed">
                                    Paket Presenter hanya dapat dipilih setelah naskah abstrak Anda dinyatakan <strong>DITERIMA (Accepted)</strong> oleh reviewer. Silakan pantau atau kirim naskah di menu <strong>Submission</strong> terlebih dahulu.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label
                                    v-for="fee in presenterFees"
                                    :key="fee.id"
                                    :class="[
                                        'group relative flex flex-col justify-between rounded-2xl border-2 p-5 transition-all duration-300',
                                        !props.isAbstractAccepted
                                            ? 'opacity-50 cursor-not-allowed bg-slate-50/80 border-dashed border-slate-300'
                                            : (regForm.registration_fee_id === fee.id
                                                ? 'cursor-pointer border-purple-600 bg-purple-50/50 shadow-md ring-2 ring-purple-600/30 scale-[1.01]'
                                                : 'cursor-pointer border-slate-200 bg-white hover:border-purple-300 hover:shadow-sm')
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        :value="fee.id"
                                        :disabled="!props.isAbstractAccepted"
                                        v-model="regForm.registration_fee_id"
                                        class="sr-only"
                                        required
                                    />
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span
                                                :class="[
                                                    'rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider',
                                                    fee.mode === 'offline' ? 'bg-purple-100 text-purple-900' : 'bg-teal-100 text-teal-900'
                                                ]"
                                            >
                                                {{ fee.mode === 'offline' ? '📍 Onsite' : '💻 Online' }}
                                            </span>
                                            <div
                                                class="h-5 w-5 rounded-full border flex items-center justify-center transition-colors"
                                                :class="regForm.registration_fee_id === fee.id ? 'border-purple-600 bg-purple-700 text-gold' : 'border-slate-300 bg-white'"
                                            >
                                                <span v-if="regForm.registration_fee_id === fee.id" class="text-[10px] font-black">✓</span>
                                            </div>
                                        </div>

                                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-950 transition-colors">
                                            {{ fee.name }}
                                        </h3>
                                        <p class="text-xl font-black text-purple-950 font-mono mt-1.5">
                                            Rp {{ Number(fee.price).toLocaleString('id-ID') }}
                                        </p>
                                        <p v-if="fee.description" class="text-xs text-slate-500 leading-relaxed mt-2 border-t border-slate-100 pt-2">
                                            {{ fee.description }}
                                        </p>
                                    </div>

                                    <div v-if="!props.isAbstractAccepted" class="mt-3 pt-2 border-t border-dashed border-slate-200">
                                        <span class="text-[10px] text-amber-700 font-semibold flex items-center gap-1">
                                            🔒 Aktif setelah naskah diterima
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Non-Presenter Packages (Direct Ticketing) -->
                        <div v-if="nonPresenterFees.length > 0 && (isNonPresenterUser || props.abstractStatus === 'rejected')" class="space-y-4">
                            <div v-if="!isNonPresenterUser" class="rounded-xl bg-purple-50 p-3 text-xs text-purple-900">
                                <strong>Paket Alternatif:</strong> Sebagai opsi alternatif, Anda dapat memilih tiket peserta umum untuk tetap menghadiri seminar konferensi.
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label
                                    v-for="fee in nonPresenterFees"
                                    :key="fee.id"
                                    :class="[
                                        'group relative flex flex-col justify-between rounded-2xl border-2 p-5 transition-all duration-300 cursor-pointer',
                                        regForm.registration_fee_id === fee.id
                                            ? 'border-purple-600 bg-purple-50/60 shadow-md ring-2 ring-purple-600/30 scale-[1.01]'
                                            : 'border-slate-200 bg-white hover:border-purple-300 hover:shadow-sm'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        :value="fee.id"
                                        v-model="regForm.registration_fee_id"
                                        class="sr-only"
                                        required
                                    />
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span
                                                :class="[
                                                    'rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider',
                                                    fee.mode === 'offline' ? 'bg-purple-100 text-purple-900' : 'bg-teal-100 text-teal-900'
                                                ]"
                                            >
                                                {{ fee.mode === 'offline' ? '📍 Offline Onsite' : '💻 Online Webinar' }}
                                            </span>
                                            <div
                                                class="h-5 w-5 rounded-full border flex items-center justify-center transition-colors"
                                                :class="regForm.registration_fee_id === fee.id ? 'border-purple-600 bg-purple-700 text-gold' : 'border-slate-300 bg-white'"
                                            >
                                                <span v-if="regForm.registration_fee_id === fee.id" class="text-[10px] font-black">✓</span>
                                            </div>
                                        </div>

                                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-950 transition-colors">
                                            {{ fee.name }}
                                        </h3>
                                        <p class="text-2xl font-black text-purple-950 font-mono mt-1.5">
                                            Rp {{ Number(fee.price).toLocaleString('id-ID') }}
                                        </p>
                                        <p v-if="fee.description" class="text-xs text-slate-500 leading-relaxed mt-2 border-t border-slate-100 pt-2">
                                            {{ fee.description }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <p v-if="regForm.errors.registration_fee_id" class="text-xs text-red-600 font-semibold">
                            {{ regForm.errors.registration_fee_id }}
                        </p>
                    </div>
                </div>

                <!-- Right Column: Order Summary & Checkout Card (col-span-4) -->
                <div class="lg:col-span-4 sticky top-6">
                    <form @submit.prevent="submitRegistration" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block">RINGKASAN PESANAN</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-0.5">Konfirmasi Pendaftaran</h3>
                        </div>

                        <!-- Selected Fee Preview Card -->
                        <div v-if="selectedFee" class="rounded-2xl bg-purple-50/70 border border-purple-100 p-4 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-900 block">Paket Terpilih</span>
                                    <h4 class="text-xs font-bold text-slate-900 mt-0.5 leading-snug">{{ selectedFee.name }}</h4>
                                </div>
                                <span
                                    :class="[
                                        'rounded-md px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider shrink-0',
                                        selectedFee.mode === 'offline' ? 'bg-purple-200 text-purple-900' : 'bg-teal-200 text-teal-900'
                                    ]"
                                >
                                    {{ selectedFee.mode }}
                                </span>
                            </div>

                            <div class="border-t border-purple-200/60 pt-2.5 flex items-baseline justify-between">
                                <span class="text-xs text-slate-600 font-medium">Total Tagihan:</span>
                                <span class="text-lg font-black font-mono text-purple-950">
                                    Rp {{ Number(selectedFee.price).toLocaleString('id-ID') }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="rounded-2xl bg-slate-50 border border-slate-200/60 p-4 text-center text-xs text-slate-400">
                            Pilih salah satu paket di samping untuk melihat rincian biaya.
                        </div>

                        <!-- Notes (Optional) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea
                                v-model="regForm.notes"
                                rows="3"
                                placeholder="Instansi pada kuitansi..."
                                class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 p-3 focus:bg-white focus:border-purple-600 focus:outline-none transition resize-none placeholder:text-slate-400"
                            ></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="regForm.processing || !regForm.registration_fee_id || (!isNonPresenterUser && !props.isAbstractAccepted)"
                            class="w-full rounded-2xl bg-purple-900 hover:bg-purple-950 text-gold font-extrabold text-xs py-3.5 px-4 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                        >
                            <span v-if="regForm.processing" class="animate-spin text-sm">⏳</span>
                            <span>{{ regForm.processing ? 'Menerbitkan Invoice...' : 'Terbitkan Invoice & Bayar →' }}</span>
                        </button>

                        <!-- Safety notes -->
                        <div class="border-t border-slate-100 pt-3 space-y-2 text-[11px] text-slate-500">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-600 text-xs">✓</span>
                                <span>Rekening transfer resmi universitas</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-purple-600 text-xs">✓</span>
                                <span>Nomor invoice diterbitkan seketika</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 2: INVOICE & PAYMENT PROOF UPLOAD     -->
            <!-- ========================================== -->
            <div v-else class="space-y-6">
                <!-- Registration & Invoice Status Card -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">INVOICE NUMBER</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <h2 class="text-xl font-black text-purple-900 font-mono">{{ existingRegistration.invoice_number }}</h2>
                                <button
                                    type="button"
                                    @click="copyItem('invoice', existingRegistration.invoice_number)"
                                    class="inline-flex items-center gap-1 text-slate-400 hover:text-purple-800 transition cursor-pointer p-0.5"
                                    title="Copy Invoice Number"
                                >
                                    <span class="material-symbols-outlined text-[16px] leading-none">
                                        {{ copiedKey === 'invoice' ? 'check' : 'content_copy' }}
                                    </span>
                                    <span v-if="copiedKey === 'invoice'" class="text-emerald-600 font-bold text-[10px]">Copied!</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="[
                                'inline-flex items-center rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider border',
                                payment?.status === 'verified' || existingRegistration.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                payment?.status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' :
                                payment?.status === 'pending' || existingRegistration.status === 'waiting_verification' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                'bg-amber-50 text-amber-700 border-amber-200'
                            ]">
                                {{ payment?.status ? (payment.status === 'pending' ? 'Waiting Verification' : payment.status.replace('_', ' ')) : 'Unpaid' }}
                            </span>
                        </div>
                    </div>

                    <!-- Invoice Summary Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 text-xs">
                        <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                            <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Paket Pilihan</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ selectedPackageName }}</span>
                            <span class="inline-block mt-1 text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-purple-100 text-purple-800">
                                {{ selectedPackageMode }}
                            </span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                            <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block">Total Tagihan</span>
                            <span class="font-black text-purple-950 text-base font-mono block mt-0.5">
                                Rp {{ invoiceAmount.toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Two-Column Payment Details & Receipt Upload -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: Bank Transfer Details Card -->
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">
                            Bank Transfer Instructions
                        </h3>

                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2 text-xs text-slate-700">
                            <p class="font-bold text-slate-900">Please transfer the exact amount to:</p>
                            <div class="space-y-1.5 py-1">
                                <p><span class="text-slate-400 font-sans">Bank:</span> <strong>{{ activeConference?.bank_name || 'Bank JATIM' }}</strong></p>
                                
                                <div class="flex items-center gap-2">
                                    <p><span class="text-slate-400 font-sans">Account No:</span> <strong class="text-purple-900 font-mono text-sm tracking-wider">{{ activeConference?.bank_account_number || '0323300101' }}</strong></p>
                                    <button
                                        type="button"
                                        @click="copyItem('bank', activeConference?.bank_account_number || '0323300101')"
                                        class="inline-flex items-center gap-1 text-slate-400 hover:text-purple-800 transition cursor-pointer p-0.5"
                                        title="Copy Account Number"
                                    >
                                        <span class="material-symbols-outlined text-[16px] leading-none">
                                             {{ copiedKey === 'bank' ? 'check' : 'content_copy' }}
                                        </span>
                                        <span v-if="copiedKey === 'bank'" class="text-emerald-600 font-bold text-[10px]">Copied!</span>
                                    </button>
                                </div>

                                <p><span class="text-slate-400 font-sans">Account Name:</span> <strong>{{ activeConference?.bank_account_holder || 'MARS FK UMSURA' }}</strong></p>

                                <div class="flex items-center gap-2 pt-1 border-t border-slate-200">
                                    <p><span class="text-slate-400 font-sans">Amount:</span> <strong class="text-purple-950 font-mono text-sm">Rp {{ invoiceAmount.toLocaleString('id-ID') }}</strong></p>
                                    <button
                                        type="button"
                                        @click="copyItem('amount', String(invoiceAmount))"
                                        class="inline-flex items-center gap-1 text-slate-400 hover:text-purple-800 transition cursor-pointer p-0.5"
                                        title="Copy Amount"
                                    >
                                        <span class="material-symbols-outlined text-[16px] leading-none">
                                             {{ copiedKey === 'amount' ? 'check' : 'content_copy' }}
                                        </span>
                                        <span v-if="copiedKey === 'amount'" class="text-emerald-600 font-bold text-[10px]">Copied!</span>
                                    </button>
                                </div>
                            </div>
                            <p v-if="activeConference?.bank_instructions" class="text-[11px] text-slate-600 pt-1 border-t border-slate-200">
                                {{ activeConference.bank_instructions }}
                            </p>
                            <p class="text-[11px] text-slate-500 italic pt-1 border-t border-slate-200">
                                Include Invoice Number <strong>{{ existingRegistration.invoice_number }}</strong> in transfer description.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Payment Proof Upload Form -->
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">
                            Payment Receipt
                        </h3>

                        <!-- If verified -->
                        <div v-if="payment?.status === 'verified'" class="rounded-xl bg-emerald-50 border border-emerald-200 p-5 text-center text-xs text-emerald-800 space-y-2.5">
                            <div class="text-2xl">✅</div>
                            <p class="font-black text-sm text-emerald-950">Payment Verified & Approved</p>
                            
                            <!-- Verified At Timestamp -->
                            <div v-if="payment?.verified_at" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/90 text-emerald-900 font-bold text-[11px] border border-emerald-200">
                                <span>🕒 Approved on:</span>
                                <span>{{ formatDateTime(payment.verified_at) }}</span>
                            </div>

                            <p class="pt-1 text-slate-600">Your registration is confirmed. You can now attend the event and download your certificate once the conference concludes.</p>
                            <div v-if="payment?.proof_file" class="pt-2">
                                <button
                                    @click="isProofModalOpen = true"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 underline hover:text-emerald-900 cursor-pointer"
                                >
                                    🔍 View Uploaded Receipt
                                </button>
                            </div>
                        </div>

                        <!-- If waiting verification (already uploaded) -->
                        <div v-else-if="(payment?.status === 'pending' || existingRegistration.status === 'waiting_verification') && !showReuploadForm" class="rounded-xl bg-blue-50 border border-blue-200 p-5 text-center text-xs text-blue-900 space-y-2.5">
                            <div class="text-2xl">⏳</div>
                            <p class="font-black text-sm text-blue-950">Payment Proof Submitted</p>
                            
                            <!-- Submitted At Timestamp -->
                            <div v-if="payment?.paid_at || payment?.created_at" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100/90 text-blue-900 font-bold text-[11px] border border-blue-200">
                                <span>🕒 Submitted on:</span>
                                <span>{{ formatDateTime(payment.paid_at || payment.created_at) }}</span>
                            </div>

                            <p class="text-blue-800 pt-1">
                                Your payment proof is currently being verified by the committee (takes 1-2 business days).
                            </p>
                            <div class="flex items-center justify-center gap-3 pt-1">
                                <button
                                    v-if="payment?.proof_file"
                                    @click="isProofModalOpen = true"
                                    type="button"
                                    class="rounded-xl bg-blue-100 hover:bg-blue-200 text-blue-900 px-3.5 py-1.5 font-bold text-xs transition cursor-pointer"
                                >
                                    🔍 View Receipt
                                </button>
                                <button
                                    @click="showReuploadForm = true"
                                    type="button"
                                    class="rounded-xl border border-blue-300 bg-white hover:bg-blue-50 text-blue-900 px-3.5 py-1.5 font-bold text-xs transition cursor-pointer"
                                >
                                    🔄 Update Receipt
                                </button>
                            </div>
                        </div>

                        <!-- If not uploaded yet OR rejected OR user clicks update receipt -->
                        <form v-else @submit.prevent="submitPayment" class="space-y-4">
                            <!-- Rejection Alert -->
                            <div v-if="payment?.status === 'rejected'" class="rounded-xl bg-red-50 border border-red-200 p-3 text-xs text-red-700">
                                <p class="font-bold">❌ Previous Proof Rejected</p>
                                <p class="mt-0.5 text-[11px]">{{ payment?.rejection_reason || 'Please upload a clearer receipt photo showing transaction date and amount.' }}</p>
                            </div>

                            <div v-if="showReuploadForm" class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                <span class="text-xs font-bold text-slate-700">Replacing Existing Receipt</span>
                                <button @click="showReuploadForm = false" type="button" class="text-xs text-slate-500 hover:text-slate-800 underline">Cancel</button>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                                <select v-model="paymentForm.payment_method" class="w-full text-xs font-semibold rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white focus:border-purple-600">
                                    <optgroup label="Bank Transfer (Indonesia)">
                                        <option value="Bank Transfer (Bank JATIM)">Bank JATIM</option>
                                        <option value="Bank Transfer (BSI)">Bank Syariah Indonesia (BSI)</option>
                                        <option value="Bank Transfer (Mandiri)">Bank Mandiri</option>
                                        <option value="Bank Transfer (BCA)">Bank Central Asia (BCA)</option>
                                        <option value="Bank Transfer (BRI)">Bank Rakyat Indonesia (BRI)</option>
                                        <option value="Bank Transfer (BNI)">Bank Negara Indonesia (BNI)</option>
                                    </optgroup>
                                    <optgroup label="E-Wallet & QRIS">
                                        <option value="QRIS / E-Wallet (GoPay, OVO, Dana, ShopeePay)">QRIS / E-Wallet (GoPay, OVO, Dana, ShopeePay)</option>
                                    </optgroup>
                                    <optgroup label="Credit Card & International">
                                        <option value="Credit Card / Debit Card (Visa / Mastercard)">Credit Card / Debit Card (Visa / Mastercard)</option>
                                        <option value="International Wire / TT (SWIFT)">International Wire Transfer (SWIFT / Telegraphic Transfer)</option>
                                        <option value="PayPal / Stripe">PayPal / Stripe</option>
                                    </optgroup>
                                    <optgroup label="Other">
                                        <option value="Institutional Sponsorship / Invoice Billing">Institutional Sponsorship / Invoice Billing</option>
                                        <option value="Other Payment Method">Other Payment Method</option>
                                    </optgroup>
                                </select>
                                <p v-if="paymentForm.errors.payment_method" class="text-xs font-bold text-red-600 mt-1">
                                    {{ paymentForm.errors.payment_method }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Receipt / Transfer Slip File <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,application/pdf"
                                    @change="onFileChange"
                                    required
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer border border-slate-200 rounded-xl p-1"
                                />
                                <p class="text-[10px] text-slate-400 mt-1">Accepted: JPG, PNG, PDF (Max 5MB)</p>
                                <p v-if="paymentForm.errors.proof_file" class="text-xs font-bold text-red-600 mt-1">
                                    {{ paymentForm.errors.proof_file }}
                                </p>
                                <p v-if="paymentForm.errors.registration_id" class="text-xs font-bold text-red-600 mt-1">
                                    {{ paymentForm.errors.registration_id }}
                                </p>
                            </div>

                            <!-- Preview -->
                            <div v-if="proofPreview" class="pt-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Selected Preview</span>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-2 text-center">
                                    <img v-if="!isPdf(proofPreview)" :src="proofPreview" alt="Receipt Preview" class="max-h-40 mx-auto rounded-lg object-contain shadow-xs" />
                                    <span v-else class="text-xs font-bold text-purple-800">📄 PDF Document Ready to Upload</span>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="paymentForm.processing || !paymentForm.proof_file"
                                class="w-full rounded-xl bg-purple-900 hover:bg-purple-950 text-gold font-bold text-xs py-2.5 transition shadow-xs cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <span v-if="paymentForm.processing" class="animate-spin">⏳</span>
                                <span>{{ paymentForm.processing ? 'Uploading Payment Proof...' : 'Submit Payment Proof →' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Receipt Modal -->
            <div v-if="isProofModalOpen && payment?.proof_file" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
                <div class="relative max-w-2xl w-full bg-white rounded-2xl p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-sm text-slate-900">Your Uploaded Receipt</h4>
                        <button @click="isProofModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                    </div>
                    <div class="max-h-[65vh] overflow-y-auto text-center">
                        <img
                            v-if="!isPdf(payment.proof_file)"
                            :src="payment.proof_url || formatStorageUrl(payment.proof_file)"
                            alt="Receipt Proof"
                            class="max-h-[60vh] mx-auto rounded-xl shadow-xs"
                        />
                        <a
                            v-else
                            :href="payment.proof_url || formatStorageUrl(payment.proof_file)"
                            target="_blank"
                            class="inline-block rounded-xl bg-purple-100 text-purple-800 font-bold px-4 py-2 text-xs"
                        >
                            Open PDF Receipt in New Tab ↗
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </ParticipantLayout>
</template>
