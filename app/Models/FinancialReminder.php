<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReminder extends Model
{
    use BelongsToCompany;


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

    ];


    protected function casts(): array
    {
        return [

            'amount' => 'decimal:2',

            'scheduled_at' => 'datetime',

            'dismissed_at' => 'datetime',

            'completed_at' => 'datetime',

            'notified_at' => 'datetime',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relaciones
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
    | Helpers
    |--------------------------------------------------------------------------
    */


    public function isPending(): bool
    {
        return $this->status === 'pending';
    }


    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }


    public function isDue(): bool
    {
        return $this->isPending()
            && $this->scheduled_at->isPast();
    }
}