<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\CompanyContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientLookupController extends Controller
{
    public function __construct(
        private readonly CompanyContextService $companyContext
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Buscar clientes para movimientos financieros
    |--------------------------------------------------------------------------
    |
    | Disponible para usuarios autenticados de la empresa.
    |
    | No permite crear, editar ni eliminar clientes.
    |
    | Retorna únicamente clientes activos de la empresa
    | actualmente seleccionada.
    |
    */

    public function __invoke(Request $request): JsonResponse
    {
        $companyId = $this->companyContext->id();

        abort_unless($companyId !== null, 403);

        $validated = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $search = trim($validated['q'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Evitar consultas vacías
        |--------------------------------------------------------------------------
        */

        if (mb_strlen($search) < 2) {
            return response()->json([
                'clients' => [],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Consulta por empresa
        |--------------------------------------------------------------------------
        */

        $clients = Client::query()
            ->forCompany($companyId)
            ->active()
            ->search($search)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('business_name')
            ->limit(10)
            ->get([
                'id',
                'uuid',
                'person_type',
                'first_name',
                'last_name',
                'business_name',
                'tax_id',
                'document_number',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Respuesta para Vue 3
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'clients' => $clients->map(function (Client $client) {

                return [
                    'id' => $client->id,
                    'name' => $client->display_name,
                    'tax_id' => $client->tax_id,
                    'document_number' => $client->document_number,
                    'person_type' => $client->person_type,
                ];

            })->values(),
        ]);
    }
}
