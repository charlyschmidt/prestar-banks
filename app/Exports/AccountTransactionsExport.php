<?php

namespace App\Exports;

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


        $accountBalance = AccountDailyBalance::with('account')
            ->where(
                'financial_day_id',
                $this->day->id
            )
            ->where(
                'account_id',
                $this->accountId
            )
            ->first();


        $rows->push([
            'PRESTAR FINANZAS',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Banco',
            $accountBalance->account->name,
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
            ''
        ]);


        $rows->push([
            'Inicio jornada',
            $this->day->opened_at,
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
            ''
        ]);


        $rows->push([
            '',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'SALDO INICIAL',
            '',
            '',
            '',
            '',
            ''
        ]);


        $rows->push([
            'Saldo inicial',
            $accountBalance->initial_balance,
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
            ''
        ]);


        $rows->push([
            'MOVIMIENTOS',
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
            'Tipo',
            'Monto',
            'Saldo después'
        ]);


        $balance = $accountBalance->initial_balance;


        $transactions = Transaction::with('user')
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

            if (
                in_array(
                    $transaction->type,
                    [
                        'income'
                    ]
                )
            ) {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }


            $rows->push([

                Carbon::parse($transaction->date)
                    ->format('d/m/Y H:i'),

                $transaction->description
                    ?? 'Sin descripción',

                $transaction->user?->name
                    ?? 'Sin registro',

                in_array(
                    $transaction->type,
                    [
                        'income'
                    ]
                )
                    ? 'Ingreso'
                    : 'Egreso',

                $transaction->amount,

                $balance

            ]);
        }


        return $rows;
    }
}