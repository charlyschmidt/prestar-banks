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
        | 1. Idempotencia inicial
        |--------------------------------------------------------------------------
        |
        | Evitamos crear nuevamente un movimiento que
        | ya fue registrado desde la misma fuente.
        |
        */

        if ($externalId !== null && $externalId !== '') {

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
        | 2. Jornada financiera
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
        | 3. Validar tipo e importe
        |--------------------------------------------------------------------------
        */

        $type = $data['type'] ?? null;

        if (!in_array(
            $type,
            [
                'income',
                'expense',
                'reserve',
                'transfer_in',
                'transfer_out',
            ],
            true
        )) {
            throw new RuntimeException(
                'El tipo de movimiento no es válido.'
            );
        }

        if (
            !isset($data['amount']) ||
            !is_numeric($data['amount']) ||
            (float) $data['amount'] <= 0
        ) {
            throw new RuntimeException(
                'El importe del movimiento debe ser mayor que cero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Cuenta y moneda de la empresa
        |--------------------------------------------------------------------------
        */

        $accountBalance = AccountBalance::withoutGlobalScopes()
            ->where('id', $data['account_balance_id'] ?? null)
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

        /*
        |--------------------------------------------------------------------------
        | 5. Impuesto de transferencia
        |--------------------------------------------------------------------------
        |
        | Se aplica a egresos que tienen banco destino,
        | según la configuración de la cuenta.
        |
        | Una entrega en efectivo sin banco destino
        | no genera este impuesto.
        |
        */

        $transferTaxRate = 0;
        $transferTaxAmount = 0;

        $isTransfer =
            $type === 'expense'
            && !empty($data['destination_bank']);

        if ($isTransfer) {

            $transferTaxRate =
                (float) $account->transfer_tax_rate;

            $transferTaxAmount = round(
                (
                    (float) $data['amount']
                    * $transferTaxRate
                ) / 100,
                2
            );
        }

        $data['transfer_tax_rate'] =
            $transferTaxRate;

        $data['transfer_tax_amount'] =
            $transferTaxAmount;

        if ($type !== 'expense') {
            $data['destination_bank'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Datos internos
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
        | 7. Transacción financiera
        |--------------------------------------------------------------------------
        |
        | La creación del movimiento y la actualización
        | del saldo se ejecutan de forma atómica.
        |
        | Si existe una transacción exterior, como
        | la confirmación de una liquidación de cheques,
        | todo queda comprendido en esa transacción.
        |
        */

        return DB::transaction(function () use (
            $data,
            $day,
            $accountBalance,
            $companyId,
            $source,
            $externalId
        ) {

            /*
            |--------------------------------------------------------------------------
            | 8. Bloquear saldo diario
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
            | 9. Validar consistencia de cuenta
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
            | 10. Segunda comprobación de idempotencia
            |--------------------------------------------------------------------------
            |
            | Se realiza dentro de la transacción,
            | después de bloquear el saldo.
            |
            | Esto protege contra solicitudes simultáneas
            | que utilizan la misma cuenta.
            |
            */

            if ($externalId !== null && $externalId !== '') {

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
            | 11. Saldo disponible
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
                        (float) $balance->current_balance,
                        2,
                        ',',
                        '.'
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 12. Crear movimiento
            |--------------------------------------------------------------------------
            */

            $transaction = Transaction::withoutGlobalScopes()
                ->create($data);

            /*
            |--------------------------------------------------------------------------
            | 13. Actualizar saldo
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
            | 14. Guardar saldo resultante
            |--------------------------------------------------------------------------
            */

            $balance->refresh();

            $transaction->update([
                'balance_after' =>
                    $balance->current_balance,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 15. Notificación realtime después del COMMIT
            |--------------------------------------------------------------------------
            |
            | No emitimos TransactionCreated mientras
            | la transacción de base de datos esté abierta.
            |
            | Si la operación se revierte, no se enviará
            | ningún evento.
            |
            | Si existe una transacción exterior, Laravel
            | esperará a que se confirme definitivamente.
            |
            */

            $transactionId = $transaction->id;

            DB::afterCommit(function () use ($transactionId) {

                $committedTransaction =
                    Transaction::withoutGlobalScopes()
                    ->find($transactionId);

                if (!$committedTransaction) {
                    return;
                }

                $committedTransaction->load([
                    'account',
                    'accountBalance',
                    'user',
                    'financialDay',
                    'apiKey',
                ]);

                event(
                    new TransactionCreated(
                        $committedTransaction
                    )
                );
            });

            return $transaction;

        }, 3);
    }
}
