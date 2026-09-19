<?php

namespace App\Models\Scopes;

use App\Services\CompanyContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(
        Builder $builder,
        Model $model
    ): void {

        if (!auth()->check()) {
            return;
        }

        $companyId = app(
            CompanyContextService::class
        )->id();

        if (!$companyId) {
            /*
             * Si hay usuario autenticado pero todavía
             * no hay empresa activa, no permitimos
             * devolver datos de ninguna empresa.
             */
            $builder->whereRaw(
                '1 = 0'
            );

            return;
        }

        $builder->where(
            $model->qualifyColumn('company_id'),
            $companyId
        );
    }
}