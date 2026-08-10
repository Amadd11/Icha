<?php

namespace App\Services\Admin;

use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Speaker;

class DashboardService
{
    /**
     * Get aggregated dashboard statistics and data for the selected conference.
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
        $paymentQuery = Payment::query();

        if ($activeConferenceId) {
            $registrationQuery->where('conference_id', $activeConferenceId);
            $paymentQuery->whereHas('registration', function ($q) use ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId);
            });
        }

        // Aggregate statistics using count() directly in DB
        $stats = [
            'total_participants' => (clone $registrationQuery)->distinct('user_id')->count('user_id'),
            'total_registrations' => (clone $registrationQuery)->count(),
            'paid_registrations' => (clone $paymentQuery)->where('status', 'verified')->count(),
            'pending_payments' => (clone $paymentQuery)->where('status', 'pending')->count(),
            'total_abstracts' => 0, // Placeholder for Phase 3 Abstract Submissions
            'total_full_papers' => 0, // Placeholder for Phase 4 Full Papers
            'total_presentations' => 0, // Placeholder for Phase 5 Presentations
        ];

        // Fetch recent registrations (limited to 5 records with light eager loading)
        $recentRegistrations = (clone $registrationQuery)
            ->with(['user:id,name,email', 'registrationType:id,name', 'payment:id,registration_id,status,amount'])
            ->latest()
            ->take(5)
            ->get();

        // Get list of available conferences for the conference switcher dropdown
        $availableConferences = Conference::select('id', 'title', 'year', 'slug', 'is_active', 'status')
            ->orderByDesc('year')
            ->get();

        // Deadlines list
        $deadlines = [
            ['label' => 'Abstract Submission', 'date' => '03 Oct 2026', 'status' => 'Upcoming'],
            ['label' => 'Abstract Review', 'date' => '10 Oct 2026', 'status' => 'Upcoming'],
            ['label' => 'Full Paper Submission', 'date' => '30 Oct 2026', 'status' => 'Upcoming'],
            ['label' => 'Conference Event', 'date' => '10–11 Nov 2026', 'status' => 'Upcoming'],
        ];

        return [
            'selectedConference' => $selectedConference,
            'availableConferences' => $availableConferences,
            'stats' => $stats,
            'recentRegistrations' => $recentRegistrations,
            'deadlines' => $deadlines,
        ];
    }
}
