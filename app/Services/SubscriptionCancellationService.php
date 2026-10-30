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

        $providerStatus = $subscription->provider_status;

        $cancelledStatuses = ['cancelled', 'canceled'];

        if ($providerId) {

            // Consultar estado real.
            $data = $this->mercadoPago->getSubscription($providerId);

            $providerStatus = $data['status'] ?? null;

            if (!in_array(
                $providerStatus,
                $cancelledStatuses,
                true
            )) {

                if (!in_array(
                    $providerStatus,
                    ['pending', 'authorized', 'paused'],
                    true
                )) {
                    throw new RuntimeException(
                        'Estado no previsto en Mercado Pago: '
                            . ($providerStatus ?? 'desconocido')
                    );
                }

                // Solicitar cancelación.
                $this->mercadoPago->cancelSubscription($providerId);

                // Verificar nuevamente en Mercado Pago.
                $verified = $this->mercadoPago
                    ->getSubscription($providerId);

                $providerStatus = $verified['status'] ?? null;
            }

            // No actualizar AERIA sin confirmación.
            if (!in_array(
                $providerStatus,
                $cancelledStatuses,
                true
            )) {
                throw new RuntimeException(
                    'Mercado Pago no confirmó la cancelación. '
                        . 'Estado: '
                        . ($providerStatus ?? 'desconocido')
                );
            }
        } elseif ($subscription->init_point) {

            throw new RuntimeException(
                'Existe un checkout sin identificador externo. '
                    . 'Requiere revisión administrativa.'
            );
        }

        // Actualizar AERIA después de confirmar.
        $subscription->update([
            'status' => 'cancelled',
            'provider_status' => $providerStatus,
            'cancelled_at' => now(),
            'next_billing_at' => null,
            'renewal_prepared_at' => null,
        ]);
    }
}
