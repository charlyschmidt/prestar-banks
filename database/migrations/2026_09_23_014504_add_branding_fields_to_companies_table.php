<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('background_color', 7)
                ->default('#104072')
                ->after('status');

            $table->string('logo')
                ->nullable()
                ->after('background_color');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'background_color',
                'logo',
            ]);
        });
    }
};