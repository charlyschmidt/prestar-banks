<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBalance extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'account_id',
        'currency',
        'low_balance_threshold'
    ];

    protected function casts(): array
    {
        return [
            'low_balance_threshold' => 'decimal:2',
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Account
    |--------------------------------------------------------------------------
    */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Daily Balances
    |--------------------------------------------------------------------------
    */

    public function dailyBalances()
    {
        return $this->hasMany(
            AccountDailyBalance::class,
            'account_balance_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Daily Balance
    |--------------------------------------------------------------------------
    */

    public function dailyBalance()
    {
        return $this->hasOne(
            AccountDailyBalance::class,
            'account_balance_id'
        );
    }
}
