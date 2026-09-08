<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function assignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function abstractSubmission()
    {
        return $this->belongsTo(AbstractSubmission::class, 'submission_id');
    }

    public function fullPaper()
    {
        return $this->belongsTo(FullPaper::class, 'submission_id');
    }

    public function getSubmissionAttribute()
    {
        if ($this->submission_type === 'abstract') {
            return $this->abstractSubmission;
        } elseif ($this->submission_type === 'full_paper') {
            return $this->fullPaper;
        }
        return null;
    }

    public function transitionTo(string $status): void
    {
        $this->update(['status' => $status]);
    }

    public function finalRecommendation(): ?string
    {
        $assignments = $this->assignments()->with('review')->get();
        $total = $assignments->count();
        $completed = $assignments->where('status', 'completed')->count();

        if ($total === 0 || $completed < $total) {
            return null;
        }

        $requiredCount = ($this->submission_type === 'full_paper') ? 2 : 3;
        if ($this->round_number === 1 && $total !== $requiredCount) {
            return null;
        }

        $recommendations = $assignments->map(function ($assignment) {
            return match (strtoupper((string) $assignment->review?->recommendation)) {
                'ACCEPTED', 'ACCEPT', 'ORAL' => 'ORAL',
                'POSTER' => 'POSTER',
                'REVISION', 'REVISION_REQUIRED' => 'REVISION',
                'REJECT', 'REJECTED' => 'REJECT',
                default => null,
            };
        })->filter();

        if ($recommendations->count() !== $total) {
            return null;
        }

        $counts = $recommendations->countBy()->sortDesc();

        return $counts->keys()->first() ?? null;
    }
}
