<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSponsorRequest;
use App\Http\Requests\Admin\UpdateSponsorRequest;
use App\Models\Conference;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SponsorController extends Controller
{
    public function index(Request $request): Response
    {
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $sponsors = Sponsor::with('conference:id,title')
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->orderBy('tier')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Sponsors/Index', [
            'sponsors'    => $sponsors,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sponsors/Form', [
            'sponsor'     => null,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function store(StoreSponsorRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')
                ->store('sponsors', 'public');
        }

        Sponsor::create($validated);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor added successfully.');
    }

    public function edit(Sponsor $sponsor): Response
    {
        return Inertia::render('Admin/Sponsors/Form', [
            'sponsor'     => $sponsor,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            if ($sponsor->logo) {
                Storage::disk('public')->delete($sponsor->logo);
            }
            $validated['logo'] = $request->file('logo')
                ->store('sponsors', 'public');
        }

        $sponsor->update($validated);

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor updated successfully.');
    }

    public function destroy(Sponsor $sponsor)
    {
        if ($sponsor->logo) {
            Storage::disk('public')->delete($sponsor->logo);
        }
        $sponsor->delete();

        return redirect()->route('admin.sponsors.index')
            ->with('success', 'Sponsor deleted.');
    }
}
