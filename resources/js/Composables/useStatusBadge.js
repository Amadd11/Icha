export function useStatusBadge() {
    const badgeStyles = {
        // Success states
        paid: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        verified: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        accepted: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        published: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        uploaded: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        completed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        active: 'bg-emerald-100 text-emerald-800 border-emerald-200',

        // Pending / Info states
        pending: 'bg-blue-100 text-blue-800 border-blue-200',
        waiting_verification: 'bg-blue-100 text-blue-800 border-blue-200',
        under_review: 'bg-blue-100 text-blue-800 border-blue-200',
        in_review: 'bg-blue-100 text-blue-800 border-blue-200',
        submitted: 'bg-blue-100 text-blue-800 border-blue-200',

        // Warning / Revision states
        unpaid: 'bg-amber-100 text-amber-800 border-amber-200',
        minor_revision: 'bg-amber-100 text-amber-800 border-amber-200',
        major_revision: 'bg-amber-100 text-amber-800 border-amber-200',
        revision_required: 'bg-amber-100 text-amber-800 border-amber-200',
        draft: 'bg-slate-100 text-slate-700 border-slate-200',

        // Danger / Rejected states
        rejected: 'bg-rose-100 text-rose-800 border-rose-200',
        declined: 'bg-rose-100 text-rose-800 border-rose-200',
        cancelled: 'bg-rose-100 text-rose-800 border-rose-200',
        inactive: 'bg-slate-100 text-slate-600 border-slate-200',

        // Special roles
        presenter: 'bg-purple-100 text-purple-800 border-purple-200',
        author: 'bg-purple-100 text-purple-800 border-purple-200',
        speaker: 'bg-amber-100 text-amber-900 border-amber-200',
        participant: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    };

    const statusLabels = {
        paid: 'Paid ✓',
        verified: 'Verified ✓',
        waiting_verification: 'Waiting Verification',
        unpaid: 'Unpaid',
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
