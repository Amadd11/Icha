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
        $authorId = $assignment->round?->abstractSubmission?->user_id
            ?? $assignment->round?->fullPaper?->user_id;
        if ($authorId && $authorId === Auth::id()) {
            abort(403, 'Conflict of interest: You cannot review your own submission.');
        }

        // Check if final decision has already been made by Admin
        $round = $assignment->round;
        $submission = $round?->abstractSubmission ?? $round?->fullPaper;
        $isDecided = ($round?->status === 'completed')
            || in_array($submission?->status, ['accepted', 'rejected'], true)
            || ($submission?->status === 'revision_required' && $round?->status !== 'open');

        if ($isDecided) {
            return redirect()->back()->withErrors(['error' => 'Penilaian tidak dapat diubah karena Admin telah menetapkan keputusan final.']);
        }

        $this->service->submitReview($assignment, $request->validated());

        return redirect()->back()->with('success', 'Review submitted successfully.');
    }
}
