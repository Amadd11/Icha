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
        'abstract_open_date',
        'abstract_deadline',
        'paper_deadline',
        'venue',
        'address',
        'city',
        'country',
        'theme',
        'email',
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
            'hero_images'        => 'array',
            'start_date'         => 'date',
            'end_date'           => 'date',
            'abstract_open_date' => 'date',
            'abstract_deadline'  => 'date',
            'paper_deadline'     => 'date',
            'is_active'          => 'boolean',
        ];
    }

    /**
     * Check if abstract submissions are currently open.
     */
    public function isAbstractSubmissionOpen(): bool
    {
        $now = now();
        if ($this->abstract_open_date && $now->lt($this->abstract_open_date->startOfDay())) {
            return false;
        }
        if ($this->abstract_deadline && $now->gt($this->abstract_deadline->endOfDay())) {
            return false;
        }
        return true;
    }

    /**
     * Check if full paper submissions are currently open.
     */
    public function isPaperSubmissionOpen(): bool
    {
        if ($this->paper_deadline && now()->gt($this->paper_deadline->endOfDay())) {
            return false;
        }
        return true;
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
