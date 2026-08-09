<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use App\Models\RegistrationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class FrontendController extends Controller
{
    /**
     * Display the landing page.
     */
    public function home()
    {
        $activeConference = Conference::with([
            'categories',
            'speakers',
            'timelines',
            'sponsors' => fn($q) => $q->where('is_active', true),
        ])
            ->where('is_active', true)
            ->first();

        return Inertia::render('LandingPage', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'activeConference' => $activeConference,
        ]);
    }

    /**
     * Display the public registration information page.
     */
    public function registration()
    {
        $activeConference = Conference::active()->first();
        $registrationTypes = $activeConference
            ? RegistrationType::where('conference_id', $activeConference->id)
            ->where('is_active', true)
            ->get()
            : [];

        return Inertia::render('Registration', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'activeConference' => $activeConference,
            'registrationTypes' => $registrationTypes,
        ]);
    }
}
