<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeLiquidationDelivery extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tipos de entrega
    |--------------------------------------------------------------------------
    */

    public const TYPE_CASH = 'cash';

    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_THIRD_PARTY_CHEQUE = 'third_party_cheque';

    protected $table = 'cheque_liquidation_deliveries';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'cheque_liquidation_id',
        'type',
        'currency',
        'amount',
        'exchange_rate',
        'amount_ars',
        'account_id',
        'delivered_cheque_id',
        'transaction_id',
        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversiones
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'amount_ars' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Liquidación que estamos pagando
    |--------------------------------------------------------------------------
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
    | Cuenta desde la que entregamos dinero
    |--------------------------------------------------------------------------
    */

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'account_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Movimiento financiero generado
    |--------------------------------------------------------------------------
    |
    | Para efectivo y transferencias se genera
    | un egreso desde TransactionService.
    |
    | Para cheques de terceros, transaction_id
    | permanece NULL porque no sale dinero
    | de una cuenta financiera.
    |
    */

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'transaction_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cheque de terceros entregado
    |--------------------------------------------------------------------------
    |
    | Conserva la liquidación original del cheque.
    | Permite consultar en qué operación fue entregado.
    |
    */

    public function deliveredCheque(): BelongsTo
    {
        return $this->belongsTo(
            Cheque::class,
            'delivered_cheque_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas de tipo
    |--------------------------------------------------------------------------
    */

    public function isCash(): bool
    {
        return $this->type === self::TYPE_CASH;
    }

    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    public function isThirdPartyCheque(): bool
    {
        return $this->type === self::TYPE_THIRD_PARTY_CHEQUE;
    }

    public function isMonetary(): bool
    {
        return $this->isCash() || $this->isTransfer();
    }

    public function isForeignCurrency(): bool
    {
        return $this->currency !== 'ARS';
    }

    /*
    |--------------------------------------------------------------------------
    | Identificador único del movimiento
    |--------------------------------------------------------------------------
    |
    | Cada entrega debe generar un external_id
    | diferente para evitar que TransactionService
    | interprete dos pagos como duplicados.
    |
    */

    public function transactionExternalId(): string
    {
        return $this->cheque_liquidation_id
            . ':delivery:'
            . $this->id;
    }
}
