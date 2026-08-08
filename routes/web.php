<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Conference;

// ─── Public Routes ──────────────────────────────────────────────────────────

Route::get('/', function () {
    $activeConference = Conference::with([
        'categories',
        'speakers',
        'timelines',
        'sponsors' => fn($q) => $q->where('is_active', true),
    ])->where('is_active', true)->first();

    return Inertia::render('LandingPage', [
        'canLogin'         => Route::has('login'),
        'canRegister'      => Route::has('register'),
        'activeConference' => $activeConference,
    ]);
});

Route::get('/registration', function () {
    return Inertia::render('Registration', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// ─── Authenticated: Participant Dashboard ────────────────────────────────────

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ─── Authenticated: Admin Area ───────────────────────────────────────────────

Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        Route::resource('conferences', Admin\ConferenceController::class);
        Route::resource('speakers',    Admin\SpeakerController::class)->except(['show']);
        Route::resource('sponsors',    Admin\SponsorController::class)->except(['show']);
        Route::resource('categories',  Admin\CategoryController::class)->except(['show']);
        Route::resource('committees',  Admin\CommitteeController::class)->except(['show']);
        Route::resource('timelines',   Admin\TimelineController::class)->except(['show']);
    });

// ─── Profile (any authenticated user) ────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
