import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useDeleteConfirm() {
    const isModalOpen = ref(false);
    const itemToDelete = ref(null);
    const deleteTitle = ref('Confirm Deletion');
    const deleteMessage = ref('Are you sure you want to delete this item?');
    const deleteUrl = ref('');
    const isDeleting = ref(false);

    function openDeleteModal({ item = null, title = 'Confirm Deletion', message = '', url = '' }) {
        itemToDelete.value = item;
        deleteTitle.value = title;
        deleteMessage.value = message || `Are you sure you want to delete "${item?.name || item?.title || 'this item'}"?`;
        deleteUrl.value = url;
        isModalOpen.value = true;
    }

    function closeDeleteModal() {
        if (!isDeleting.value) {
            isModalOpen.value = false;
            itemToDelete.value = null;
        }
    }

    function confirmDelete(callbacks = {}) {
        if (!deleteUrl.value) return;

        isDeleting.value = true;
        router.delete(deleteUrl.value, {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                itemToDelete.value = null;
                callbacks.onSuccess?.();
            },
            onError: (err) => {
                callbacks.onError?.(err);
            },
            onFinish: () => {
                isDeleting.value = false;
                callbacks.onFinish?.();
            },
        });
    }

    return {
        isModalOpen,
        itemToDelete,
        deleteTitle,
        deleteMessage,
        deleteUrl,
        isDeleting,
        openDeleteModal,
        closeDeleteModal,
        confirmDelete,
    };
}
