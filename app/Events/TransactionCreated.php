<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;


    public function __construct(
        public Transaction $transaction
    ) {}


    public function broadcastOn(): array
    {
        return [
            new Channel('dashboard'),
        ];
    }


    public function broadcastAs(): string
    {
        return 'transaction.created';
    }


    public function broadcastWith(): array
    {
        $account = $this->transaction->account;


        /*
        |--------------------------------------------------------------------------
        | Movimientos de la cuenta en la jornada
        |--------------------------------------------------------------------------
        */

        $transactions = Transaction::where(
            'account_id',
            $account->id
        )
            ->where(
                'financial_day_id',
                $this->transaction->financial_day_id
            );


        /*
        |--------------------------------------------------------------------------
        | Ingresos de la cuenta
        |--------------------------------------------------------------------------
        */

        $income = (clone $transactions)
            ->whereIn('type', [
                'income'
               
            ])
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Egresos de la cuenta
        |--------------------------------------------------------------------------
        |
        | Las reservas NO se incluyen acá porque las mostramos
        | de manera independiente.
        |
        */

        $expense = (clone $transactions)
            ->whereIn('type', [
                'expense',
                'transfer_out'
            ])
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Reservas de la cuenta
        |--------------------------------------------------------------------------
        */

        $reserve = (clone $transactions)
            ->where(
                'type',
                'reserve'
            )
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Saldo de la cuenta
        |--------------------------------------------------------------------------
        */

        $balance = AccountDailyBalance::where(
            'account_id',
            $account->id
        )
            ->where(
                'financial_day_id',
                $this->transaction->financial_day_id
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Saldo inicial de la cuenta
        |--------------------------------------------------------------------------
        */

        $initialBalance = $balance
            ? $balance->initial_balance
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Todos los movimientos de la jornada
        |--------------------------------------------------------------------------
        */

        $dayTransactions = Transaction::where(
            'financial_day_id',
            $this->transaction->financial_day_id
        );


        /*
        |--------------------------------------------------------------------------
        | Ingresos generales de la jornada
        |--------------------------------------------------------------------------
        */

        $dayIncome = (clone $dayTransactions)
            ->whereIn('type', [
                'income'
            ])
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Egresos generales de la jornada
        |--------------------------------------------------------------------------
        |
        | Acá SÍ incluimos las reservas porque también descuentan
        | dinero del saldo disponible.
        |
        */

        $dayExpense = (clone $dayTransactions)
            ->whereIn('type', [
                'expense',
                'reserve',
                'transfer_out'
            ])
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Reservas generales de la jornada
        |--------------------------------------------------------------------------
        */

        $dayReserve = (clone $dayTransactions)
            ->where(
                'type',
                'reserve'
            )
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Balance total de todos los bancos
        |--------------------------------------------------------------------------
        */

        $balanceTotal = AccountDailyBalance::where(
            'financial_day_id',
            $this->transaction->financial_day_id
        )
            ->sum('current_balance');


        /*
        |--------------------------------------------------------------------------
        | Respuesta para Echo / Reverb
        |--------------------------------------------------------------------------
        */

        return [


            /*
            |--------------------------------------------------------------------------
            | Jornada
            |--------------------------------------------------------------------------
            */

            'dayDate' =>
                $this->transaction->financialDay->date,


            /*
            |--------------------------------------------------------------------------
            | Movimiento
            |--------------------------------------------------------------------------
            */

            'transaction' => [

                'id' =>
                    $this->transaction->id,

                'amount' =>
                    $this->transaction->amount,

                'type' =>
                    $this->transaction->type,

                'description' =>
                    $this->transaction->description,

                'destination_bank' =>
                    $this->transaction->destination_bank,

                'date' =>
                    $this->transaction->date,

                'balance_after' =>
                    $balance
                        ? $balance->current_balance
                        : 0,

                'executed_at' =>
                    $this->transaction->executed_at,

                'user' => [

                    'id' =>
                        $this->transaction->user?->id,

                    'name' =>
                        $this->transaction->user?->name
                        ?? 'Sin registro',

                ],

            ],


            /*
            |--------------------------------------------------------------------------
            | Cuenta
            |--------------------------------------------------------------------------
            */

            'account' => [

                'id' =>
                    $account->id,

                'name' =>
                    $account->name,

                'logo' =>
                    $account->logo,

            ],


            /*
            |--------------------------------------------------------------------------
            | Datos de la cuenta
            |--------------------------------------------------------------------------
            */

            'balance' =>
                $balance
                    ? $balance->current_balance
                    : 0,

            'initialBalance' =>
                $initialBalance,

            'movements' =>
                (clone $transactions)->count(),

            'income' =>
                $income,

            'expense' =>
                $expense,

            'reserve' =>
                $reserve,


            /*
            |--------------------------------------------------------------------------
            | Datos generales de la jornada / header
            |--------------------------------------------------------------------------
            */

            'balanceTotal' =>
                $balanceTotal,

            'dayIncome' =>
                $dayIncome,

            'dayExpense' =>
                $dayExpense,

            'dayReserve' =>
                $dayReserve,

        ];
    }
}