<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDailyBalance extends Model
{

    protected $fillable = [
        'financial_day_id',
        'account_id',
        'initial_balance',
        'current_balance',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
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
