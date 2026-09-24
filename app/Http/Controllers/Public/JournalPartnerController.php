<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\JournalPartner;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class JournalPartnerController extends Controller
{
    /**
     * Display the dedicated official journal partners page.
     */
    public function index(): Response
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::first();

        $journalPartners = JournalPartner::when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->get();

        $availableConferences = Conference::where('status', '!=', 'draft')
            ->select('id', 'title', 'slug', 'year')
            ->orderByDesc('year')
            ->get();

        return Inertia::render('Public/JournalPartners/Index', [
            'canLogin'             => Route::has('login'),
            'canRegister'          => Route::has('register'),
            'activeConference'     => $activeConference,
            'availableConferences' => $availableConferences,
            'journalPartners'      => $journalPartners,
        ]);
    }
}
