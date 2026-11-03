
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar información de confirmación financiera.
     */
    public function up(): void
    {
        Schema::table('cheque_liquidations', function (Blueprint $table) {

            // Movimiento de egreso generado al confirmar.
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('status')
                ->constrained('transactions')
                ->restrictOnDelete();

            // Fecha y hora de entrega del dinero al cliente.
            $table->timestamp('confirmed_at')
                ->nullable()
                ->after('transaction_id');

            // Evita vincular un mismo movimiento a varias liquidaciones.
            $table->unique(
                'transaction_id',
                'cheque_liquidations_transaction_unique'
            );
        });
    }

    /**
     * Revertir únicamente los campos incorporados.
     */
    public function down(): void
    {
        Schema::table('cheque_liquidations', function (Blueprint $table) {

            $table->dropUnique(
                'cheque_liquidations_transaction_unique'
            );

            $table->dropForeign(['transaction_id']);

            $table->dropColumn([
                'transaction_id',
                'confirmed_at',
            ]);
        });
    }
};
