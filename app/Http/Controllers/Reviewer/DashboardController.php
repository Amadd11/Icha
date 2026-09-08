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
        return $this->abstracts($request);
    }

    public function abstracts(Request $request): Response
    {
        $assignments = $this->dashboardService->getAssignments('abstract');

        return Inertia::render('Reviewer/Abstracts/Index', [
            'submissionType' => 'abstract',
            'stats'          => $this->dashboardService->getStats($assignments, 'abstract'),
            'assignments'    => ReviewerAssignmentResource::collection($assignments)->resolve(),
        ]);
    }
}
