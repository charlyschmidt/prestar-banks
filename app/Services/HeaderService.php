<?php

namespace App\Services;


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
        | Alertas activas
        |--------------------------------------------------------------------------
        */

        $alerts =
            $this->lowBalanceAlertService
                ->getActiveAlerts();


        /*
        |--------------------------------------------------------------------------
        | Sin jornada abierta
        |--------------------------------------------------------------------------
        |
        | Los valores monetarios SIEMPRE deben mantener
        | el mismo formato multimoneda.
        |
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
                    [],

                'alerts_count' =>
                    0,

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
                $alerts,

            'alerts_count' =>
                $alerts->count(),

        ];

    }

}