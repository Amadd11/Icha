<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewerAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array for the Reviewer Portal.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $round = $this->round;
        $submission = $round?->abstractSubmission ?? $round?->submission;

        $isDecided = ($round?->status === 'completed')
            || in_array($submission?->status, ['accepted', 'rejected'], true)
            || ($submission?->status === 'revision_required' && $round?->status !== 'open');

        $abstractCode = $submission?->abstract_code ?? ('ABS-' . str_pad($submission?->id ?? 1, 3, '0', STR_PAD_LEFT));
        $title = $submission?->title ?? 'Untitled';
        $abstractText = $submission?->abstract_text ?? null;
        $keywords = $submission?->keywords ?? null;

        $category = [
            'id'   => $submission?->category?->id,
            'name' => $submission?->category?->name ?? 'General Track',
        ];

        return [
            'id'              => $this->id,
            'review_round_id' => $this->review_round_id,
            'reviewer_id'     => $this->reviewer_id,
            'status'          => $this->status,
            'is_decided'      => (bool) $isDecided,
            'submission'      => [
                'id'            => $submission?->id,
                'status'        => $submission?->status,
                'abstract_code' => $abstractCode,
                'title'         => $title,
                'abstract_text' => $abstractText,
                'keywords'      => $keywords,
                'file_path'     => $submission?->file_path ?? null,
                'category'      => $category,
            ],
            'round' => [
                'id'              => $round?->id,
                'round_number'    => $round?->round_number ?? 1,
                'submission_type' => 'abstract',
                'status'          => $round?->status ?? 'pending',
            ],
            'previous_history' => $this->previous_history ?? [],
            'review' => $this->review ? [
                'id'               => $this->review->id,
                'score_criteria_1' => $this->review->score_criteria_1,
                'score_criteria_2' => $this->review->score_criteria_2,
                'recommendation'   => $this->review->recommendation,
                'summary'          => $this->review->summary,
            ] : null,
        ];
    }
}
