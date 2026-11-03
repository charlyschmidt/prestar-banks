<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChequeLiquidation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'client_name',
        'operation_date',
        'calendar_days',
        'clearing_days',
        'term_days',
        'annual_rate',
        'cheque_tax_rate',
        'gross_income_rate',
        'vat_rate',
        'nominal_amount',
        'interest_amount',
        'cheque_tax_amount',
        'gross_income_amount',
        'vat_amount',
        'total_discount',
        'net_amount',
        'status',
        'transaction_id',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',

            'nominal_amount' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'cheque_tax_amount' => 'decimal:2',
            'gross_income_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'net_amount' => 'decimal:2',

            'confirmed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UUID público
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (ChequeLiquidation $liquidation) {
            if (empty($liquidation->uuid)) {
                $liquidation->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function cheques(): HasMany
    {
        return $this->hasMany(
            Cheque::class,
            'cheque_liquidation_id'
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(
            ChequeLiquidationEvent::class,
            'cheque_liquidation_id'
        );
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'transaction_id'
        );
    }
}
