<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Speaker extends Model
{
    protected $fillable = [
        'conference_id',
        'name',
        'title',
        'institution',
        'country',
        'country_code',
        'bio',
        'photo',
        'email',
        'type',
        'order',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
