<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
        });


        Schema::table('financial_days', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
        });


        Schema::table('transactions', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
        });


        Schema::table('account_daily_balances', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
        });
    }


    public function down(): void
    {
        Schema::table('account_daily_balances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });


        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });


        Schema::table('financial_days', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });


        Schema::table('accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};