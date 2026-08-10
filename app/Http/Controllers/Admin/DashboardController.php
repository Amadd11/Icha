<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        $conferenceId = $request->query('conference_id')
            ? (int) $request->query('conference_id')
            : null;

        $data = $this->dashboardService->getDashboardData($conferenceId);

        return Inertia::render('Admin/Dashboard', $data);
    }
}
