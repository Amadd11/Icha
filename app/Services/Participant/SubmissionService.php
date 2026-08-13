<?php

namespace App\Services\Participant;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class SubmissionService
{
    /**
     * Get all necessary data for the participant submission page.
     */
    public function getSubmissionData(User $user): array
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        $categories = $activeConference 
            ? Category::where('conference_id', $activeConference->id)->orWhereNull('conference_id')->get(['id', 'name', 'badge'])
            : Category::all(['id', 'name', 'badge']);

        $abstracts = AbstractSubmission::with('category')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        $papers = FullPaper::with('abstract')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        // Check if user has a verified payment for this active conference
        $isPaid = Payment::whereHas('registration', function ($q) use ($user, $activeConference) {
            $q->where('user_id', $user->id);
            if ($activeConference) {
                $q->where('conference_id', $activeConference->id);
            }
        })->where('status', 'verified')->exists();

        $hasUploadedAbstract = $abstracts->count() > 0;
        $userCode = 'ICHA-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

        return [
            'activeConference' => $activeConference,
            'categories'       => $categories,
            'abstracts'        => $abstracts,
            'papers'           => $papers,
            'isPaid'           => $isPaid,
            'statusChecklist'  => [
                'hasUploadedAbstract' => $hasUploadedAbstract,
                'hasPaid'             => $isPaid,
                'zoomLink'            => 'Coming Soon.',
            ],
            'userSummary'      => [
                'name' => $user->name,
                'role' => ucfirst($user->role),
                'code' => $userCode,
            ]
        ];
    }

    /**
     * Handle Abstract Submission logic & file storage.
     */
    public function submitAbstract(User $user, array $data, ?UploadedFile $file = null): AbstractSubmission
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        // Ensure user is paid before storing
        $isPaid = Payment::whereHas('registration', function ($q) use ($user, $activeConference) {
            $q->where('user_id', $user->id);
            if ($activeConference) {
                $q->where('conference_id', $activeConference->id);
            }
        })->where('status', 'verified')->exists();

        if (!$isPaid) {
            abort(403, 'Payment verification is required before submitting an abstract.');
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('abstracts', 'public');
        }

        $abstractCount = AbstractSubmission::count() + 1;
        $abstractCode = 'ABS-' . date('Y') . '-' . str_pad($abstractCount, 4, '0', STR_PAD_LEFT);

        return AbstractSubmission::create([
            'user_id'           => $user->id,
            'conference_id'     => $activeConference?->id,
            'category_id'       => $data['category_id'],
            'abstract_code'     => $abstractCode,
            'title'             => $data['title'],
            'abstract_text'     => $data['abstract_text'] ?? null,
            'keywords'          => $data['keywords'] ?? null,
            'presentation_type' => $data['presentation_type'] ?? 'oral',
            'file_path'         => $filePath,
            'status'            => 'pending',
        ]);
    }

    /**
     * Handle Full Paper Submission logic & file storage.
     */
    public function submitPaper(User $user, array $data, ?UploadedFile $file = null): FullPaper
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        // Ensure user is paid before storing paper
        $isPaid = Payment::whereHas('registration', function ($q) use ($user, $activeConference) {
            $q->where('user_id', $user->id);
            if ($activeConference) {
                $q->where('conference_id', $activeConference->id);
            }
        })->where('status', 'verified')->exists();

        if (!$isPaid) {
            abort(403, 'Payment verification is required before submitting a full paper.');
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('papers', 'public');
        }

        $paperCount = FullPaper::count() + 1;
        $paperCode = 'FP-' . date('Y') . '-' . str_pad($paperCount, 4, '0', STR_PAD_LEFT);

        return FullPaper::create([
            'user_id'       => $user->id,
            'conference_id' => $activeConference?->id,
            'abstract_id'   => $data['abstract_id'],
            'paper_code'    => $paperCode,
            'title'         => $data['title'],
            'file_path'     => $filePath,
            'status'        => 'pending',
        ]);
    }
}
