<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'invoice_number',
        'user_id',
        'conference_id',
        'registration_fee_id',
        'is_early_bird',
        'currency',
        'amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_early_bird' => 'boolean',
            'amount'        => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function registrationFee(): BelongsTo
    {
        return $this->belongsTo(RegistrationFee::class, 'registration_fee_id');
    }

    /**
     * Alias for backward compatibility.
     */
    public function registrationType(): BelongsTo
    {
        return $this->registrationFee();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
