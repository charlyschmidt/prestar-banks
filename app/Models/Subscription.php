<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan',
        'price_usd',
        'exchange_rate',
        'amount_ars',
        'provider',
        'provider_subscription_id',
        'provider_status',
        'init_point',
        'status',
        'started_at',
        'next_billing_at',
        'cancelled_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_ars' => 'decimal:2',

        'started_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Empresa
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pagos
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(
            SubscriptionPayment::class
        );
    }
}
