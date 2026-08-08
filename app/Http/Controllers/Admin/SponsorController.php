<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SponsorRequest;
use App\Models\Conference;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SponsorController extends Controller
{
    public function index(): Response
    {
        $sponsors = Sponsor::with('conference:id,title')
            ->orderBy('conference_id')
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

    public function store(SponsorRequest $request)
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

    public function update(SponsorRequest $request, Sponsor $sponsor)
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
