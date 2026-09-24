<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\AeriaMailService;
use Illuminate\Console\Command;

class SendExpiredTrialEmails extends Command
{
    protected $signature = 'aeria:send-expired-trial-emails';

    protected $description =
        'Envía el aviso de finalización del período de prueba';

    public function handle(
        AeriaMailService $mailService
    ): int {

        $companies = Company::query()
            ->where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->whereNull('trial_expired_email_sent_at')
            ->get();

        foreach ($companies as $company) {

            /*
             * Si ya tiene una suscripción activa,
             * no corresponde enviar el aviso.
             */
            if ($company->hasActiveSubscription()) {
                continue;
            }

            /*
             * Buscamos los administradores de la empresa.
             */
            $admins = $company->users()
                ->wherePivot('is_admin', true)
                ->get();

            if ($admins->isEmpty()) {
                $this->warn(
                    "Empresa {$company->id}: sin administradores."
                );

                continue;
            }

            /*
             * Enviamos el aviso a cada administrador.
             */
            foreach ($admins as $admin) {
                $mailService->sendTrialExpired(
                    $admin
                );
            }

            /*
             * Marcamos el aviso como enviado solamente
             * después de completar los envíos.
             */
            $company->update([
                'trial_expired_email_sent_at' => now(),
            ]);

            $this->info(
                "Aviso enviado: {$company->name}"
            );
        }

        return self::SUCCESS;
    }
}