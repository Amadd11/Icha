<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewerResource;
use App\Http\Resources\Submission\AdminAbstractResource;
use App\Models\AbstractSubmission;
use App\Models\User;
use App\Services\Admin\AbstractReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AbstractController extends Controller
{
    public function __construct(
        protected AbstractReviewService $abstractReviewService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $abstracts = $this->abstractReviewService->getAbstracts($status);
        $reviewers = User::where('role', 'reviewer')->with('categories')->orderBy('name')->get();

        return Inertia::render('Admin/Abstracts/Index', [
            'abstracts' => AdminAbstractResource::collection($abstracts)->resolve(),
            'reviewers' => ReviewerResource::collection($reviewers)->resolve(),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function review(Request $request, AbstractSubmission $abstract): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,under_review,revision_required,accepted,rejected'],
            'review_notes' => ['nullable', 'string'],
        ]);

        $this->abstractReviewService->reviewAbstract(
            $abstract,
            $request->user(),
            $data
        );

        return redirect()->back()->with('success', 'Abstract review status updated!');
    }
}
