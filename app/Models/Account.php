<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

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
}
