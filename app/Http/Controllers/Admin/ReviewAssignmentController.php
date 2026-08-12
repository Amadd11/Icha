<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignReviewerRequest;
use App\Models\AbstractSubmission;
use App\Services\Admin\ReviewAssignmentService;
use Illuminate\Http\RedirectResponse;

class ReviewAssignmentController extends Controller
{
    public function __construct(
        protected ReviewAssignmentService $service
    ) {}

    public function store(AssignReviewerRequest $request, AbstractSubmission $abstract): RedirectResponse
    {
        $this->service->assignReviewers($abstract, $request->validated()['reviewer_ids']);

        return redirect()->back()->with('success', 'Reviewers assigned successfully.');
    }
}
