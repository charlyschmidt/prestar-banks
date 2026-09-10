<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('account_daily_balances', function (Blueprint $table) {

            $table->id();


            $table->foreignId('financial_day_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();


            // saldo al iniciar el día
            $table->decimal('initial_balance', 12, 2)
                ->default(0);


            // saldo actual de la jornada
            $table->decimal('current_balance', 12, 2)
                ->default(0);


            $table->timestamps();


            // un banco solo puede tener un saldo por jornada
            $table->unique([
                'financial_day_id',
                'account_id'
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('account_daily_balances');
    }

};