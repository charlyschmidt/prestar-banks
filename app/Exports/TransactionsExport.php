<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromCollection, WithHeadings, ShouldAutoSize
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
            ''
        ]);


        $rows->push([
            'Fecha jornada',
            $this->day->date,
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Inicio jornada',
            $this->day->opened_at,
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Exportado por',
            $this->user->username,
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Fecha exportación',
            now()->format('d/m/Y H:i'),
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
        | Saldos iniciales
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'SALDOS INICIALES',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Banco',
            'Saldo inicial',
            '',
            '',
            '',
            '',
            ''
        ]);


        $balances = AccountDailyBalance::with('account')
            ->where(
                'financial_day_id',
                $this->financialDayId
            )
            ->get();


        foreach ($balances as $balance) {

            $rows->push([
                $balance->account->name,
                $balance->initial_balance,
                '',
                '',
                '',
                '',
                ''
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Movimientos
        |--------------------------------------------------------------------------
        */

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


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
            'Banco',
            'Usuario',
            'Tipo',
            'Monto',
            'Saldo después'
        ]);


        $transactions = Transaction::with([
            'account',
            'user'
        ])
            ->where(
                'financial_day_id',
                $this->financialDayId
            )
            ->orderBy('date')
            ->orderBy('id')
            ->get();


        $balances = AccountDailyBalance::where(
            'financial_day_id',
            $this->financialDayId
        )
            ->pluck(
                'initial_balance',
                'account_id'
            );


        foreach ($transactions as $transaction) {

            if (
                in_array(
                    $transaction->type,
                    [
                        'income',
                        'transfer_in'
                    ]
                )
            ) {
                $balances[$transaction->account_id] += $transaction->amount;
            } else {
                $balances[$transaction->account_id] -= $transaction->amount;
            }


            $rows->push([

                Carbon::parse($transaction->date)
                    ->format('d/m/Y H:i'),

                $transaction->description
                    ?? 'Sin descripción',

                $transaction->account->name,

                $transaction->user?->name
                    ?? 'Sin registro',

                in_array(
                    $transaction->type,
                    [
                        'income',
                        'transfer_in'
                    ]
                )
                    ? 'Ingreso'
                    : 'Egreso',

                $transaction->amount,

                $balances[$transaction->account_id],

            ]);
        }


        return $rows;
    }


    public function headings(): array
    {
        return [];
    }
}