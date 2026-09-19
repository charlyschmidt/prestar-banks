<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class Account extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'name',
        'type',
        'logo'
    ];



    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    public function dailyBalances()
    {
        return $this->hasMany(AccountDailyBalance::class);
    }

    public function balances()
    {
        return $this->hasMany(
            AccountBalance::class
        );
    }
}
