<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'company_id',
        'price_usd',
        'exchange_rate',
        'amount_ars',
        'provider_payment_id',
        'provider_status',
        'provider_status_detail',
        'status',
        'period_start',
        'period_end',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_ars' => 'decimal:2',

        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Suscripción
    |--------------------------------------------------------------------------
    */

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            Subscription::class
        );
    }

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
}