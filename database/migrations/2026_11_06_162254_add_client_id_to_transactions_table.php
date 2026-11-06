
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Agregar cliente a movimientos
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->restrictOnDelete();

            $table->index(
                ['company_id', 'client_id'],
                'transactions_company_client_index'
            );

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Revertir migración
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropIndex(
                'transactions_company_client_index'
            );

            $table->dropConstrainedForeignId(
                'client_id'
            );

        });
    }
};
