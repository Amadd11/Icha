<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('profile');

        return Inertia::render('Participant/Profile/Edit', [
            'user'    => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user name
        $user->update(['name' => $validated['name']]);

        // Update or create profile according to updated migration
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone'                => $validated['phone'] ?? null,
                'institution'          => $validated['institution'] ?? null,
                'country'              => $validated['country'],
                'city'                 => $validated['city'] ?? null,
                'participant_category' => $validated['participant_category'] ?? 'non_student',
                'gender'               => $validated['gender'] ?? null,
            ]
        );

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
