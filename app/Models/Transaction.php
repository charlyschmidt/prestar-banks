<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [

        'account_id',
        'financial_day_id',
        'type',
        'amount',
        'description',
        'transfer_id',
        'date'

    ];

    protected $casts = [

        'amount' => 'decimal:2',

        'date' => 'datetime',

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
}
