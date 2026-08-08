<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Committee extends Model
{
    protected $fillable = ['conference_id', 'name', 'role', 'institution', 'group', 'order'];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}
