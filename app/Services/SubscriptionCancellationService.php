<?php

namespace App\Services;

use App\Models\Subscription;
use RuntimeException;

class SubscriptionCancellationService
{
    public function __construct(
        private MercadoPagoSubscriptionService $mercadoPago
    ) {}

    public function cancel(Subscription $subscription): void
    {
        $subscription->refresh();

        // Idempotencia: una cancelación ya finalizada
        // no vuelve a ejecutarse.
        if ($subscription->status === 'cancelled') {
            return;
        }

        if (!in_array(
            $subscription->status,
            ['pending', 'active'],
            true
        )) {
            throw new RuntimeException(
                'Esta suscripción no admite cancelación.'
            );
        }

        $providerId = $subscription->provider_subscription_id;

        if ($providerId) {

            // Primero verificamos el estado REAL en Mercado Pago.
            $data = $this->mercadoPago->getSubscription(
                $providerId
            );

            $status = $data['status'] ?? null;

            if ($status !== 'canceled') {

                if (!in_array(
                    $status,
                    ['pending', 'authorized', 'paused'],
                    true
                )) {
                    throw new RuntimeException(
                        'Mercado Pago informó un estado no previsto: '
                        . ($status ?? 'desconocido')
                    );
                }

                $this->mercadoPago->cancelSubscription(
                    $providerId
                );

                // Confirmar que la cancelación se aplicó.
                $verified = $this->mercadoPago->getSubscription(
                    $providerId
                );

                if (($verified['status'] ?? null) !== 'canceled') {
                    throw new RuntimeException(
                        'Mercado Pago todavía no confirmó '
                        . 'la cancelación. Reintentá más tarde.'
                    );
                }
            }

        } elseif ($subscription->init_point) {

            // Hay checkout pero no podemos identificarlo
            // de forma segura en Mercado Pago.
            throw new RuntimeException(
                'La suscripción tiene un checkout pendiente '
                . 'sin identificador de Mercado Pago. '
                . 'Requiere revisión administrativa.'
            );
        }

        // Sólo después de validar el proveedor.
        $subscription->update([
            'status' => 'cancelled',
            'provider_status' => $providerId
                ? 'canceled'
                : $subscription->provider_status,
            'cancelled_at' => now(),
            'next_billing_at' => null,
            'renewal_prepared_at' => null,
        ]);
    }
}
