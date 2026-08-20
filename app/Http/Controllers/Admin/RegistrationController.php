<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $registrations = Registration::with(['user.profile', 'registrationFee', 'payment'])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Registrations/Index', [
            'registrations' => $registrations,
            'currentFilter' => $status,
        ]);
    }

    public function show(Registration $registration): Response
    {
        $registration->load(['user.profile', 'registrationFee', 'payment.verifier']);

        return Inertia::render('Admin/Registrations/Show', [
            'registration' => $registration,
        ]);
    }

    public function sendInvoice(Registration $registration): RedirectResponse
    {
        $registration->loadMissing(['user.profile', 'conference', 'registrationFee']);

        $userEmail = $registration->user?->email;

        if (!$userEmail) {
            return back()->with('error', 'User email not found for this registration.');
        }

        try {
            Mail::to($userEmail)->send(new InvoiceMail($registration));
            return back()->with('success', "Invoice #{$registration->invoice_number} has been sent successfully to {$userEmail}.");
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to send invoice email: ' . $e->getMessage());
        }
    }
}
