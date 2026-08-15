<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Services\Reviewer\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $assignments = $this->dashboardService->getAssignments();

        $formattedAssignments = $assignments->map(function ($assignment) {
            $submission = $assignment->round?->abstractSubmission 
                ?? $assignment->round?->fullPaper 
                ?? $assignment->round?->submission;

            return [
                'id'              => $assignment->id,
                'review_round_id' => $assignment->review_round_id,
                'reviewer_id'     => $assignment->reviewer_id,
                'status'          => $assignment->status,
                'submission'      => [
                    'id'            => $submission?->id,
                    'abstract_code' => $submission?->abstract_code ?? $submission?->paper_code ?? ('ABS-' . str_pad($submission?->id ?? 1, 3, '0', STR_PAD_LEFT)),
                    'title'         => $submission?->title ?? 'Untitled Abstract',
                    'abstract_text' => $submission?->abstract_text ?? null,
                    'keywords'      => $submission?->keywords ?? null,
                    'file_path'     => $submission?->file_path ?? null,
                    'category'      => [
                        'id'   => $submission?->category?->id,
                        'name' => $submission?->category?->name ?? 'General Track',
                    ],
                ],
                'round' => [
                    'id'              => $assignment->round?->id,
                    'submission_type' => $assignment->round?->submission_type ?? 'abstract',
                    'status'          => $assignment->round?->status ?? 'pending',
                ],
                'review' => $assignment->review ? [
                    'id'               => $assignment->review->id,
                    'score_criteria_1' => $assignment->review->score_criteria_1,
                    'score_criteria_2' => $assignment->review->score_criteria_2,
                    'recommendation'   => $assignment->review->recommendation,
                    'summary'          => $assignment->review->summary,
                ] : null,
            ];
        });

        return Inertia::render('Reviewer/Dashboard', [
            'stats'       => $this->dashboardService->getStats(),
            'assignments' => $formattedAssignments,
        ]);
    }
}
