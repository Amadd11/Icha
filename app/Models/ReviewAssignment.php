<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewAssignment extends Model
{
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
}
