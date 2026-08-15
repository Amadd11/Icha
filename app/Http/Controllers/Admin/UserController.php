<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the users with pagination (20 per page).
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $query = User::with('profile')
            ->when($role && $role !== 'all', function ($q) use ($role) {
                $q->where('role', $role);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($pQ) use ($search) {
                            $pQ->where('institution', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id');

        $users = $query->paginate(20)->withQueryString();

        $roleCounts = [
            'all'         => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'admin'       => User::where('role', 'admin')->count(),
            'reviewer'    => User::where('role', 'reviewer')->count(),
            'participant' => User::where('role', 'participant')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users'      => $users,
            'filters'    => [
                'search' => $search ?? '',
                'role'   => $role ?? 'all',
            ],
            'roleCounts' => $roleCounts,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
            'role'        => ['required', 'string', Rule::in(['super_admin', 'admin', 'reviewer', 'participant'])],
            'institution' => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        Profile::create([
            'user_id'     => $user->id,
            'institution' => $validated['institution'] ?? null,
            'phone'       => $validated['phone'] ?? null,
        ]);

        return redirect()->back()->with('success', "User '{$user->name}' created successfully.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'    => ['nullable', 'string', 'min:8'],
            'role'        => ['required', 'string', Rule::in(['super_admin', 'admin', 'reviewer', 'participant'])],
            'institution' => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
        ]);

        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institution' => $validated['institution'] ?? null,
                'phone'       => $validated['phone'] ?? null,
            ]
        );

        return redirect()->back()->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "User '{$name}' deleted successfully.");
    }
}
