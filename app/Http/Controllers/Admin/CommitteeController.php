<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommitteeRequest;
use App\Http\Requests\Admin\UpdateCommitteeRequest;
use App\Models\Committee;
use App\Models\Conference;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeController extends Controller
{
    public function index(\Illuminate\Http\Request $request): Response
    {
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $committees = Committee::with('conference:id,title')
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->orderBy('group')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Committees/Index', [
            'committees'  => $committees,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Committees/Form', [
            'committee'   => null,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function store(StoreCommitteeRequest $request)
    {
        Committee::create($request->validated());

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee member added successfully.');
    }

    public function edit(Committee $committee): Response
    {
        return Inertia::render('Admin/Committees/Form', [
            'committee'   => $committee,
            'conferences' => Conference::all(['id', 'title']),
        ]);
    }

    public function update(UpdateCommitteeRequest $request, Committee $committee)
    {
        $committee->update($request->validated());

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee member updated successfully.');
    }

    public function destroy(Committee $committee)
    {
        $committee->delete();

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee member deleted.');
    }
}
