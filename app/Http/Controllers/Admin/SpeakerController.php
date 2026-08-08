<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpeakerRequest;
use App\Models\Conference;
use App\Models\Speaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SpeakerController extends Controller
{
    public function index(): Response
    {
        $conferences = Conference::active()->get(['id', 'title']);
        $speakers = Speaker::with('conference:id,title')
            ->orderBy('conference_id')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Speakers/Index', [
            'speakers'    => $speakers,
            'conferences' => $conferences,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Speakers/Form', [
            'speaker'     => null,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function store(SpeakerRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')
                ->store('speakers', 'public');
        }

        Speaker::create($validated);

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Speaker added successfully.');
    }

    public function edit(Speaker $speaker): Response
    {
        return Inertia::render('Admin/Speakers/Form', [
            'speaker'     => $speaker,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function update(SpeakerRequest $request, Speaker $speaker)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            if ($speaker->photo) {
                Storage::disk('public')->delete($speaker->photo);
            }
            $validated['photo'] = $request->file('photo')
                ->store('speakers', 'public');
        }

        $speaker->update($validated);

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Speaker updated successfully.');
    }

    public function destroy(Speaker $speaker)
    {
        if ($speaker->photo) {
            Storage::disk('public')->delete($speaker->photo);
        }
        $speaker->delete();

        return redirect()->route('admin.speakers.index')
            ->with('success', 'Speaker deleted.');
    }
}
