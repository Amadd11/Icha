<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'registration_id',
        'amount',
        'currency',
        'payment_method',
        'proof_file',
        'status',
        'rejection_reason',
        'paid_at',
        'verified_at',
        'verified_by',
    ];

    protected $appends = [
        'proof_url',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'paid_at'     => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Payment $payment) {
            if ($payment->status === 'verified') {
                throw new \DomainException('Verified payments cannot be deleted.');
            }
        });

        static::updating(function (Payment $payment) {
            if ($payment->getOriginal('status') === 'verified' && $payment->isDirty('status') && $payment->status !== 'verified') {
                throw new \DomainException('Verified payment status is frozen and cannot be modified.');
            }
        });
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_file ? route('payments.proof', $this->id) : null;
    }

    public function transitionTo(string $status): void
    {
        $allowed = [
            'pending' => ['verified', 'rejected'],
            'rejected' => ['pending'],
            'verified' => [],
        ];

        if ($this->status !== $status && !in_array($status, $allowed[$this->status] ?? [], true)) {
            throw new \DomainException("Invalid payment transition: {$this->status} -> {$status}");
        }

        $this->update(['status' => $status]);
    }
}
