<?php

namespace App\Http\Middleware;

use App\Services\CompanyContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyHasAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $companyId = app(
            CompanyContextService::class
        )->id();

        if (!$companyId) {
            return $next($request);
        }


        $company = \App\Models\Company::find(
            $companyId
        );

        if (!$company) {
            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | Iniciar período de prueba
        |--------------------------------------------------------------------------
        |
        | Una empresa aprobada comienza su período de prueba
        | recién cuando accede por primera vez a AERIA.
        |
        */

        if (
            $company->isActive()
            && !$company->trial_started_at
            && !$company->trial_ends_at
            && !$company->subscription_lifetime
            && !$company->hasActiveSubscription()
        ) {

            $company->update([
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(7),
            ]);

            $company->refresh();
        }


        /*
        |--------------------------------------------------------------------------
        | Validar acceso
        |--------------------------------------------------------------------------
        */

        if (!$company->hasAccess()) {

            return redirect()->route(
                'subscription.expired'
            );
        }


        return $next($request);
    }
}