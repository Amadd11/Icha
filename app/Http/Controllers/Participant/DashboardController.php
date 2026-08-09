<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'profile',
            'registrations.payment',
            'registrations.registrationType',
        ]);
        $activeConference = Conference::active()->first();
        $registration = $user->registrations
            ->where('conference_id', $activeConference?->id)
            ->first();

        return Inertia::render('Dashboard', [
            'profile' => $user->profile,
            'registration' => $registration,
            'activeConference' => $activeConference,
        ]);
    }
}
