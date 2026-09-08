<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignReviewerRequest;
use App\Http\Resources\ReviewerResource;
use App\Http\Resources\Submission\AdminPaperResource;
use App\Models\FullPaper;
use App\Models\User;
use App\Services\Admin\PaperReviewService;
use App\Services\Admin\ReviewAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaperController extends Controller
{
    public function __construct(
        protected PaperReviewService $paperReviewService,
        protected ReviewAssignmentService $reviewAssignmentService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $papers = $this->paperReviewService->getPapers($status);
        $reviewers = User::where('role', 'reviewer')->with('categories')->orderBy('name')->get();

        return Inertia::render('Admin/Papers/Index', [
            'papers' => AdminPaperResource::collection($papers)->resolve(),
            'reviewers' => ReviewerResource::collection($reviewers)->resolve(),
            'filters' => ['status' => $status],
        ]);
    }

    public function assignReviewers(AssignReviewerRequest $request, FullPaper $paper): RedirectResponse
    {
        $this->reviewAssignmentService->assignPaperReviewers($paper, $request->validated()['reviewer_ids']);

        return redirect()->back()->with('success', 'Reviewers assigned successfully to Full Paper.');
    }

    public function review(Request $request, FullPaper $paper): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,under_review,revision_required,accepted,rejected'],
            'review_notes' => ['nullable', 'string'],
        ]);

        $this->paperReviewService->reviewPaper(
            $paper,
            $request->user(),
            $data
        );

        return redirect()->back()->with('success', 'Full Paper review status updated!');
    }

    public function destroy(FullPaper $paper): RedirectResponse
    {
        if ($paper->file_path) {
            Storage::disk('public')->delete($paper->file_path);
        }

        $paper->delete();

        return redirect()->back()->with('success', 'Full Paper deleted successfully.');
    }
}
