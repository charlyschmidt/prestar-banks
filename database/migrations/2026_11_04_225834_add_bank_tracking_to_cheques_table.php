
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Depósito bancario
            |--------------------------------------------------------------------------
            */

            $table->date('deposited_at')->nullable();

            $table->unsignedBigInteger('deposit_account_id')
                ->nullable();

            $table->unsignedBigInteger('deposited_by')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Resultado bancario
            |--------------------------------------------------------------------------
            */

            $table->date('accredited_at')->nullable();

            $table->date('rejected_at')->nullable();

            $table->text('rejection_reason')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Optimización para alertas y cartera
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['company_id', 'status', 'due_date'],
                'cheques_company_status_due_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {

            $table->dropIndex(
                'cheques_company_status_due_index'
            );

            $table->dropColumn([
                'deposited_at',
                'deposit_account_id',
                'deposited_by',
                'accredited_at',
                'rejected_at',
                'rejection_reason',
            ]);
        });
    }
};
