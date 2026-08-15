<?php

use App\Http\Controllers\Admin\{
    AbstractController,
    CategoryController,
    ConferenceController as AdminConferenceController,
    DashboardController as AdminDashboardController,
    PaymentController,
    PaperController,
    RegistrationController as AdminRegistrationController,
    RegistrationFeeController,
    ReviewAssignmentController,
    ReviewerManagementController,
    SpeakerController,
    SponsorController,
    TimelineController,
    UserController,
};
use App\Http\Controllers\Participant\{
    CertificateController,
    DashboardController,
    RegistrationController,
    SubmissionController,
};
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ConferenceController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboardController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::get('/', HomeController::class)->name('home');
Route::get('/conferences', [ConferenceController::class, 'index'])->name('conferences.index');
Route::get('/conferences/{conference:slug}', [ConferenceController::class, 'show'])->name('conferences.show');
Route::get('/registration', [ConferenceController::class, 'registration'])->name('registration');

// Authenticated participant routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('my')->name('participant.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/registration', [RegistrationController::class, 'create'])
            ->name('registration.create');
        Route::post('/registration', [RegistrationController::class, 'store'])
            ->name('registration.store');

        Route::get('/payment', [RegistrationController::class, 'paymentIndex'])
            ->name('payment.index');
        Route::post('/payment', [RegistrationController::class, 'submitPayment'])
            ->name('payment.submit');

        // Submission routes (Abstract & Full Paper)
        Route::get('/submission', [SubmissionController::class, 'index'])
            ->name('submission.index');
        Route::post('/submission/abstract', [SubmissionController::class, 'storeAbstract'])
            ->name('submission.abstract.store');
        Route::post('/submission/paper', [SubmissionController::class, 'storePaper'])
            ->name('submission.paper.store');

        // Certificate routes
        Route::get('/certificate', [CertificateController::class, 'index'])
            ->name('certificate.index');
    });
});

// Certificate printable/view route
Route::middleware('auth')->get('/certificate/{certificate}/download', [CertificateController::class, 'download'])
    ->name('certificate.download');

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin,admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('conferences', AdminConferenceController::class);
        Route::resource('speakers', SpeakerController::class)->except(['show']);
        Route::resource('sponsors', SponsorController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('timelines', TimelineController::class)->except(['show']);
        Route::resource('registration-fees', RegistrationFeeController::class)->except(['show']);

        Route::resource('registrations', AdminRegistrationController::class)->only(['index', 'show']);
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');

        // Abstract Reviews
        Route::get('abstracts', [AbstractController::class, 'index'])->name('abstracts.index');
        Route::post('abstracts/{abstract}/review', [AbstractController::class, 'review'])->name('abstracts.review');
        Route::post('abstracts/{abstract}/assign', [ReviewAssignmentController::class, 'store'])->name('abstracts.assign');
        Route::delete('abstracts/{abstract}', [AbstractController::class, 'destroy'])->name('abstracts.destroy');

        Route::resource('papers', PaperController::class);
        Route::post('papers/{paper}/review', [PaperController::class, 'review'])->name('papers.review');

        // Reviewer Management
        Route::resource('reviewers', ReviewerManagementController::class)->except(['show']);

        // Super Admin Only: User Management
        Route::middleware('role:super_admin')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });
    });

// Reviewer routes
Route::prefix('reviewer')
    ->middleware(['auth', 'role:reviewer'])
    ->name('reviewer.')
    ->group(function () {
        Route::get('/dashboard', [ReviewerDashboardController::class, 'index'])
            ->name('dashboard');
        Route::post('/assignments/{assignment}/review', [\App\Http\Controllers\Reviewer\ReviewSubmissionController::class, 'store'])
            ->name('assignments.review');
    });

// Legacy profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// Email preview routes for local testing
Route::get('/preview-mail/approved', function () {
    $payment = \App\Models\Payment::with(['registration.user', 'registration.conference', 'registration.registrationType'])->first();
    if (!$payment) return 'No payment found in database to preview. Please seed or register a participant first.';
    return new \App\Mail\PaymentApprovedMail($payment);
});

Route::get('/preview-mail/rejected', function () {
    $payment = \App\Models\Payment::with(['registration.user', 'registration.conference', 'registration.registrationType'])->first();
    if (!$payment) return 'No payment found in database to preview. Please seed or register a participant first.';
    $payment->rejection_reason = 'Gambar bukti transfer kurang jelas / nominal transfer tidak sesuai invoice.';
    return new \App\Mail\PaymentRejectedMail($payment);
});
