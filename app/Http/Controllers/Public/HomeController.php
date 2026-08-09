<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the root conference portal / home page.
     */
    public function __invoke(): Response
    {
        $activeConference = Conference::with([
            'categories',
            'speakers',
            'timelines',
            'sponsors' => fn($q) => $q->where('is_active', true),
        ])
            ->where('is_active', true)
            ->first();

        $availableConferences = Conference::where('status', '!=', 'draft')
            ->select('id', 'title', 'slug', 'year')
            ->orderByDesc('year')
            ->get();

        return Inertia::render('Public/Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'activeConference' => $activeConference,
            'availableConferences' => $availableConferences,
        ]);
    }
}
