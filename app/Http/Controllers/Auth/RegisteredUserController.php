<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'  => 'nullable|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'name'        => 'nullable|string|max:255',
            'country'     => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'email'       => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password'    => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Construct full name if first_name / last_name provided, fallback to request->name or Participant
        $fullName = trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $request->name ?? 'Participant';
        }

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create Profile record
        $user->profile()->create([
            'phone' => $request->phone,
            'institution' => $request->institution,
            'country' => $request->country ?? 'Indonesia',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
