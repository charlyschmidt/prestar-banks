<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Cheque extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'cheque_liquidation_id',
        'kind',
        'format',
        'bank',
        'issuer',
        'number',
        'due_date',
        'nominal_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'nominal_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cheque $cheque) {
            if (empty($cheque->uuid)) {
                $cheque->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(
            ChequeLiquidation::class,
            'cheque_liquidation_id'
        );
    }
}
