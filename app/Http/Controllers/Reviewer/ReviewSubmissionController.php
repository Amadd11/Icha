<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviewer\SubmitReviewRequest;
use App\Models\ReviewAssignment;
use App\Services\Reviewer\ReviewSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewSubmissionController extends Controller
{
    public function __construct(
        protected ReviewSubmissionService $service
    ) {}

    public function store(SubmitReviewRequest $request, ReviewAssignment $assignment): RedirectResponse
    {
        // Check ownership
        if ($assignment->reviewer_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        // Conflict of Interest: Author cannot review their own submission
        $authorId = $assignment->round?->abstractSubmission?->user_id;
        if ($authorId && $authorId === Auth::id()) {
            abort(403, 'Conflict of interest: You cannot review your own submission.');
        }

        // Category Expertise Check: Reviewer must have this topic category
        $categoryExpertise = Auth::user()?->categories()->where('categories.id', $assignment->round?->abstractSubmission?->category_id)->exists();
        if ($assignment->round?->abstractSubmission?->category_id && !$categoryExpertise) {
            abort(403, 'Anda tidak memiliki bidang kepakaran pada kategori abstrak ini.');
        }

        // Check if final decision has already been made by Admin
        $round = $assignment->round;
        $submission = $round?->abstractSubmission;
        $isDecided = ($round?->status === 'completed')
            || in_array($submission?->status, ['accepted', 'rejected'], true)
            || ($submission?->status === 'revision_required' && $round?->status !== 'open');

        if ($isDecided) {
            return redirect()->back()->withErrors(['error' => 'Penilaian tidak dapat diubah karena Admin telah menetapkan keputusan final.']);
        }

        // First-Come, First-Served slot limit check: Max 3 reviewers per abstract on Round 1
        if (($round?->round_number ?? 1) === 1 && $assignment->status !== 'completed') {
            $otherCompletedCount = $round->assignments()
                ->where('status', 'completed')
                ->where('id', '!=', $assignment->id)
                ->count();

            if ($otherCompletedCount >= 3) {
                return redirect()->back()->withErrors(['error' => 'Maaf, slot review untuk naskah ini sudah penuh (3/3 telah diisi oleh reviewer lain).']);
            }
        }

        $this->service->submitReview($assignment, $request->validated());

        return redirect()->back()->with('success', 'Review submitted successfully.');
    }
}
