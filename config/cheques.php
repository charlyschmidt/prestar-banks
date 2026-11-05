<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recordatorios automáticos
    |--------------------------------------------------------------------------
    |
    | Configuración general del sistema.
    |
    | La activación definitiva se realizará
    | desde el Scheduler después de las pruebas.
    |
    */

    'reminders' => [

        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Días hábiles de anticipación
        |--------------------------------------------------------------------------
        */

        'advance_business_days' => [
            3,
            1,
        ],

        /*
        |--------------------------------------------------------------------------
        | Horario de notificación
        |--------------------------------------------------------------------------
        |
        | Zona horaria argentina.
        |
        */

        'notification_time' => '09:00',

        'timezone' => 'America/Argentina/Buenos_Aires',

        /*
        |--------------------------------------------------------------------------
        | Avisos posteriores al vencimiento
        |--------------------------------------------------------------------------
        |
        | Un único recordatorio por cheque,
        | usuario y fecha de vencimiento.
        |
        */

        'notify_overdue' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Calendario bancario
    |--------------------------------------------------------------------------
    |
    | Los sábados y domingos no son hábiles.
    |
    | Los feriados bancarios deben verificarse
    | contra el calendario oficial aplicable.
    |
    | No cargamos fechas sin verificar.
    |
    */

    'bank_holidays' => [

        // '2026-11-XX',

    ],

    /*
    |--------------------------------------------------------------------------
    | Presentación bancaria
    |--------------------------------------------------------------------------
    |
    | El vencimiento nominal puede no coincidir
    | con el día hábil de presentación.
    |
    | La regla definitiva deberá considerar:
    | - Cheques físicos.
    | - ECHEQ.
    | - Feriados bancarios.
    | - Condiciones de presentación aplicables.
    |
    */

    'presentation' => [

        'adjust_non_business_days' => false,

    ],

];
