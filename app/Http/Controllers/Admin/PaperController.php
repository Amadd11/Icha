<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FullPaper;
use App\Services\Admin\PaperReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaperController extends Controller
{
    public function __construct(
        protected PaperReviewService $paperReviewService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $papers = $this->paperReviewService->getPapers($status);

        return Inertia::render('Admin/Papers/Index', [
            'papers' => $papers,
            'filters' => ['status' => $status],
        ]);
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
}
