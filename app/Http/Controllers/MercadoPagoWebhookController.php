<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\MercadoPagoSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\SubscriptionPayment;

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

        /*
|--------------------------------------------------------------------------
| Pago recurrente de suscripción
|--------------------------------------------------------------------------
*/

        if ($type === 'subscription_authorized_payment') {
            return $this->handleAuthorizedPayment($request);
        }


        /*
|--------------------------------------------------------------------------
| Otros eventos que todavía no procesamos
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

        $localStatus = $this->mapSubscriptionStatus(
            $providerStatus,
            $subscription->status
        );


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
            in_array(
                $providerStatus,
                ['cancelled', 'canceled'],
                true
            )
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

    /*
|--------------------------------------------------------------------------
| Procesar pago recurrente
|--------------------------------------------------------------------------
*/

    private function handleAuthorizedPayment(
        Request $request
    ): JsonResponse {

        /*
    |--------------------------------------------------------------------------
    | ID de factura Mercado Pago
    |--------------------------------------------------------------------------
    */

        $authorizedPaymentId =
            $request->query('data.id')
            ?? $request->input('data.id');


        if (!$authorizedPaymentId) {
            return response()->json([
                'received' => true,
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Consultar factura real en Mercado Pago
    |--------------------------------------------------------------------------
    */

        try {

            $providerData = $this->mercadoPagoService
                ->getAuthorizedPayment(
                    (string) $authorizedPaymentId
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
    | Identificar suscripción
    |--------------------------------------------------------------------------
    */

        $providerSubscriptionId =
            $providerData['preapproval_id'] ?? null;


        if (!$providerSubscriptionId) {
            return response()->json([
                'received' => true,
            ]);
        }


        $subscription = Subscription::query()
            ->where(
                'provider_subscription_id',
                $providerSubscriptionId
            )
            ->first();


        if (!$subscription) {
            return response()->json([
                'received' => true,
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Datos del pago real
    |--------------------------------------------------------------------------
    */

        $payment =
            $providerData['payment'] ?? [];

        $providerPaymentId =
            $payment['id'] ?? null;

        $providerStatus =
            $payment['status']
            ?? $providerData['status']
            ?? null;

        $statusDetail =
            $payment['status_detail']
            ?? null;

        $amountArs =
            (float) (
                $providerData['transaction_amount']
                ?? $subscription->amount_ars
            );


        /*
    |--------------------------------------------------------------------------
    | Estado local
    |--------------------------------------------------------------------------
    */

        $localStatus = match ($providerStatus) {

            'approved' => 'paid',

            'rejected' => 'rejected',

            'cancelled' => 'cancelled',

            'refunded' => 'refunded',

            'in_process',
            'pending',
            'authorized' => 'pending',

            default => 'pending',
        };


        /*
    |--------------------------------------------------------------------------
    | Período correspondiente
    |--------------------------------------------------------------------------
    */

        $periodStart = !empty($providerData['debit_date'])
            ? \Carbon\Carbon::parse(
                $providerData['debit_date']
            )
            : now();


        $periodEnd = $subscription->plan === 'annual'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();


        /*
    |--------------------------------------------------------------------------
    | Crear o actualizar pago
    |--------------------------------------------------------------------------
    |
    | Usamos el ID de pago real de Mercado Pago para evitar duplicados
    | cuando Mercado Pago notifique varias actualizaciones del mismo cobro.
    |
    */

        if ($providerPaymentId) {

            SubscriptionPayment::updateOrCreate(

                [
                    'provider_payment_id' =>
                    (string) $providerPaymentId,
                ],

                [
                    'subscription_id' =>
                    $subscription->id,

                    'company_id' =>
                    $subscription->company_id,

                    'price_usd' =>
                    $subscription->price_usd,

                    'exchange_rate' =>
                    $subscription->exchange_rate,

                    'amount_ars' =>
                    $amountArs,

                    'provider_status' =>
                    $providerStatus,

                    'provider_status_detail' =>
                    $statusDetail,

                    'status' =>
                    $localStatus,

                    'period_start' =>
                    $periodStart,

                    'period_end' =>
                    $periodEnd,

                    'due_at' =>
                    $periodStart,

                    'paid_at' =>
                    $providerStatus === 'approved'
                        ? now()
                        : null,
                ]
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Pago aprobado
    |--------------------------------------------------------------------------
    */

        if ($providerStatus === 'approved') {

            $subscription->update([

                'status' => 'active',

                'provider_status' => 'authorized',

                'started_at' =>
                $subscription->started_at
                    ?? now(),

            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Volver a sincronizar la suscripción
    |--------------------------------------------------------------------------
    |
    | Un rechazo puede hacer que Mercado Pago pause/cancele/cambie
    | el estado del preapproval.
    |
    */

        try {

            $subscriptionData =
                $this->mercadoPagoService
                ->getSubscription(
                    $providerSubscriptionId
                );


            $providerSubscriptionStatus =
                $subscriptionData['status'] ?? null;


            $subscriptionStatus = $this->mapSubscriptionStatus(
                $providerSubscriptionStatus,
                $subscription->status
            );


            $subscription->update([

                'provider_status' =>
                $providerSubscriptionStatus,

                'status' =>
                $subscriptionStatus,

                'next_billing_at' =>
                $subscriptionData['next_payment_date']
                    ?? $subscription->next_billing_at,

                'cancelled_at' =>
                in_array(
                    $providerSubscriptionStatus,
                    ['cancelled', 'canceled'],
                    true
                )
                    ? ($subscription->cancelled_at ?? now())
                    : $subscription->cancelled_at,

            ]);
        } catch (\Throwable $e) {

            /*
        |--------------------------------------------------------------------------
        | El pago ya fue registrado.
        |--------------------------------------------------------------------------
        |
        | Si falla esta segunda consulta no devolvemos 500 porque no queremos
        | que Mercado Pago reintente innecesariamente un evento que ya
        | persistimos.
        |
        */

            report($e);
        }


        return response()->json([
            'received' => true,
        ]);
    }

    private function mapSubscriptionStatus(
        ?string $providerStatus,
        string $currentStatus
    ): string {
        return match ($providerStatus) {
            'authorized' => 'active',
            'pending' => 'pending',
            'paused' => 'paused',

            'cancelled',
            'canceled' => 'cancelled',

            default => $currentStatus,
        };
    }
}
