<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;


use App\Events\ReminderDue;
use App\Services\FinancialReminderService;


Schedule::command('subscriptions:prepare-renewals')
    ->hourly()
    ->withoutOverlapping();

    
Schedule::command('aeria:send-expired-trial-emails')
    ->hourly()
    ->withoutOverlapping();
    
Schedule::command('movement-control:cleanup')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


//programar cron en el servidor con
//* * * * * cd /home/prestar/public_html && /usr/local/lsws/lsphp83/bin/php artisan schedule:run >> /dev/null 2>&1

Schedule::call(function () {

    $service = app(
        FinancialReminderService::class
    );

    $reminders =
        $service->getDueForNotification();


    foreach ($reminders as $reminder) {

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

// Cotizaciones de referencia Finanzas Argy; Scheduler único punto de consulta.
// Emite un evento por WebSocket incluso cuando conserva el último dato
// y marca stale=true ante un fallo de la fuente externa.
Schedule::call(function () {
    $data = app(\App\Services\FinanzasArgyRatesService::class)
        ->refresh();

    event(new \App\Events\OfficialRatesUpdated($data));
})
    ->name('finanzas-argy-rates-refresh')
    ->everyFiveMinutes()
    ->withoutOverlapping();
