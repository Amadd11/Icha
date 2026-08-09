<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Participant as ParticipantCtrl;
use App\Http\Controllers\ProfileController;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationType;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\FrontendController;

// Public routes
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/registration', [FrontendController::class, 'registration'])->name('registration');

// Authenticated participant routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ParticipantCtrl\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('my')->name('participant.')->group(function () {
        Route::get('/profile', [ParticipantCtrl\ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::put('/profile', [ParticipantCtrl\ProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/registration', [ParticipantCtrl\RegistrationController::class, 'create'])
            ->name('registration.create');
        Route::post('/registration', [ParticipantCtrl\RegistrationController::class, 'store'])
            ->name('registration.store');

        Route::get('/payment', [ParticipantCtrl\RegistrationController::class, 'paymentIndex'])
            ->name('payment.index');
        Route::post('/payment', [ParticipantCtrl\RegistrationController::class, 'submitPayment'])
            ->name('payment.submit');
    });
});

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('conferences', Admin\ConferenceController::class);
        Route::resource('speakers', Admin\SpeakerController::class)->except(['show']);
        Route::resource('sponsors', Admin\SponsorController::class)->except(['show']);
        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('committees', Admin\CommitteeController::class)->except(['show']);
        Route::resource('timelines', Admin\TimelineController::class)->except(['show']);

        Route::resource('registrations', Admin\RegistrationController::class)->only(['index', 'show']);
        Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/verify', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
    });

// Legacy profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
