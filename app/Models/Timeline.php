<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timeline extends Model
{
    protected $fillable = ['conference_id', 'title', 'description', 'date', 'period', 'is_completed', 'order'];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_completed' => 'boolean',
        ];
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}
