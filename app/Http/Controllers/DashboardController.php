<?php

namespace App\Http\Controllers;

use App\Services\BalanceDayService;


class DashboardController extends Controller
{

    public function index(
        BalanceDayService $balanceDayService
    ) {

        $summary =
            $balanceDayService->getDashboardSummary();


        if (!$summary['has_day']) {

            return view(
                'dashboard.no-day',
                [
                    'summary' => $summary
                ]
            );
        }


        return view(
            'dashboard.index',
            [
                'summary' => $summary
            ]
        );
    }


    public function sync(
        BalanceDayService $balanceDayService
    ) {

        $summary =
            $balanceDayService->getDashboardSummary();


        /*
    |--------------------------------------------------------------------------
    | Sin jornada abierta
    |--------------------------------------------------------------------------
    */

        if (!$summary['has_day']) {

            return response()->json([
                'has_day' => false
            ]);
        }


        return response()->json([

            'has_day' => true,


            /*
        |--------------------------------------------------------------------------
        | Totales por moneda
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | balance_total:
        | {
        |     ARS: 1500000,
        |     USD: 2500
        | }
        |
        */

            'balance_total' =>
            $summary['balance_total'],

            'day_income' =>
            $summary['ingresos_jornada'],

            'day_expense' =>
            $summary['egresos_jornada'],

            'movements_total' =>
            $summary['movimientos_total'],


            /*
        |--------------------------------------------------------------------------
        | Cuentas
        |--------------------------------------------------------------------------
        */

            'accounts' =>
            collect($summary['accounts'])
                ->map(function ($account) {

                    return [

                        'id' =>
                        $account['id'],

                        'name' =>
                        $account['name'],

                        'type' =>
                        $account['type'],

                        'balances' =>
                        collect($account['balances'])
                            ->map(function ($balance) {

                                return [

                                    'account_balance_id' =>
                                    $balance['account_balance_id'],

                                    'currency' =>
                                    $balance['currency'],

                                    'initial_balance' =>
                                    $balance['initial_balance'],

                                    'balance' =>
                                    $balance['balance'],

                                    'income' =>
                                    $balance['income'],

                                    'expense' =>
                                    $balance['expense'],

                                    'reserve' =>
                                    $balance['reserve'],

                                    'movements' =>
                                    $balance['movements'],

                                ];
                            })
                            ->values(),

                    ];
                })
                ->values(),


            /*
        |--------------------------------------------------------------------------
        | Movimientos
        |--------------------------------------------------------------------------
        */

            'movements' =>
            collect($summary['movements'])
                ->map(function ($movement) {

                    return [

                        'id' =>
                        $movement->id,

                        'account_balance_id' =>
                        $movement->account_balance_id,

                        'currency' =>
                        $movement->accountBalance?->currency,

                        'type' =>
                        $movement->type,

                        'amount' =>
                        $movement->amount,

                        'description' =>
                        $movement->description,

                        'date' =>
                        $movement->date,

                        'initial_balance' =>
                        $movement->initial_balance,

                        'balance_after' =>
                        $movement->balance_after,


                        /*
                        |--------------------------------------------------------------------------
                        | Cuenta
                        |--------------------------------------------------------------------------
                        */

                        'account' => [

                            'id' =>
                            $movement->account->id,

                            'name' =>
                            $movement->account->name,

                        ],


                        /*
                        |--------------------------------------------------------------------------
                        | Moneda
                        |--------------------------------------------------------------------------
                        */

                        'account_balance' => [

                            'id' =>
                            $movement->accountBalance?->id,

                            'currency' =>
                            $movement->accountBalance?->currency,

                        ],


                        /*
                        |--------------------------------------------------------------------------
                        | Usuario
                        |--------------------------------------------------------------------------
                        */

                        'user' => [

                            'id' =>
                            $movement->user?->id,

                            'email' =>
                            $movement->user?->email,

                        ],

                    ];
                })
                ->values(),

        ]);
    }
}
