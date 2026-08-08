<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['conference_id', 'name', 'badge', 'description', 'icon', 'order'];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}
