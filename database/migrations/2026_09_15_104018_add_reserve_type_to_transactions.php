<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN type ENUM(
                'income',
                'expense',
                'reserve',
                'transfer_in',
                'transfer_out'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        /*
         * Antes de quitar 'reserve' del ENUM,
         * no puede existir ningún movimiento
         * con ese tipo.
         */
        DB::table('transactions')
            ->where('type', 'reserve')
            ->update([
                'type' => 'expense'
            ]);

        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN type ENUM(
                'income',
                'expense',
                'transfer_in',
                'transfer_out'
            ) NOT NULL
        ");
    }
};