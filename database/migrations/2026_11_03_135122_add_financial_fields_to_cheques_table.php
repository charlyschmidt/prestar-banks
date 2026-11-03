
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los cálculos financieros individuales
     * a cada cheque.
     *
     * Los campos son nullable para mantener
     * compatibilidad con los cheques históricos.
     */
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PLAZOS
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('calendar_days')
                ->nullable();

            $table->unsignedInteger('clearing_days')
                ->nullable();

            $table->unsignedInteger('term_days')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | PORCENTAJES
            |--------------------------------------------------------------------------
            */

            $table->decimal('annual_rate', 8, 4)
                ->nullable();

            $table->decimal('cheque_tax_rate', 8, 4)
                ->nullable();

            $table->decimal('gross_income_rate', 8, 4)
                ->nullable();

            $table->decimal('vat_rate', 8, 4)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | IMPORTES FINANCIEROS
            |--------------------------------------------------------------------------
            */

            $table->decimal('interest_amount', 18, 2)
                ->nullable();

            $table->decimal('cheque_tax_amount', 18, 2)
                ->nullable();

            $table->decimal('gross_income_amount', 18, 2)
                ->nullable();

            $table->decimal('vat_amount', 18, 2)
                ->nullable();

            $table->decimal('total_discount', 18, 2)
                ->nullable();

            $table->decimal('net_amount', 18, 2)
                ->nullable();

        });
    }

    /**
     * Revierte exclusivamente las columnas nuevas.
     */
    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {

            $table->dropColumn([
                'calendar_days',
                'clearing_days',
                'term_days',

                'annual_rate',
                'cheque_tax_rate',
                'gross_income_rate',
                'vat_rate',

                'interest_amount',
                'cheque_tax_amount',
                'gross_income_amount',
                'vat_amount',
                'total_discount',
                'net_amount',
            ]);

        });
    }
};
