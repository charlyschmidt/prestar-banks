<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Asignar UUID a los cheques existentes.
        DB::table('cheques')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(200, function ($cheques) {
                foreach ($cheques as $cheque) {
                    DB::table('cheques')
                        ->where('id', $cheque->id)
                        ->whereNull('uuid')
                        ->update([
                            'uuid' => (string) Str::uuid(),
                        ]);
                }
            });

        // Evitar identificadores públicos duplicados.
        Schema::table('cheques', function (Blueprint $table) {
            $table->unique('uuid', 'cheques_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropUnique('cheques_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};
