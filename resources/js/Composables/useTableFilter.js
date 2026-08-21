import { reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useTableFilter(routeName, initialFilters = {}, options = {}) {
    const filters = reactive({ ...initialFilters });
    const preserveScroll = options.preserveScroll ?? true;
    const preserveState = options.preserveState ?? true;

    function applyFilter(customParams = {}) {
        const queryParams = { ...filters, ...customParams };

        // Clean empty / 'all' values for clean URLs if desired
        const cleanParams = {};
        for (const [key, value] of Object.entries(queryParams)) {
            if (value !== '' && value !== null && value !== undefined) {
                cleanParams[key] = value;
            }
        }

        router.get(
            typeof routeName === 'function' ? routeName() : route(routeName),
            cleanParams,
            {
                preserveScroll,
                preserveState,
                replace: true,
            }
        );
    }

    function resetFilter() {
        for (const key of Object.keys(filters)) {
            filters[key] = initialFilters[key] ?? (key.includes('id') ? null : (key === 'status' ? 'all' : ''));
        }
        applyFilter();
    }

    const isFiltered = computed(() => {
        return Object.keys(filters).some(key => {
            const val = filters[key];
            const initVal = initialFilters[key];
            return val !== initVal && val !== '' && val !== 'all' && val !== null;
        });
    });

    return {
        filters,
        applyFilter,
        resetFilter,
        isFiltered,
    };
}
