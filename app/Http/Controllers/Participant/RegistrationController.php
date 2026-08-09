<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ConferenceRegistrationRequest;
use App\Http\Requests\Participant\PaymentProofRequest;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationType;
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
        $activeConference = Conference::active()->firstOrFail();
        $existingRegistration = Registration::where('user_id', $user->id)
            ->where('conference_id', $activeConference->id)
            ->with(['registrationType', 'payment'])
            ->first();

        $registrationTypes = RegistrationType::where('conference_id', $activeConference->id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('Participant/Registration/Form', [
            'activeConference'     => $activeConference,
            'existingRegistration' => $existingRegistration,
            'registrationTypes'    => $registrationTypes,
            'userProfile'          => $user->profile,
        ]);
    }

    public function store(ConferenceRegistrationRequest $request)
    {
        $user = $request->user();
        $this->registrationService->createRegistration($user, $request->validated());

        return redirect()->route('participant.payment.index')
            ->with('success', 'Registration submitted. Please proceed to payment.');
    }

    public function paymentIndex(Request $request): Response
    {
        $user = $request->user();
        $activeConference = Conference::active()->first();
        $registration = Registration::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->with(['registrationType', 'payment'])
            ->firstOrFail();

        return Inertia::render('Participant/Payment/Index', [
            'registration' => $registration,
            'payment'      => $registration->payment,
        ]);
    }

    public function submitPayment(PaymentProofRequest $request)
    {
        $user = $request->user();
        $registration = Registration::where('id', $request->validated('registration_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->paymentService->submitPaymentProof(
            $registration,
            $request->validated('payment_method'),
            $request->file('proof_file')
        );

        return redirect()->back()
            ->with('success', 'Payment proof uploaded successfully. Awaiting admin verification.');
    }
}
