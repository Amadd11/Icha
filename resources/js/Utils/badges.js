/**
 * Get Tailwind color classes for user roles.
 *
 * @param {string} role
 * @returns {string}
 */
export function getRoleBadgeClass(role) {
    return {
        super_admin: 'bg-purple-50 text-purple-700 border-purple-200',
        admin:       'bg-blue-50 text-blue-700 border-blue-200',
        reviewer:    'bg-amber-50 text-amber-700 border-amber-200',
        participant: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    }[role] ?? 'bg-slate-50 text-slate-600 border-slate-200';
}

/**
 * Get Tailwind color classes for submission / payment statuses.
 *
 * @param {string} status
 * @returns {string}
 */
export function getStatusBadgeClass(status) {
    return {
        accepted:           'bg-emerald-50 text-emerald-700 border-emerald-200',
        verified:           'bg-emerald-50 text-emerald-700 border-emerald-200',
        paid:               'bg-emerald-50 text-emerald-700 border-emerald-200',
        completed:          'bg-emerald-50 text-emerald-700 border-emerald-200',
        rejected:           'bg-red-50 text-red-700 border-red-200',
        revision_required:  'bg-amber-50 text-amber-700 border-amber-200',
        pending:            'bg-amber-50 text-amber-700 border-amber-200',
        waiting_verification: 'bg-amber-50 text-amber-700 border-amber-200',
        under_review:       'bg-purple-50 text-purple-700 border-purple-200',
        in_progress:        'bg-indigo-50 text-indigo-700 border-indigo-200',
    }[status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
}
