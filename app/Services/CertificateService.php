<?php

namespace App\Services;

use App\Models\AbstractSubmission;
use App\Models\Certificate;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\User;

class CertificateService
{
    /**
     * Get or issue certificates for a given user.
     */
    public function getUserCertificates(User $user): array
    {
        $activeConference = Conference::where('is_active', true)->first() 
            ?? Conference::latest()->first();

        if (!$activeConference) {
            return [
                'certificates' => [],
                'isEligible' => false,
                'message' => 'No active conference found.',
            ];
        }

        // Check eligibility
        $registration = Registration::where('user_id', $user->id)
            ->where('conference_id', $activeConference->id)
            ->where('status', 'paid')
            ->first();

        $hasAcceptedAbstract = AbstractSubmission::where('user_id', $user->id)
            ->where('conference_id', $activeConference->id)
            ->where('status', 'accepted')
            ->exists();

        $isEligibleForParticipant = (bool) $registration;
        $isEligibleForPresenter = $hasAcceptedAbstract;

        $certificates = Certificate::with('conference')
            ->where('user_id', $user->id)
            ->where('conference_id', $activeConference->id)
            ->get()
            ->map(function ($cert) {
                return [
                    'id'                 => $cert->id,
                    'certificate_number' => $cert->certificate_number,
                    'type'               => $cert->type,
                    'role_title'         => $cert->role_title,
                    'file_path'          => $cert->file_path,
                    'file_url'           => $cert->file_path ? '/storage/' . $cert->file_path : null,
                    'issued_at'          => $cert->issued_at,
                    'conference'         => $cert->conference,
                ];
            });

        return [
            'certificates'       => $certificates,
            'isEligible'         => $isEligibleForParticipant || $isEligibleForPresenter,
            'hasUploadedCert'    => $certificates->whereNotNull('file_path')->isNotEmpty(),
            'registrationStatus' => $registration ? 'paid' : 'unpaid',
            'activeConference'   => $activeConference,
        ];
    }

    /**
     * Helper to issue a certificate record if it doesn't exist yet.
     */
    protected function issueIfNotExists(int $userId, int $conferenceId, string $type, string $roleTitle): Certificate
    {
        $existing = Certificate::where('user_id', $userId)
            ->where('conference_id', $conferenceId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return $existing;
        }

        $code = 'CERT-ICHA-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

        return Certificate::create([
            'certificate_number' => $code,
            'user_id' => $userId,
            'conference_id' => $conferenceId,
            'type' => $type,
            'role_title' => $roleTitle,
            'issued_at' => now(),
        ]);
    }
}
