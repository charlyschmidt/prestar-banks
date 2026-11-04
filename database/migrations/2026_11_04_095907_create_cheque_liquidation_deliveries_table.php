
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque_liquidation_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cheque_liquidation_id')
                ->constrained('cheque_liquidations')
                ->cascadeOnDelete();

            // cash, transfer, third_party_cheque
            $table->string('type', 30);

            // ARS, USD, EUR
            $table->string('currency', 3)->default('ARS');

            // Importe en la moneda entregada
            $table->decimal('amount', 18, 2);

            // Cotización utilizada en la operación
            $table->decimal('exchange_rate', 18, 6)
                ->default(1);

            // Equivalente en pesos argentinos
            $table->decimal('amount_ars', 18, 2);

            // Cuenta desde la que se entregan fondos
            $table->foreignId('account_id')
                ->nullable()
                ->index();

            // Cheque de terceros entregado desde cartera
            $table->unsignedBigInteger('delivered_cheque_id')
                ->nullable()
                ->index();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['cheque_liquidation_id', 'type'],
                'cheque_delivery_liquidation_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_liquidation_deliveries');
    }
};
