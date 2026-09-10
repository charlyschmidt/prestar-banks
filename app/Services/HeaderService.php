<?php

namespace App\Services;


class HeaderService
{


    public function __construct(
        protected BalanceDayService $balanceDayService
    ){}



    public function getData(): array
    {

        $summary = $this->balanceDayService->getDashboardSummary();



        if(!$summary['has_day']) {

            return [

                'has_day' => false,

                'balance' => 0,

                'income' => 0,

                'expense' => 0,

                'movements' => 0

            ];

        }



        return [

            'has_day' => true,

            'balance' => $summary['balance_total'],

            'income' => $summary['ingresos_jornada'],

            'expense' => $summary['egresos_jornada'],

            'movements' => $summary['movimientos_total']

        ];

    }


}