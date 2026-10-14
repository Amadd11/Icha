<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Timeline;
use App\Models\User;

class DashboardService
{
    /**
     * Get aggregated dashboard statistics and financial recap summary.
     */
    public function getDashboardData(?int $conferenceId = null): array
    {
        // Get target conference or default to current active conference
        $selectedConference = $conferenceId
            ? Conference::find($conferenceId)
            : Conference::active()->first();

        if (!$selectedConference) {
            $selectedConference = Conference::latest()->first();
        }

        $activeConferenceId = $selectedConference?->id;

        // Base queries scoped by conference_id
        $registrationQuery = Registration::query();
        $paymentQuery      = Payment::query();
        $abstractQuery     = AbstractSubmission::query();

        if ($activeConferenceId) {
            $registrationQuery->where('conference_id', $activeConferenceId);
            $paymentQuery->whereHas('registration', function ($q) use ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId);
            });
            $abstractQuery->where('conference_id', $activeConferenceId);
        }

        // 1. Participant User Statistics (Seluruh akun pendaftar)
        $participantUsersQuery = User::where('role', 'participant');
        $totalParticipants = (clone $participantUsersQuery)->count();

        $presentersCount = (clone $participantUsersQuery)
            ->whereHas('abstractSubmissions', function ($q) use ($activeConferenceId) {
                if ($activeConferenceId) {
                    $q->where('conference_id', $activeConferenceId);
                }
            })->count();

        $nonPresentersCount = max(0, $totalParticipants - $presentersCount);
        $totalRegistrations = (clone $registrationQuery)->count();

        // 2. Financial Metrics (Invoiced vs Received)
        // A. Total Tagihan Terbit (Seluruh invoice pendaftaran yang tercatat di sistem)
        $totalInvoicedIdr = (clone $registrationQuery)->sum('amount');

        // B. Dana Masuk Terverifikasi (Lunas)
        $verifiedRevenueIdr = (clone $paymentQuery)->where('status', 'verified')->sum('amount');
        $verifiedPaymentsCount = (clone $paymentQuery)->where('status', 'verified')->count();

        // C. Antrean Verifikasi Bukti Bayar (Struk terunggah menunggu persetujuan admin)
        $pendingPaymentsQuery = (clone $paymentQuery)->where('status', 'pending');
        $pendingPaymentsCount = (clone $pendingPaymentsQuery)->count();
        $pendingPaymentsIdr = (clone $pendingPaymentsQuery)->sum('amount');

        // D. Sisa Tagihan Belum Lunas (Seluruh invoice pendaftaran yang belum berstatus lunas)
        $unpaidRegistrationsQuery = (clone $registrationQuery)->whereDoesntHave('payment', fn($q) => $q->where('status', 'verified'));
        $unpaidInvoicesCount = (clone $unpaidRegistrationsQuery)->count();
        $unpaidRevenueIdr = (clone $unpaidRegistrationsQuery)->sum('amount');

        // 3. Abstract Review Progress Breakdown
        $totalAbstracts       = (clone $abstractQuery)->count();
        $acceptedAbstracts    = (clone $abstractQuery)->where('status', 'accepted')->count();
        $underReviewAbstracts = (clone $abstractQuery)->whereIn('status', ['pending', 'under_review'])->count();
        $revisionAbstracts    = (clone $abstractQuery)->where('status', 'revision_required')->count();
        $rejectedAbstracts    = (clone $abstractQuery)->where('status', 'rejected')->count();

        $stats = [
            'total_participants'     => $totalParticipants,
            'presenters_count'       => $presentersCount,
            'non_presenters_count'   => $nonPresentersCount,
            'total_registrations'    => $totalRegistrations,

            // Financial Metrics
            'total_invoiced_idr'     => $totalInvoicedIdr,
            'verified_revenue_idr'   => $verifiedRevenueIdr,
            'verified_payments'      => $verifiedPaymentsCount,
            'unpaid_revenue_idr'     => $unpaidRevenueIdr,
            'unpaid_invoices_count'  => $unpaidInvoicesCount,
            'pending_payments'       => $pendingPaymentsCount,
            'pending_payments_idr'   => $pendingPaymentsIdr,

            // Abstract Peer Review Breakdown
            'total_abstracts'        => $totalAbstracts,
            'accepted_abstracts'     => $acceptedAbstracts,
            'under_review_abstracts' => $underReviewAbstracts,
            'revision_abstracts'     => $revisionAbstracts,
            'rejected_abstracts'     => $rejectedAbstracts,
        ];

        // Track Category Recap Breakdown
        $trackCategories = Category::where(function ($q) use ($activeConferenceId) {
            if ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId)->orWhereNull('conference_id');
            }
        })->withCount([
            'abstracts' => function ($q) use ($activeConferenceId) {
                if ($activeConferenceId) $q->where('conference_id', $activeConferenceId);
            },
            'abstracts as accepted_count' => function ($q) use ($activeConferenceId) {
                $q->where('status', 'accepted');
                if ($activeConferenceId) $q->where('conference_id', $activeConferenceId);
            },
            'abstracts as under_review_count' => function ($q) use ($activeConferenceId) {
                $q->whereIn('status', ['pending', 'under_review']);
                if ($activeConferenceId) $q->where('conference_id', $activeConferenceId);
            },
        ])->get()->map(function ($cat) {
            return [
                'id'                 => $cat->id,
                'name'               => $cat->name,
                'badge'              => $cat->badge,
                'abstracts_count'    => $cat->abstracts_count,
                'accepted_count'     => $cat->accepted_count,
                'under_review_count' => $cat->under_review_count,
            ];
        });

        // Recent Registrations
        $recentRegistrations = (clone $registrationQuery)
            ->with([
                'user:id,name,email',
                'registrationFee:id,name,type',
                'payment:id,registration_id,status,amount,currency,paid_at,verified_at,created_at',
                'user.abstractSubmissions' => function ($q) use ($activeConferenceId) {
                    if ($activeConferenceId) $q->where('conference_id', $activeConferenceId);
                }
            ])
            ->latest()
            ->take(8)
            ->get();

        // Available Conferences list
        $availableConferences = Conference::select('id', 'title', 'year', 'slug', 'is_active', 'status')
            ->orderByDesc('year')
            ->get();

        // Timeline Schedule
        $timelineRecords = $activeConferenceId 
            ? Timeline::where('conference_id', $activeConferenceId)->orderBy('order')->get()
            : Timeline::orderBy('order')->get();

        $deadlines = $timelineRecords->map(fn($t) => [
            'label'  => $t->title,
            'date'   => $t->period ?? $t->date,
            'status' => $t->is_completed ? 'Completed' : 'Active',
        ]);

        return [
            'selectedConference'   => $selectedConference,
            'availableConferences' => $availableConferences,
            'stats'                => $stats,
            'trackCategories'      => $trackCategories,
            'recentRegistrations'  => $recentRegistrations,
            'deadlines'            => $deadlines,
        ];
    }
}
