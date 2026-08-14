<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationFee extends Model
{
    protected $fillable = [
        'conference_id',
        'name',
        'mode',
        'type',
        'category',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
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

    public function isPresenter(): bool
    {
        return $this->type === 'presenter';
    }

    public function isNonPresenter(): bool
    {
        return $this->type === 'non_presenter';
    }
}
