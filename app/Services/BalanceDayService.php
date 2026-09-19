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
        $this->financialDayService =
            $financialDayService;
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function getDashboardSummary(): array
    {
        $day =
            $this->financialDayService
                ->current();


        if (!$day) {

            return [

                'has_day' => false,

                'day' => null,

                'balance_total' => [],

                'ingresos_jornada' => [],

                'egresos_jornada' => [],

                'movimientos_total' => 0,

                'accounts' => [],

                'movements' => [],

                'movimiento_ultimo' => null,

            ];
        }


        return [

            'has_day' => true,

            'day' => $day,

            /*
             * Los totales ahora están agrupados
             * por moneda.
             *
             * Nunca sumamos monedas diferentes.
             */

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
                $this->getMovementsCount($day),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Cantidad de movimientos
    |--------------------------------------------------------------------------
    */

    public function getMovementsCount(
        FinancialDay $day
    ): int {

        return Transaction::where(
            'financial_day_id',
            $day->id
        )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Balance total por moneda
    |--------------------------------------------------------------------------
    */

    public function getTotalBalance(
        FinancialDay $day
    ): array {

        return AccountDailyBalance::with(
            'accountBalance'
        )
            ->where(
                'financial_day_id',
                $day->id
            )
            ->get()
            ->filter(
                fn ($balance) =>
                    $balance->accountBalance !== null
            )
            ->groupBy(
                fn ($balance) =>
                    $balance->accountBalance->currency
            )
            ->map(
                fn ($balances) =>
                    $balances->sum('current_balance')
            )
            ->sortKeys()
            ->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | Balance por cuenta y moneda
    |--------------------------------------------------------------------------
    */

    public function getAccountsBalance(
        FinancialDay $day
    ) {

        $dailyBalances =
            AccountDailyBalance::with([
                'account',
                'accountBalance',
            ])
                ->where(
                    'financial_day_id',
                    $day->id
                )
                ->get()
                ->filter(function ($balance) {

                    /*
                     * Protección adicional:
                     * si la cuenta o el balance de moneda
                     * ya no existe / no pertenece al tenant,
                     * no lo exponemos.
                     */

                    return
                        $balance->account !== null
                        &&
                        $balance->accountBalance !== null;

                });


        /*
         * Primero agrupamos por cuenta.
         */

        return $dailyBalances
            ->groupBy('account_id')
            ->map(function ($balances) {

                $first =
                    $balances->first();

                $account =
                    $first->account;


                return [

                    'id' =>
                        $account->id,

                    'name' =>
                        $account->name,

                    'type' =>
                        $account->type,

                    'logo' =>
                        $account->logo
                            ? Storage::url(
                                $account->logo
                            )
                            : null,


                    /*
                     * Cada cuenta puede tener
                     * varios saldos independientes.
                     */

                    'balances' =>
                        $balances
                            ->sortBy(
                                fn ($balance) =>
                                    $balance
                                        ->accountBalance
                                        ->currency
                            )
                            ->map(function ($balance) {

                                return [

                                    'account_balance_id' =>
                                        $balance
                                            ->account_balance_id,

                                    'currency' =>
                                        $balance
                                            ->accountBalance
                                            ->currency,

                                    'initial_balance' =>
                                        $balance
                                            ->initial_balance,

                                    'balance' =>
                                        $balance
                                            ->current_balance,

                                    'income' =>
                                        $this->countIncome(
                                            $balance
                                                ->account_balance_id,
                                            $balance
                                                ->financial_day_id
                                        ),

                                    'expense' =>
                                        $this->countExpense(
                                            $balance
                                                ->account_balance_id,
                                            $balance
                                                ->financial_day_id
                                        ),

                                    'reserve' =>
                                        $this->countReserve(
                                            $balance
                                                ->account_balance_id,
                                            $balance
                                                ->financial_day_id
                                        ),

                                    'movements' =>
                                        $this->countMovements(
                                            $balance
                                                ->account_balance_id,
                                            $balance
                                                ->financial_day_id
                                        ),

                                ];

                            })
                            ->values()
                            ->toArray(),

                ];

            })
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Cantidad de movimientos por moneda
    |--------------------------------------------------------------------------
    */

    private function countMovements(
        $accountBalanceId,
        $dayId
    ): int {

        return Transaction::where(
            'account_balance_id',
            $accountBalanceId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Ingresos por moneda
    |--------------------------------------------------------------------------
    */

    private function countIncome(
        $accountBalanceId,
        $dayId
    ) {

        return Transaction::where(
            'account_balance_id',
            $accountBalanceId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->where(
                'type',
                'income'
            )
            ->sum('amount');
    }


    /*
    |--------------------------------------------------------------------------
    | Egresos por moneda
    |--------------------------------------------------------------------------
    */

    private function countExpense(
        $accountBalanceId,
        $dayId
    ) {

        return Transaction::where(
            'account_balance_id',
            $accountBalanceId
        )
            ->where(
                'financial_day_id',
                $dayId
            )
            ->whereIn(
                'type',
                [
                    'expense',
                    'transfer_out',
                ]
            )
            ->sum('amount');
    }


    /*
    |--------------------------------------------------------------------------
    | Reservas por moneda
    |--------------------------------------------------------------------------
    */

    private function countReserve(
        $accountBalanceId,
        $dayId
    ) {

        return Transaction::where(
            'account_balance_id',
            $accountBalanceId
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


    /*
    |--------------------------------------------------------------------------
    | Último movimiento
    |--------------------------------------------------------------------------
    */

    public function getLastMovement(
        FinancialDay $day
    ) {

        return Transaction::with([
            'account',
            'accountBalance',
            'user',
        ])
            ->where(
                'financial_day_id',
                $day->id
            )
            ->latest('date')
            ->latest('id')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Movimientos de la jornada
    |--------------------------------------------------------------------------
    */

    public function getLatestMovements(
        FinancialDay $day
    ) {

        $movements =
            Transaction::with([
                'account',
                'accountBalance',
                'user',
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


        /*
         * IMPORTANTE:
         *
         * El saldo ahora se identifica por
         * account_balance_id y no por account_id.
         *
         * De esta manera ARS, USD, EUR, etc.
         * mantienen recorridos independientes.
         */

        $balances =
            AccountDailyBalance::where(
                'financial_day_id',
                $day->id
            )
                ->pluck(
                    'initial_balance',
                    'account_balance_id'
                )
                ->toArray();


        foreach (
            $movements as $movement
        ) {

            $accountBalanceId =
                $movement->account_balance_id;


            if (
                !array_key_exists(
                    $accountBalanceId,
                    $balances
                )
            ) {

                continue;
            }


            /*
             * Saldo antes del movimiento.
             */

            $movement->initial_balance =
                $balances[
                    $accountBalanceId
                ];


            /*
             * Ingreso suma.
             *
             * Egreso, reserva y transfer_out
             * descuentan.
             */

            if (
                $movement->type === 'income'
            ) {

                $balances[
                    $accountBalanceId
                ] += $movement->amount;

            } else {

                $balances[
                    $accountBalanceId
                ] -= $movement->amount;

            }


            /*
             * Saldo después del movimiento.
             */

            $movement->balance_after =
                $balances[
                    $accountBalanceId
                ];
        }


        return $movements
            ->sortByDesc(
                function ($movement) {

                    return $movement->date;

                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Ingresos totales de la jornada
    | AGRUPADOS POR MONEDA
    |--------------------------------------------------------------------------
    */

    public function getDayIncome(
        FinancialDay $day
    ): array {

        return Transaction::with(
            'accountBalance'
        )
            ->where(
                'financial_day_id',
                $day->id
            )
            ->where(
                'type',
                'income'
            )
            ->get()
            ->filter(
                fn ($transaction) =>
                    $transaction->accountBalance !== null
            )
            ->groupBy(
                fn ($transaction) =>
                    $transaction
                        ->accountBalance
                        ->currency
            )
            ->map(
                fn ($transactions) =>
                    $transactions->sum('amount')
            )
            ->sortKeys()
            ->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | Egresos totales de la jornada
    | AGRUPADOS POR MONEDA
    |--------------------------------------------------------------------------
    |
    | Incluye reserva porque también
    | descuenta saldo.
    |--------------------------------------------------------------------------
    */

    public function getDayExpenses(
        FinancialDay $day
    ): array {

        return Transaction::with(
            'accountBalance'
        )
            ->where(
                'financial_day_id',
                $day->id
            )
            ->whereIn(
                'type',
                [
                    'expense',
                    'reserve',
                    'transfer_out',
                ]
            )
            ->get()
            ->filter(
                fn ($transaction) =>
                    $transaction->accountBalance !== null
            )
            ->groupBy(
                fn ($transaction) =>
                    $transaction
                        ->accountBalance
                        ->currency
            )
            ->map(
                fn ($transactions) =>
                    $transactions->sum('amount')
            )
            ->sortKeys()
            ->toArray();
    }
}