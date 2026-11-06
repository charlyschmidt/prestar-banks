<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Client extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'person_type',
        'first_name',
        'last_name',
        'business_name',
        'document_number',
        'tax_id',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'notes',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de atributos
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Generación automática de UUID
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Client $client) {

            if (empty($client->uuid)) {
                $client->uuid = (string) Str::uuid();
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Identificador público
    |--------------------------------------------------------------------------
    |
    | Laravel utilizará UUID cuando el modelo sea
    | empleado en la generación de rutas.
    |
    */

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre para mostrar
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        if ($this->person_type === 'company') {
            return $this->business_name
                ?: 'Empresa sin razón social';
        }

        $name = trim(
            ($this->last_name ?? '') . ' ' .
            ($this->first_name ?? '')
        );

        return $name !== ''
            ? $name
            : 'Cliente sin nombre';
    }

    /*
    |--------------------------------------------------------------------------
    | CUIT normalizado
    |--------------------------------------------------------------------------
    */

    public function setTaxIdAttribute(?string $value): void
    {
        $normalized = preg_replace('/\D+/', '', $value ?? '');

        $this->attributes['tax_id'] =
            $normalized !== '' ? $normalized : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Documento normalizado
    |--------------------------------------------------------------------------
    */

    public function setDocumentNumberAttribute(?string $value): void
    {
        $normalized = preg_replace('/\D+/', '', $value ?? '');

        $this->attributes['document_number'] =
            $normalized !== '' ? $normalized : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros reutilizables
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Búsqueda
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $term
    ): Builder {

        $term = trim($term ?? '');

        if ($term === '') {
            return $query;
        }

        $digits = preg_replace('/\D+/', '', $term);

        return $query->where(function (Builder $q) use ($term, $digits) {

            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('business_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");

            if ($digits !== '') {
                $q->orWhere('tax_id', 'like', "%{$digits}%")
                    ->orWhere('document_number', 'like', "%{$digits}%");
            }
        });
    }
}
