<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MercadoPagoSubscriptionService
{
    private const BASE_URL = 'https://api.mercadopago.com';

    /*
    |--------------------------------------------------------------------------
    | Cliente HTTP
    |--------------------------------------------------------------------------
    */

    private function client(): PendingRequest
    {
        $accessToken = config(
            'services.mercadopago.access_token'
        );

        if (!$accessToken) {
            throw new RuntimeException(
                'Mercado Pago no está configurado.'
            );
        }

        return Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener una suscripción
    |--------------------------------------------------------------------------
    */

    public function getSubscription(
        string $providerSubscriptionId
    ): array {
        $response = $this->client()
            ->get(
                self::BASE_URL
                    . '/preapproval/'
                    . $providerSubscriptionId
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'No se pudo consultar la suscripción en Mercado Pago.'
            );
        }

        return $response->json();
    }

    /*
|--------------------------------------------------------------------------
| Probar conexión
|--------------------------------------------------------------------------
*/

    public function testConnection(): array
    {
        $response = $this->client()
            ->get(
                self::BASE_URL . '/preapproval/search',
                [
                    'limit' => 1,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'No se pudo conectar con Mercado Pago. HTTP '
                    . $response->status()
            );
        }

        return $response->json();
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar suscripción
    |--------------------------------------------------------------------------
    */
    public function buildSubscriptionData(
        string $plan,
        string $payerEmail,
        string $externalReference,
        float $amountArs,
        \Carbon\CarbonInterface $startDate
    ): array {

        $planService = app(SubscriptionPlanService::class);

        $planData = $planService->get($plan);

        $frequency = $plan === SubscriptionPlanService::ANNUAL
            ? 12
            : 1;

        return [
            'reason' => 'AERIA Finance - Plan ' . $planData['name'],

            'external_reference' => $externalReference,

            'payer_email' => $payerEmail,

            'auto_recurring' => [
                'frequency' => $frequency,
                'frequency_type' => 'months',

                'start_date' => $startDate
                    ->copy()
                    ->utc()
                    ->format('Y-m-d\TH:i:s.000\Z'),

                'transaction_amount' => round($amountArs, 2),

                'currency_id' => 'ARS',
            ],

            'back_url' => config('app.url') . '/subscription/return',

            'status' => 'pending',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Crear suscripción
    |--------------------------------------------------------------------------
    */

    public function createSubscription(array $data): array
    {
        $response = $this->client()
            ->post(
                self::BASE_URL . '/preapproval',
                $data
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'No se pudo crear la suscripción en Mercado Pago. HTTP '
                    . $response->status()
                    . ' - '
                    . $response->body()
            );
        }

        return $response->json();
    }

    public function cancelSubscription(
        string $providerSubscriptionId
    ): array {
        $response = $this->client()
            ->put(
                self::BASE_URL
                    . '/preapproval/'
                    . $providerSubscriptionId,
                [
                    'status' => 'canceled',
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'No se pudo cancelar la suscripción en Mercado Pago. HTTP '
                    . $response->status()
                    . ' - '
                    . $response->body()
            );
        }

        return $response->json();
    }

    /*
|--------------------------------------------------------------------------
| Obtener pago autorizado de una suscripción
|--------------------------------------------------------------------------
*/

public function getAuthorizedPayment(
    string $authorizedPaymentId
): array {
    $response = $this->client()
        ->get(
            self::BASE_URL
                . '/authorized_payments/'
                . $authorizedPaymentId
        );

    if (!$response->successful()) {
        throw new RuntimeException(
            'No se pudo consultar el pago autorizado en Mercado Pago. HTTP '
                . $response->status()
                . ' - '
                . $response->body()
        );
    }

    return $response->json();
}
}
