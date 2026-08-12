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

        // Check if round is already locked
        if ($assignment->round->status === 'locked' || $assignment->round->status === 'completed') {
            return redirect()->back()->withErrors(['error' => 'This review round is already locked.']);
        }

        $this->service->submitReview($assignment, $request->validated());

        return redirect()->back()->with('success', 'Review submitted successfully.');
    }
}
