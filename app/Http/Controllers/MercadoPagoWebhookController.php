<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\MercadoPagoSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        private MercadoPagoSubscriptionService $mercadoPagoService,
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Recibir notificación Mercado Pago
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validar firma
        |--------------------------------------------------------------------------
        */

        if (!$this->isValidSignature($request)) {

            return response()->json(
                [
                    'received' => false,
                    'message' => 'Invalid signature',
                ],
                401
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tipo de evento
        |--------------------------------------------------------------------------
        */

        $type = $request->input('type')
            ?? $request->query('type');


        /*
        |--------------------------------------------------------------------------
        | Por ahora procesamos cambios de suscripción
        |--------------------------------------------------------------------------
        */

        if ($type !== 'subscription_preapproval') {

            return response()->json([
                'received' => true,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ID de la suscripción Mercado Pago
        |--------------------------------------------------------------------------
        */

        $providerSubscriptionId =
            $request->query('data.id')
            ?? $request->input('data.id');


        if (!$providerSubscriptionId) {

            return response()->json([
                'received' => true,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Buscar suscripción local
        |--------------------------------------------------------------------------
        */

        $subscription = Subscription::query()
            ->where(
                'provider_subscription_id',
                $providerSubscriptionId
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Puede llegar el webhook antes de terminar nuestro POST /preapproval
        |--------------------------------------------------------------------------
        */

        if (!$subscription) {

            return response()->json([
                'received' => true,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Consultar estado real en Mercado Pago
        |--------------------------------------------------------------------------
        */

        try {

            $providerData = $this->mercadoPagoService
                ->getSubscription(
                    $subscription->provider_subscription_id
                );

        } catch (\Throwable $e) {

            report($e);

            return response()->json(
                [
                    'received' => false,
                ],
                500
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Estado Mercado Pago
        |--------------------------------------------------------------------------
        */

        $providerStatus =
            $providerData['status'] ?? null;


        /*
        |--------------------------------------------------------------------------
        | Mapear estado local
        |--------------------------------------------------------------------------
        */

        $localStatus = match ($providerStatus) {

            'authorized' => 'active',

            'pending' => 'pending',

            'paused' => 'paused',

            'cancelled' => 'cancelled',

            default => $subscription->status,
        };


        /*
        |--------------------------------------------------------------------------
        | Datos a actualizar
        |--------------------------------------------------------------------------
        */

        $updateData = [

            'provider_status' =>
                $providerStatus,

            'status' =>
                $localStatus,

            'next_billing_at' =>
                $providerData['next_payment_date'] ?? null,

        ];


        /*
        |--------------------------------------------------------------------------
        | Primera autorización
        |--------------------------------------------------------------------------
        */

        if (
            $providerStatus === 'authorized'
            && !$subscription->started_at
        ) {

            $updateData['started_at'] = now();
        }


        /*
        |--------------------------------------------------------------------------
        | Cancelación
        |--------------------------------------------------------------------------
        */

        if (
            $providerStatus === 'cancelled'
            && !$subscription->cancelled_at
        ) {

            $updateData['cancelled_at'] = now();
        }


        /*
        |--------------------------------------------------------------------------
        | Guardar
        |--------------------------------------------------------------------------
        */

        $subscription->update(
            $updateData
        );


        return response()->json([
            'received' => true,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validar firma Mercado Pago
    |--------------------------------------------------------------------------
    */

    private function isValidSignature(
        Request $request
    ): bool {

        $secret = config(
            'services.mercadopago.webhook_secret'
        );

        if (!$secret) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Headers
        |--------------------------------------------------------------------------
        */

        $xSignature = $request->header(
            'x-signature'
        );

        $xRequestId = $request->header(
            'x-request-id'
        );


        if (
            !$xSignature
            || !$xRequestId
        ) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Extraer ts y v1
        |--------------------------------------------------------------------------
        */

        $timestamp = null;
        $signature = null;

        foreach (
            explode(',', $xSignature)
            as $part
        ) {

            $pieces = explode(
                '=',
                trim($part),
                2
            );

            if (count($pieces) !== 2) {
                continue;
            }

            [$key, $value] = $pieces;

            $key = trim($key);
            $value = trim($value);


            if ($key === 'ts') {
                $timestamp = $value;
            }


            if ($key === 'v1') {
                $signature = $value;
            }
        }


        if (
            !$timestamp
            || !$signature
        ) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | data.id
        |--------------------------------------------------------------------------
        |
        | Mercado Pago utiliza el data.id enviado en la URL
        | para construir la firma.
        |
        */

        $dataId = $request->query(
            'data.id'
        );


        if (!$dataId) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalizar ID
        |--------------------------------------------------------------------------
        */

        $dataId = strtolower(
            (string) $dataId
        );


        /*
        |--------------------------------------------------------------------------
        | Manifest Mercado Pago
        |--------------------------------------------------------------------------
        */

        $manifest =
            'id:' . $dataId
            . ';request-id:' . $xRequestId
            . ';ts:' . $timestamp
            . ';';


        /*
        |--------------------------------------------------------------------------
        | HMAC SHA256
        |--------------------------------------------------------------------------
        */

        $expectedSignature = hash_hmac(
            'sha256',
            $manifest,
            $secret
        );


        /*
        |--------------------------------------------------------------------------
        | Comparación segura
        |--------------------------------------------------------------------------
        */

        return hash_equals(
            $expectedSignature,
            $signature
        );
    }
}