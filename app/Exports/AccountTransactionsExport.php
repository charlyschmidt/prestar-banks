<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AccountTransactionsExport implements FromCollection, ShouldAutoSize
{
    protected $accountId;
    protected $day;
    protected $user;


    public function __construct(
        $accountId,
        $day,
        $user
    ) {
        $this->accountId = $accountId;
        $this->day = $day;
        $this->user = $user;
    }


    public function collection(): Collection
    {
        $rows = collect();


        /*
        |--------------------------------------------------------------------------
        | Cuenta
        |--------------------------------------------------------------------------
        */

        $account = Account::findOrFail(
            $this->accountId
        );


        /*
        |--------------------------------------------------------------------------
        | Saldos de la jornada por moneda
        |--------------------------------------------------------------------------
        */

        $dailyBalances = AccountDailyBalance::with(
            'accountBalance'
        )
            ->where(
                'financial_day_id',
                $this->day->id
            )
            ->where(
                'account_id',
                $this->accountId
            )
            ->get()
            ->sortBy(
                fn($balance) =>
                $balance->accountBalance?->currency
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Encabezado
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'PRESTAR FINANZAS',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Banco',
            $account->name,
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Fecha jornada',
            Carbon::parse($this->day->date)
                ->format('d/m/Y'),
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Inicio jornada',
            $this->day->opened_at
                ? Carbon::parse($this->day->opened_at)
                    ->format('d/m/Y H:i')
                : '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Exportado por',
            $this->user->name,
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        /*
        |--------------------------------------------------------------------------
        | Saldos por moneda
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'SALDOS DE LA JORNADA',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Moneda',
            'Saldo inicial',
            'Saldo actual',
            '',
            '',
            '',
            ''
        ]);


        foreach ($dailyBalances as $dailyBalance) {

            $rows->push([

                $dailyBalance->accountBalance?->currency
                    ?? '---',

                (float) $dailyBalance->initial_balance,

                (float) $dailyBalance->current_balance,

                '',
                '',
                '',
                ''

            ]);
        }


        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        /*
        |--------------------------------------------------------------------------
        | Movimientos
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'MOVIMIENTOS',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Fecha',
            'Concepto',
            'Usuario',
            'Moneda',
            'Tipo',
            'Monto',
            'Saldo después'
        ]);


        /*
        |--------------------------------------------------------------------------
        | Saldo corriente independiente por AccountBalance
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Nunca mezclamos ARS, USD, EUR, etc.
        |
        | Cada AccountBalance mantiene su propio saldo durante
        | la reconstrucción del historial.
        |
        */

        $runningBalances = $dailyBalances
            ->mapWithKeys(function ($dailyBalance) {

                return [

                    $dailyBalance->account_balance_id =>
                    (float) $dailyBalance->initial_balance

                ];
            })
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Transacciones
        |--------------------------------------------------------------------------
        */

        $transactions = Transaction::with([
            'user',
            'accountBalance',
        ])
            ->where(
                'financial_day_id',
                $this->day->id
            )
            ->where(
                'account_id',
                $this->accountId
            )
            ->orderBy('date')
            ->orderBy('id')
            ->get();


        foreach ($transactions as $transaction) {

            $accountBalanceId =
                $transaction->account_balance_id;


            /*
            |--------------------------------------------------------------------------
            | Saldo inicial de esa moneda
            |--------------------------------------------------------------------------
            */

            if (
                !array_key_exists(
                    $accountBalanceId,
                    $runningBalances
                )
            ) {

                $runningBalances[$accountBalanceId] = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | Aplicar movimiento
            |--------------------------------------------------------------------------
            */

            if ($transaction->type === 'income') {

                $runningBalances[$accountBalanceId] +=
                    (float) $transaction->amount;
            } else {

                $runningBalances[$accountBalanceId] -=
                    (float) $transaction->amount;
            }


            /*
            |--------------------------------------------------------------------------
            | Fila
            |--------------------------------------------------------------------------
            */

            $rows->push([

                Carbon::parse($transaction->date)
                    ->format('d/m/Y H:i'),

                $transaction->description
                    ?? 'Sin descripción',

                $transaction->user?->name
                    ?? 'Sin registro',

                $transaction->accountBalance?->currency
                    ?? '---',

                match ($transaction->type) {

                    'income' =>
                    'Ingreso',

                    'reserve' =>
                    'Reserva',

                    default =>
                    'Egreso',

                },

                (float) $transaction->amount,

                $runningBalances[$accountBalanceId]

            ]);
        }


        return $rows;
    }
}