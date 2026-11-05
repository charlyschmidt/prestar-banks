<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

use App\Events\ReminderDue;
use App\Events\OfficialRatesUpdated;

use App\Services\FinancialReminderService;
use App\Services\ChequeReminderService;
use App\Services\FinanzasArgyRatesService;

/*
|--------------------------------------------------------------------------
| AERIA Finance - Scheduler
|--------------------------------------------------------------------------
|
| Todas las tareas programadas de la aplicación.
|
| Producción:
| El cron ejecuta schedule:run cada minuto.
|
*/

/*
|--------------------------------------------------------------------------
| Suscripciones
|--------------------------------------------------------------------------
*/

Schedule::command('subscriptions:prepare-renewals')
    ->hourly()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Avisos de pruebas vencidas
|--------------------------------------------------------------------------
*/

Schedule::command('aeria:send-expired-trial-emails')
    ->hourly()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Limpieza de movimientos
|--------------------------------------------------------------------------
*/

Schedule::command('movement-control:cleanup')
    ->dailyAt('03:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Comando de ejemplo
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Recordatorios financieros y cheques
|--------------------------------------------------------------------------
|
| Se ejecuta cada minuto.
|
| Los recordatorios manuales conservan
| su funcionamiento actual.
|
| Los recordatorios de cheques:
|
| - Se validan antes de enviarse.
| - Deben pertenecer a un cheque en cartera.
| - Solo se envían a Super Admin.
| - Se reservan antes de emitir el evento.
| - No se envían nuevamente si ya fueron reservados.
|
*/

Schedule::call(function () {

    $service = app(
        FinancialReminderService::class
    );

    /*
    |--------------------------------------------------------------------------
    | Obtener recordatorios vencidos
    |--------------------------------------------------------------------------
    */

    $reminders = $service->getDueForNotification();

    foreach ($reminders as $reminder) {

        /*
        |--------------------------------------------------------------------------
        | Recordatorios de cheques
        |--------------------------------------------------------------------------
        */

        if ($reminder->isChequeReminder()) {

            /*
             * La reserva realiza una nueva
             * validación del cheque y sus permisos.
             *
             * Si otro proceso ya reservó el aviso,
             * no lo volvemos a enviar.
             */

            if (!$service->claimForNotification($reminder)) {
                continue;
            }

            try {

                /*
                |--------------------------------------------------------------------------
                | Emitir notificación por Reverb
                |--------------------------------------------------------------------------
                */

                ReminderDue::dispatch(
                    $reminder
                );

            } catch (\Throwable $exception) {

                /*
                 * Registramos el fallo para revisión.
                 *
                 * No liberamos automáticamente la reserva:
                 * si el evento llegó a Reverb antes del
                 * error, liberarla podría duplicar el aviso.
                 *
                 * La recuperación de envíos fallidos
                 * deberá gestionarse de forma controlada.
                 */

                Log::error(
                    'Error enviando recordatorio de cheque',
                    [
                        'reminder_id' => $reminder->id,
                        'cheque_id' => $reminder->cheque_id,
                        'company_id' => $reminder->company_id,
                        'user_id' => $reminder->user_id,
                        'message' => $exception->getMessage(),
                    ]
                );
            }

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Recordatorios financieros existentes
        |--------------------------------------------------------------------------
        |
        | Conservamos su flujo original.
        |
        */

        ReminderDue::dispatch(
            $reminder
        );

        $service->markAsNotified(
            $reminder
        );
    }

})
    ->name('financial-reminders')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Sincronización automática de cheques
|--------------------------------------------------------------------------
|
| Por ahora desactivada desde config/cheques.php.
|
| Cuando esté habilitada:
|
| - Consulta los cheques de todas las empresas.
| - Calcula los días hábiles bancarios.
| - Genera los avisos correspondientes.
| - Selecciona solamente Super Admin.
| - Descarta recordatorios pendientes obsoletos.
|
| No emite notificaciones directamente.
|
*/

if (config('cheques.reminders.enabled', false)) {

    Schedule::call(function () {

        $stats = app(
            ChequeReminderService::class
        )->sync();

        /*
        |--------------------------------------------------------------------------
        | Log de sincronización
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Sincronización de recordatorios de cheques',
            $stats
        );

    })
        ->name('cheque-reminders-sync')
        ->dailyAt('08:55')
        ->timezone(
            config(
                'cheques.reminders.timezone',
                'America/Argentina/Buenos_Aires'
            )
        )
        ->withoutOverlapping();
}

/*
|--------------------------------------------------------------------------
| Cotizaciones Finanzas Argy
|--------------------------------------------------------------------------
|
| Consulta de cotizaciones cada cinco minutos.
|
| Conservamos el servicio existente
| y la emisión del evento en tiempo real.
|
*/

Schedule::call(function () {

    $data = app(
        FinanzasArgyRatesService::class
    )->refresh();

    event(
        new OfficialRatesUpdated($data)
    );

})
    ->name('finanzas-argy-rates-refresh')
    ->everyFiveMinutes()
    ->withoutOverlapping();
