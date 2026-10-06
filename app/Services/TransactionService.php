<?php

namespace App\Services;

use App\Events\TransactionCreated;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\AccountDailyBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    public function create(
        array $data,
        int $companyId,
        ?int $userId = null,
        string $source = 'manual',
        ?string $externalId = null,
        ?int $apiKeyId = null
    ): Transaction {

        /*
        |--------------------------------------------------------------------------
        | Idempotencia
        |--------------------------------------------------------------------------
        */

        if ($externalId) {

            $existing = Transaction::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source', $source)
                ->where('external_id', $externalId)
                ->first();

            if ($existing) {
                return $existing;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Jornada actual de la empresa
        |--------------------------------------------------------------------------
        */

        $day = app(FinancialDayService::class)
            ->current($companyId);

        if (!$day) {
            throw new RuntimeException(
                'No hay una jornada abierta. Primero iniciá el día.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta + moneda
        |--------------------------------------------------------------------------
        */

        $accountBalance = AccountBalance::withoutGlobalScopes()
            ->where('id', $data['account_balance_id'])
            ->where('company_id', $companyId)
            ->first();

        if (!$accountBalance) {
            throw new RuntimeException(
                'El saldo o moneda seleccionada no existe.'
            );
        }


        $account = Account::withoutGlobalScopes()
            ->where('id', $accountBalance->account_id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            throw new RuntimeException(
                'La cuenta seleccionada no existe.'
            );
        }

        if (!$accountBalance) {
            throw new RuntimeException(
                'La moneda seleccionada no pertenece a la cuenta.'
            );
        }


        $account = Account::withoutGlobalScopes()
            ->where('id', $accountBalance->account_id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            throw new RuntimeException(
                'La cuenta seleccionada no existe.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Impuesto de transferencia
        |--------------------------------------------------------------------------
        */

        $transferTaxRate = 0;
        $transferTaxAmount = 0;

        $isTransfer =
            $data['type'] === 'expense'
            && !empty($data['destination_bank']);

        if ($isTransfer) {

            $transferTaxRate =
                (float) $account->transfer_tax_rate;

            $transferTaxAmount = round(
                ((float) $data['amount'] * $transferTaxRate) / 100,
                2
            );
        }

        $data['transfer_tax_rate'] =
            $transferTaxRate;

        $data['transfer_tax_amount'] =
            $transferTaxAmount;


        if ($data['type'] !== 'expense') {
            $data['destination_bank'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Datos internos
        |--------------------------------------------------------------------------
        */

        $data['company_id'] = $companyId;
        $data['user_id'] = $userId;

        $data['source'] = $source;
        $data['external_id'] = $externalId;

        $data['financial_day_id'] = $day->id;

        $data['account_id'] =
            $accountBalance->account_id;

        $data['account_balance_id'] =
            $accountBalance->id;

        $data['api_key_id'] = $apiKeyId;

        /*
        |--------------------------------------------------------------------------
        | Crear + actualizar saldo
        |--------------------------------------------------------------------------
        */

        $transaction = DB::transaction(
            function () use (
                $data,
                $day,
                $accountBalance,
                $companyId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Bloquear saldo
                |--------------------------------------------------------------------------
                */

                $balance = AccountDailyBalance::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('financial_day_id', $day->id)
                    ->where(
                        'account_balance_id',
                        $accountBalance->id
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$balance) {
                    throw new RuntimeException(
                        'La moneda seleccionada no pertenece a la jornada actual.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Consistencia
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $balance->account_id !==
                    (int) $data['account_id']
                ) {
                    throw new RuntimeException(
                        'El saldo seleccionado no pertenece a la cuenta.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Saldo disponible
                |--------------------------------------------------------------------------
                */

                $isExpense = in_array(
                    $data['type'],
                    [
                        'expense',
                        'reserve',
                    ],
                    true
                );


                $amountToDebit =
                    (float) $data['amount']
                    + (float) $data['transfer_tax_amount'];


                if (
                    $isExpense &&
                    (float) $balance->current_balance <
                    $amountToDebit
                ) {

                    throw new RuntimeException(
                        'Saldo insuficiente. Disponible: '
                            . $accountBalance->currency
                            . ' '
                            . number_format(
                                $balance->current_balance,
                                2,
                                ',',
                                '.'
                            )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Crear movimiento
                |--------------------------------------------------------------------------
                */

                $transaction =
                    Transaction::withoutGlobalScopes()
                    ->create($data);


                /*
                |--------------------------------------------------------------------------
                | Actualizar saldo
                |--------------------------------------------------------------------------
                */

                if ($transaction->type === 'income') {

                    $balance->increment(
                        'current_balance',
                        $transaction->amount
                    );
                } else {

                    $amountToDebit =
                        (float) $transaction->amount
                        + (float) $transaction->transfer_tax_amount;

                    $balance->decrement(
                        'current_balance',
                        $amountToDebit
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Saldo resultante
                |--------------------------------------------------------------------------
                */

                $balance->refresh();

                $transaction->update([
                    'balance_after' =>
                    $balance->current_balance,
                ]);


                return $transaction;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Realtime
        |--------------------------------------------------------------------------
        */

        $transaction->refresh();

        $transaction->load([
            'account',
            'accountBalance',
            'user',
            'financialDay',
            'apiKey',
        ]);

        event(
            new TransactionCreated(
                $transaction
            )
        );


        return $transaction;
    }
}
