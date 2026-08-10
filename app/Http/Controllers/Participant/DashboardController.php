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
        // Redirect admins to their own panel — keeping routing clean
        if (in_array($request->user()->role, ['super_admin', 'admin'])) {
            return redirect()->route('admin.dashboard');
        }

        $data = $this->dashboardService->getDashboardData($request->user());

        return Inertia::render('Participant/Dashboard', $data);
    }
}
