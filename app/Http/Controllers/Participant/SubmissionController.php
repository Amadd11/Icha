<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreAbstractRequest;
use App\Http\Requests\Participant\StorePaperRequest;
use App\Services\Participant\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->submissionService->getSubmissionData($request->user());

        return Inertia::render('Participant/Submission/Index', $data);
    }

    public function storeAbstract(StoreAbstractRequest $request): RedirectResponse
    {
        $this->submissionService->submitAbstract(
            $request->user(),
            $request->validated(),
            $request->file('file')
        );

        return redirect()->back()->with('success', 'Abstract submitted successfully!');
    }

    public function storePaper(StorePaperRequest $request): RedirectResponse
    {
        $this->submissionService->submitPaper(
            $request->user(),
            $request->validated(),
            $request->file('file')
        );

        return redirect()->back()->with('success', 'Full Paper submitted successfully!');
    }
}
