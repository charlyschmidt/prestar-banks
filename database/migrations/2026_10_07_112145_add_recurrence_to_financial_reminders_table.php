<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_reminders', function (Blueprint $table) {

            $table->string('recurrence_type', 20)
                ->nullable()
                ->after('scheduled_at');

        });
    }


    public function down(): void
    {
        Schema::table('financial_reminders', function (Blueprint $table) {

            $table->dropColumn('recurrence_type');

        });
    }
};