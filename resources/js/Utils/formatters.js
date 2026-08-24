/**
 * Format storage file path to accessible public URL.
 *
 * @param {string|null} path
 * @returns {string}
 */
export function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

/**
 * Format numeric amount to localized currency string (IDR / USD).
 *
 * @param {number|string} amount
 * @param {string} currency
 * @returns {string}
 */
export function formatCurrency(amount, currency = 'IDR') {
    const num = Number(amount) || 0;
    if (currency.toUpperCase() === 'USD') {
        return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return 'Rp ' + num.toLocaleString('id-ID');
}

/**
 * Format ISO date string to human-readable date.
 *
 * @param {string|Date} date
 * @param {Intl.DateTimeFormatOptions} [options]
 * @returns {string}
 */
export function formatDate(date, options = { year: 'numeric', month: 'short', day: 'numeric' }) {
    if (!date) return '-';
    const d = typeof date === 'string' ? new Date(date) : date;
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('id-ID', options);
}

/**
 * Format ISO date string to human-readable date & time in English (e.g. 24 Aug 2026, 14:30).
 *
 * @param {string|Date} date
 * @returns {string}
 */
export function formatDateTime(date) {
    if (!date) return '-';
    const d = typeof date === 'string' ? new Date(date) : date;
    if (isNaN(d.getTime())) return '-';
    
    return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }) + ', ' + d.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}
