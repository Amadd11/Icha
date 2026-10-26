<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewRound extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'submission_type',
        'submission_id',
        'round_number',
        'status',
    ];

    protected $appends = ['submission'];

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function abstractSubmission(): BelongsTo
    {
        return $this->belongsTo(AbstractSubmission::class, 'submission_id');
    }

    public function getSubmissionAttribute(): ?AbstractSubmission
    {
        return $this->abstractSubmission;
    }

    public function transitionTo(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Get the latest completed assignments for each unique reviewer up to this round.
     * When round > 1, aggregates latest reviews of all unique reviewers across rounds.
     *
     * @return array<int, ReviewAssignment>
     */
    public function getLatestEvaluationAssignments(): array
    {
        $assignments = $this->assignments()->with('review')->get();
        $completedAssignments = $assignments->filter(function ($a) {
            return $a->status === 'completed' || $a->review !== null;
        });

        if ($this->round_number <= 1) {
            return $completedAssignments->all();
        }

        $allRounds = self::where('submission_type', $this->submission_type)
            ->where('submission_id', $this->submission_id)
            ->where('round_number', '<=', $this->round_number)
            ->orderBy('round_number')
            ->with('assignments.review')
            ->get();

        $evalAssignments = [];
        foreach ($allRounds as $r) {
            foreach ($r->assignments as $a) {
                if ($a->status === 'completed' || $a->review !== null) {
                    $evalAssignments[$a->reviewer_id] = $a;
                }
            }
        }

        return $evalAssignments;
    }

    public function recommendedPresentationType(): string
    {
        $evalAssignments = $this->getLatestEvaluationAssignments();

        $oralVotes = 0;
        $posterVotes = 0;

        foreach ($evalAssignments as $a) {
            $rec = strtoupper((string) ($a->review?->recommendation ?? ''));
            if (in_array($rec, ['ORAL', 'ACCEPT', 'ACCEPTED'], true)) {
                $oralVotes++;
            } elseif ($rec === 'POSTER') {
                $posterVotes++;
            }
        }

        return ($posterVotes > $oralVotes) ? 'poster' : 'oral';
    }

    /**
     * Determine automatic decision outcome based on completed reviews:
     * Returns: ['status' => 'accepted'|'revision_required'|'rejected', 'presentation_type' => 'oral'|'poster'|null, 'notes' => string|null]
     */
    public function autoDecisionOutcome(): ?array
    {
        $assignments = $this->assignments()->with('review')->get();
        $completedAssignments = $assignments->filter(function ($a) {
            return $a->status === 'completed' || $a->review !== null;
        });

        $count = $completedAssignments->count();
        $target = min(3, max(1, $assignments->count()));
        $isReady = ($this->round_number === 1)
            ? ($assignments->count() > 0 && $count >= $target)
            : ($assignments->count() > 0 && $count >= $assignments->count());

        if (!$isReady) {
            return null;
        }

        // Aggregate the latest reviews of all unique reviewers across rounds.
        // Reviewers who accepted in round 1 will still have their recommendation counted!
        $evalAssignments = $this->getLatestEvaluationAssignments();

        $oralVotes = 0;
        $posterVotes = 0;
        $revisionVotes = 0;
        $rejectVotes = 0;
        $comments = [];

        $reviewerIndex = 1;
        foreach ($evalAssignments as $a) {
            $rec = strtoupper((string) ($a->review?->recommendation ?? ''));
            $reviewerAlias = 'Reviewer #' . $reviewerIndex;
            $reviewerIndex++;
            $summary = trim((string) ($a->review?->summary ?? ''));
            $score = $a->review?->total_score;

            if (in_array($rec, ['REVISION', 'REVISION_REQUIRED'], true)) {
                $revisionVotes++;
                $label = 'Revisi';
            } elseif (in_array($rec, ['REJECT', 'REJECTED'], true)) {
                $rejectVotes++;
                $label = 'Ditolak';
            } elseif ($rec === 'POSTER') {
                $posterVotes++;
                $label = 'Poster';
            } else {
                $oralVotes++;
                $label = 'Oral';
            }

            if (!empty($summary)) {
                $scoreStr = $score !== null ? " (Skor: {$score}/10)" : '';
                $comments[] = "• {$reviewerAlias} [{$label}]{$scoreStr}: {$summary}";
            }
        }

        if ($revisionVotes > 0) {
            $status = 'revision_required';
            $presentationType = null;
        } elseif ($rejectVotes >= 2 || ($rejectVotes > ($oralVotes + $posterVotes))) {
            $status = 'rejected';
            $presentationType = null;
        } else {
            $status = 'accepted';
            $presentationType = ($posterVotes > $oralVotes) ? 'poster' : 'oral';
        }

        return [
            'status'            => $status,
            'presentation_type' => $presentationType,
            'notes'             => !empty($comments) ? implode("\n", $comments) : null,
        ];
    }

    public function applyAutoDecision(): bool
    {
        $outcome = $this->autoDecisionOutcome();
        if (!$outcome) {
            return false;
        }

        $submission = $this->abstractSubmission;
        if (!$submission) {
            return false;
        }

        // If submission is already finalized (e.g. by Admin override), preserve Admin's decision
        if (in_array($submission->status, ['accepted', 'revision_required', 'rejected'], true)) {
            if ($this->status !== 'completed') {
                $this->update(['status' => 'completed']);
            }
            return false;
        }

        $updateData = [
            'status'      => $outcome['status'],
            'reviewed_at' => now(),
        ];

        if ($outcome['status'] === 'accepted') {
            $updateData['presentation_type'] = $outcome['presentation_type'];
        }

        $updateData['review_notes'] = !empty($outcome['notes']) ? $outcome['notes'] : null;

        $submission->update($updateData);
        $this->update(['status' => 'completed']);

        return true;
    }

    protected static function booted(): void
    {
        static::deleting(function (ReviewRound $round) {
            $round->assignments()->each(function ($assignment) {
                $assignment->delete();
            });
        });
    }
}
