<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationType extends Model
{
    protected $fillable = [
        'conference_id',
        'name',
        'category',
        'role_type',
        'is_international',
        'early_bird_price_idr',
        'regular_price_idr',
        'early_bird_price_usd',
        'regular_price_usd',
        'early_bird_deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_international' => 'boolean',
            'is_active'        => 'boolean',
            'early_bird_deadline' => 'date',
        ];
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
