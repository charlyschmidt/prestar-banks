<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_days', function (Blueprint $table) {

            /*
             * Antes:
             * UNIQUE(date)
             *
             * Ahora:
             * UNIQUE(company_id, date)
             */

            $table->dropUnique(
                'financial_days_date_unique'
            );

            $table->unique(
                ['company_id', 'date'],
                'financial_days_company_date_unique'
            );
        });
    }


    public function down(): void
    {
        Schema::table('financial_days', function (Blueprint $table) {

            $table->dropUnique(
                'financial_days_company_date_unique'
            );

            $table->unique(
                'date',
                'financial_days_date_unique'
            );
        });
    }
};