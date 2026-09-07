<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Conference extends Model
{
    use SoftDeletes;
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
        'hero_images',
        'poster',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bank_instructions',
        'abstract_template',
        'paper_template',
        'status',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($conference) {
            if (empty($conference->slug)) {
                $baseSlug = Str::slug($conference->title);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $conference->id ?? 0)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $conference->slug = $slug;
            }

            if ($conference->is_active) {
                static::where('id', '!=', $conference->id)->update(['is_active' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'hero_images' => 'array',
            'start_date'  => 'date',
            'end_date'    => 'date',
            'is_active'   => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('id');
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class)->orderBy('order');
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
