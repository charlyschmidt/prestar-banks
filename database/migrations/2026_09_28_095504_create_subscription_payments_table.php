<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Precio del período
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'price_usd',
                10,
                2
            );

            $table->decimal(
                'exchange_rate',
                15,
                4
            );

            $table->decimal(
                'amount_ars',
                15,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Mercado Pago
            |--------------------------------------------------------------------------
            */

            $table->string(
                'provider_payment_id'
            )->nullable();

            $table->string(
                'provider_status',
                50
            )->nullable();

            $table->string(
                'provider_status_detail',
                100
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado interno
            |--------------------------------------------------------------------------
            */

            $table->string(
                'status',
                30
            )->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Período facturado
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'period_start'
            )->nullable();

            $table->timestamp(
                'period_end'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fechas del pago
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'due_at'
            )->nullable();

            $table->timestamp(
                'paid_at'
            )->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'subscription_id',
                'status',
            ]);

            $table->index([
                'company_id',
                'created_at',
            ]);

            $table->unique(
                'provider_payment_id'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'subscription_payments'
        );
    }
};