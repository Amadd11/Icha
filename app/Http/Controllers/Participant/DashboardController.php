<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Services\Participant\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        // Redirect admins and reviewers to their respective dashboards
        if (in_array($request->user()->role, ['super_admin', 'admin'])) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->role === 'reviewer') {
            return redirect()->route('reviewer.dashboard');
        }

        $data = $this->dashboardService->getDashboardData($request->user());

        return Inertia::render('Participant/Dashboard', $data);
    }
}
