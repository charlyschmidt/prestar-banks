<?php

namespace App\Http\Middleware;

use App\Services\CompanyContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChequeSuperAdmin
{
    /*
    |--------------------------------------------------------------------------
    | Acceso al módulo AERIA Cheques
    |--------------------------------------------------------------------------
    |
    | Solo los Super Admin de la empresa activa pueden
    | consultar y operar con cheques y liquidaciones.
    |
    | La autorización se realiza en el servidor.
    | No depende de que el menú esté visible.
    |
    */

    public function handle(
        Request $request,
        Closure $next
    ): Response {

        // Usuario autenticado.
        abort_unless(auth()->check(), 401);

        // Empresa activa válida.
        $companyId = (int) app(
            CompanyContextService::class
        )->id();

        abort_if($companyId <= 0, 403);

        /*
         * isSuperAdmin() utiliza la pertenencia
         * administrativa a la empresa activa.
         *
         * No utilizamos users.is_admin ni el
         * administrador global de la plataforma.
         */

        abort_unless(
            auth()->user()->isSuperAdmin(),
            403,
            'No tenés permisos para acceder al módulo de Cheques.'
        );

        return $next($request);
    }
}
