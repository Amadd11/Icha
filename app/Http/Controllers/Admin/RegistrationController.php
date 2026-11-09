<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Conference;
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
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 15;
        }
        $search = trim((string) $request->input('search', ''));
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $registrations = Registration::with(['user.profile', 'registrationFee', 'payment.verifier', 'conference'])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->when($status, function ($q, $status) {
                if ($status === 'unpaid' || $status === 'pending') {
                    $q->whereIn('status', ['pending', 'unpaid']);
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('profile', function ($pq) use ($search) {
                                    $pq->where('phone', 'like', "%{$search}%")
                                        ->orWhere('institution', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('registrationFee', function ($fq) use ($search) {
                            $fq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Registrations/Index', [
            'registrations' => $registrations,
            'currentFilter' => $status,
            'filters'       => [
                'status'   => $status,
                'per_page' => $perPage,
                'search'   => $search,
            ],
        ]);
    }

    public function show(Registration $registration): Response
    {
        $registration->load(['user.profile', 'registrationFee', 'payment.verifier', 'conference']);

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
