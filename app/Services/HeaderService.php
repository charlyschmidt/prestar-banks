<?php

namespace App\Services;

use App\Models\FinancialReminder;

class HeaderService
{
    public function __construct(
        protected BalanceDayService $balanceDayService,
        protected LowBalanceAlertService $lowBalanceAlertService
    ) {
    }


    public function getData(): array
    {
        $summary =
            $this->balanceDayService
                ->getDashboardSummary();


        /*
        |--------------------------------------------------------------------------
        | Alertas de saldo bajo
        |--------------------------------------------------------------------------
        */

        $lowBalanceAlerts =
            $this->lowBalanceAlertService
                ->getActiveAlerts();


        /*
        |--------------------------------------------------------------------------
        | Recordatorios vencidos del usuario
        |--------------------------------------------------------------------------
        |
        | Los recordatorios funcionan independientemente
        | de que exista o no una jornada financiera abierta.
        |
        */

        $reminders = FinancialReminder::query()
            ->where(
                'user_id',
                auth()->id()
            )
            ->where(
                'status',
                'pending'
            )
            ->where(
                'scheduled_at',
                '<=',
                now()
            )
            ->orderBy(
                'scheduled_at'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Total de alertas
        |--------------------------------------------------------------------------
        */

        $alertsCount =
            $lowBalanceAlerts->count()
            +
            $reminders->count();


        /*
        |--------------------------------------------------------------------------
        | Sin jornada abierta
        |--------------------------------------------------------------------------
        */

        if (!$summary['has_day']) {

            return [

                'has_day' =>
                    false,

                'balance' =>
                    [],

                'income' =>
                    [],

                'expense' =>
                    [],

                'movements' =>
                    0,

                'alerts' =>
                    $lowBalanceAlerts,

                'reminders' =>
                    $reminders,

                'alerts_count' =>
                    $alertsCount,

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | Jornada abierta
        |--------------------------------------------------------------------------
        */

        return [

            'has_day' =>
                true,

            'balance' =>
                $summary['balance_total'] ?? [],

            'income' =>
                $summary['ingresos_jornada'] ?? [],

            'expense' =>
                $summary['egresos_jornada'] ?? [],

            'movements' =>
                $summary['movimientos_total'] ?? 0,

            'alerts' =>
                $lowBalanceAlerts,

            'reminders' =>
                $reminders,

            'alerts_count' =>
                $alertsCount,

        ];
    }
}