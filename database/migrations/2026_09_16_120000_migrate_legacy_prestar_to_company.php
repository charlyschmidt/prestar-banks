<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. Crear / recuperar empresa Prestar
            |--------------------------------------------------------------------------
            */

            $company = DB::table('companies')
                ->where('slug', 'prestar')
                ->first();


            if (!$company) {

                $companyId = DB::table('companies')
                    ->insertGetId([
                        'name' => 'Prestar',
                        'slug' => 'prestar',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

            } else {

                $companyId = $company->id;

            }


            /*
            |--------------------------------------------------------------------------
            | 2. Asignar usuarios legacy a Prestar
            |--------------------------------------------------------------------------
            */

            DB::table('users')
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 3. Crear relaciones company_user
            |--------------------------------------------------------------------------
            |
            | Conservamos role e is_admin de la versión monoempresa.
            |
            */

            $users = DB::table('users')
                ->where('company_id', $companyId)
                ->get();


            foreach ($users as $user) {

                DB::table('company_user')
                    ->updateOrInsert(
                        [
                            'company_id' => $companyId,
                            'user_id' => $user->id,
                        ],
                        [
                            'role' => $user->role ?? 'operator',
                            'is_admin' => (bool) ($user->is_admin ?? false),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | 4. Asignar cuentas existentes
            |--------------------------------------------------------------------------
            */

            DB::table('accounts')
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 5. Asignar jornadas existentes
            |--------------------------------------------------------------------------
            */

            DB::table('financial_days')
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 6. Asignar movimientos históricos
            |--------------------------------------------------------------------------
            */

            DB::table('transactions')
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 7. Asignar balances diarios históricos
            |--------------------------------------------------------------------------
            */

            DB::table('account_daily_balances')
                ->whereNull('company_id')
                ->update([
                    'company_id' => $companyId,
                ]);

        });
    }


    public function down(): void
    {
        /*
         * Intencionalmente vacío.
         *
         * Esta migración adapta datos reales de una instalación
         * monoempresa a la arquitectura multiempresa.
         *
         * No hacemos rollback automático porque podríamos
         * desvincular información creada posteriormente.
         */
    }
};