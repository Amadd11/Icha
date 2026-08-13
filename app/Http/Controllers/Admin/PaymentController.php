<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentVerificationRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status', 'pending');
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $payments = Payment::with(['registration.user.profile', 'registration.registrationType', 'verifier'])
            ->when($confId, function ($q) use ($confId) {
                $q->whereHas('registration', fn($rq) => $rq->where('conference_id', $confId));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments'      => $payments,
            'currentFilter' => $status,
        ]);
    }

    public function verify(PaymentVerificationRequest $request, Payment $payment)
    {
        $this->paymentService->verifyPayment(
            $payment,
            $request->user(),
            $request->validated('action'),
            $request->validated('rejection_reason')
        );

        $msg = $request->validated('action') === 'approve'
            ? 'Payment verified and approved.'
            : 'Payment rejected.';

        return redirect()->back()->with('success', $msg);
    }
}
