<?php

namespace App\Services\Participant;

use App\Models\AbstractSubmission;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Timeline;
use App\Models\User;

class DashboardService
{
    /**
     * Get participant progress and status data.
     */
    public function getDashboardData(User $user): array
    {
        $user->load([
            'profile',
            'registrations' => function ($q) {
                $q->with(['conference', 'registrationFee', 'payment']);
            },
        ]);

        $activeConference = Conference::active()->first() ?? Conference::latest()->first();
        
        $activeRegistration = $user->registrations
            ->where('conference_id', $activeConference?->id)
            ->first();

        $payment = $activeRegistration?->payment;

        // Stages Evaluation
        $isRegistered = (bool) $activeRegistration;
        $isPaid = $payment && $payment->status === 'verified';
        $paymentStatus = $payment ? $payment->status : 'unpaid';

        // Abstract & Paper real status
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

        // Certificate status (Ready if admin uploaded certificate file)
        $hasCertificate = \App\Models\Certificate::where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->whereNotNull('file_path')
            ->exists();

        // Determine Next Action
        $nextAction = [
            'title' => 'Register for Conference',
            'description' => 'You have not registered for ' . ($activeConference?->title ?? 'the active conference') . '.',
            'button_label' => 'Register Now',
            'url' => route('participant.registration.create'),
        ];

        if (!$isRegistered) {
            $nextAction = [
                'title' => 'Complete Registration',
                'description' => 'Choose your category to register for ' . ($activeConference?->title ?? 'the conference') . '.',
                'button_label' => 'Register Now',
                'url' => route('participant.registration.create'),
            ];
        } elseif ($paymentStatus === 'unpaid') {
            $nextAction = [
                'title' => 'Complete Payment',
                'description' => 'Upload your payment receipt to complete registration.',
                'button_label' => 'Upload Payment Receipt',
                'url' => route('participant.payment.index'),
            ];
        } elseif ($paymentStatus === 'pending') {
            $nextAction = [
                'title' => 'Payment Verification Pending',
                'description' => 'Your payment receipt has been submitted and is currently being verified by admin.',
                'button_label' => 'View Payment Status',
                'url' => route('participant.payment.index'),
            ];
        } elseif ($abstractStatus === 'not_submitted') {
            $nextAction = [
                'title' => 'Submit Abstract',
                'description' => 'Submit your abstract before the upcoming submission deadline.',
                'button_label' => 'Submit Abstract',
                'url' => route('participant.submission.index'),
            ];
        } elseif ($fullPaperStatus === 'not_submitted' && $abstractStatus === 'accepted') {
            $nextAction = [
                'title' => 'Submit Full Paper',
                'description' => 'Your abstract has been accepted! Submit your full paper.',
                'button_label' => 'Submit Full Paper',
                'url' => route('participant.submission.index'),
            ];
        } elseif ($hasCertificate) {
            $nextAction = [
                'title' => 'Download E-Certificate',
                'description' => 'Your official verified E-Certificate is issued and ready for download!',
                'button_label' => 'Get Certificate',
                'url' => route('participant.certificate.index'),
            ];
        }

        // Timeline / Stages array
        $stages = [
            [
                'key' => 'registration',
                'label' => 'Registration',
                'status' => $isRegistered ? 'completed' : 'current',
                'desc' => $isRegistered ? 'Registered' : 'Pending',
            ],
            [
                'key' => 'payment',
                'label' => 'Payment',
                'status' => $isPaid ? 'completed' : ($isRegistered ? 'current' : 'pending'),
                'desc' => ucfirst($paymentStatus),
            ],
            [
                'key' => 'abstract',
                'label' => 'Abstract',
                'status' => $abstract ? ($abstractStatus === 'accepted' ? 'completed' : 'current') : 'pending',
                'desc' => $abstract ? ucfirst(str_replace('_', ' ', $abstractStatus)) : 'Not Submitted',
            ],
            [
                'key' => 'full_paper',
                'label' => 'Full Paper',
                'status' => $fullPaper ? ($fullPaperStatus === 'accepted' ? 'completed' : 'current') : 'pending',
                'desc' => $fullPaper ? ucfirst(str_replace('_', ' ', $fullPaperStatus)) : 'Not Submitted',
            ],
            [
                'key' => 'presentation',
                'label' => 'Presentation',
                'status' => $abstractStatus === 'accepted' ? 'current' : 'pending',
                'desc' => $abstractStatus === 'accepted' ? 'Ready' : 'Pending',
            ],
            [
                'key' => 'certificate',
                'label' => 'Certificate',
                'status' => $hasCertificate ? 'completed' : 'pending',
                'desc' => $hasCertificate ? 'Issued' : 'Not Issued',
            ],
        ];

        // Dynamic Nearest Deadline from Timeline model
        $nextTimeline = $activeConference
            ? Timeline::where('conference_id', $activeConference->id)->orderBy('order')->first()
            : null;

        $nearestDeadline = [
            'title' => $nextTimeline?->title ?? 'Abstract Submission Deadline',
            'date' => $nextTimeline?->period ?? '03 October 2026',
        ];

        return [
            'user' => $user,
            'activeConference' => $activeConference,
            'activeRegistration' => $activeRegistration,
            'payment' => $payment,
            'paymentStatus' => $paymentStatus,
            'abstract' => $abstract,
            'fullPaper' => $fullPaper,
            'hasCertificate' => $hasCertificate,
            'stages' => $stages,
            'nextAction' => $nextAction,
            'nearestDeadline' => $nearestDeadline,
        ];
    }
}
