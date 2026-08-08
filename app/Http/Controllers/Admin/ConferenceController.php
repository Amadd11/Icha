<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConferenceRequest;
use App\Models\Conference;
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

    public function create(): Response
    {
        return Inertia::render('Admin/Conferences/Form', [
            'conference' => null,
        ]);
    }

    public function store(ConferenceRequest $request)
    {
        Conference::create($request->validated());

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

    public function update(ConferenceRequest $request, Conference $conference)
    {
        $conference->update($request->validated());

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference updated successfully.');
    }

    public function destroy(Conference $conference)
    {
        $conference->delete();

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference deleted.');
    }
}
