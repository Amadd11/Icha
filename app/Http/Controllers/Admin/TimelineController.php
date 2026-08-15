<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTimelineRequest;
use App\Http\Requests\Admin\UpdateTimelineRequest;
use App\Models\Conference;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    public function index(Request $request): Response
    {
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $timelines = Timeline::with('conference:id,title')
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Timelines/Index', [
            'timelines'   => $timelines,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.timelines.index');
    }

    public function store(StoreTimelineRequest $request): RedirectResponse
    {
        Timeline::create($request->validated());

        return redirect()->route('admin.timelines.index')
            ->with('success', 'Timeline item added successfully.');
    }

    public function edit(Timeline $timeline): RedirectResponse
    {
        return redirect()->route('admin.timelines.index');
    }

    public function update(UpdateTimelineRequest $request, Timeline $timeline)
    {
        $timeline->update($request->validated());

        return redirect()->route('admin.timelines.index')
            ->with('success', 'Timeline item updated successfully.');
    }

    public function destroy(Timeline $timeline)
    {
        $timeline->delete();

        return redirect()->route('admin.timelines.index')
            ->with('success', 'Timeline item deleted.');
    }
}
