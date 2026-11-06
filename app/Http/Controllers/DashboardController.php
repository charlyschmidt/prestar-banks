<?php

namespace App\Http\Controllers;

use App\Services\BalanceDayService;
use App\Services\CompanyContextService;
use App\Models\Account;

class DashboardController extends Controller
{
    public function index(
        BalanceDayService $balanceDayService,
        CompanyContextService $companyContextService
    ) {

        $summary =
            $balanceDayService->getDashboardSummary();

        $company =
            $companyContextService->company();


        /*
        |--------------------------------------------------------------------------
        | Sin jornada abierta
        |--------------------------------------------------------------------------
        */

        if (!$summary['has_day']) {

            /*
            |--------------------------------------------------------------------------
            | Onboarding inicial
            |--------------------------------------------------------------------------
            */

            if (
                $company
                && !$company->hasCompletedOnboarding()
            ) {

                $accountsCount = Account::count();

                return view(
                    'dashboard.onboarding',
                    [
                        'summary' => $summary,
                        'company' => $company,
                        'accountsCount' => $accountsCount,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Funcionamiento habitual
            |--------------------------------------------------------------------------
            */

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


        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Los movimientos provienen del resumen financiero.
        | Cargamos las relaciones necesarias para generar
        | el JSON evitando consultas repetidas.
        |
        | Client pertenece al movimiento y a la empresa.
        |
        */

        $movements = collect($summary['movements']);

        $movements->each(function ($movement) {

            $movement->loadMissing([
                'account',
                'accountBalance',
                'user',
                'apiKey',
                'client',
            ]);
        });


        return response()->json([

            'has_day' => true,


            /*
            |--------------------------------------------------------------------------
            | Totales por moneda
            |--------------------------------------------------------------------------
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
            | HTML de las tarjetas de cuentas
            |--------------------------------------------------------------------------
            */

            'accounts_html' => view(
                'dashboard.partials.account-cards',
                [
                    'accounts' => $summary['accounts'],
                ]
            )->render(),


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
            $movements
                ->map(function ($movement) {

                    return [

                        'id' =>
                        $movement->id,


                        /*
                            |--------------------------------------------------------------------------
                            | Cliente asociado
                            |--------------------------------------------------------------------------
                            |
                            | Es opcional. Enviamos solamente
                            | UUID público y nombre para la UI.
                            |
                            */

                        'client' => $movement->client
                            ? [
                                'uuid' =>
                                $movement->client->uuid,

                                'name' =>
                                $movement->client->display_name,
                            ]
                            : null,


                        'account_balance_id' =>
                        $movement->account_balance_id,

                        'currency' =>
                        $movement->accountBalance?->currency,

                        'type' =>
                        $movement->type,

                        'amount' =>
                        $movement->amount,

                        'transfer_tax_rate' =>
                        $movement->transfer_tax_rate ?? 0,

                        'transfer_tax_amount' =>
                        $movement->transfer_tax_amount ?? 0,

                        'description' =>
                        $movement->description,

                        'date' =>
                        $movement->date,

                        'initial_balance' =>
                        $movement->initial_balance,

                        'balance_after' =>
                        $movement->balance_after,

                        'executed_at' =>
                        $movement->executed_at,


                        /*
                            |--------------------------------------------------------------------------
                            | Origen
                            |--------------------------------------------------------------------------
                            */

                        'source' =>
                        $movement->source,

                        'external_id' =>
                        $movement->external_id,

                        'api_key' => $movement->apiKey
                            ? [
                                'id' =>
                                $movement->apiKey->id,

                                'name' =>
                                $movement->apiKey->name,
                            ]
                            : null,


                        /*
                            |--------------------------------------------------------------------------
                            | Cuenta
                            |--------------------------------------------------------------------------
                            */

                        'account' => [

                            'id' =>
                            $movement->account?->id,

                            'name' =>
                            $movement->account?->name,

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
