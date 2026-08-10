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
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ConferenceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', HomeController::class)->name('home');
Route::get('/conferences', [ConferenceController::class, 'index'])->name('conferences.index');
Route::get('/conferences/{conference:slug}', [ConferenceController::class, 'show'])->name('conferences.show');
Route::get('/registration', [ConferenceController::class, 'registration'])->name('registration');

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

        // Submission routes (Abstract & Full Paper)
        Route::get('/submission', [\App\Http\Controllers\Participant\SubmissionController::class, 'index'])
            ->name('submission.index');
        Route::post('/submission/abstract', [\App\Http\Controllers\Participant\SubmissionController::class, 'storeAbstract'])
            ->name('submission.abstract.store');
        Route::post('/submission/paper', [\App\Http\Controllers\Participant\SubmissionController::class, 'storePaper'])
            ->name('submission.paper.store');

        // Certificate routes
        Route::get('/certificate', [\App\Http\Controllers\Participant\CertificateController::class, 'index'])
            ->name('certificate.index');
    });
});

// Certificate printable/view route
Route::middleware('auth')->get('/certificate/{certificate}/download', [\App\Http\Controllers\Participant\CertificateController::class, 'download'])
    ->name('certificate.download');

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

        // Abstract Reviews
        Route::get('abstracts', [Admin\AbstractController::class, 'index'])->name('abstracts.index');
        Route::post('abstracts/{abstract}/review', [Admin\AbstractController::class, 'review'])->name('abstracts.review');

        // Full Paper Reviews
        Route::get('papers', [Admin\PaperController::class, 'index'])->name('papers.index');
        Route::post('papers/{paper}/review', [Admin\PaperController::class, 'review'])->name('papers.review');
    });

// Legacy profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
