<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpeakerRequest;
use App\Http\Requests\Admin\UpdateSpeakerRequest;
use App\Models\Conference;
use App\Models\Speaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SpeakerController extends Controller
{
    public function index(\Illuminate\Http\Request $request): Response
    {
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $conferences = Conference::select(['id', 'title'])->get();
        $speakers = Speaker::with('conference:id,title')
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
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

    public function store(StoreSpeakerRequest $request)
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

    public function update(UpdateSpeakerRequest $request, Speaker $speaker)
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
