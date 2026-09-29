<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Plan contratado
            |--------------------------------------------------------------------------
            */

            $table->string('plan', 20);

            $table->decimal('price_usd', 10, 2);

            /*
            |--------------------------------------------------------------------------
            | Conversión utilizada para el cobro
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'exchange_rate',
                15,
                4
            )->nullable();

            $table->decimal(
                'amount_ars',
                15,
                2
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Proveedor de pagos
            |--------------------------------------------------------------------------
            */

            $table->string(
                'provider',
                30
            )->default('mercadopago');

            $table->string(
                'provider_subscription_id'
            )->nullable();

            $table->string(
                'provider_status',
                50
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado interno AERIA
            |--------------------------------------------------------------------------
            */

            $table->string(
                'status',
                30
            )->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'started_at'
            )->nullable();

            $table->timestamp(
                'next_billing_at'
            )->nullable();

            $table->timestamp(
                'cancelled_at'
            )->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'company_id',
                'status',
            ]);

            $table->unique(
                'provider_subscription_id'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'subscriptions'
        );
    }
};