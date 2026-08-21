<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTableFilter } from '@/Composables/useTableFilter';
import { useStatusBadge } from '@/Composables/useStatusBadge';
import { useModal } from '@/Composables/useModal';

const props = defineProps({
    conferences: Array,
    selectedConferenceId: Number,
    selectedConference: Object,
    participants: Array,
    filters: Object,
    stats: Object,
});

const { filters, applyFilter } = useTableFilter('admin.certificates.index', {
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    conference_id: props.selectedConferenceId,
});

const { getBadgeClass, getStatusLabel } = useStatusBadge();
const { isOpen: isModalOpen, activeItem: activeParticipant, open: openModal, close: closeModalState } = useModal();

const form = useForm({
    user_id: null,
    conference_id: props.selectedConferenceId,
    file: null,
});

const fileName = ref('');

function openUploadModal(participant) {
    form.reset();
    form.user_id = participant.user_id;
    form.conference_id = props.selectedConferenceId;
    form.file = null;
    fileName.value = '';
    openModal(participant);
}

function closeModal() {
    closeModalState();
    form.reset();
    fileName.value = '';
}

function handleFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.file = file;
        fileName.value = file.name;
    }
}

function submitUpload() {
    form.post(route('admin.certificates.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeModal();
        },
    });
}

function deleteCertificate(certId) {
    if (confirm('Are you sure you want to delete this certificate and its PDF file?')) {
        router.delete(route('admin.certificates.destroy', certId), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Participant Certificates Management" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">Participant Certificates</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Upload and manage official E-Certificate PDF documents for conference participants.</p>
                </div>

                <!-- Conference Switcher -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Conference:</label>
                    <select
                        v-model="filters.conference_id"
                        @change="applyFilter()"
                        class="admin-input py-2 text-xs font-bold w-auto min-w-[200px]"
                    >
                        <option v-for="c in conferences" :key="c.id" :value="c.id">
                            {{ c.title }} ({{ c.year }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Registered</span>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ stats.total_participants }}</p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Certificates Ready (Uploaded)</span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-2xl font-black text-emerald-950 mt-1">{{ stats.uploaded_count }}</p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Pending Upload</span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-2xl font-black text-amber-950 mt-1">{{ stats.pending_count }}</p>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200">
                <div class="relative w-full sm:w-80 flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px] pointer-events-none leading-none select-none">
                        search
                    </span>
                    <input
                        v-model="filters.search"
                        @keyup.enter="applyFilter()"
                        type="text"
                        placeholder="Search by name or email..."
                        class="admin-input text-xs !pl-10"
                    />
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select
                        v-model="filters.status"
                        @change="applyFilter()"
                        class="admin-input py-2 text-xs font-bold w-full sm:w-auto"
                    >
                        <option value="all">All Statuses</option>
                        <option value="uploaded">Uploaded / Ready</option>
                        <option value="not_uploaded">Not Uploaded (Pending)</option>
                    </select>

                    <button
                        @click="applyFilter()"
                        class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0 cursor-pointer"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <!-- Participants Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Participant</th>
                            <th class="px-4 py-4">Category / Package</th>
                            <th class="px-4 py-4">Payment</th>
                            <th class="px-5 py-4 text-right">Official Certificate (PDF)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-if="participants.length === 0">
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                No participants found for this conference.
                            </td>
                        </tr>
                        <tr
                            v-for="p in participants"
                            :key="p.user_id"
                            class="hover:bg-slate-50/70 transition"
                        >
                            <!-- Participant Name & Info -->
                            <td class="px-5 py-4">
                                <span class="font-extrabold text-slate-900 block text-xs">{{ p.name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ p.email }}</span>
                                <span v-if="p.institution !== '-'" class="text-[10px] text-purple-700 font-semibold mt-0.5 inline-block">
                                    🏛️ {{ p.institution }}
                                </span>
                            </td>

                            <!-- Package / Role -->
                            <td class="px-4 py-4">
                                <span class="font-bold text-slate-800 block">{{ p.registration_package }}</span>
                                <span v-if="p.invoice_number !== '-'" class="font-mono text-[10px] text-slate-400">
                                    {{ p.invoice_number }}
                                </span>
                            </td>

                            <!-- Payment Status -->
                            <td class="px-4 py-4">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border',
                                    getBadgeClass(p.payment_status)
                                ]">
                                    {{ getStatusLabel(p.payment_status) }}
                                </span>
                            </td>

                            <!-- Certificate Action Column -->
                            <td class="px-5 py-4 text-right">
                                <div v-if="p.certificate && p.certificate.file_path" class="flex items-center justify-end gap-3 text-xs">
                                    <a
                                        :href="p.certificate.file_url"
                                        target="_blank"
                                        class="font-bold text-purple-700 hover:underline"
                                    >
                                        View
                                    </a>
                                    <button
                                        @click="openUploadModal(p)"
                                        class="font-semibold text-slate-600 hover:text-slate-900 cursor-pointer"
                                    >
                                        Replace
                                    </button>
                                    <button
                                        @click="deleteCertificate(p.certificate.id)"
                                        class="font-semibold text-red-600 hover:text-red-800 cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </div>
                                <div v-else class="flex justify-end">
                                    <button
                                        @click="openUploadModal(p)"
                                        class="rounded-lg bg-gold hover:bg-amber-400 text-slate-950 px-3 py-1 font-bold text-xs transition cursor-pointer"
                                    >
                                        Upload
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Certificate Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
        >
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 space-y-5 animate-fade-in-scale">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700">Official Certificate Upload</span>
                        <h2 class="text-base font-black text-slate-900">Upload Certificate PDF</h2>
                    </div>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
                </div>

                <div v-if="activeParticipant" class="bg-purple-50/60 rounded-2xl p-4 border border-purple-100 space-y-1.5 text-xs text-purple-950">
                    <div class="flex justify-between items-center border-b border-purple-100/80 pb-1.5">
                        <span class="font-bold">{{ activeParticipant.name }}</span>
                        <span :class="[
                            'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border',
                            getBadgeClass(activeParticipant.payment_status)
                        ]">
                            {{ getStatusLabel(activeParticipant.payment_status) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-0.5">
                        <p><strong>Package:</strong> <span class="text-purple-700 font-bold">{{ activeParticipant.registration_package }}</span></p>
                        <p><strong>Invoice:</strong> <span class="font-mono text-slate-600">{{ activeParticipant.invoice_number }}</span></p>
                        <p class="col-span-2"><strong>Email:</strong> {{ activeParticipant.email }}</p>
                        <p class="col-span-2" v-if="activeParticipant.institution !== '-'"><strong>Institution:</strong> {{ activeParticipant.institution }}</p>
                    </div>
                </div>

                <form @submit.prevent="submitUpload" class="space-y-4">
                    <!-- File Upload Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Certificate File (.pdf) <span class="text-red-500">*</span></label>
                        <input
                            type="file"
                            accept=".pdf"
                            @change="handleFileChange"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-gold file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-slate-950 hover:file:bg-amber-400 cursor-pointer"
                            required
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Upload the official signed PDF certificate (Max 20MB). Certificate code and package role will be assigned automatically.</p>
                        <p v-if="form.errors.file" class="text-xs font-bold text-red-600 mt-1">{{ form.errors.file }}</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold shadow-md transition cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Uploading...' : 'Save & Upload Certificate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
