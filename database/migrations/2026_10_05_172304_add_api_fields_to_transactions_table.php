<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->string('source', 20)
                ->default('manual')
                ->after('user_id');

            $table->string('external_id', 191)
                ->nullable()
                ->after('source');

            $table->unique(
                ['company_id', 'source', 'external_id'],
                'transactions_company_source_external_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropUnique(
                'transactions_company_source_external_unique'
            );

            $table->dropColumn([
                'source',
                'external_id',
            ]);
        });
    }
};