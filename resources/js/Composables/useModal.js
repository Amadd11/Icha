import { ref } from 'vue';

/**
 * Composable for managing modal open/close state and active payload data.
 * 
 * @param {boolean} initialState - Initial modal open state (default: false)
 * @returns {Object} { isOpen, activeItem, open, close, toggle }
 */
export function useModal(initialState = false) {
    const isOpen = ref(initialState);
    const activeItem = ref(null);

    function open(item = null) {
        activeItem.value = item;
        isOpen.value = true;
    }

    function close() {
        isOpen.value = false;
        activeItem.value = null;
    }

    function toggle() {
        isOpen.value = !isOpen.value;
        if (!isOpen.value) {
            activeItem.value = null;
        }
    }

    return {
        isOpen,
        activeItem,
        open,
        close,
        toggle,
    };
}
