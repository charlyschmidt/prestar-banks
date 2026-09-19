<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Crear índice normal para la foreign key
        |--------------------------------------------------------------------------
        |
        | El índice UNIQUE viejo:
        |
        | financial_day_id + account_id
        |
        | estaba siendo utilizado por MySQL para soportar la FK de
        | financial_day_id.
        |
        | Antes de eliminarlo creamos un índice independiente.
        |
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->index(
                'financial_day_id',
                'account_daily_balances_financial_day_id_index'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | 2. Eliminar UNIQUE viejo
        |--------------------------------------------------------------------------
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->dropUnique(
                'account_daily_balances_financial_day_id_account_id_unique'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | 3. Crear UNIQUE multimoneda
        |--------------------------------------------------------------------------
        |
        | Una jornada puede tener varias monedas para la misma cuenta.
        |
        | La unicidad ahora corresponde a:
        |
        | financial_day_id + account_balance_id
        |
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->unique(
                [
                    'financial_day_id',
                    'account_balance_id',
                ],
                'account_daily_balances_day_account_balance_unique'
            );

        });
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Eliminar UNIQUE multimoneda
        |--------------------------------------------------------------------------
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->dropUnique(
                'account_daily_balances_day_account_balance_unique'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Restaurar UNIQUE anterior
        |--------------------------------------------------------------------------
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->unique(
                [
                    'financial_day_id',
                    'account_id',
                ],
                'account_daily_balances_financial_day_id_account_id_unique'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Eliminar índice auxiliar
        |--------------------------------------------------------------------------
        */

        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->dropIndex(
                'account_daily_balances_financial_day_id_index'
            );

        });
    }
};