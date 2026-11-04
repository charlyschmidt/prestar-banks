<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cheque extends Model
{
    use BelongsToCompany;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PORTFOLIO = 'in_portfolio';
    public const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'company_id',
        'cheque_liquidation_id',

        // Identificación del cheque
        'kind',
        'format',
        'bank',
        'issuer',
        'number',
        'due_date',
        'nominal_amount',
        'status',

        // Plazos individuales
        'calendar_days',
        'clearing_days',
        'term_days',

        // Porcentajes individuales
        'annual_rate',
        'cheque_tax_rate',
        'gross_income_rate',
        'vat_rate',

        // Resultado financiero individual
        'interest_amount',
        'cheque_tax_amount',
        'gross_income_amount',
        'vat_amount',
        'total_discount',
        'net_amount',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',

            'calendar_days' => 'integer',
            'clearing_days' => 'integer',
            'term_days' => 'integer',

            'annual_rate' => 'decimal:4',
            'cheque_tax_rate' => 'decimal:4',
            'gross_income_rate' => 'decimal:4',
            'vat_rate' => 'decimal:4',

            'nominal_amount' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'cheque_tax_amount' => 'decimal:2',
            'gross_income_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UUID público
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Liquidación de origen
    |--------------------------------------------------------------------------
    |
    | Se conserva la relación original.
    | Entregar un cheque NO cambia su liquidación de origen.
    |
    */

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(
            ChequeLiquidation::class,
            'cheque_liquidation_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Entregas en las que fue utilizado
    |--------------------------------------------------------------------------
    */

    public function deliveries(): HasMany
    {
        return $this->hasMany(
            ChequeLiquidationDelivery::class,
            'delivered_cheque_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cheques disponibles en cartera
    |--------------------------------------------------------------------------
    */

    public function scopeInPortfolio(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_IN_PORTFOLIO
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas de estado
    |--------------------------------------------------------------------------
    */

    public function isInPortfolio(): bool
    {
        return $this->status === self::STATUS_IN_PORTFOLIO;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /*
    |--------------------------------------------------------------------------
    | Puede entregarse como medio de pago
    |--------------------------------------------------------------------------
    */

    public function canBeDelivered(): bool
    {
        return $this->isInPortfolio()
            && (float) $this->nominal_amount > 0;
    }
}
