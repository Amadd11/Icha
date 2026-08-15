<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbstractSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'abstracts';

    protected $fillable = [
        'abstract_code',
        'user_id',
        'conference_id',
        'category_id',
        'title',
        'abstract_text',
        'keywords',
        'presentation_type',
        'file_path',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function fullPaper(): HasOne
    {
        return $this->hasOne(FullPaper::class, 'abstract_id');
    }

    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class, 'submission_id')->where('submission_type', 'abstract');
    }

    protected static function booted(): void
    {
        static::deleting(function (AbstractSubmission $abstract) {
            $abstract->reviewRounds()->each(function ($round) {
                // Manually delete assignments to ensure review constraints handle it if any, 
                // but since ReviewRound deletes its assignments, we can just delete the round.
                // Assuming ReviewRound has its own deleting event or foreign keys for assignments.
                $round->assignments()->delete();
                $round->delete();
            });
            
            if ($abstract->fullPaper) {
                $abstract->fullPaper->delete();
            }
        });
    }
}
