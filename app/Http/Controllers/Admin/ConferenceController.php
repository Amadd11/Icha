<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConferenceRequest;
use App\Http\Requests\Admin\UpdateConferenceRequest;
use App\Models\Conference;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ConferenceController extends Controller
{
    public function index(): Response
    {
        $conferences = Conference::latest()->get();

        return Inertia::render('Admin/Conferences/Index', [
            'conferences' => $conferences,
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->user()->role !== 'super_admin') {
            abort(403, 'Only Super Admin can create new conference editions.');
        }

        return Inertia::render('Admin/Conferences/Form', [
            'conference' => null,
        ]);
    }

    public function store(StoreConferenceRequest $request)
    {
        if ($request->user()->role !== 'super_admin') {
            abort(403, 'Only Super Admin can create new conference editions.');
        }

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('conferences/logos', 'public');
        }

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('conferences/heroes', 'public');
        }

        Conference::create($data);

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference created successfully.');
    }

    public function show(Conference $conference): Response
    {
        $conference->load(['categories', 'speakers', 'timelines', 'sponsors']);

        return Inertia::render('Admin/Conferences/Show', [
            'conference' => $conference,
        ]);
    }

    public function edit(Conference $conference): Response
    {
        return Inertia::render('Admin/Conferences/Form', [
            'conference' => $conference,
        ]);
    }

    public function update(UpdateConferenceRequest $request, Conference $conference)
    {
        $data = $request->validated();

        if ($request->boolean('remove_logo')) {
            if ($conference->logo) {
                Storage::disk('public')->delete($conference->logo);
            }
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($conference->logo) {
                Storage::disk('public')->delete($conference->logo);
            }
            $data['logo'] = $request->file('logo')->store('conferences/logos', 'public');
        } else {
            unset($data['logo']);
        }

        if ($request->boolean('remove_hero_image')) {
            if ($conference->hero_image) {
                Storage::disk('public')->delete($conference->hero_image);
            }
            $data['hero_image'] = null;
        } elseif ($request->hasFile('hero_image')) {
            if ($conference->hero_image) {
                Storage::disk('public')->delete($conference->hero_image);
            }
            $data['hero_image'] = $request->file('hero_image')->store('conferences/heroes', 'public');
        } else {
            unset($data['hero_image']);
        }

        $conference->update($data);

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference updated successfully.');
    }

    public function destroy(Request $request, Conference $conference)
    {
        if ($request->user()->role !== 'super_admin') {
            abort(403, 'Only Super Admin can delete conference editions.');
        }

        if ($conference->logo) {
            Storage::disk('public')->delete($conference->logo);
        }
        if ($conference->hero_image) {
            Storage::disk('public')->delete($conference->hero_image);
        }

        $conference->delete();

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference deleted.');
    }
}
