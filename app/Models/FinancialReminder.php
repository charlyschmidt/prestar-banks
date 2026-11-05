<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReminder extends Model
{
    use BelongsToCompany;

    /*
    |--------------------------------------------------------------------------
    | Tipos de recordatorios
    |--------------------------------------------------------------------------
    */

    public const TYPE_CHEQUE_DUE = 'cheque_due';

    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISMISSED = 'dismissed';

    /*
    |--------------------------------------------------------------------------
    | Asignación masiva
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'user_id',
        'account_id',
        'account_balance_id',

        'title',
        'amount',
        'scheduled_at',
        'status',
        'dismissed_at',
        'completed_at',
        'notified_at',
        'recurrence_type',

        // Vinculación con cheques
        'cheque_id',
        'reminder_type',
        'reminder_key',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de atributos
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',

            'scheduled_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'completed_at' => 'datetime',
            'notified_at' => 'datetime',

            'cheque_id' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones existentes
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Account::class
        );
    }

    public function accountBalance(): BelongsTo
    {
        return $this->belongsTo(
            AccountBalance::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cheque relacionado
    |--------------------------------------------------------------------------
    |
    | Únicamente aplica a recordatorios automáticos
    | originados desde el módulo de Cheques.
    |
    | Los recordatorios manuales mantienen cheque_id NULL.
    |
    */

    public function cheque(): BelongsTo
    {
        return $this->belongsTo(
            Cheque::class,
            'cheque_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeChequeDue(
        Builder $query
    ): Builder {
        return $query->where(
            'reminder_type',
            self::TYPE_CHEQUE_DUE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers existentes
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDue(): bool
    {
        return $this->isPending()
            && $this->scheduled_at !== null
            && $this->scheduled_at->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de cheques
    |--------------------------------------------------------------------------
    */

    public function isChequeReminder(): bool
    {
        return $this->reminder_type === self::TYPE_CHEQUE_DUE;
    }

    public function belongsToCheque(int $chequeId): bool
    {
        return $this->isChequeReminder()
            && (int) $this->cheque_id === $chequeId;
    }

    /*
    |--------------------------------------------------------------------------
    | Puede notificarse
    |--------------------------------------------------------------------------
    |
    | Los recordatorios completados o descartados
    | no deben generar nuevas notificaciones.
    |
    | Tampoco se vuelve a notificar un registro
    | que ya tenga notified_at.
    |
    */

    public function canNotify(): bool
    {
        return $this->isPending()
            && $this->notified_at === null
            && $this->scheduled_at !== null
            && $this->scheduled_at->isPast();
    }
}
