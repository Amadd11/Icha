<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TimelineRequest;
use App\Models\Conference;
use App\Models\Timeline;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    public function index(): Response
    {
        $timelines = Timeline::with('conference:id,title')
            ->orderBy('conference_id')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Timelines/Index', [
            'timelines'   => $timelines,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Timelines/Form', [
            'timeline'    => null,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function store(TimelineRequest $request)
    {
        Timeline::create($request->validated());

        return redirect()->route('admin.timelines.index')
            ->with('success', 'Timeline item added successfully.');
    }

    public function edit(Timeline $timeline): Response
    {
        return Inertia::render('Admin/Timelines/Form', [
            'timeline'    => $timeline,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function update(TimelineRequest $request, Timeline $timeline)
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
