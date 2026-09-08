<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Review\ReviewerAssignmentResource;
use App\Services\Reviewer\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        return $this->renderSubmissionView('abstract');
    }

    public function abstracts(Request $request): Response
    {
        return $this->renderSubmissionView('abstract');
    }

    public function papers(Request $request): Response
    {
        return $this->renderSubmissionView('full_paper');
    }

    private function renderSubmissionView(string $submissionType): Response
    {
        $assignments = $this->dashboardService->getAssignments($submissionType);
        $component = ($submissionType === 'full_paper') ? 'Reviewer/Papers/Index' : 'Reviewer/Abstracts/Index';

        return Inertia::render($component, [
            'submissionType' => $submissionType,
            'stats'          => $this->dashboardService->getStats($assignments, $submissionType),
            'assignments'    => ReviewerAssignmentResource::collection($assignments)->resolve(),
        ]);
    }
}
