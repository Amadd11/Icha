<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreAbstractRequest;
use App\Http\Requests\Participant\StorePaperRequest;
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
        $user = $request->user();
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        $registration = Registration::with(['registrationFee', 'payment'])
            ->where('user_id', $user->id)
            ->where('conference_id', $activeConference?->id)
            ->latest()
            ->first();

        if (!$registration) {
            return redirect()->route('participant.registration.create')->with('error', 'Please complete your conference registration with a Presenter package to access the Submission portal.');
        }

        if ($registration->registrationFee && $registration->registrationFee->type === 'non_presenter') {
            return redirect()->route('dashboard')->with('error', 'The Call for Papers / Submission portal is exclusively available for Presenter ticket holders.');
        }

        $isPaid = ($registration->status === 'paid' || $registration->payment?->status === 'verified');
        if (!$isPaid) {
            return redirect()->route('participant.registration.create')->with('error', 'Please complete and verify your Presenter registration payment to access the Submission portal.');
        }

        $data = $this->submissionService->getSubmissionData($user);

        return Inertia::render('Participant/Submission/Index', $data);
    }

    public function storeAbstract(StoreAbstractRequest $request): RedirectResponse
    {
        try {
            $this->submissionService->submitAbstract(
                $request->user(),
                $request->validated(),
                $request->file('file')
            );

            return redirect()->back()->with('success', 'Abstract submitted successfully!');
        } catch (\App\Exceptions\SubmissionException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storePaper(StorePaperRequest $request): RedirectResponse
    {
        try {
            $this->submissionService->submitPaper(
                $request->user(),
                $request->validated(),
                $request->file('file')
            );

            return redirect()->back()->with('success', 'Full Paper submitted successfully!');
        } catch (\App\Exceptions\SubmissionException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadTemplate(Conference $conference, string $type)
    {
        $filePath = $type === 'abstract' ? $conference->abstract_template : $conference->paper_template;

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
