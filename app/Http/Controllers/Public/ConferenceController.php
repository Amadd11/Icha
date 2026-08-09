<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\RegistrationType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ConferenceController extends Controller
{
    /**
     * Display the conference archive/list.
     */
    public function index(): Response
    {
        $conferences = Conference::where('status', '!=', 'draft')->latest()->get();

        return Inertia::render('Public/Conferences/Index', [
            'conferences' => $conferences,
        ]);
    }

    /**
     * Display the dynamic conference landing page.
     */
    public function show(Conference $conference): Response
    {
        if ($conference->status === 'draft' && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $conference->load([
            'categories',
            'speakers',
            'timelines',
            'sponsors' => fn($q) => $q->where('is_active', true),
        ]);

        $availableConferences = Conference::where('status', '!=', 'draft')
            ->select('id', 'title', 'slug', 'year')
            ->orderByDesc('year')
            ->get();

        return Inertia::render('Public/Conference/Show', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'conference' => $conference,
            'availableConferences' => $availableConferences,
        ]);
    }

    /**
     * Display registration details for active conference.
     */
    public function registration(): Response
    {
        $activeConference = Conference::active()->first();
        $registrationTypes = $activeConference
            ? RegistrationType::where('conference_id', $activeConference->id)
                ->where('is_active', true)
                ->get()
            : [];

        return Inertia::render('Public/Conference/Registration', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'activeConference' => $activeConference,
            'registrationTypes' => $registrationTypes,
        ]);
    }
}
