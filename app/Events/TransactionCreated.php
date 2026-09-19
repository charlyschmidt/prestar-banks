<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\AccountDailyBalance;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class TransactionCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;


    public function __construct(
        public Transaction $transaction
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Canal
    |--------------------------------------------------------------------------
    |
    | Cada empresa escucha únicamente su propio dashboard.
    |
    */

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'dashboard.' . $this->transaction->company_id
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Nombre del evento
    |--------------------------------------------------------------------------
    */

    public function broadcastAs(): string
    {
        return 'transaction.created';
    }


    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    */

    public function broadcastWith(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Relaciones
        |--------------------------------------------------------------------------
        */

        $this->transaction->loadMissing([
            'account',
            'accountBalance',
            'user',
            'financialDay',
        ]);


        $account =
            $this->transaction->account;


        $accountBalance =
            $this->transaction->accountBalance;


        /*
        |--------------------------------------------------------------------------
        | Saldo diario de ESTA moneda
        |--------------------------------------------------------------------------
        |
        | Ya no buscamos solamente por account_id.
        |
        | Banco Provincia ARS y Banco Provincia USD son balances
        | completamente independientes.
        |
        */

        $dailyBalance = AccountDailyBalance::where(
            'financial_day_id',
            $this->transaction->financial_day_id
        )
            ->where(
                'account_balance_id',
                $this->transaction->account_balance_id
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Payload
        |--------------------------------------------------------------------------
        |
        | El evento solamente informa qué movimiento cambió.
        |
        | dashboard.js posteriormente ejecuta syncDashboard(), que obtiene
        | los totales completos y agrupados correctamente por moneda.
        |
        */

        return [

            /*
            |--------------------------------------------------------------------------
            | Empresa
            |--------------------------------------------------------------------------
            */

            'companyId' =>
            $this->transaction->company_id,


            /*
            |--------------------------------------------------------------------------
            | Jornada
            |--------------------------------------------------------------------------
            */

            'dayDate' =>
            $this->transaction
                ->financialDay?->date,


            /*
            |--------------------------------------------------------------------------
            | Movimiento
            |--------------------------------------------------------------------------
            */

            'transaction' => [

                'id' =>
                $this->transaction->id,

                'account_balance_id' =>
                $this->transaction->account_balance_id,

                'currency' =>
                $accountBalance?->currency,

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

                'initial_balance' =>
                $dailyBalance?->initial_balance
                ?? 0,

                'balance_after' =>
                $dailyBalance?->current_balance
                ?? 0,

                'executed_at' =>
                $this->transaction->executed_at,


                /*
                |--------------------------------------------------------------------------
                | Usuario
                |--------------------------------------------------------------------------
                */

                'user' => [

                    'id' =>
                    $this->transaction
                        ->user?->id,

                    'name' =>
                    $this->transaction
                        ->user?->name
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
                $account?->id,

                'name' =>
                $account?->name,

                'logo' =>
                $account?->logo,

            ],


            /*
            |--------------------------------------------------------------------------
            | Moneda / AccountBalance
            |--------------------------------------------------------------------------
            */

            'account_balance' => [

                'id' =>
                $accountBalance?->id,

                'currency' =>
                $accountBalance?->currency,

            ],


            /*
            |--------------------------------------------------------------------------
            | Compatibilidad con dashboard.js
            |--------------------------------------------------------------------------
            */

            'accountBalanceId' =>
            $this->transaction->account_balance_id,

            'currency' =>
            $accountBalance?->currency,

            'balance' =>
            $dailyBalance?->current_balance
            ?? 0,

            'initialBalance' =>
            $dailyBalance?->initial_balance
            ?? 0,

        ];
    }
}