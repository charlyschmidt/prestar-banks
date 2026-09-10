<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_days', function (Blueprint $table) {
            $table->id();

            // Día operativo
            $table->date('date')->unique();

            // abierta / cerrada
            $table->enum('status', [
                'open',
                'closed'
            ])->default('open');

            // Usuario que abrió la jornada (opcional por ahora)
            $table->foreignId('opened_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Fecha de cierre
            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_days');
    }
};