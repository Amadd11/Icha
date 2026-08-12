<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewRound extends Model
{
    protected $fillable = [
        'submission_type',
        'submission_id',
        'status',
    ];

    public function assignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function submission()
    {
        // MorphTo simulation since we use submission_type as 'abstract' or 'full_paper'
        // If 'abstract', it belongs to AbstractSubmission, etc.
        // We'll write a custom accessor/method for this if needed, or use morphTo if we used proper class names.
        // For now, this helper will fetch the model based on type.
        if ($this->submission_type === 'abstract') {
            return $this->belongsTo(AbstractSubmission::class, 'submission_id');
        } elseif ($this->submission_type === 'full_paper') {
            return $this->belongsTo(FullPaper::class, 'submission_id');
        }
    }
}
