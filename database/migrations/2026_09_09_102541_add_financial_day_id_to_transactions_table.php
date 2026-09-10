<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->foreignId('financial_day_id')
                ->nullable()
                ->after('account_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }


    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropForeign(['financial_day_id']);
            $table->dropColumn('financial_day_id');
        });
    }
};
