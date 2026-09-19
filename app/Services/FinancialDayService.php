<?php

namespace App\Services;

use App\Models\AccountBalance;
use App\Models\FinancialDay;
use App\Models\AccountDailyBalance;
use Illuminate\Support\Facades\DB;

class FinancialDayService
{

    /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

    public function current()
    {
        return FinancialDay::whereDate(
            'date',
            today()
        )
            ->where(
                'status',
                'open'
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Buscar jornada por fecha
    |--------------------------------------------------------------------------
    */

    public function findByDate($date)
    {
        return FinancialDay::whereDate(
            'date',
            $date
        )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Abrir jornada
    |--------------------------------------------------------------------------
    */

    public function open(array $balances)
    {
        $existing = FinancialDay::whereDate(
            'date',
            today()
        )
            ->where(
                'status',
                'open'
            )
            ->first();


        if ($existing) {
            return $existing;
        }


        return DB::transaction(
            function () use ($balances) {

                $day = FinancialDay::create([

                    'date' =>
                        today(),

                    'status' =>
                        'open',

                    'opened_by' =>
                        auth()->id(),

                    'opened_at' =>
                        now(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Saldos iniciales
                |--------------------------------------------------------------------------
                |
                | $balances ahora tiene esta estructura:
                |
                | [
                |     account_balance_id => amount,
                |     account_balance_id => amount,
                | ]
                |
                | Ejemplo:
                |
                | [
                |     1 => 1500000, // Santander ARS
                |     2 => 12000,   // Santander USD
                |     3 => 3500,    // Santander EUR
                | ]
                |
                */

                foreach (
                    $balances as $accountBalanceId => $amount
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Validar saldo / moneda
                    |--------------------------------------------------------------------------
                    |
                    | AccountBalance utiliza BelongsToCompany.
                    | Por lo tanto solamente podremos encontrar
                    | saldos pertenecientes a la empresa activa.
                    |
                    */

                    $accountBalance =
                        AccountBalance::with('account')
                            ->findOrFail(
                                $accountBalanceId
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Crear saldo diario
                    |--------------------------------------------------------------------------
                    */

                    AccountDailyBalance::updateOrCreate(

                        [
                            'financial_day_id' =>
                                $day->id,

                            'account_balance_id' =>
                                $accountBalance->id,
                        ],

                        [
                            /*
                             * Conservamos account_id por
                             * compatibilidad con el sistema
                             * actual.
                             */

                            'account_id' =>
                                $accountBalance->account_id,

                            'initial_balance' =>
                                $amount,

                            'current_balance' =>
                                $amount,
                        ]

                    );
                }


                return $day;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar jornada
    |--------------------------------------------------------------------------
    */

    public function close(
        FinancialDay $day
    ) {

        if (
            $day->status === 'closed'
        ) {
            return $day;
        }


        $day->update([

            'status' =>
                'closed',

            'closed_at' =>
                now(),

        ]);


        return $day;
    }
}