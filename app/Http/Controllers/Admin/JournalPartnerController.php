<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJournalPartnerRequest;
use App\Http\Requests\Admin\UpdateJournalPartnerRequest;
use App\Models\Conference;
use App\Models\JournalPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalPartnerController extends Controller
{
    public function index(Request $request): Response
    {
        $confId = $request->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $conferences = Conference::select(['id', 'title'])->get();
        $journalPartners = JournalPartner::with('conference:id,title')
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->latest()
            ->get();

        return Inertia::render('Admin/JournalPartners/Index', [
            'journalPartners'      => $journalPartners,
            'conferences'          => $conferences,
            'selectedConferenceId' => (int) $confId,
        ]);
    }

    public function store(StoreJournalPartnerRequest $request): RedirectResponse
    {
        JournalPartner::create($request->validated());

        return redirect()->route('admin.journal-partners.index')
            ->with('success', 'Journal partner added successfully.');
    }

    public function update(UpdateJournalPartnerRequest $request, JournalPartner $journalPartner): RedirectResponse
    {
        $journalPartner->update($request->validated());

        return redirect()->route('admin.journal-partners.index')
            ->with('success', 'Journal partner updated successfully.');
    }

    public function destroy(JournalPartner $journalPartner): RedirectResponse
    {
        $journalPartner->delete();

        return redirect()->route('admin.journal-partners.index')
            ->with('success', 'Journal partner deleted.');
    }
}

