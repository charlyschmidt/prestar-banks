<?php

namespace App\Services;


use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BalanceService
{

    public function getDashboardSummary(): array//getHistoricalSummary
    {

        return [

            'balance_total' => $this->getTotalBalance(),

            'ingresos_mes' => $this->getMonthlyIncome(),

            'egresos_mes' => $this->getMonthlyExpenses(),

            'ingresos_hoy' => $this->getTodayIncome(),

            'egresos_hoy' => $this->getTodayExpenses(),

            'movimientos_mes' => $this->getMonthlyMovements(),

            'accounts' => $this->getAccountsBalance(),

            'movements' => $this->getLatestMovements(),

            'movimiento_ultimo' => $this->getLastMovement()


        ];
    }


    public function getLastMovement()
    {

        return Transaction::with('account')
            ->latest('date')
            ->latest('id')
            ->first();
    }
    /**
     * Saldo general de todas las cuentas
     */
    public function getTotalBalance()
    {

        $income = Transaction::whereIn(
            'type',
            [
                'income'
            ]
        )
            ->sum('amount');



        $expense = Transaction::whereIn(
            'type',
            [
                'expense',
                'transfer_out'
            ]
        )
            ->sum('amount');



        return $income - $expense;
    }





    /**
     * Saldo por banco
     */
    public function getAccountsBalance()
    {

        return Account::with('transactions')
            ->get()
            ->map(function ($account) {


                $income = $account->transactions
                    ->whereIn('type', [
                        'income'
                    ])
                    ->sum('amount');



                $expense = $account->transactions
                    ->whereIn('type', [
                        'expense',
                        'transfer_out'
                    ])
                    ->sum('amount');



                return [

                    'id' => $account->id,

                    'name' => $account->name,

                    'type' => $account->type,


                    'logo' => $account->logo
                        ? Storage::url($account->logo)
                        : null,


                    'balance' => $income - $expense,


                    'income' => $account->transactions
                        ->whereIn('type', [
                            'income'
                        ])
                        ->count(),


                    'expense' => $account->transactions
                        ->whereIn('type', [
                            'expense',
                            'transfer_out'
                        ])
                        ->count(),


                    'movements' => $account->transactions->count()

                ];
            });
    }







    /**
     * Ingresos del mes actual
     */
    public function getMonthlyIncome()
    {

        return Transaction::whereIn(
            'type',
            [
                'income'
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
            ->sum('amount');
    }






    /**
     * Egresos del mes actual
     */
    public function getMonthlyExpenses()
    {

        return Transaction::whereIn(
            'type',
            [
                'expense',
                'transfer_out'
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
            ->sum('amount');
    }





    public function getMonthlyMovements()
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

    public function getLatestMovements()
    {

        return Transaction::with('account')
            ->orderBy(
                'date',
                'desc'
            )
            ->limit(10)
            ->get();
    }

    public function getTodayIncome()
    {
        return Transaction::whereIn(
            'type',
            [
                'income'
            ]
        )
            ->whereDate(
                'date',
                today()
            )
            ->sum('amount');
    }



    public function getTodayExpenses()
    {
        return Transaction::whereIn(
            'type',
            [
                'expense',
                'transfer_out'
            ]
        )
            ->whereDate(
                'date',
                today()
            )
            ->sum('amount');
    }
}
