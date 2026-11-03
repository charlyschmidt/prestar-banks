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
        /*
        |--------------------------------------------------------------------------
        | Agregar columna UUID
        |--------------------------------------------------------------------------
        */

        Schema::table('cheque_liquidations', function (Blueprint $table) {
            $table->uuid('uuid')
                ->nullable()
                ->after('id');
        });

        /*
        |--------------------------------------------------------------------------
        | Generar UUID para liquidaciones existentes
        |--------------------------------------------------------------------------
        */

        DB::table('cheque_liquidations')
            ->whereNull('uuid')
            ->chunkById(200, function ($liquidations) {
                foreach ($liquidations as $liquidation) {
                    DB::table('cheque_liquidations')
                        ->where('id', $liquidation->id)
                        ->whereNull('uuid')
                        ->update([
                            'uuid' => (string) Str::uuid(),
                        ]);
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Índice único
        |--------------------------------------------------------------------------
        */

        Schema::table('cheque_liquidations', function (Blueprint $table) {
            $table->unique(
                'uuid',
                'cheque_liquidations_uuid_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cheque_liquidations', function (Blueprint $table) {
            $table->dropUnique(
                'cheque_liquidations_uuid_unique'
            );

            $table->dropColumn('uuid');
        });
    }
};
