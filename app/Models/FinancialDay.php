<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;

class FinancialDay extends Model
{
    use BelongsToCompany;

    protected $fillable = [

        'date',
        'status',
        'opened_by',
        'opened_at',
        'closed_at'
    ];



    protected $casts = [

        'date' => 'date',

        'closed_at' => 'datetime',

        'opened_at' => 'datetime',

    ];




    public function opener()
    {
        return $this->belongsTo(
            User::class,
            'opened_by'
        );
    }




    public function accountBalances()
    {
        return $this->hasMany(
            AccountDailyBalance::class
        );
    }




    public function transactions()
    {
        return $this->hasMany(
            Transaction::class
        );
    }




    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
