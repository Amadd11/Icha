<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $conferences = Conference::orderByDesc('year')->get();
        $selectedConferenceId = $request->input('conference_id') 
            ?? Conference::where('is_active', true)->value('id') 
            ?? $conferences->first()?->id;

        $selectedConference = $conferences->firstWhere('id', $selectedConferenceId);

        $search = $request->input('search');
        $statusFilter = $request->input('status'); // 'all', 'uploaded', 'not_uploaded'

        // Retrieve participants registered for this conference
        $registrationsQuery = Registration::with(['user.profile', 'registrationFee', 'payment'])
            ->where('conference_id', $selectedConferenceId);

        if ($search) {
            $registrationsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $registrations = $registrationsQuery->latest()->get();

        // Retrieve existing certificates for this conference
        $certificates = Certificate::with('user')
            ->where('conference_id', $selectedConferenceId)
            ->get()
            ->groupBy('user_id');

        // Combine into unified participant rows
        $participantsList = $registrations->map(function ($reg) use ($certificates) {
            if (!$reg->user) return null;

            $userId = $reg->user->id;
            $userCerts = $certificates->get($userId, collect());
            $cert = $userCerts->first();

            return [
                'user_id'              => $userId,
                'name'                 => $reg->user->name,
                'email'                => $reg->user->email,
                'institution'          => $reg->user->profile?->institution ?? '-',
                'registration_id'      => $reg->id,
                'invoice_number'       => $reg->invoice_number,
                'registration_package' => $reg->registrationFee?->name ?? 'Participant',
                'payment_status'       => $reg->payment?->status ?? $reg->status,
                'certificate'          => $cert ? [
                    'id'                 => $cert->id,
                    'certificate_number' => $cert->certificate_number,
                    'role_title'         => $cert->role_title,
                    'file_path'          => $cert->file_path,
                    'file_url'           => $cert->file_path ? '/storage/' . $cert->file_path : null,
                    'issued_at'          => $cert->issued_at?->format('d M Y H:i'),
                ] : null,
            ];
        })->filter()->values();

        // Apply status filters
        if ($statusFilter === 'uploaded') {
            $participantsList = $participantsList->filter(function ($p) {
                return !empty($p['certificate']['file_path']);
            })->values();
        } elseif ($statusFilter === 'not_uploaded') {
            $participantsList = $participantsList->filter(function ($p) {
                return empty($p['certificate']['file_path']);
            })->values();
        }

        return Inertia::render('Admin/Certificates/Index', [
            'conferences'          => $conferences,
            'selectedConferenceId' => (int) $selectedConferenceId,
            'selectedConference'   => $selectedConference,
            'participants'         => $participantsList,
            'filters'              => [
                'search'        => $search,
                'status'        => $statusFilter ?? 'all',
                'conference_id' => (int) $selectedConferenceId,
            ],
            'stats' => [
                'total_participants' => $registrations->count(),
                'uploaded_count'     => $registrations->filter(function ($r) use ($certificates) {
                    $userCerts = $certificates->get($r->user_id);
                    return $userCerts && !empty($userCerts->first()?->file_path);
                })->count(),
                'pending_count'      => $registrations->filter(function ($r) use ($certificates) {
                    $userCerts = $certificates->get($r->user_id);
                    return !$userCerts || empty($userCerts->first()?->file_path);
                })->count(),
            ]
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'       => ['required', 'exists:users,id'],
            'conference_id' => ['required', 'exists:conferences,id'],
            'file'          => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $conference = Conference::findOrFail($validated['conference_id']);

        // Retrieve registration to get accurate package role title
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->where('conference_id', $conference->id)
            ->first();

        $roleTitle = $registration?->registrationFee?->name ?? 'Participant / Attendee';

        // Check if certificate record exists
        $certificate = Certificate::where('user_id', $user->id)
            ->where('conference_id', $conference->id)
            ->first();

        // Handle file upload
        $file = $request->file('file');
        $cleanUserName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->name);
        $filename = 'CERT_' . $user->id . '_' . time() . '_' . $cleanUserName . '.pdf';
        $filePath = $file->storeAs('certificates', $filename, 'public');

        if ($certificate) {
            // Delete old file
            if ($certificate->file_path) {
                Storage::disk('public')->delete($certificate->file_path);
            }

            $certificate->update([
                'file_path'  => $filePath,
                'type'       => 'participant',
                'role_title' => $roleTitle,
                'issued_at'  => now(),
            ]);
        } else {
            $code = 'CERT-ICHA-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

            Certificate::create([
                'user_id'            => $user->id,
                'conference_id'      => $conference->id,
                'type'               => 'participant',
                'role_title'         => $roleTitle,
                'certificate_number' => $code,
                'file_path'          => $filePath,
                'issued_at'          => now(),
            ]);
        }

        return redirect()->back()->with('success', "Certificate uploaded successfully for {$user->name}.");
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        if ($certificate->file_path) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return redirect()->back()->with('success', 'Certificate deleted successfully.');
    }
}
