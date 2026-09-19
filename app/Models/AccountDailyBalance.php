<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class AccountDailyBalance extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'financial_day_id',
        'account_id',
        'account_balance_id',
        'initial_balance',
        'current_balance'
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
        return $this->belongsTo(
            Account::class
        );
    }


    public function accountBalance()
    {
        return $this->belongsTo(
            AccountBalance::class,
            'account_balance_id'
        );
    }
}