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
        $allowed = [
            'pending' => ['open'],
            'open' => ['locked'],
            'locked' => ['completed'],
            'completed' => [],
        ];

        if ($this->status !== $status && !in_array($status, $allowed[$this->status] ?? [], true)) {
            throw new \DomainException("Invalid review round transition: {$this->status} -> {$status}");
        }

        if ($status === 'locked') {
            $totalAssignments = $this->assignments()->count();
            $completedAssignments = $this->assignments()->where('status', 'completed')->count();

            if ($totalAssignments !== 3 || $completedAssignments !== 3) {
                throw new \DomainException("Review round cannot be locked until exactly 3 reviewers have completed their reviews.");
            }
        }

        $this->update(['status' => $status]);
    }

    public function finalRecommendation(): ?string
    {
        $assignments = $this->assignments()->with('review')->get();

        if ($assignments->count() !== 3 || $assignments->where('status', 'completed')->count() !== 3) {
            return null;
        }

        $recommendations = $assignments->map(function ($assignment) {
            return match (strtoupper((string) $assignment->review?->recommendation)) {
                'ACCEPTED', 'ORAL' => 'ORAL',
                'POSTER' => 'POSTER',
                'REVISION', 'REVISION_REQUIRED' => 'REVISION',
                'REJECT', 'REJECTED' => 'REJECT',
                default => null,
            };
        })->filter();

        if ($recommendations->count() !== 3) {
            return null;
        }

        $counts = $recommendations->countBy()->sortDesc();

        return $counts->first() >= 2 ? $counts->keys()->first() : null;
    }
}
