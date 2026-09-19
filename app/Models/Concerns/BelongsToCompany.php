<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Services\CompanyContextService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(
            new CompanyScope
        );


        static::creating(function ($model) {

            if (!auth()->check()) {
                return;
            }

            $companyId = app(
                CompanyContextService::class
            )->id();

            if (!$companyId) {
                throw new \RuntimeException(
                    'No hay una empresa activa.'
                );
            }

            /*
             * Siempre lo determina el servidor.
             */
            $model->company_id =
                $companyId;
        });
    }


    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }
}