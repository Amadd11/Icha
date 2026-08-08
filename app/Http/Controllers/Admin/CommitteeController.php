<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommitteeRequest;
use App\Models\Committee;
use App\Models\Conference;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeController extends Controller
{
    public function index(): Response
    {
        $committees = Committee::with('conference:id,title')
            ->orderBy('conference_id')
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

    public function store(CommitteeRequest $request)
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

    public function update(CommitteeRequest $request, Committee $committee)
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
