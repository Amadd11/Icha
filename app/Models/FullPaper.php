<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FullPaper extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'full_papers';

    protected $fillable = [
        'paper_code',
        'user_id',
        'conference_id',
        'abstract_id',
        'title',
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

    public function abstract(): BelongsTo
    {
        return $this->belongsTo(AbstractSubmission::class, 'abstract_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class, 'submission_id')->where('submission_type', 'full_paper');
    }

    public function transitionTo(string $status): void
    {
        $allowed = [
            'pending' => ['under_review'],
            'under_review' => ['revision_required', 'accepted', 'rejected'],
            'revision_required' => ['under_review', 'accepted', 'rejected'],
            'accepted' => [],
            'rejected' => [],
        ];

        if ($this->status !== $status && !in_array($status, $allowed[$this->status] ?? [], true)) {
            throw new \DomainException("Invalid full paper transition: {$this->status} -> {$status}");
        }

        $this->update(['status' => $status]);
    }
}
