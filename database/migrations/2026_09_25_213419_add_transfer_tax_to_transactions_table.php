<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->decimal('transfer_tax_rate', 8, 4)
                ->default(0)
                ->after('amount');

            $table->decimal('transfer_tax_amount', 15, 2)
                ->default(0)
                ->after('transfer_tax_rate');

        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropColumn([
                'transfer_tax_rate',
                'transfer_tax_amount',
            ]);

        });
    }
};