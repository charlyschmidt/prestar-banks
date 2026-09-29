<?php

namespace App\Services;

use App\Models\Subscription;
use RuntimeException;

class SubscriptionRenewalService
{
    public function __construct(
        private MercadoPagoSubscriptionService $mercadoPagoService,
        private ExchangeRateService $exchangeRateService,
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Actualizar importe para próxima renovación
    |--------------------------------------------------------------------------
    */

    public function updateRenewalAmount(
        Subscription $subscription
    ): void {

        if ($subscription->status !== 'active') {
            throw new RuntimeException(
                'La suscripción no está activa.'
            );
        }

        if (!$subscription->provider_subscription_id) {
            throw new RuntimeException(
                'La suscripción no posee ID de Mercado Pago.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cotización actual
        |--------------------------------------------------------------------------
        */

        $exchangeRate = $this->exchangeRateService
            ->getUsdSellRate();


        /*
        |--------------------------------------------------------------------------
        | Calcular importe ARS
        |--------------------------------------------------------------------------
        */

        $priceUsd = (float) $subscription->price_usd;

        $amountArs = round(
            $priceUsd * $exchangeRate,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Actualizar Mercado Pago primero
        |--------------------------------------------------------------------------
        |
        | Si Mercado Pago falla, no modificamos nuestra base.
        |
        */

        $this->mercadoPagoService
            ->updateSubscriptionAmount(
                $subscription->provider_subscription_id,
                $amountArs
            );


        /*
        |--------------------------------------------------------------------------
        | Guardar nueva cotización local
        |--------------------------------------------------------------------------
        */

        $subscription->update([
            'exchange_rate' => $exchangeRate,
            'amount_ars' => $amountArs,
            'renewal_prepared_at' => now(),
        ]);
    }
}
