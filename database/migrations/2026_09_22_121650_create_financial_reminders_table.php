<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reminders', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Contexto
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Cuenta / moneda opcionales
            |--------------------------------------------------------------------------
            */

            $table->foreignId('account_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('account_balance_id')
                ->nullable()
                ->constrained('account_balances')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Recordatorio
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->decimal(
                'amount',
                15,
                2
            )->nullable();

            $table->dateTime('scheduled_at');


            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->string('status', 20)
                ->default('pending');

            $table->timestamp('dismissed_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'company_id',
                'user_id',
                'status',
                'scheduled_at',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'financial_reminders'
        );
    }
}; 