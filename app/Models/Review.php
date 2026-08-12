<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'review_assignment_id',
        'score_criteria_1',
        'score_criteria_2',
        'total_score',
        'recommendation',
        'summary',
    ];

    public function assignment()
    {
        return $this->belongsTo(ReviewAssignment::class, 'review_assignment_id');
    }
}
