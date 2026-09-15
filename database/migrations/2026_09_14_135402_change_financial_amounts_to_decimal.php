<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->decimal(
                'initial_balance',
                15,
                2
            )->change();

            $table->decimal(
                'current_balance',
                15,
                2
            )->change();

        });


        Schema::table('transactions', function (Blueprint $table) {

            $table->decimal(
                'amount',
                15,
                2
            )->change();

            $table->decimal(
                'balance_after',
                15,
                2
            )
                ->nullable()
                ->change();

        });
    }


    public function down(): void
    {
        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->decimal(
                'initial_balance',
                15,
                0
            )->change();

            $table->decimal(
                'current_balance',
                15,
                0
            )->change();

        });


        Schema::table('transactions', function (Blueprint $table) {

            $table->decimal(
                'amount',
                15,
                0
            )->change();

            $table->decimal(
                'balance_after',
                15,
                0
            )
                ->nullable()
                ->change();

        });
    }
};