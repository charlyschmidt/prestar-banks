<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;


class BalanceService
{
    /*
    |--------------------------------------------------------------------------
    | Resumen histórico
    |--------------------------------------------------------------------------
    */

    public function getDashboardSummary(): array
    {
        return [

            'balance_total' =>
            $this->getTotalBalance(),

            'ingresos_mes' =>
            $this->getMonthlyIncome(),

            'egresos_mes' =>
            $this->getMonthlyExpenses(),

            'ingresos_hoy' =>
            $this->getTodayIncome(),

            'egresos_hoy' =>
            $this->getTodayExpenses(),

            'movimientos_mes' =>
            $this->getMonthlyMovements(),

            'accounts' =>
            $this->getAccountsBalance(),

            'movements' =>
            $this->getLatestMovements(),

            'movimiento_ultimo' =>
            $this->getLastMovement(),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Último movimiento
    |--------------------------------------------------------------------------
    */

    public function getLastMovement()
    {
        return Transaction::with([
            'account',
            'accountBalance',
        ])
            ->latest('date')
            ->latest('id')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Balance histórico total por moneda
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | Nunca sumamos monedas diferentes entre sí.
    |
    | Resultado:
    |
    | [
    |     'ARS' => 1500000,
    |     'USD' => 2500,
    | ]
    |
    */

    public function getTotalBalance(): array
    {
        $balances = [];


        $transactions = Transaction::with(
            'accountBalance'
        )->get();


        foreach ($transactions as $transaction) {

            $currency =
                $transaction->accountBalance?->currency;


            if (!$currency) {
                continue;
            }


            if (!isset($balances[$currency])) {
                $balances[$currency] = 0;
            }


            if ($transaction->type === 'income') {

                $balances[$currency] +=
                    (float) $transaction->amount;

            } elseif (
                in_array(
                    $transaction->type,
                    [
                        'expense',
                        'reserve',
                        'transfer_out',
                    ],
                    true
                )
            ) {

                $balances[$currency] -=
                    (float) $transaction->amount;
            }
        }


        ksort($balances);


        return $balances;
    }


    /*
    |--------------------------------------------------------------------------
    | Balance histórico por cuenta y moneda
    |--------------------------------------------------------------------------
    */

    public function getAccountsBalance()
    {
        return Account::with([
            'balances',
            'transactions.accountBalance',
        ])
            ->get()
            ->map(function ($account) {

                /*
                |--------------------------------------------------------------------------
                | Agrupar movimientos por AccountBalance
                |--------------------------------------------------------------------------
                */

                $balances = $account->balances
                    ->map(function ($accountBalance) use ($account) {

                        $transactions =
                            $account->transactions
                            ->where(
                                'account_balance_id',
                                $accountBalance->id
                            );


                        $incomeAmount =
                            $transactions
                            ->where('type', 'income')
                            ->sum('amount');


                        $expenseAmount =
                            $transactions
                            ->whereIn(
                                'type',
                                [
                                    'expense',
                                    'reserve',
                                    'transfer_out',
                                ]
                            )
                            ->sum('amount');


                        $incomeCount =
                            $transactions
                            ->where('type', 'income')
                            ->count();


                        $expenseCount =
                            $transactions
                            ->whereIn(
                                'type',
                                [
                                    'expense',
                                    'transfer_out',
                                ]
                            )
                            ->count();


                        $reserveCount =
                            $transactions
                            ->where('type', 'reserve')
                            ->count();


                        return [

                            'account_balance_id' =>
                            $accountBalance->id,

                            'currency' =>
                            $accountBalance->currency,

                            'balance' =>
                            (float) $incomeAmount
                            - (float) $expenseAmount,

                            'income' =>
                            $incomeCount,

                            'expense' =>
                            $expenseCount,

                            'reserve' =>
                            $reserveCount,

                            'movements' =>
                            $transactions->count(),

                        ];
                    })
                    ->values();


                return [

                    'id' =>
                    $account->id,

                    'name' =>
                    $account->name,

                    'type' =>
                    $account->type,

                    'logo' =>
                    $account->logo
                        ? Storage::url($account->logo)
                        : null,

                    'balances' =>
                    $balances,

                ];
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Ingresos del mes por moneda
    |--------------------------------------------------------------------------
    */

    public function getMonthlyIncome(): array
    {
        return $this->sumByCurrency(
            Transaction::where(
                'type',
                'income'
            )
                ->whereMonth(
                    'date',
                    now()->month
                )
                ->whereYear(
                    'date',
                    now()->year
                )
                ->with('accountBalance')
                ->get()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Egresos del mes por moneda
    |--------------------------------------------------------------------------
    */

    public function getMonthlyExpenses(): array
    {
        return $this->sumByCurrency(
            Transaction::whereIn(
                'type',
                [
                    'expense',
                    'reserve',
                    'transfer_out',
                ]
            )
                ->whereMonth(
                    'date',
                    now()->month
                )
                ->whereYear(
                    'date',
                    now()->year
                )
                ->with('accountBalance')
                ->get()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cantidad de movimientos del mes
    |--------------------------------------------------------------------------
    */

    public function getMonthlyMovements(): int
    {
        return Transaction::whereMonth(
            'date',
            now()->month
        )
            ->whereYear(
                'date',
                now()->year
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Últimos movimientos
    |--------------------------------------------------------------------------
    */

    public function getLatestMovements()
    {
        return Transaction::with([
            'account',
            'accountBalance',
            'user',
        ])
            ->orderBy(
                'date',
                'desc'
            )
            ->orderBy(
                'id',
                'desc'
            )
            ->limit(10)
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Ingresos de hoy por moneda
    |--------------------------------------------------------------------------
    */

    public function getTodayIncome(): array
    {
        return $this->sumByCurrency(
            Transaction::where(
                'type',
                'income'
            )
                ->whereDate(
                    'date',
                    today()
                )
                ->with('accountBalance')
                ->get()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Egresos de hoy por moneda
    |--------------------------------------------------------------------------
    */

    public function getTodayExpenses(): array
    {
        return $this->sumByCurrency(
            Transaction::whereIn(
                'type',
                [
                    'expense',
                    'reserve',
                    'transfer_out',
                ]
            )
                ->whereDate(
                    'date',
                    today()
                )
                ->with('accountBalance')
                ->get()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: sumar por moneda
    |--------------------------------------------------------------------------
    */

    private function sumByCurrency(
        $transactions
    ): array {

        $totals = [];


        foreach ($transactions as $transaction) {

            $currency =
                $transaction->accountBalance?->currency;


            if (!$currency) {
                continue;
            }


            if (!isset($totals[$currency])) {
                $totals[$currency] = 0;
            }


            $totals[$currency] +=
                (float) $transaction->amount;
        }


        ksort($totals);


        return $totals;
    }
}