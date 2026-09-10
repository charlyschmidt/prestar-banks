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
    Schema::table('financial_days', function (Blueprint $table) {

        $table->timestamp('opened_at')
            ->nullable()
            ->after('opened_by');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_days', function (Blueprint $table) {
            //
        });
    }
};
