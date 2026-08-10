<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Timeline;

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
        $abstractQuery = AbstractSubmission::query();
        $paperQuery = FullPaper::query();

        if ($activeConferenceId) {
            $registrationQuery->where('conference_id', $activeConferenceId);
            $paymentQuery->whereHas('registration', function ($q) use ($activeConferenceId) {
                $q->where('conference_id', $activeConferenceId);
            });
            $abstractQuery->where('conference_id', $activeConferenceId);
            $paperQuery->where('conference_id', $activeConferenceId);
        }

        // Aggregate statistics using real DB counts
        $stats = [
            'total_participants' => (clone $registrationQuery)->distinct('user_id')->count('user_id'),
            'total_registrations' => (clone $registrationQuery)->count(),
            'paid_registrations' => (clone $paymentQuery)->where('status', 'verified')->count(),
            'verified_payments' => (clone $paymentQuery)->where('status', 'verified')->count(),
            'pending_payments' => (clone $paymentQuery)->where('status', 'pending')->count(),
            'total_abstracts' => (clone $abstractQuery)->count(),
            'total_full_papers' => (clone $paperQuery)->count(),
            'total_presentations' => 0,
        ];

        // Fetch recent registrations
        $recentRegistrations = (clone $registrationQuery)
            ->with(['user:id,name,email', 'registrationType:id,name', 'payment:id,registration_id,status,amount'])
            ->latest()
            ->take(5)
            ->get();

        // Get list of available conferences for the dropdown
        $availableConferences = Conference::select('id', 'title', 'year', 'slug', 'is_active', 'status')
            ->orderByDesc('year')
            ->get();

        // Dynamic Deadlines from Timeline model
        $timelineRecords = $activeConferenceId 
            ? Timeline::where('conference_id', $activeConferenceId)->orderBy('order')->get()
            : Timeline::orderBy('order')->get();

        $deadlines = $timelineRecords->count() > 0 
            ? $timelineRecords->map(fn($t) => [
                'label' => $t->title,
                'date' => $t->period,
                'status' => 'Active',
              ])->toArray()
            : [
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
