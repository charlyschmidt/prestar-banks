<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->timestamp('executed_at')
                ->nullable()
                ->after('destination_bank');

            $table->foreignId('executed_by')
                ->nullable()
                ->after('executed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropForeign([
                'executed_by'
            ]);

            $table->dropColumn([
                'executed_at',
                'executed_by'
            ]);
        });
    }
};