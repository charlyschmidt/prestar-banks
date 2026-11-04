
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'cheque_liquidation_deliveries',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Movimiento financiero individual
                |--------------------------------------------------------------------------
                |
                | Cada entrega monetaria puede generar
                | su propio movimiento financiero.
                |
                | Las entregas de cheques de terceros
                | no generan un egreso de efectivo.
                |
                */

                $table->foreignId('transaction_id')
                    ->nullable()
                    ->constrained('transactions')
                    ->restrictOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Evitar entregar dos veces el mismo cheque
                |--------------------------------------------------------------------------
                |
                | MySQL permite múltiples NULL en un
                | índice UNIQUE.
                |
                | Por eso las entregas en efectivo y
                | transferencia no se ven afectadas.
                |
                */

                $table->unique(
                    'delivered_cheque_id',
                    'cheque_delivery_unique_cheque'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'cheque_liquidation_deliveries',
            function (Blueprint $table) {

                $table->dropUnique(
                    'cheque_delivery_unique_cheque'
                );

                $table->dropForeign([
                    'transaction_id'
                ]);

                $table->dropColumn(
                    'transaction_id'
                );
            }
        );
    }
};
