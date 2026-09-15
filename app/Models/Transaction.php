<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'account_id',
        'financial_day_id',
        'user_id',
        'type',
        'amount',
        'description',
        'transfer_id',
        'date',
        'balance_after',
        'destination_bank',
        'executed_at',
        'executed_by',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

        'date' => 'datetime',

        'executed_at' => 'datetime',

    ];

    public function financialDay()
    {
        return $this->belongsTo(
            FinancialDay::class,
            'financial_day_id'
        );
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)
            ->withTrashed();
    }

    public function executedBy()
    {
        return $this->belongsTo(
            User::class,
            'executed_by'
        )->withTrashed();
    }
}
