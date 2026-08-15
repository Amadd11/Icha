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
}
