<?php

namespace App\Http\Middleware;

use App\Services\FinancialDayService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinancialDayOpen
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $day = app(
            FinancialDayService::class
        )->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Debés iniciar la jornada antes de continuar.'
                );
        }


        return $next($request);
    }
}