<?php

namespace App\Http\Controllers;

use App\Services\BalanceDayService;


class DashboardController extends Controller
{

    public function index(
        BalanceDayService $balanceDayService
    ) {

        $summary = $balanceDayService->getDashboardSummary();



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
        \App\Services\BalanceDayService $balanceDayService
    ) {
        $summary = $balanceDayService->getDashboardSummary();


        if (!$summary['has_day']) {

            return response()->json([
                'has_day' => false
            ]);
        }


        return response()->json([

            'has_day' => true,

            'balance_total' => $summary['balance_total'],

            'day_income' => $summary['ingresos_jornada'],

            'day_expense' => $summary['egresos_jornada'],

            'movements_total' => $summary['movimientos_total'],

            'accounts' => collect($summary['accounts'])
                ->map(function ($account) {

                    return [
                        'id' => $account['id'],
                        'balance' => $account['balance'],
                        'income' => $account['income'],
                        'expense' => $account['expense'],
                        'movements' => $account['movements'],
                    ];
                })
                ->values(),

            'movements' => collect($summary['movements'])
                ->map(function ($movement) {

                    return [

                        'id' => $movement->id,

                        'type' => $movement->type,

                        'amount' => $movement->amount,

                        'description' => $movement->description,

                        'date' => $movement->date,

                        'initial_balance' => $movement->initial_balance,

                        'balance_after' => $movement->balance_after,

                        'account' => [
                            'id' => $movement->account->id,
                            'name' => $movement->account->name,
                        ],

                        'user' => [
                            'id' => $movement->user?->id,
                            'name' => $movement->user?->name
                                ?? 'Sin registro',
                        ],

                    ];
                })
                ->values(),

        ]);
    }
}
