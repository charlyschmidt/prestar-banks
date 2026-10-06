<?php

namespace App\Http\Controllers;

use App\Models\CompanyApiKey;
use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyApiKeyController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(CompanyContextService::class)->company();

        if (!$company) {
            abort(404);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generar API Key
        |--------------------------------------------------------------------------
        */

        $plainKey = 'aeria_' . Str::random(64);

        $apiKey = CompanyApiKey::create([
            'company_id' => $company->id,
            'name' => trim($data['name']),
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 14),
        ]);

        /*
        |--------------------------------------------------------------------------
        | La clave completa se muestra UNA sola vez
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('settings.api')
            ->with('generated_api_key', $plainKey)
            ->with('generated_api_key_name', $apiKey->name);
    }

    public function destroy(CompanyApiKey $apiKey)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(CompanyContextService::class)->company();

        if (
            !$company ||
            (int) $apiKey->company_id !== (int) $company->id
        ) {
            abort(403);
        }

        $apiKey->update([
            'revoked_at' => now(),
        ]);

        return redirect()
            ->route('settings.api')
            ->with('success', 'API Key revocada correctamente.');
    }

    public function delete(CompanyApiKey $apiKey)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(CompanyContextService::class)->company();

        if (
            !$company ||
            (int) $apiKey->company_id !== (int) $company->id
        ) {
            abort(403);
        }

        /*
    |--------------------------------------------------------------------------
    | Sólo permitimos eliminar claves revocadas
    |--------------------------------------------------------------------------
    */

        if (!$apiKey->revoked_at) {
            return redirect()
                ->route('settings.api')
                ->with(
                    'error',
                    'Primero debés revocar la API Key.'
                );
        }

        $apiKey->delete();

        return redirect()
            ->route('settings.api')
            ->with(
                'success',
                'API Key eliminada correctamente.'
            );
    }
}
