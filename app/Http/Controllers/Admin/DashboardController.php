<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $activeConference = Conference::active()->first();

        $stats = [
            'total_participants' => User::where('role', 'participant')->count(),
            'total_admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'total_registrations' => Registration::count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'verified_payments' => Payment::where('status', 'verified')->count(),
            'total_speakers' => Speaker::count(),
            'total_sponsors' => Sponsor::count(),
        ];

        $recentRegistrations = Registration::with(['user', 'registrationType', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'activeConference' => $activeConference,
            'recentRegistrations' => $recentRegistrations,
        ]);
    }
}
