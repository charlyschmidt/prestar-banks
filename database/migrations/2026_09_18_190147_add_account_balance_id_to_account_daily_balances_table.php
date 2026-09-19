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

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->foreignId('account_balance_id')
                ->nullable()
                ->after('account_id')
                ->constrained('account_balances')
                ->cascadeOnDelete();

        });


        /*
        |--------------------------------------------------------------------------
        | Crear saldo ARS para todas las cuentas existentes
        |--------------------------------------------------------------------------
        */

        $accounts = DB::table('accounts')
            ->select('id', 'company_id')
            ->get();


        foreach ($accounts as $account) {

            $exists = DB::table('account_balances')
                ->where('account_id', $account->id)
                ->where('currency', 'ARS')
                ->exists();


            if (!$exists) {

                DB::table('account_balances')->insert([
                    'company_id' => $account->company_id,
                    'account_id' => $account->id,
                    'currency' => 'ARS',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Vincular saldos diarios existentes con ARS
        |--------------------------------------------------------------------------
        */

        $dailyBalances = DB::table('account_daily_balances')
            ->select(
                'id',
                'account_id'
            )
            ->get();


        foreach ($dailyBalances as $dailyBalance) {

            $accountBalance = DB::table('account_balances')
                ->where(
                    'account_id',
                    $dailyBalance->account_id
                )
                ->where(
                    'currency',
                    'ARS'
                )
                ->first();


            if (!$accountBalance) {

                throw new \RuntimeException(
                    'No se encontró el saldo ARS para la cuenta ID ' .
                    $dailyBalance->account_id
                );

            }


            DB::table('account_daily_balances')
                ->where(
                    'id',
                    $dailyBalance->id
                )
                ->update([
                    'account_balance_id' =>
                        $accountBalance->id
                ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Verificar migración
        |--------------------------------------------------------------------------
        */

        $withoutAccountBalance =
            DB::table('account_daily_balances')
                ->whereNull('account_balance_id')
                ->count();


        if ($withoutAccountBalance > 0) {

            throw new \RuntimeException(
                'Quedaron ' .
                $withoutAccountBalance .
                ' saldos diarios sin account_balance_id.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Hacer account_balance_id obligatorio
        |--------------------------------------------------------------------------
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->unsignedBigInteger('account_balance_id')
                ->nullable(false)
                ->change();

        });
    }


    public function down(): void
    {
        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->dropForeign([
                'account_balance_id'
            ]);

            $table->dropColumn(
                'account_balance_id'
            );

        });
    }
};