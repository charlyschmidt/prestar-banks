<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToCompany;

class Transaction extends Model
{
    use SoftDeletes;
    use BelongsToCompany;

    protected $fillable = [

        'account_id',
        'account_balance_id',
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
        'executed_by'

    ];


    protected $casts = [

        'amount' => 'decimal:2',

        'balance_after' => 'decimal:2',

        'date' => 'datetime',

        'executed_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Financial Day
    |--------------------------------------------------------------------------
    */

    public function financialDay()
    {
        return $this->belongsTo(
            FinancialDay::class,
            'financial_day_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Account
    |--------------------------------------------------------------------------
    */

    public function account()
    {
        return $this->belongsTo(
            Account::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Account Balance / Currency
    |--------------------------------------------------------------------------
    */

    public function accountBalance()
    {
        return $this->belongsTo(
            AccountBalance::class,
            'account_balance_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        )->withTrashed();
    }


    /*
    |--------------------------------------------------------------------------
    | Executed By
    |--------------------------------------------------------------------------
    */

    public function executedBy()
    {
        return $this->belongsTo(
            User::class,
            'executed_by'
        )->withTrashed();
    }
}