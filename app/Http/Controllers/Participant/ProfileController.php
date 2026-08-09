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

        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone'                => $validated['phone'],
                'institution'          => $validated['institution'],
                'country'              => $validated['country'],
                'city'                 => $validated['city'],
                'address'              => $validated['address'] ?? null,
                'participant_category' => $validated['participant_category'],
                'identity_number'      => $validated['identity_number'],
                'gender'               => $validated['gender'],
            ]
        );

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
