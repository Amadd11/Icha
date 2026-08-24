<?php

namespace App\Services\Participant;

use App\Models\AbstractSubmission;
use App\Models\Certificate;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Timeline;
use App\Models\User;

class DashboardService
{
    /**
     * Get aggregated participant dashboard data.
     */
    public function getDashboardData(User $user): array
    {
        // 1. Eager load user relationships cleanly
        $user->loadMissing([
            'profile',
            'registrations' => fn ($q) => $q->with(['conference', 'registrationFee', 'payment']),
        ]);

        // 2. Resolve Active Conference
        $activeConference = Conference::active()->first() ?? Conference::latest()->first();
        
        // 3. Resolve Current Conference Registration & Payment
        /** @var Registration|null $activeRegistration */
        $activeRegistration = $user->registrations
            ->where('conference_id', $activeConference?->id)
            ->first();

        $payment = $activeRegistration?->payment;
        $isRegistered = (bool) $activeRegistration;
        $isPaid = $payment && $payment->status === 'verified';
        $paymentStatus = $this->resolvePaymentStatus($payment);

        // 4. Resolve Abstract & Full Paper Submission Status
        $abstract = AbstractSubmission::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->latest()
            ->first();

        $fullPaper = FullPaper::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->latest()
            ->first();

        $abstractStatus = $abstract ? $abstract->status : 'not_submitted';
        $fullPaperStatus = $fullPaper ? $fullPaper->status : 'not_submitted';

        // 5. Resolve Certificate Availability
        $hasCertificate = Certificate::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->whereNotNull('file_path')
            ->exists();

        // 6. Evaluate Presenter vs General Participant Track
        $isPresenterPackage = $activeRegistration?->registrationFee?->type === 'presenter';
        $isPaidPresenter = $isPresenterPackage && $isPaid;

        // 7. Assemble Actionable Guidance and Stages
        $nextAction = $this->resolveNextAction(
            activeConference: $activeConference,
            isRegistered: $isRegistered,
            paymentStatus: $paymentStatus,
            isPaidPresenter: $isPaidPresenter,
            hasCertificate: $hasCertificate,
            abstractStatus: $abstractStatus,
            fullPaperStatus: $fullPaperStatus
        );

        $stages = $isPaidPresenter
            ? $this->buildPresenterStages($isRegistered, $isPaid, $paymentStatus, $abstract, $abstractStatus, $fullPaper, $fullPaperStatus, $hasCertificate)
            : $this->buildGeneralStages($isRegistered, $isPaid, $paymentStatus, $hasCertificate);

        $nearestDeadline = $this->resolveNearestDeadline($activeConference);

        return [
            'user'               => $user,
            'activeConference'   => $activeConference,
            'activeRegistration' => $activeRegistration,
            'payment'            => $payment,
            'paymentStatus'      => $paymentStatus,
            'abstract'           => $abstract,
            'fullPaper'          => $fullPaper,
            'hasCertificate'     => $hasCertificate,
            'stages'             => $stages,
            'nextAction'         => $nextAction,
            'nearestDeadline'    => $nearestDeadline,
        ];
    }

    /**
     * Resolve normalized payment status key for UI components.
     */
    private function resolvePaymentStatus(?Payment $payment): string
    {
        if (!$payment) {
            return 'unpaid';
        }

        return match ($payment->status) {
            'pending'  => 'waiting_verification',
            'verified' => 'paid',
            default    => $payment->status,
        };
    }

    /**
     * Determine the singular next priority action for the participant.
     */
    private function resolveNextAction(
        ?Conference $activeConference,
        bool $isRegistered,
        string $paymentStatus,
        bool $isPaidPresenter,
        bool $hasCertificate,
        string $abstractStatus,
        string $fullPaperStatus
    ): array {
        if (!$isRegistered) {
            return [
                'title'        => 'Complete Registration',
                'description'  => 'Choose your category to register for ' . ($activeConference?->title ?? 'the conference') . '.',
                'button_label' => 'Register Now',
                'url'          => route('participant.registration.create'),
            ];
        }

        if ($paymentStatus === 'unpaid') {
            return [
                'title'        => 'Complete Payment',
                'description'  => 'Upload your payment receipt to complete registration.',
                'button_label' => 'Upload Payment Receipt',
                'url'          => route('participant.registration.create'),
            ];
        }

        if ($paymentStatus === 'waiting_verification') {
            return [
                'title'        => 'Payment Verification Pending',
                'description'  => 'Your payment receipt has been submitted and is currently being verified by admin.',
                'button_label' => 'View Payment Status',
                'url'          => route('participant.registration.create'),
            ];
        }

        if ($paymentStatus === 'rejected') {
            return [
                'title'        => 'Payment Receipt Rejected',
                'description'  => 'Your payment receipt was rejected by admin. Please re-upload a valid proof file.',
                'button_label' => 'Re-upload Receipt',
                'url'          => route('participant.registration.create'),
            ];
        }

        // Non-Presenter / Unverified Flow
        if (!$isPaidPresenter) {
            if ($hasCertificate) {
                return [
                    'title'        => 'Download E-Certificate',
                    'description'  => 'Your official verified E-Certificate is issued and ready for download!',
                    'button_label' => 'Get Certificate',
                    'url'          => route('participant.certificate.index'),
                ];
            }

            return [
                'title'        => 'Registration Confirmed',
                'description'  => 'Your conference registration is confirmed and verified. We look forward to seeing you!',
                'button_label' => 'View My Profile',
                'url'          => route('participant.profile.edit'),
            ];
        }

        // Paid Presenter Scientific Track Flow
        if ($abstractStatus === 'not_submitted') {
            return [
                'title'        => 'Submit Abstract',
                'description'  => 'Submit your abstract before the upcoming submission deadline.',
                'button_label' => 'Submit Abstract',
                'url'          => route('participant.submission.index'),
            ];
        }

        if ($fullPaperStatus === 'not_submitted' && $abstractStatus === 'accepted') {
            return [
                'title'        => 'Submit Full Paper',
                'description'  => 'Your abstract has been accepted! Submit your full paper.',
                'button_label' => 'Submit Full Paper',
                'url'          => route('participant.submission.index'),
            ];
        }

        if ($hasCertificate) {
            return [
                'title'        => 'Download E-Certificate',
                'description'  => 'Your official verified E-Certificate is issued and ready for download!',
                'button_label' => 'Get Certificate',
                'url'          => route('participant.certificate.index'),
            ];
        }

        return [
            'title'        => 'Submission Under Review',
            'description'  => 'Your paper submission is currently being reviewed by peer reviewers.',
            'button_label' => 'View Submission Status',
            'url'          => route('participant.submission.index'),
        ];
    }

    /**
     * Build standard 4-stage journey for new / general participants.
     */
    private function buildGeneralStages(
        bool $isRegistered,
        bool $isPaid,
        string $paymentStatus,
        bool $hasCertificate
    ): array {
        return [
            [
                'key'    => 'registration',
                'label'  => 'Registration',
                'status' => $isRegistered ? 'completed' : 'current',
                'desc'   => $isRegistered ? 'Registered' : 'Not Registered',
            ],
            [
                'key'    => 'payment',
                'label'  => 'Payment',
                'status' => $isPaid ? 'completed' : ($isRegistered ? 'current' : 'pending'),
                'desc'   => $isPaid ? 'Paid' : ($paymentStatus === 'waiting_verification' ? 'Waiting Verification' : ($paymentStatus === 'rejected' ? 'Rejected' : 'Unpaid')),
            ],
            [
                'key'    => 'attendance',
                'label'  => 'Conference Pass',
                'status' => $isPaid ? 'completed' : 'pending',
                'desc'   => $isPaid ? 'Confirmed' : 'Pending Payment',
            ],
            [
                'key'    => 'certificate',
                'label'  => 'E-Certificate',
                'status' => $hasCertificate ? 'completed' : 'pending',
                'desc'   => $hasCertificate ? 'Issued' : 'Not Issued',
            ],
        ];
    }

    /**
     * Build complete 6-stage journey for verified paid presenters.
     */
    private function buildPresenterStages(
        bool $isRegistered,
        bool $isPaid,
        string $paymentStatus,
        ?AbstractSubmission $abstract,
        string $abstractStatus,
        ?FullPaper $fullPaper,
        string $fullPaperStatus,
        bool $hasCertificate
    ): array {
        return [
            [
                'key'    => 'registration',
                'label'  => 'Registration',
                'status' => $isRegistered ? 'completed' : 'current',
                'desc'   => $isRegistered ? 'Registered' : 'Not Registered',
            ],
            [
                'key'    => 'payment',
                'label'  => 'Payment',
                'status' => $isPaid ? 'completed' : ($isRegistered ? 'current' : 'pending'),
                'desc'   => $isPaid ? 'Paid' : ($paymentStatus === 'waiting_verification' ? 'Waiting Verification' : ($paymentStatus === 'rejected' ? 'Rejected' : 'Unpaid')),
            ],
            [
                'key'    => 'abstract',
                'label'  => 'Abstract',
                'status' => $abstract ? ($abstractStatus === 'accepted' ? 'completed' : 'current') : 'pending',
                'desc'   => $abstract ? ucfirst(str_replace('_', ' ', $abstractStatus)) : 'Not Submitted',
            ],
            [
                'key'    => 'full_paper',
                'label'  => 'Full Paper',
                'status' => $fullPaper ? ($fullPaperStatus === 'accepted' ? 'completed' : 'current') : 'pending',
                'desc'   => $fullPaper ? ucfirst(str_replace('_', ' ', $fullPaperStatus)) : 'Not Submitted',
            ],
            [
                'key'    => 'presentation',
                'label'  => 'Presentation',
                'status' => $abstractStatus === 'accepted' ? 'current' : 'pending',
                'desc'   => $abstractStatus === 'accepted' ? 'Ready' : 'Pending',
            ],
            [
                'key'    => 'certificate',
                'label'  => 'Certificate',
                'status' => $hasCertificate ? 'completed' : 'pending',
                'desc'   => $hasCertificate ? 'Issued' : 'Not Issued',
            ],
        ];
    }

    /**
     * Resolve the upcoming conference timeline deadline.
     */
    private function resolveNearestDeadline(?Conference $activeConference): array
    {
        $nextTimeline = $activeConference
            ? Timeline::where('conference_id', $activeConference->id)->orderBy('order')->first()
            : null;

        return [
            'title' => $nextTimeline?->title ?? 'Registration & Abstract Deadline',
            'date'  => $nextTimeline?->period ?? 'October 2026',
        ];
    }
}
