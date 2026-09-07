<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = ['conference_id', 'name', 'badge', 'description'];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function abstracts(): HasMany
    {
        return $this->hasMany(AbstractSubmission::class);
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
