<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {

            $table->string('billing_name')
                ->nullable()
                ->after('name');

            $table->string('billing_address')
                ->nullable()
                ->after('phone');

            $table->string('billing_city')
                ->nullable()
                ->after('billing_address');

            $table->string('billing_province')
                ->nullable()
                ->after('billing_city');

            $table->string('billing_postal_code', 20)
                ->nullable()
                ->after('billing_province');

            $table->string('billing_tax_status', 50)
                ->nullable()
                ->after('billing_postal_code');
        });
    }


    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {

            $table->dropColumn([
                'billing_name',
                'billing_address',
                'billing_city',
                'billing_province',
                'billing_postal_code',
                'billing_tax_status',
            ]);
        });
    }
};