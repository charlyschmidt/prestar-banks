
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_reminders', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Cheque relacionado
            |--------------------------------------------------------------------------
            |
            | Nullable para conservar los recordatorios
            | financieros existentes.
            |
            */

            $table->foreignId('cheque_id')
                ->nullable()
                ->constrained('cheques')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tipo de recordatorio
            |--------------------------------------------------------------------------
            |
            | cheque_due: vencimiento de cheque.
            |
            | NULL: recordatorio existente.
            |
            */

            $table->string('reminder_type', 40)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Identificador único
            |--------------------------------------------------------------------------
            |
            | Evita generar dos veces el mismo
            | recordatorio automático.
            |
            | Cada aviso tendrá su propia clave.
            |
            */

            $table->string('reminder_key', 191)
                ->nullable()
                ->unique('financial_reminders_key_unique');

            /*
            |--------------------------------------------------------------------------
            | Índice de consultas
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'reminder_type',
                    'cheque_id',
                    'status'
                ],
                'financial_reminders_cheque_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('financial_reminders', function (Blueprint $table) {

            $table->dropIndex(
                'financial_reminders_cheque_lookup'
            );

            $table->dropUnique(
                'financial_reminders_key_unique'
            );

            $table->dropForeign(
                ['cheque_id']
            );

            $table->dropColumn([
                'cheque_id',
                'reminder_type',
                'reminder_key'
            ]);
        });
    }
};
