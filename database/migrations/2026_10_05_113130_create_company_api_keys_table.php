<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_api_keys', function (Blueprint $table) {
        $table->id();

        $table->foreignId('company_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('name', 100);

        // Nunca guardamos la API Key real.
        $table->string('key_hash', 64)->unique();

        // Para poder identificarla visualmente.
        $table->string('key_prefix', 20);

        $table->timestamp('last_used_at')->nullable();
        $table->timestamp('revoked_at')->nullable();

        $table->timestamps();

        $table->index(['company_id', 'revoked_at']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_api_keys');
    }
};
