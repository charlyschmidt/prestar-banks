<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque_liquidations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name', 150);
            $table->date('operation_date');
            $table->unsignedSmallInteger('calendar_days');
            $table->unsignedSmallInteger('clearing_days')->default(2);
            $table->unsignedSmallInteger('term_days');
            $table->decimal('annual_rate', 8, 4);
            $table->decimal('cheque_tax_rate', 7, 4);
            $table->decimal('gross_income_rate', 7, 4);
            $table->decimal('vat_rate', 7, 4);
            $table->decimal('nominal_amount', 17, 2);
            $table->decimal('interest_amount', 17, 2);
            $table->decimal('cheque_tax_amount', 17, 2);
            $table->decimal('gross_income_amount', 17, 2);
            $table->decimal('vat_amount', 17, 2);
            $table->decimal('total_discount', 17, 2);
            $table->decimal('net_amount', 17, 2);
            $table->string('status', 20)->default('draft'); // draft | held
            $table->timestamps();
            $table->index(['company_id', 'status', 'operation_date'], 'cheque_liquidation_company_status');
        });

        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cheque_liquidation_id')->constrained('cheque_liquidations')->cascadeOnDelete();
            $table->string('kind', 24)->default('third_party');
            $table->string('format', 12)->default('paper'); // paper | echeq
            $table->string('bank', 120)->nullable();
            $table->string('issuer', 150)->nullable();
            $table->string('number', 80)->nullable();
            $table->date('due_date');
            $table->decimal('nominal_amount', 17, 2);
            $table->string('status', 24)->default('draft'); // cheque aún no recibido/ingresado en cartera
            $table->timestamps();
            $table->index(['company_id', 'due_date']);
        });

        Schema::create('cheque_liquidation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cheque_liquidation_id')->constrained('cheque_liquidations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['company_id', 'cheque_liquidation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_liquidation_events');
        Schema::dropIfExists('cheques');
        Schema::dropIfExists('cheque_liquidations');
    }
};
