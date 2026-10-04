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
        $isNonPresenter = ($user->profile?->participation_type === 'non_presenter')
            || ($activeRegistration?->registrationFee?->type === 'non_presenter');

        // 7. Assemble Actionable Guidance and Stages
        $nextAction = $this->resolveNextAction(
            activeConference: $activeConference,
            isRegistered:     $isRegistered,
            paymentStatus:    $paymentStatus,
            isNonPresenter:   $isNonPresenter,
            hasCertificate:   $hasCertificate,
            abstract:         $abstract,
            abstractStatus:   $abstractStatus
        );

        $stages = $isNonPresenter
            ? $this->buildGeneralStages($isRegistered, $isPaid, $paymentStatus, $hasCertificate)
            : $this->buildPresenterStages($isRegistered, $isPaid, $paymentStatus, $abstract, $abstractStatus, $hasCertificate);

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
        bool $isNonPresenter,
        bool $hasCertificate,
        ?AbstractSubmission $abstract,
        string $abstractStatus
    ): array {
        // Track 1: Non-Presenter (Audience / General Attendee)
        if ($isNonPresenter) {
            if ($paymentStatus === 'unpaid') {
                return [
                    'title'        => 'Complete Non-Presenter Payment',
                    'description'  => 'Upload your payment receipt to complete your conference attendance registration.',
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
                'url'          => route('participant.registration.create'),
            ];
        }

        // Track 2: Presenter Track (Scientific Author)
        if (!$abstract || $abstractStatus === 'not_submitted') {
            return [
                'title'        => 'Submit Your Abstract',
                'description'  => 'Submit your paper abstract for ' . ($activeConference?->title ?? 'the conference') . ' to enter the peer review selection.',
                'button_label' => 'Submit Abstract',
                'url'          => route('participant.submission.index'),
            ];
        }

        if ($abstractStatus === 'pending' || $abstractStatus === 'under_review') {
            return [
                'title'        => 'Abstract Under Peer Review',
                'description'  => 'Your abstract is currently being evaluated by the scientific committee and peer reviewers. Payment will be unlocked once accepted.',
                'button_label' => 'View Submission Status',
                'url'          => route('participant.submission.index'),
            ];
        }

        if ($abstractStatus === 'revision_required') {
            return [
                'title'        => 'Abstract Revision Required',
                'description'  => 'Reviewers requested revisions on your abstract. Please check feedback and upload your revised draft.',
                'button_label' => 'Upload Revised Abstract',
                'url'          => route('participant.submission.index'),
            ];
        }

        if ($abstractStatus === 'rejected') {
            return [
                'title'        => 'Abstract Not Accepted',
                'description'  => 'Your abstract was not accepted for oral/poster presentation. You can still attend the conference as a Non-Presenter audience.',
                'button_label' => 'Register as Non-Presenter',
                'url'          => route('participant.registration.create'),
            ];
        }

        // Abstract is ACCEPTED!
        if ($paymentStatus === 'unpaid') {
            return [
                'title'        => 'Abstract Accepted - Complete Payment',
                'description'  => 'Congratulations! Your abstract has been accepted. Please select your presenter package and upload proof of payment.',
                'button_label' => 'Complete Presenter Payment',
                'url'          => route('participant.registration.create'),
            ];
        }

        if ($paymentStatus === 'waiting_verification') {
            return [
                'title'        => 'Payment Verification Pending',
                'description'  => 'Your presenter payment receipt has been submitted and is currently being verified by admin.',
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

        if ($hasCertificate) {
            return [
                'title'        => 'Download E-Certificate',
                'description'  => 'Your official verified E-Certificate is issued and ready for download!',
                'button_label' => 'Get Certificate',
                'url'          => route('participant.certificate.index'),
            ];
        }

        return [
            'title'        => 'Presentation Pass Confirmed',
            'description'  => 'Your presenter registration is confirmed and verified. You are scheduled for presentation.',
            'button_label' => 'View Submission & Presentation',
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
                'label'  => 'Ticket Selection',
                'status' => $isRegistered ? 'completed' : 'current',
                'desc'   => $isRegistered ? 'Package Selected' : 'Choose Ticket',
            ],
            [
                'key'    => 'payment',
                'label'  => 'Payment',
                'status' => $isPaid ? 'completed' : ($isRegistered ? 'current' : 'pending'),
                'desc'   => $isPaid ? 'Paid & Verified' : ($paymentStatus === 'waiting_verification' ? 'Waiting Verification' : ($paymentStatus === 'rejected' ? 'Rejected' : 'Unpaid')),
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
     * Build complete 5-stage journey for Presenters (Review-First Flow).
     */
    private function buildPresenterStages(
        bool $isRegistered,
        bool $isPaid,
        string $paymentStatus,
        ?AbstractSubmission $abstract,
        string $abstractStatus,
        bool $hasCertificate
    ): array {
        // Stage 2: Abstract & Review
        $abstractStepStatus = 'current';
        $abstractStepDesc = 'Not Submitted';

        if ($abstract) {
            if ($abstractStatus === 'accepted') {
                $abstractStepStatus = 'completed';
                $abstractStepDesc = 'Accepted';
            } elseif ($abstractStatus === 'rejected') {
                $abstractStepStatus = 'rejected';
                $abstractStepDesc = 'Rejected';
            } elseif ($abstractStatus === 'revision_required') {
                $abstractStepStatus = 'current';
                $abstractStepDesc = 'Revision Required';
            } else {
                $abstractStepStatus = 'current';
                $abstractStepDesc = 'Under Review';
            }
        }

        // Stage 3: Payment (Unlocked after abstract accepted)
        $paymentStepStatus = 'pending';
        $paymentStepDesc = 'Locked until Accepted';

        if ($abstractStatus === 'accepted') {
            if ($isPaid) {
                $paymentStepStatus = 'completed';
                $paymentStepDesc = 'Paid & Verified';
            } elseif ($paymentStatus === 'waiting_verification') {
                $paymentStepStatus = 'current';
                $paymentStepDesc = 'Waiting Verification';
            } elseif ($paymentStatus === 'rejected') {
                $paymentStepStatus = 'current';
                $paymentStepDesc = 'Receipt Rejected';
            } else {
                $paymentStepStatus = 'current';
                $paymentStepDesc = 'Payment Unlocked';
            }
        }

        return [
            [
                'key'    => 'registration',
                'label'  => 'Registration',
                'status' => 'completed',
                'desc'   => 'Account Created',
            ],
            [
                'key'    => 'abstract',
                'label'  => 'Abstract & Review',
                'status' => $abstractStepStatus,
                'desc'   => $abstractStepDesc,
            ],
            [
                'key'    => 'payment',
                'label'  => 'Conference Fee',
                'status' => $paymentStepStatus,
                'desc'   => $paymentStepDesc,
            ],
            [
                'key'    => 'presentation',
                'label'  => 'Presentation',
                'status' => ($isPaid && $abstractStatus === 'accepted') ? 'current' : 'pending',
                'desc'   => ($isPaid && $abstractStatus === 'accepted') ? 'Ready' : 'Pending Payment',
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
