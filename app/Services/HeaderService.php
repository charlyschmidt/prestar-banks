<?php

namespace App\Services;


class HeaderService
{

    public function __construct(
        protected BalanceDayService $balanceDayService
    ) {
    }


    public function getData(): array
    {

        $summary =
            $this->balanceDayService
                ->getDashboardSummary();


        /*
        |--------------------------------------------------------------------------
        | Sin jornada abierta
        |--------------------------------------------------------------------------
        |
        | Los valores monetarios SIEMPRE deben mantener
        | el mismo formato multimoneda.
        |
        | Nunca devolvemos 0 porque el Blade espera:
        |
        | [
        |     'ARS' => ...,
        |     'USD' => ...,
        | ]
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

        ];

    }

}