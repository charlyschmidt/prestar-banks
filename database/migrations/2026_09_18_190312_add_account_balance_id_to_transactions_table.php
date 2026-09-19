<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Agregar account_balance_id
        |--------------------------------------------------------------------------
        */

        Schema::table('transactions', function (Blueprint $table) {

            $table->foreignId('account_balance_id')
                ->nullable()
                ->after('account_id')
                ->constrained('account_balances')
                ->cascadeOnDelete();

        });


        /*
        |--------------------------------------------------------------------------
        | Vincular movimientos existentes con ARS
        |--------------------------------------------------------------------------
        |
        | La migración de account_daily_balances ya creó un
        | AccountBalance ARS para cada cuenta existente.
        |
        */

        $transactions = DB::table('transactions')
            ->select(
                'id',
                'account_id'
            )
            ->get();


        foreach ($transactions as $transaction) {

            $accountBalance = DB::table('account_balances')
                ->where(
                    'account_id',
                    $transaction->account_id
                )
                ->where(
                    'currency',
                    'ARS'
                )
                ->first();


            if (!$accountBalance) {

                throw new \RuntimeException(
                    'No se encontró el saldo ARS para la cuenta ID ' .
                    $transaction->account_id .
                    ' al migrar la transacción ID ' .
                    $transaction->id
                );

            }


            DB::table('transactions')
                ->where(
                    'id',
                    $transaction->id
                )
                ->update([
                    'account_balance_id' =>
                        $accountBalance->id
                ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Verificar que todas las transacciones tengan moneda
        |--------------------------------------------------------------------------
        */

        $withoutAccountBalance =
            DB::table('transactions')
                ->whereNull('account_balance_id')
                ->count();


        if ($withoutAccountBalance > 0) {

            throw new \RuntimeException(
                'Quedaron ' .
                $withoutAccountBalance .
                ' movimientos sin account_balance_id.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Hacer account_balance_id obligatorio
        |--------------------------------------------------------------------------
        */

        Schema::table('transactions', function (Blueprint $table) {

            $table->unsignedBigInteger('account_balance_id')
                ->nullable(false)
                ->change();

        });
    }


    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropForeign([
                'account_balance_id'
            ]);

            $table->dropColumn(
                'account_balance_id'
            );

        });
    }
};