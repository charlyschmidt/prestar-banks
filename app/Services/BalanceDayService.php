<?php

namespace App\Services;

use App\Models\FinancialDay;
use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Illuminate\Support\Facades\Storage;


class BalanceDayService
{

    protected FinancialDayService $financialDayService;


    public function __construct(
        FinancialDayService $financialDayService
    ) {
        $this->financialDayService = $financialDayService;
    }


    public function getDashboardSummary(): array
    {

        $day = $this->financialDayService->current();


        if (!$day) {

            return [

                'has_day' => false,

                'day' => null,

                'balance_total' => 0,

                'ingresos_jornada' => 0,

                'egresos_jornada' => 0,

                'movimientos_total' => 0,

                'accounts' => [],

                'movements' => [],

                'movimiento_ultimo' => null

            ];
        }


        return [

            'has_day' => true,

            'day' => $day,

            'balance_total' =>
                $this->getTotalBalance($day),

            'ingresos_jornada' =>
                $this->getDayIncome($day),

            /*
             * Incluye egresos + reservas
             * porque ambos descuentan saldo.
             */
            'egresos_jornada' =>
                $this->getDayExpenses($day),

            'accounts' =>
                $this->getAccountsBalance($day),

            'movements' =>
                $this->getLatestMovements($day),

            'movimiento_ultimo' =>
                $this->getLastMovement($day),

            'movimientos_total' =>
                $this->getMovementsCount($day)

        ];
    }


    public function getMovementsCount(
        FinancialDay $day
    ) {

        return Transaction::where(
            'financial_day_id',
            $day->id
        )
            ->count();
    }


    public function getTotalBalance(
        FinancialDay $day
    ) {

        return AccountDailyBalance::where(
            'financial_day_id',
            $day->id
        )
            ->sum('current_balance');
    }


    public function getAccountsBalance(
        FinancialDay $day
    ) {

        return AccountDailyBalance::with('account')
            ->where(
                'financial_day_id',
                $day->id
            )
            ->get()
            ->map(function ($balance) {

                return [

                    'id' =>
                        $balance->account->id,

                    'name' =>
                        $balance->account->name,

                    'type' =>
                        $balance->account->type,

                    'logo' =>
                        $balance->account->logo
                            ? Storage::url(
                                $balance->account->logo
                            )
                            : null,


                    'initial_balance' =>
                        $balance->initial_balance,


                    'balance' =>
                        $balance->current_balance,


                    /*
                    |--------------------------------------------------------------------------
                    | Ingresos
                    |--------------------------------------------------------------------------
                    */

                    'income' =>
                        $this->countIncome(
                            $balance->account_id,
                            $balance->financial_day_id
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | Egresos
                    |--------------------------------------------------------------------------
                    |
                    | NO incluye reservas.
                    */

                    'expense' =>
                        $this->countExpense(
                            $balance->account_id,
                            $balance->financial_day_id
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | Reservas
                    |--------------------------------------------------------------------------
                    */

                    'reserve' =>
                        $this->countReserve(
                            $balance->account_id,
                            $balance->financial_day_id
                        ),


                    'movements' =>
                        $this->countMovements(
                            $balance->account_id,
                            $balance->financial_day_id
                        ),

                ];
            });
    }


    private function countMovements(
        $accountId,
        $dayId
    ) {

        return Transaction::where(
            'account_id',
            $accountId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->count();
    }


    private function countIncome(
        $accountId,
        $dayId
    ) {

        return Transaction::where(
            'account_id',
            $accountId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->whereIn('type', [
                'income'
            ])
            ->sum('amount');
    }


    private function countExpense(
        $accountId,
        $dayId
    ) {

        return Transaction::where(
            'account_id',
            $accountId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->whereIn('type', [
                'expense',
                'transfer_out'
            ])
            ->sum('amount');
    }


    private function countReserve(
        $accountId,
        $dayId
    ) {

        return Transaction::where(
            'account_id',
            $accountId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->where(
                'type',
                'reserve'
            )
            ->sum('amount');
    }


    public function getLastMovement(
        FinancialDay $day
    ) {

        return Transaction::with([
            'account',
            'user'
        ])
            ->where(
                'financial_day_id',
                $day->id
            )
            ->latest('date')
            ->latest('id')
            ->first();
    }


    public function getLatestMovements(
        FinancialDay $day
    ) {

        $movements = Transaction::with([
            'account',
            'user'
        ])
            ->where(
                'financial_day_id',
                $day->id
            )
            ->orderBy(
                'date',
                'asc'
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();


        $balances = AccountDailyBalance::where(
            'financial_day_id',
            $day->id
        )
            ->pluck(
                'initial_balance',
                'account_id'
            );


        foreach ($movements as $movement) {

            if (
                !isset(
                    $balances[$movement->account_id]
                )
            ) {
                continue;
            }


            /*
             * Saldo antes del movimiento
             */
            $movement->initial_balance =
                $balances[$movement->account_id];


            /*
             * Ingreso suma.
             *
             * Egreso y Reserva restan.
             */
            if (
                in_array(
                    $movement->type,
                    [
                        'income'
                    ]
                )
            ) {

                $balances[$movement->account_id] +=
                    $movement->amount;

            } else {

                $balances[$movement->account_id] -=
                    $movement->amount;
            }


            /*
             * Saldo después del movimiento
             */
            $movement->balance_after =
                $balances[$movement->account_id];
        }


        return $movements
            ->sortByDesc(function ($movement) {

                return $movement->date;

            })
            ->values();
    }


    public function getDayIncome(
        FinancialDay $day
    ) {

        return Transaction::where(
            'financial_day_id',
            $day->id
        )
            ->whereIn('type', [
                'income'
            ])
            ->sum('amount');
    }


    /*
    |--------------------------------------------------------------------------
    | Egresos totales de la jornada
    |--------------------------------------------------------------------------
    |
    | Acá SÍ incluimos Reserva porque el header
    | debe mostrar todo lo que descontó saldo.
    */

    public function getDayExpenses(
        FinancialDay $day
    ) {

        return Transaction::where(
            'financial_day_id',
            $day->id
        )
            ->whereIn('type', [
                'expense',
                'reserve',
                'transfer_out'
            ])
            ->sum('amount');
    }
}