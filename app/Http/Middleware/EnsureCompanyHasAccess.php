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

        if (!$company->hasAccess()) {
            return redirect()->route(
                'subscription.expired'
            );
        }

        return $next($request);
    }
}
