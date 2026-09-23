<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Migration duplicada histórica
        |--------------------------------------------------------------------------
        |
        | is_platform_admin ya fue creada por:
        |
        | 2026_09_18_101820_add_is_platform_admin_to_users_table
        |
        | Conservamos esta migration para no alterar el historial de migrations
        | de instalaciones donde ya fue registrada.
        |
        */

        if (!Schema::hasColumn('users', 'is_platform_admin')) {
            throw new \RuntimeException(
                'La columna users.is_platform_admin debería haber sido creada por la migration anterior.'
            );
        }
    }

    public function down(): void
    {
        /*
         * No eliminamos la columna porque pertenece a la migration anterior.
         */
    }
};