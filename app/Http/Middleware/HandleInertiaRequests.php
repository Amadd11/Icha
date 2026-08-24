<?php

namespace App\Http\Middleware;

use App\Models\Conference;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Handle Admin Active Conference Scope via Session / Query Parameter
        if ($request->query('conference_id')) {
            session(['admin_conference_id' => (int) $request->query('conference_id')]);
        }

        $selectedConfId = session('admin_conference_id');
        $activeConference = null;
        $availableConferences = [];

        try {
            $activeConference = $selectedConfId ? Conference::find($selectedConfId) : Conference::where('is_active', true)->first();
            
            if (!$activeConference) {
                $activeConference = Conference::latest()->first();
            }

            $availableConferences = Conference::select('id', 'title', 'year', 'slug', 'is_active', 'status')
                ->orderByDesc('year')
                ->get();
        } catch (\Throwable $e) {
            // Graceful fallback if database is not migrated yet on fresh hosting
            $activeConference = null;
            $availableConferences = [];
        }

        $user = $request->user();
        $isPresenter = false;
        $isPaidPresenter = false;

        if ($user && $user->role === 'participant') {
            $activeConfId = $activeConference?->id;
            $reg = \App\Models\Registration::with(['registrationFee', 'payment'])
                ->where('user_id', $user->id)
                ->when($activeConfId, fn($q) => $q->where('conference_id', $activeConfId))
                ->latest()
                ->first();

            if ($reg && $reg->registrationFee) {
                $isPresenter = ($reg->registrationFee->type === 'presenter');
                $isPaid = ($reg->status === 'paid' || $reg->payment?->status === 'verified');
                $isPaidPresenter = ($isPresenter && $isPaid);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role,
                    'is_presenter' => $isPaidPresenter,
                ] : null,
            ],
            'activeConference' => $activeConference,
            'availableConferences' => $availableConferences,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
