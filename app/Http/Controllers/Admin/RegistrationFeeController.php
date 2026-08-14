<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRegistrationFeeRequest;
use App\Http\Requests\Admin\UpdateRegistrationFeeRequest;
use App\Models\Conference;
use App\Models\RegistrationFee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFeeController extends Controller
{
    public function index(Request $request): Response
    {
        $confId = $request->query('conference_id')
            ?? session('admin_conference_id')
            ?? Conference::where('is_active', true)->first()?->id;

        $registrationFees = RegistrationFee::with('conference:id,title')
            ->when($confId, function ($q) use ($confId) {
                $q->where('conference_id', $confId);
            })
            ->latest('id')
            ->get();

        return Inertia::render('Admin/RegistrationFees/Index', [
            'registrationFees' => $registrationFees,
            'conferences'      => Conference::all(['id', 'title']),
            'selectedConfId'   => $confId ? (int) $confId : null,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RegistrationFees/Form', [
            'registrationFee' => null,
            'conferences'     => Conference::all(['id', 'title']),
        ]);
    }

    public function store(StoreRegistrationFeeRequest $request)
    {
        RegistrationFee::create($request->validated());

        return redirect()->route('admin.registration-fees.index')
            ->with('success', 'Registration fee created successfully.');
    }

    public function edit(RegistrationFee $registrationFee): Response
    {
        return Inertia::render('Admin/RegistrationFees/Form', [
            'registrationFee' => $registrationFee,
            'conferences'     => Conference::all(['id', 'title']),
        ]);
    }

    public function update(UpdateRegistrationFeeRequest $request, RegistrationFee $registrationFee)
    {
        $registrationFee->update($request->validated());

        return redirect()->route('admin.registration-fees.index')
            ->with('success', 'Registration fee updated successfully.');
    }

    public function destroy(RegistrationFee $registrationFee)
    {
        $registrationFee->delete();

        return redirect()->route('admin.registration-fees.index')
            ->with('success', 'Registration fee deleted successfully.');
    }
}
