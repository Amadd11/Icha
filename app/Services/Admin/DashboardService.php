<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Timeline;

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
        $paperQuery        = FullPaper::query();

        if ($activeConferenceId) {
            $registrationQuery->where('conference_id', $activeConferenceId);
            $paymentQuery->whereHas('registration', function ($q) use ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId);
            });
            $abstractQuery->where('conference_id', $activeConferenceId);
            $paperQuery->where('conference_id', $activeConferenceId);
        }

        // Executive Aggregate Statistics
        $totalParticipants   = (clone $registrationQuery)->distinct('user_id')->count('user_id');
        $totalRegistrations  = (clone $registrationQuery)->count();
        $verifiedPayments    = (clone $paymentQuery)->where('status', 'verified')->count();
        $pendingPayments     = (clone $paymentQuery)->where('status', 'pending')->count();

        // 💵 Total Invoiced Revenue (Daftar Tagihan dari Seluruh Peserta Mendaftar)
        $totalInvoicedIdr    = (clone $registrationQuery)->where('currency', 'IDR')->sum('amount');
        $totalInvoicedUsd    = (clone $registrationQuery)->where('currency', 'USD')->sum('amount');

        // 💰 Verified Received Revenue (Total Uang Masuk yang Sudah Lunas & Terverifikasi)
        $verifiedRevenueIdr  = (clone $paymentQuery)->where('status', 'verified')->where('currency', 'IDR')->sum('amount');
        $verifiedRevenueUsd  = (clone $paymentQuery)->where('status', 'verified')->where('currency', 'USD')->sum('amount');

        // ⏳ Unpaid / Pending Revenue (Sisa Uang Tagihan Peserta yang Belum Lunas)
        $unpaidRevenueIdr    = max(0, $totalInvoicedIdr - $verifiedRevenueIdr);
        $unpaidRevenueUsd    = max(0, $totalInvoicedUsd - $verifiedRevenueUsd);

        // Abstract Breakdown
        $totalAbstracts      = (clone $abstractQuery)->count();
        $acceptedAbstracts   = (clone $abstractQuery)->where('status', 'accepted')->count();
        $revisionAbstracts   = (clone $abstractQuery)->where('status', 'revision_required')->count();
        $pendingAbstracts    = (clone $abstractQuery)->whereIn('status', ['pending', 'under_review'])->count();

        // Full Paper Breakdown
        $totalPapers         = (clone $paperQuery)->count();
        $acceptedPapers      = (clone $paperQuery)->where('status', 'accepted')->count();
        $revisionPapers      = (clone $paperQuery)->where('status', 'revision_required')->count();
        $pendingPapers       = (clone $paperQuery)->whereIn('status', ['pending', 'under_review'])->count();

        $stats = [
            'total_participants'   => $totalParticipants,
            'total_registrations'  => $totalRegistrations,
            'verified_payments'    => $verifiedPayments,
            'pending_payments'     => $pendingPayments,

            // Financial Breakdown
            'total_invoiced_idr'   => $totalInvoicedIdr,
            'total_invoiced_usd'   => $totalInvoicedUsd,
            'verified_revenue_idr' => $verifiedRevenueIdr,
            'verified_revenue_usd' => $verifiedRevenueUsd,
            'unpaid_revenue_idr'   => $unpaidRevenueIdr,
            'unpaid_revenue_usd'   => $unpaidRevenueUsd,

            'total_abstracts'      => $totalAbstracts,
            'accepted_abstracts'   => $acceptedAbstracts,
            'revision_abstracts'   => $revisionAbstracts,
            'pending_abstracts'    => $pendingAbstracts,
            'total_full_papers'    => $totalPapers,
            'accepted_papers'      => $acceptedPapers,
            'revision_papers'      => $revisionPapers,
            'pending_papers'       => $pendingPapers,
        ];

        // Track Category Recap Breakdown
        $trackCategories = Category::where(function ($q) use ($activeConferenceId) {
            if ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId)->orWhereNull('conference_id');
            }
        })->withCount([
            'abstracts' => function ($q) use ($activeConferenceId) {
                if ($activeConferenceId) $q->where('conference_id', $activeConferenceId);
            }
        ])->get()->map(function ($cat) {
            return [
                'id'              => $cat->id,
                'name'            => $cat->name,
                'badge'           => $cat->badge,
                'abstracts_count' => $cat->abstracts_count,
            ];
        });

        // Recent Registrations
        $recentRegistrations = (clone $registrationQuery)
            ->with(['user:id,name,email', 'registrationType:id,name', 'payment:id,registration_id,status,amount,currency'])
            ->latest()
            ->take(6)
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
            'date'   => $t->period || $t->date,
            'status' => $t->is_completed ? 'Completed' : 'Active',
        ]);

        return [
            'selectedConference'   => $selectedConference,
            'availableConferences' => $availableConferences,
            'stats'                => $stats,
            'trackCategories'      => $trackCategories,
            'recentRegistrations' => $recentRegistrations,
            'deadlines'            => $deadlines,
        ];
    }
}
