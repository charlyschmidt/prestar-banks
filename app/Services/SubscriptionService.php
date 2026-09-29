<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use RuntimeException;

class SubscriptionService
{
    public function __construct(
        private SubscriptionPlanService $planService,
        private ExchangeRateService $exchangeRateService,
        private MercadoPagoSubscriptionService $mercadoPagoService,
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Crear suscripción
    |--------------------------------------------------------------------------
    */

    public function create(
        Company $company,
        string $plan,
        string $payerEmail
    ): Subscription {

        if (!$this->planService->exists($plan)) {
            throw new RuntimeException(
                'El plan seleccionado no es válido.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Precio del plan
        |--------------------------------------------------------------------------
        */

        $priceUsd = $this->planService->price($plan);


        /*
        |--------------------------------------------------------------------------
        | Cotización USD → ARS
        |--------------------------------------------------------------------------
        */

        $exchangeRate = $this->exchangeRateService
            ->getUsdSellRate();

        $amountArs = round(
            $priceUsd * $exchangeRate,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Crear primero la suscripción local
        |--------------------------------------------------------------------------
        */

        $subscription = Subscription::create([

            'company_id' => $company->id,

            'plan' => $plan,

            'price_usd' => $priceUsd,

            'exchange_rate' => $exchangeRate,

            'amount_ars' => $amountArs,

            'provider' => 'mercadopago',

            'status' => 'pending',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Referencia única AERIA
        |--------------------------------------------------------------------------
        */

        $externalReference =
            'aeria-subscription-' . $subscription->id;


        /*
        |--------------------------------------------------------------------------
        | Inicio de facturación
        |--------------------------------------------------------------------------
        |
        | Si todavía quedan días del período de prueba,
        | Mercado Pago comenzará a facturar cuando finalice.
        |
        | Si el trial ya terminó, comienza inmediatamente.
        |
        */

        $billingStartDate = $company->trial_ends_at
            && $company->trial_ends_at->isFuture()
            ? $company->trial_ends_at
            : now();


        /*
        |--------------------------------------------------------------------------
        | Preparar payload Mercado Pago
        |--------------------------------------------------------------------------
        */

        $data = $this->mercadoPagoService
            ->buildSubscriptionData(
                $plan,
                $payerEmail,
                $externalReference,
                $amountArs,
                $billingStartDate
            );


        /*
        |--------------------------------------------------------------------------
        | Crear suscripción en Mercado Pago
        |--------------------------------------------------------------------------
        */

        try {

            $providerData = $this->mercadoPagoService
                ->createSubscription($data);
        } catch (\Throwable $e) {

            $subscription->update([
                'status' => 'failed',
            ]);

            throw $e;
        }


        /*
        |--------------------------------------------------------------------------
        | Validar respuesta
        |--------------------------------------------------------------------------
        */

        if (
            empty($providerData['id'])
            || empty($providerData['init_point'])
        ) {

            $subscription->update([
                'status' => 'failed',
            ]);

            throw new RuntimeException(
                'Mercado Pago no devolvió los datos necesarios para iniciar la suscripción.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Guardar datos de Mercado Pago
        |--------------------------------------------------------------------------
        */

        $subscription->update([

            'provider_subscription_id' =>
            $providerData['id'],

            'provider_status' =>
            $providerData['status'] ?? null,

            'init_point' =>
            $providerData['init_point'],

            'next_billing_at' =>
            $providerData['next_payment_date'] ?? null,

        ]);


        return $subscription->fresh();
    }

    /*
|--------------------------------------------------------------------------
| Crear suscripción de prueba Mercado Pago
|--------------------------------------------------------------------------
|
| Uso exclusivo para verificar el circuito real de cobro.
| Importe fijo: ARS 50.
| Inicio: inmediato.
|
*/

    public function createTestSubscription(
        Company $company,
        string $payerEmail
    ): Subscription {

        /*
    |--------------------------------------------------------------------------
    | Datos de prueba
    |--------------------------------------------------------------------------
    */

        $plan = SubscriptionPlanService::MONTHLY;

        $priceUsd = 0;

        $exchangeRate = 1;

        $amountArs = 150;


        /*
    |--------------------------------------------------------------------------
    | Crear suscripción local
    |--------------------------------------------------------------------------
    */

        $subscription = Subscription::create([

            'company_id' => $company->id,

            'plan' => $plan,

            'price_usd' => $priceUsd,

            'exchange_rate' => $exchangeRate,

            'amount_ars' => $amountArs,

            'provider' => 'mercadopago',

            'status' => 'pending',

        ]);


        /*
    |--------------------------------------------------------------------------
    | Referencia única
    |--------------------------------------------------------------------------
    */

        $externalReference =
            'aeria-test-subscription-' . $subscription->id;


        /*
    |--------------------------------------------------------------------------
    | Inicio inmediato
    |--------------------------------------------------------------------------
    */

        $billingStartDate = now();


        /*
    |--------------------------------------------------------------------------
    | Payload Mercado Pago
    |--------------------------------------------------------------------------
    */

        $data = $this->mercadoPagoService
            ->buildSubscriptionData(
                $plan,
                $payerEmail,
                $externalReference,
                $amountArs,
                $billingStartDate
            );


        /*
    |--------------------------------------------------------------------------
    | Identificar claramente la prueba en Mercado Pago
    |--------------------------------------------------------------------------
    */

        $data['reason'] =
            'AERIA Finance - Prueba de suscripción';


        /*
    |--------------------------------------------------------------------------
    | Crear preapproval
    |--------------------------------------------------------------------------
    */

        try {

            $providerData = $this->mercadoPagoService
                ->createSubscription($data);
        } catch (\Throwable $e) {

            $subscription->update([
                'status' => 'failed',
            ]);

            throw $e;
        }


        /*
    |--------------------------------------------------------------------------
    | Validar respuesta
    |--------------------------------------------------------------------------
    */

        if (
            empty($providerData['id'])
            || empty($providerData['init_point'])
        ) {

            $subscription->update([
                'status' => 'failed',
            ]);

            throw new RuntimeException(
                'Mercado Pago no devolvió los datos necesarios para la suscripción de prueba.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Guardar respuesta Mercado Pago
    |--------------------------------------------------------------------------
    */

        $subscription->update([

            'provider_subscription_id' =>
            $providerData['id'],

            'provider_status' =>
            $providerData['status'] ?? null,

            'init_point' =>
            $providerData['init_point'],

            'next_billing_at' =>
            $providerData['next_payment_date'] ?? null,

        ]);


        return $subscription->fresh();
    }
}
