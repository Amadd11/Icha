<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conference extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'year',
        'tagline',
        'description',
        'start_date',
        'end_date',
        'venue',
        'city',
        'country',
        'theme',
        'email',
        'logo',
        'hero_image',
        'status',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($conference) {
            if (empty($conference->slug)) {
                $conference->slug = Str::slug($conference->title);
            }

            if ($conference->is_active) {
                static::where('id', '!=', $conference->id)->update(['is_active' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('order');
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class)->orderBy('order');
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class)->orderBy('order');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class)->orderBy('order');
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class)->orderBy('order');
    }

    public function registrationFees(): HasMany
    {
        return $this->hasMany(RegistrationFee::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
