<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class TransactionsExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected int $financialDayId;

    protected $day;

    protected $user;


    public function __construct(
        int $financialDayId,
        $day,
        $user
    ) {
        $this->financialDayId = $financialDayId;
        $this->day = $day;
        $this->user = $user;
    }


    public function collection(): Collection
    {
        $rows = collect();


        /*
        |--------------------------------------------------------------------------
        | Saldos diarios
        |--------------------------------------------------------------------------
        |
        | Cada registro corresponde a una moneda concreta de una cuenta.
        |
        */

        $dailyBalances = AccountDailyBalance::with([
            'account',
            'accountBalance',
        ])
            ->where(
                'financial_day_id',
                $this->financialDayId
            )
            ->get()
            ->sortBy([
                fn($a, $b) =>
                strcmp(
                    $a->account?->name ?? '',
                    $b->account?->name ?? ''
                ),

                fn($a, $b) =>
                strcmp(
                    $a->accountBalance?->currency ?? '',
                    $b->accountBalance?->currency ?? ''
                ),
            ])
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Encabezado del reporte
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'PRESTAR FINANZAS',
            '',
            '',
            '',
            '',
            '',
            '',
        ]);


        $rows->push([
            'Fecha jornada',
            Carbon::parse($this->day->date)
                ->format('d/m/Y'),
            '',
            '',
            '',
            '',
            '',
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
            '',
        ]);


        $rows->push([
            'Exportado por',
            $this->user->name,
            '',
            '',
            '',
            '',
            '',
        ]);


        $rows->push([
            'Fecha exportación',
            now()->format('d/m/Y H:i'),
            '',
            '',
            '',
            '',
            '',
        ]);


        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Saldos de la jornada
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'SALDOS DE LA JORNADA',
            '',
            '',
            '',
            '',
            '',
            '',
        ]);


        $rows->push([
            'Banco',
            'Moneda',
            'Saldo inicial',
            'Saldo actual',
            '',
            '',
            '',
        ]);


        foreach ($dailyBalances as $dailyBalance) {

            $rows->push([

                $dailyBalance->account?->name
                    ?? 'Cuenta eliminada',

                $dailyBalance->accountBalance?->currency
                    ?? '---',

                (float) $dailyBalance->initial_balance,

                (float) $dailyBalance->current_balance,

                '',
                '',
                '',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Separador
        |--------------------------------------------------------------------------
        */

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            '',
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
            '',
        ]);


        $rows->push([
            'Fecha',
            'Concepto',
            'Banco',
            'Usuario',
            'Moneda',
            'Tipo',
            'Monto',
            'Saldo después',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Transacciones
        |--------------------------------------------------------------------------
        */

        $transactions = Transaction::with([
            'account',
            'accountBalance',
            'user',
        ])
            ->where(
                'financial_day_id',
                $this->financialDayId
            )
            ->orderBy('date')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Saldos corrientes por AccountBalance
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | [
        |     1 => 1000000, // Provincia ARS
        |     2 => 2000,    // Provincia USD
        |     3 => 500000,  // Santander ARS
        | ]
        |
        | De esta manera nunca mezclamos monedas.
        |
        */

        $runningBalances = $dailyBalances
            ->mapWithKeys(
                function ($dailyBalance) {

                    return [

                        $dailyBalance->account_balance_id =>
                        (float) $dailyBalance->initial_balance,

                    ];
                }
            )
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Construir movimientos
        |--------------------------------------------------------------------------
        */

        foreach ($transactions as $transaction) {

            $accountBalanceId =
                $transaction->account_balance_id;


            /*
            |--------------------------------------------------------------------------
            | Protección ante registros antiguos
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
            | Actualizar saldo de ESA moneda
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
            | Tipo
            |--------------------------------------------------------------------------
            */

            $type = match ($transaction->type) {

                'income' =>
                'Ingreso',

                'reserve' =>
                'Reserva',

                default =>
                'Egreso',

            };


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

                $transaction->account?->name
                    ?? 'Cuenta eliminada',

                $transaction->user?->name
                    ?? 'Sin registro',

                $transaction->accountBalance?->currency
                    ?? '---',

                $type,

                (float) $transaction->amount,

                $runningBalances[$accountBalanceId],

            ]);
        }


        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    |
    | El reporte ya construye manualmente todos sus encabezados dentro
    | de collection(), por eso no agregamos una fila adicional.
    |
    */

    public function headings(): array
    {
        return [];
    }
}