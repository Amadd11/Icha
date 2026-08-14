<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ConferenceRegistrationRequest;
use App\Http\Requests\Participant\PaymentProofRequest;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Services\PaymentService;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected PaymentService $paymentService
    ) {}

    public function create(Request $request): Response
    {
        $user = $request->user()->load('profile');
        $activeConference = Conference::active()->first() ?? Conference::latest()->first();

        $existingRegistration = Registration::where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->with(['registrationFee', 'payment'])
            ->first();

        $registrationFees = $activeConference
            ? RegistrationFee::where('conference_id', $activeConference->id)->get()
            : RegistrationFee::all();

        return Inertia::render('Participant/Registration/Form', [
            'activeConference'     => $activeConference,
            'existingRegistration' => $existingRegistration,
            'payment'              => $existingRegistration?->payment,
            'registrationFees'     => $registrationFees,
            'userProfile'          => $user->profile,
        ]);
    }

    public function store(ConferenceRegistrationRequest $request)
    {
        $user = $request->user();
        $this->registrationService->createRegistration($user, $request->validated());

        return redirect()->route('participant.registration.create')
            ->with('success', 'Registration submitted. Please proceed to payment proof upload below.');
    }

    public function paymentIndex(Request $request): Response
    {
        return redirect()->route('participant.registration.create');
    }

    public function submitPayment(PaymentProofRequest $request)
    {
        $user = $request->user();
        $this->paymentService->submitProof(
            registrationId: (int) $request->validated('registration_id'),
            paymentMethod:  $request->validated('payment_method'),
            proofFile:      $request->file('proof_file'),
            user:           $user
        );

        return redirect()->route('participant.registration.create')
            ->with('success', 'Payment proof submitted successfully. Our team will verify your payment within 1-2 business days.');
    }
}
