/**
 * Vue Composable for formatting currency into Indonesian Rupiah (IDR).
 *
 * @example
 * // In Script Setup:
 * import { useFormatRupiah } from '@/Composables/useFormatRupiah';
 * const { formatRupiah } = useFormatRupiah();
 *
 * // Or direct helper usage:
 * import { formatRupiah } from '@/Composables/useFormatRupiah';
 * formatRupiah(1500000); // "Rp 1.500.000"
 * formatRupiah(1500000, false); // "1.500.000"
 */

/**
 * Format numeric value to Indonesian Rupiah string.
 *
 * @param {number|string|null} amount
 * @param {boolean} [withPrefix=true] - Prepend 'Rp ' to output
 * @returns {string}
 */
export function formatRupiah(amount, withPrefix = true) {
    if (amount === null || amount === undefined || isNaN(Number(amount))) {
        return withPrefix ? 'Rp 0' : '0';
    }
    const num = Math.round(Number(amount));
    const formatted = num.toLocaleString('id-ID');
    return withPrefix ? `Rp ${formatted}` : formatted;
}

/**
 * Vue Composable wrapper.
 */
export function useFormatRupiah() {
    return {
        formatRupiah,
    };
}
