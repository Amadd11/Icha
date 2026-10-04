<?php

namespace App\Http\Controllers\Participant;

use App\Exceptions\SubmissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreAbstractRequest;
use App\Models\Conference;
use App\Models\Registration;
use App\Services\Participant\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user()->loadMissing('profile');
        if ($user->profile?->participation_type === 'non_presenter') {
            return redirect()->route('dashboard')->with('error', 'Akun Anda terdaftar sebagai Peserta Umum (Non-Presenter). Menu pengiriman naskah ilmiah khusus untuk Pemakalah (Presenter).');
        }

        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        $registration = Registration::with(['registrationFee', 'payment'])
            ->where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->latest()
            ->first();

        if ($registration && $registration->registrationFee?->type === 'non_presenter' && ($registration->status === 'paid' || $registration->payment?->status === 'verified')) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda saat ini terdaftar sebagai tiket Non-Presenter (Audience). Menu pengiriman naskah ilmiah khusus untuk Pemakalah (Presenter).');
        }

        $data = $this->submissionService->getSubmissionData($user);

        return Inertia::render('Participant/Submission/Index', $data);
    }

    public function storeAbstract(StoreAbstractRequest $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('profile');
        if ($user->profile?->participation_type === 'non_presenter') {
            return redirect()->route('dashboard')->with('error', 'Peserta Non-Presenter tidak diizinkan mengirimkan naskah.');
        }

        try {
            $this->submissionService->submitAbstract(
                $user,
                $request->validated(),
                $request->file('file')
            );

            return redirect()->back()->with('success', 'Abstract submitted successfully!');
        } catch (SubmissionException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadTemplate(Conference $conference, string $type = 'abstract')
    {
        $filePath = $conference->abstract_template;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Berkas template belum diunggah oleh panitia.');
        }

        // Extract clean original filename without the timestamp prefix
        $filename = basename($filePath);
        $cleanName = preg_replace('/^\d+_/', '', $filename);
        if (empty($cleanName)) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $cleanName = ($type === 'abstract' ? 'Abstract-Template' : 'Full-Paper-Template') . '.' . $extension;
        }

        return Storage::disk('public')->download($filePath, $cleanName);
    }
}
