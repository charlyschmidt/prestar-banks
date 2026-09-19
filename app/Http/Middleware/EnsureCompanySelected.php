<?php

namespace App\Http\Middleware;

use App\Services\CompanyContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $companyId = app(
            CompanyContextService::class
        )->id();


        if (!$companyId) {

            return redirect()
                ->route('company.select');
        }


        return $next($request);
    }
}