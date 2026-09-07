<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewAssignment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'review_round_id',
        'reviewer_id',
        'status',
    ];

    public function round()
    {
        return $this->belongsTo(ReviewRound::class, 'review_round_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function transitionTo(string $status): void
    {
        $allowed = [
            'assigned' => ['completed'],
            'completed' => [],
        ];

        if ($this->status !== $status && !in_array($status, $allowed[$this->status] ?? [], true)) {
            throw new \DomainException("Invalid review assignment transition: {$this->status} -> {$status}");
        }

        $this->update(['status' => $status]);
    }
}
