<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewRound extends Model
{
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
}
