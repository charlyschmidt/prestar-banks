<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionRenewalService;
use Illuminate\Console\Command;

class PrepareSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:prepare-renewals';

    protected $description =
    'Actualiza el importe en ARS de las próximas renovaciones de suscripciones';


    public function handle(
        SubscriptionRenewalService $renewalService
    ): int {

        /*
        |--------------------------------------------------------------------------
        | Ventana de renovación
        |--------------------------------------------------------------------------
        |
        | Preparamos las suscripciones cuya próxima renovación ocurre
        | dentro de las próximas 24 horas.
        |
        */

        $from = now();
        $until = now()->addDay();


        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('renewal_prepared_at')
                    ->orWhereColumn(
                        'renewal_prepared_at',
                        '<',
                        'next_billing_at'
                    );
            })
            ->whereNotNull('provider_subscription_id')
            ->whereNotNull('next_billing_at')
            ->whereBetween(
                'next_billing_at',
                [$from, $until]
            )
            ->get();


        if ($subscriptions->isEmpty()) {
            $this->info(
                'No hay suscripciones próximas a renovar.'
            );

            return self::SUCCESS;
        }


        foreach ($subscriptions as $subscription) {

            try {

                $renewalService
                    ->updateRenewalAmount($subscription);

                $this->info(
                    "Suscripción {$subscription->id} preparada correctamente."
                );
            } catch (\Throwable $e) {

                report($e);

                $this->error(
                    "Error preparando suscripción {$subscription->id}: {$e->getMessage()}"
                );
            }
        }


        return self::SUCCESS;
    }
}
