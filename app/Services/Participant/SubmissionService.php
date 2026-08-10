<?php

namespace App\Services\Participant;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\FullPaper;
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
        $activeConference = Conference::where('is_active', true)->first();

        $categories = $activeConference 
            ? Category::where('conference_id', $activeConference->id)->get(['id', 'name', 'badge'])
            : Category::all(['id', 'name', 'badge']);

        $abstracts = AbstractSubmission::with('category')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $papers = FullPaper::with('abstract')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $latestRegistration = Registration::where('user_id', $user->id)->latest()->first();
        $hasPaid = $latestRegistration && $latestRegistration->status === 'paid';
        $hasUploadedAbstract = $abstracts->count() > 0;
        $userCode = 'ICHA-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

        return [
            'activeConference' => $activeConference,
            'categories' => $categories,
            'abstracts' => $abstracts,
            'papers' => $papers,
            'statusChecklist' => [
                'hasUploadedAbstract' => $hasUploadedAbstract,
                'hasPaid' => $hasPaid,
                'zoomLink' => 'Coming Soon.',
            ],
            'userSummary' => [
                'name' => $user->name,
                'role' => ucfirst($user->role),
                'code' => $userCode,
            ]
        ];
    }

    /**
     * Handle Abstract Submission logic & file storage.
     */
    public function submitAbstract(User $user, array $data, UploadedFile $file): AbstractSubmission
    {
        $activeConference = Conference::where('is_active', true)->first();
        $path = $file->store('abstracts', 'public');
        $code = 'ABS-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

        return AbstractSubmission::create([
            'abstract_code' => $code,
            'user_id' => $user->id,
            'conference_id' => $activeConference?->id ?? 1,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'abstract_text' => $data['abstract_text'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'presentation_type' => $data['presentation_type'] ?? 'oral',
            'file_path' => $path,
            'status' => 'pending',
        ]);
    }

    /**
     * Handle Full Paper Submission logic & file storage.
     */
    public function submitPaper(User $user, array $data, UploadedFile $file): FullPaper
    {
        $activeConference = Conference::where('is_active', true)->first();
        $path = $file->store('full_papers', 'public');
        $code = 'PAPER-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

        return FullPaper::create([
            'paper_code' => $code,
            'user_id' => $user->id,
            'conference_id' => $activeConference?->id ?? 1,
            'abstract_id' => $data['abstract_id'] ?? null,
            'title' => $data['title'],
            'file_path' => $path,
            'status' => 'pending',
        ]);
    }
}
