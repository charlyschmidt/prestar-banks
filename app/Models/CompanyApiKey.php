<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyApiKey extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'key_hash',
        'key_prefix',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}