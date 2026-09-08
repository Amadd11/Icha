<?php

namespace App\Services\Participant;

use App\Models\AbstractSubmission;
use App\Models\Certificate;
use App\Models\Conference;
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

        // 4. Resolve Abstract Submission Status
        $abstract = AbstractSubmission::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->latest()
            ->first();

        $abstractStatus = $abstract ? $abstract->status : 'not_submitted';

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
            abstractStatus: $abstractStatus
        );

        $stages = $isPaidPresenter
            ? $this->buildPresenterStages($isRegistered, $isPaid, $paymentStatus, $abstract, $abstractStatus, $hasCertificate)
            : $this->buildGeneralStages($isRegistered, $isPaid, $paymentStatus, $hasCertificate);

        $nearestDeadline = $this->resolveNearestDeadline($activeConference);

        return [
            'user'               => $user,
            'activeConference'   => $activeConference,
            'activeRegistration' => $activeRegistration,
            'payment'            => $payment,
            'paymentStatus'      => $paymentStatus,
            'abstract'           => $abstract,
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
        string $abstractStatus
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
                'description'  => 'Your payment receipt was rejected. Please re-upload a valid transfer proof.',
                'button_label' => 'Re-upload Receipt',
                'url'          => route('participant.registration.create'),
            ];
        }

        // For General Participants (Non-Presenter)
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
                'title'        => 'Access Conference Pass',
                'description'  => 'Your payment is verified. You can now download your invoice and attend sessions.',
                'button_label' => 'View Registration Details',
                'url'          => route('participant.dashboard'),
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

        if ($abstractStatus === 'revision_required') {
            return [
                'title'        => 'Upload Revised Abstract',
                'description'  => 'Your abstract requires revisions based on peer reviewer feedback.',
                'button_label' => 'Upload Revision',
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
            'title'        => 'Abstract Under Review',
            'description'  => 'Your abstract submission is currently being reviewed by peer reviewers.',
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
     * Build complete 5-stage journey for verified paid presenters.
     */
    private function buildPresenterStages(
        bool $isRegistered,
        bool $isPaid,
        string $paymentStatus,
        ?AbstractSubmission $abstract,
        string $abstractStatus,
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
        if ($activeConference) {
            $now = now();
            if ($activeConference->abstract_deadline && $now->lte($activeConference->abstract_deadline->endOfDay())) {
                return [
                    'title' => 'Abstract Submission Deadline',
                    'date'  => $activeConference->abstract_deadline->format('d M Y'),
                ];
            }
            if ($activeConference->start_date && $now->lte($activeConference->start_date->endOfDay())) {
                return [
                    'title' => 'Conference Opening Day',
                    'date'  => $activeConference->start_date->format('d M Y'),
                ];
            }
        }

        $nextTimeline = $activeConference
            ? Timeline::where('conference_id', $activeConference->id)->orderBy('order')->first()
            : null;

        return [
            'title' => $nextTimeline?->title ?? 'Registration & Abstract Deadline',
            'date'  => $nextTimeline?->period ?? 'October 2026',
        ];
    }
}
