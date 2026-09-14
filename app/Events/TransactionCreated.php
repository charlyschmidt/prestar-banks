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
        | Movimientos de la jornada
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
                'income',
                'transfer_in'
            ])
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Egresos de la cuenta
        |--------------------------------------------------------------------------
        */

        $expense = (clone $transactions)
            ->whereIn('type', [
                'expense',
                'transfer_out'
            ])
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
        | Totales generales de la jornada
        |--------------------------------------------------------------------------
        */

        $dayTransactions = Transaction::where(
            'financial_day_id',
            $this->transaction->financial_day_id
        );


        $dayIncome = (clone $dayTransactions)
            ->whereIn('type', [
                'income',
                'transfer_in'
            ])
            ->sum('amount');


        $dayExpense = (clone $dayTransactions)
            ->whereIn('type', [
                'expense',
                'transfer_out'
            ])
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
            | Movimiento
            |--------------------------------------------------------------------------
            */
            'dayDate' =>
            $this->transaction->financialDay->date,

            'transaction' => [

                'id' =>
                $this->transaction->id,

                'amount' =>
                $this->transaction->amount,

                'type' =>
                $this->transaction->type,

                'description' =>
                $this->transaction->description,

                'date' =>
                $this->transaction->date,

                'balance_after' =>
                $balance
                    ? $balance->current_balance
                    : 0,
                'user' => [
                    'id' => $this->transaction->user?->id,
                    'name' => $this->transaction->user?->name ?? 'Sin registro',
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

            'movements' => (clone $transactions)->count(),

            'income' =>
            $income,

            'expense' =>
            $expense,


            /*
            |--------------------------------------------------------------------------
            | Datos generales del header
            |--------------------------------------------------------------------------
            */

            'balanceTotal' =>
            $balanceTotal,

            'dayIncome' =>
            $dayIncome,

            'dayExpense' =>
            $dayExpense,

        ];
    }
}
