export function useStatusBadge() {
    const badgeStyles = {
        // Success states (Green / Emerald)
        paid: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        verified: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        accepted: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        published: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        uploaded: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        completed: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        active: 'bg-emerald-50 text-emerald-700 border-emerald-200/70',

        // Waiting Verification / Review states
        waiting_verification: 'bg-amber-50 text-amber-700 border-amber-200/70',
        waiting: 'bg-amber-50 text-amber-700 border-amber-200/70',
        under_review: 'bg-blue-50 text-blue-700 border-blue-200/70',
        in_review: 'bg-blue-50 text-blue-700 border-blue-200/70',
        submitted: 'bg-sky-50 text-sky-700 border-sky-200/70',

        // Warning / Unpaid / Revision states
        unpaid: 'bg-amber-50 text-amber-700 border-amber-200/70',
        pending: 'bg-amber-50 text-amber-700 border-amber-200/70',
        minor_revision: 'bg-orange-50 text-orange-700 border-orange-200/70',
        major_revision: 'bg-orange-50 text-orange-700 border-orange-200/70',
        revision_required: 'bg-orange-50 text-orange-700 border-orange-200/70',
        draft: 'bg-slate-50 text-slate-600 border-slate-200/70',

        // Danger / Rejected states (Rose)
        rejected: 'bg-rose-50 text-rose-700 border-rose-200/70',
        declined: 'bg-rose-50 text-rose-700 border-rose-200/70',
        cancelled: 'bg-rose-50 text-rose-700 border-rose-200/70',
        inactive: 'bg-slate-50 text-slate-500 border-slate-200/70',

        // Special roles
        presenter: 'bg-purple-50 text-purple-700 border-purple-200/70',
        author: 'bg-purple-50 text-purple-700 border-purple-200/70',
        speaker: 'bg-amber-50 text-amber-800 border-amber-200/70',
        participant: 'bg-indigo-50 text-indigo-700 border-indigo-200/70',
    };

    const statusLabels = {
        paid: 'Paid',
        verified: 'Paid',
        waiting_verification: 'Waiting Verification',
        waiting: 'Waiting Verification',
        unpaid: 'Unpaid',
        pending: 'Pending',
        under_review: 'Under Review',
        accepted: 'Accepted',
        rejected: 'Rejected',
        minor_revision: 'Minor Revision',
        major_revision: 'Major Revision',
        revision_required: 'Revision Required',
    };

    function getBadgeClass(status) {
        if (!status) return 'bg-slate-100 text-slate-700 border-slate-200';
        const key = String(status).toLowerCase();
        return badgeStyles[key] || 'bg-slate-100 text-slate-700 border-slate-200';
    }

    function getStatusLabel(status) {
        if (!status) return '-';
        const key = String(status).toLowerCase();
        if (statusLabels[key]) return statusLabels[key];
        return String(status).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    return {
        getBadgeClass,
        getStatusLabel,
    };
}
