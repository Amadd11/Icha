<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Services\Reviewer\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Reviewer/Dashboard', [
            'stats' => $this->dashboardService->getStats(),
        ]);
    }
}
