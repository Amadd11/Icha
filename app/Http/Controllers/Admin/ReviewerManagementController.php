<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReviewerRequest;
use App\Http\Requests\Admin\UpdateReviewerRequest;
use App\Http\Resources\ReviewerResource;
use App\Models\User;
use App\Services\Admin\ReviewerManagementService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewerManagementController extends Controller
{
    public function __construct(
        protected ReviewerManagementService $service
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Reviewers/Index', [
            'reviewers' => ReviewerResource::collection($this->service->getReviewers())->resolve(),
            'categories' => $this->service->getCategories(),
        ]);
    }

    public function store(StoreReviewerRequest $request): RedirectResponse
    {
        $this->service->createReviewer($request->validated());

        return redirect()->back()->with('success', 'Reviewer created successfully.');
    }

    public function update(UpdateReviewerRequest $request, User $reviewer): RedirectResponse
    {
        $this->service->updateReviewer($reviewer, $request->validated());

        return redirect()->back()->with('success', 'Reviewer updated successfully.');
    }

    public function destroy(User $reviewer): RedirectResponse
    {
        $this->service->deleteReviewer($reviewer);

        return redirect()->back()->with('success', 'Reviewer deleted successfully.');
    }
}
