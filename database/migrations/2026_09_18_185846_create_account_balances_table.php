<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_balances', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Moneda ISO 4217
            |--------------------------------------------------------------------------
            |
            | ARS
            | USD
            | EUR
            | BRL
            | etc.
            |
            */

            $table->char('currency', 3);

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Una cuenta sólo puede tener un saldo por moneda
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'account_id',
                'currency'
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};