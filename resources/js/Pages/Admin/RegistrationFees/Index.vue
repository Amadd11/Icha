<script setup>
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DeleteConfirmModal from "@/Components/DeleteConfirmModal.vue";
import { useDeleteConfirm } from "@/Composables/useDeleteConfirm";
import FeeStatsCards from "./Partials/FeeStatsCards.vue";
import FeeTable from "./Partials/FeeTable.vue";
import FeeFormModal from "./Partials/FeeFormModal.vue";

const props = defineProps({
    registrationFees: Array,
    conferences: Array,
    selectedConfId: Number,
});

// Modal state
const isModalOpen = ref(false);
const editingFee = ref(null);

function openCreateModal() {
    editingFee.value = null;
    isModalOpen.value = true;
}

function openEditModal(item) {
    editingFee.value = item;
    isModalOpen.value = true;
}

// Delete Confirmation
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

// Computed stats
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

            <!-- Metric Cards Component -->
            <FeeStatsCards :stats="stats" />

            <!-- Table with Filter & Search Component -->
            <FeeTable
                :fees="props.registrationFees"
                :stats="stats"
                @edit="openEditModal"
                @delete="deleteFee"
            />

            <!-- Create / Edit Registration Fee Modal -->
            <FeeFormModal
                :show="isModalOpen"
                :editing-fee="editingFee"
                :conferences="props.conferences"
                :default-conference-id="props.selectedConfId || props.conferences?.[0]?.id"
                @close="isModalOpen = false"
            />

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
