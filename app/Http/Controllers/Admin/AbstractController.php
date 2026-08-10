<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbstractSubmission;
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

        return Inertia::render('Admin/Abstracts/Index', [
            'abstracts' => $abstracts,
            'filters' => ['status' => $status],
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
