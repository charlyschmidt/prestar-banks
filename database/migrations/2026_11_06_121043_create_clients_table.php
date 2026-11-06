
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Empresa propietaria
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identificación
            |--------------------------------------------------------------------------
            */

            $table->enum('person_type', [
                'individual',
                'company',
            ])->default('individual');

            $table->string('first_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('business_name', 200)->nullable();

            $table->string('document_number', 30)->nullable();
            $table->string('tax_id', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Datos de contacto
            |--------------------------------------------------------------------------
            */

            $table->string('email', 200)->nullable();
            $table->string('phone', 60)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Domicilio
            |--------------------------------------------------------------------------
            */

            $table->string('address', 255)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('province', 150)->nullable();
            $table->string('postal_code', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Información adicional
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            // El mismo CUIT no puede repetirse dentro de una empresa.
            // Distintas empresas pueden compartir el mismo cliente.
            $table->unique(
                ['company_id', 'tax_id'],
                'clients_company_tax_id_unique'
            );

            $table->index(
                ['company_id', 'document_number'],
                'clients_company_document_index'
            );

            $table->index(
                ['company_id', 'last_name', 'first_name'],
                'clients_company_name_index'
            );

            $table->index(
                ['company_id', 'business_name'],
                'clients_company_business_index'
            );

            $table->index(
                ['company_id', 'is_active'],
                'clients_company_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
