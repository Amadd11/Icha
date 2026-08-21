<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConferenceRequest;
use App\Http\Requests\Admin\UpdateConferenceRequest;
use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        if ($request->hasFile('hero_images')) {
            $heroPaths = [];
            foreach (array_slice($request->file('hero_images'), 0, 4) as $file) {
                $heroPaths[] = $file->store('conferences/heroes', 'public');
            }
            $data['hero_images'] = $heroPaths;
        }

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('conferences/posters', 'public');
        }

        if ($request->hasFile('abstract_template')) {
            $file = $request->file('abstract_template');
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $filename = time() . '_' . $cleanName;
            $data['abstract_template'] = $file->storeAs('conferences/templates', $filename, 'public');
        }

        if ($request->hasFile('paper_template')) {
            $file = $request->file('paper_template');
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $filename = time() . '_' . $cleanName;
            $data['paper_template'] = $file->storeAs('conferences/templates', $filename, 'public');
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

        // Manage hero carousel images array (Max 4 photos)
        $currentHeroImages = is_array($conference->hero_images) ? $conference->hero_images : [];

        // Handle specific image deletions
        $removeHeroImages = $request->input('remove_hero_images', []);
        if (!empty($removeHeroImages)) {
            $filtered = [];
            foreach ($currentHeroImages as $imgPath) {
                if (in_array($imgPath, $removeHeroImages)) {
                    Storage::disk('public')->delete($imgPath);
                } else {
                    $filtered[] = $imgPath;
                }
            }
            $currentHeroImages = $filtered;
        }

        // Upload new multiple hero images up to limit of 4
        if ($request->hasFile('hero_images')) {
            foreach ($request->file('hero_images') as $file) {
                if (count($currentHeroImages) < 4) {
                    $currentHeroImages[] = $file->store('conferences/heroes', 'public');
                }
            }
        }

        $currentHeroImages = array_slice($currentHeroImages, 0, 4);
        $data['hero_images'] = array_values($currentHeroImages);

        if ($request->boolean('remove_poster')) {
            if ($conference->poster) {
                Storage::disk('public')->delete($conference->poster);
            }
            $data['poster'] = null;
        } elseif ($request->hasFile('poster')) {
            if ($conference->poster) {
                Storage::disk('public')->delete($conference->poster);
            }
            $data['poster'] = $request->file('poster')->store('conferences/posters', 'public');
        } else {
            unset($data['poster']);
        }

        // Abstract Template
        if ($request->boolean('remove_abstract_template')) {
            if ($conference->abstract_template) {
                Storage::disk('public')->delete($conference->abstract_template);
            }
            $data['abstract_template'] = null;
        } elseif ($request->hasFile('abstract_template')) {
            if ($conference->abstract_template) {
                Storage::disk('public')->delete($conference->abstract_template);
            }
            $file = $request->file('abstract_template');
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $filename = time() . '_' . $cleanName;
            $data['abstract_template'] = $file->storeAs('conferences/templates', $filename, 'public');
        } else {
            unset($data['abstract_template']);
        }

        // Full Paper Template
        if ($request->boolean('remove_paper_template')) {
            if ($conference->paper_template) {
                Storage::disk('public')->delete($conference->paper_template);
            }
            $data['paper_template'] = null;
        } elseif ($request->hasFile('paper_template')) {
            if ($conference->paper_template) {
                Storage::disk('public')->delete($conference->paper_template);
            }
            $file = $request->file('paper_template');
            $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $filename = time() . '_' . $cleanName;
            $data['paper_template'] = $file->storeAs('conferences/templates', $filename, 'public');
        } else {
            unset($data['paper_template']);
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
        if (is_array($conference->hero_images)) {
            foreach ($conference->hero_images as $imgPath) {
                Storage::disk('public')->delete($imgPath);
            }
        }
        if ($conference->poster) {
            Storage::disk('public')->delete($conference->poster);
        }
        if ($conference->abstract_template) {
            Storage::disk('public')->delete($conference->abstract_template);
        }
        if ($conference->paper_template) {
            Storage::disk('public')->delete($conference->paper_template);
        }

        $conference->delete();

        return redirect()->route('admin.conferences.index')
            ->with('success', 'Conference deleted.');
    }
}
