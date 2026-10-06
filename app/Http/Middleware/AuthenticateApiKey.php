<?php

namespace App\Http\Middleware;

use App\Models\CompanyApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Obtener Bearer Token
        |--------------------------------------------------------------------------
        */

        $plainKey = $request->bearerToken();

        if (!$plainKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key requerida.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Validar formato
        |--------------------------------------------------------------------------
        */

        if (!str_starts_with($plainKey, 'aeria_')) {
            return response()->json([
                'success' => false,
                'message' => 'API Key inválida.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Buscar hash
        |--------------------------------------------------------------------------
        */

        $keyHash = hash(
            'sha256',
            $plainKey
        );

        $apiKey = CompanyApiKey::query()
            ->with('company')
            ->where('key_hash', $keyHash)
            ->whereNull('revoked_at')
            ->first();


        if (!$apiKey || !$apiKey->company) {
            return response()->json([
                'success' => false,
                'message' => 'API Key inválida o revocada.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Empresa habilitada
        |--------------------------------------------------------------------------
        */

        if ($apiKey->company->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no se encuentra activa.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Guardamos contexto de la request
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'api_key',
            $apiKey
        );

        $request->attributes->set(
            'company',
            $apiKey->company
        );

        $request->attributes->set(
            'company_id',
            $apiKey->company_id
        );


        /*
        |--------------------------------------------------------------------------
        | Último uso
        |--------------------------------------------------------------------------
        */

        $apiKey->update([
            'last_used_at' => now(),
        ]);


        return $next($request);
    }
}