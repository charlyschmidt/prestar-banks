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

    /*
    |--------------------------------------------------------------------------
    | Estados del cheque
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PORTFOLIO = 'in_portfolio';
    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_DEPOSITED = 'deposited';
    public const STATUS_ACCREDITED = 'accredited';
    public const STATUS_REJECTED = 'rejected';

    /*
    |--------------------------------------------------------------------------
    | Asignación masiva
    |--------------------------------------------------------------------------
    */

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

        // Seguimiento bancario
        'deposited_at',
        'deposit_account_id',
        'deposited_by',
        'accredited_at',
        'rejected_at',
        'rejection_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de atributos
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'due_date' => 'date',

            'deposited_at' => 'date',
            'accredited_at' => 'date',
            'rejected_at' => 'date',

            'deposit_account_id' => 'integer',
            'deposited_by' => 'integer',

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
    | Entregar o depositar un cheque NO modifica
    | su liquidación de origen.
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
    | Scopes de estado
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

    public function scopeDeposited(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_DEPOSITED
        );
    }

    public function scopeAccredited(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_ACCREDITED
        );
    }

    public function scopeRejected(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cheques pendientes de depósito
    |--------------------------------------------------------------------------
    */

    public function scopePendingDeposit(
        Builder $query
    ): Builder {
        return $query
            ->where('kind', 'third_party')
            ->where(
                'status',
                self::STATUS_IN_PORTFOLIO
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cheques con vencimiento alcanzado
    |--------------------------------------------------------------------------
    |
    | Consulta por fecha nominal.
    | Los días hábiles y feriados serán contemplados
    | posteriormente por el servicio de alertas.
    |
    */

    public function scopeDueUntil(
        Builder $query,
        string $date
    ): Builder {
        return $query->whereDate(
            'due_date',
            '<=',
            $date
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas de estado
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isInPortfolio(): bool
    {
        return $this->status === self::STATUS_IN_PORTFOLIO;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isDeposited(): bool
    {
        return $this->status === self::STATUS_DEPOSITED;
    }

    public function isAccredited(): bool
    {
        return $this->status === self::STATUS_ACCREDITED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /*
    |--------------------------------------------------------------------------
    | Puede entregarse como medio de pago
    |--------------------------------------------------------------------------
    |
    | Conservamos exactamente la lógica existente.
    |
    */

    public function canBeDelivered(): bool
    {
        return $this->isInPortfolio()
            && (float) $this->nominal_amount > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Puede depositarse
    |--------------------------------------------------------------------------
    */

    public function canBeDeposited(): bool
    {
        return $this->isInPortfolio()
            && (float) $this->nominal_amount > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Puede acreditarse
    |--------------------------------------------------------------------------
    */

    public function canBeAccredited(): bool
    {
        return $this->isDeposited();
    }

    /*
    |--------------------------------------------------------------------------
    | Puede rechazarse
    |--------------------------------------------------------------------------
    */

    public function canBeRejected(): bool
    {
        return $this->isDeposited();
    }

    /*
    |--------------------------------------------------------------------------
    | Requiere recordatorios de depósito
    |--------------------------------------------------------------------------
    |
    | Únicamente cheques de terceros en cartera.
    |
    | Los cheques entregados, depositados,
    | acreditados y rechazados quedan excluidos.
    |
    */

    public function requiresDepositReminder(): bool
    {
        return $this->kind === 'third_party'
            && $this->isInPortfolio()
            && $this->due_date !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Etiqueta del estado
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_IN_PORTFOLIO => 'En cartera',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_DEPOSITED => 'Depositado',
            self::STATUS_ACCREDITED => 'Acreditado',
            self::STATUS_REJECTED => 'Rechazado',
            default => 'Desconocido',
        };
    }
}
