<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalPartner extends Model
{
    protected $fillable = [
        'conference_id',
        'journal_name',
        'university',
        'website',
        'submission_requirements',
        'loa_information',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}

