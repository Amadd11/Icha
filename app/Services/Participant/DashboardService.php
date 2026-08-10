<?php

namespace App\Services\Participant;

use App\Models\Conference;
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
                $q->with(['conference', 'registrationType', 'payment']);
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

        // Abstract, Paper, Presentation, Certificate statuses (defaults for current phase)
        $abstractStatus = 'not_submitted';
        $fullPaperStatus = 'not_submitted';
        $presentationStatus = 'not_submitted';
        $certificateStatus = 'not_available';

        // Determine Next Action and Nearest Deadline
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
        } elseif ($isPaid && $abstractStatus === 'not_submitted') {
            $nextAction = [
                'title' => 'Submit Abstract',
                'description' => 'Submit your abstract before the upcoming submission deadline.',
                'button_label' => 'Submit Abstract',
                'url' => '#abstract',
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
                'status' => 'pending',
                'desc' => 'Not Submitted',
            ],
            [
                'key' => 'full_paper',
                'label' => 'Full Paper',
                'status' => 'pending',
                'desc' => 'Not Submitted',
            ],
            [
                'key' => 'presentation',
                'label' => 'Presentation',
                'status' => 'pending',
                'desc' => 'Pending',
            ],
            [
                'key' => 'certificate',
                'label' => 'Certificate',
                'status' => 'pending',
                'desc' => 'Not Available',
            ],
        ];

        return [
            'user' => $user,
            'activeConference' => $activeConference,
            'activeRegistration' => $activeRegistration,
            'payment' => $payment,
            'paymentStatus' => $paymentStatus,
            'stages' => $stages,
            'nextAction' => $nextAction,
            'nearestDeadline' => [
                'title' => 'Abstract Submission Deadline',
                'date' => '03 October 2026',
            ],
        ];
    }
}
