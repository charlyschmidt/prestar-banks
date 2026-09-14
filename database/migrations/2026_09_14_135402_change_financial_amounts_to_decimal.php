<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('balance_after', 15, 2)
                ->nullable()
                ->change();
        });

        Schema::table('account_daily_balances', function (Blueprint $table) {
            $table->decimal('initial_balance', 15, 2)->change();
            $table->decimal('current_balance', 15, 2)->change();
        });
    }

    public function down(): void
    {
        //
    }
};