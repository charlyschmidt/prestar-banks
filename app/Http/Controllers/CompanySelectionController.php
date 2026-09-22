<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyContextService;
use Illuminate\Http\Request;

class CompanySelectionController extends Controller
{
    public function index()
    {
        $companies = auth()
            ->user()
            ->companies()
            ->where(
                'companies.status',
                'active'
            )
            ->orderBy(
                'companies.name'
            )
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Una sola empresa
    |--------------------------------------------------------------------------
    |
    | La seleccionamos automáticamente.
    |
    */

        if ($companies->count() === 1) {

            app(
                CompanyContextService::class
            )->set(
                $companies->first()
            );

            return redirect()
                ->route('dashboard');
        }


        /*
    |--------------------------------------------------------------------------
    | Varias empresas o ninguna
    |--------------------------------------------------------------------------
    */

        return view(
            'companies.select',
            compact('companies')
        );
    }


    public function store(
        Request $request
    ) {
        $data = $request->validate([
            'company_id' => [
                'required',
                'integer'
            ]
        ]);


        /*
         * No usamos Company::findOrFail()
         * confiando simplemente en el ID.
         *
         * CompanyContext vuelve a comprobar
         * la pertenencia del usuario.
         */
        $company = Company::findOrFail(
            $data['company_id']
        );


        app(
            CompanyContextService::class
        )->set(
            $company
        );


        return redirect()
            ->route('dashboard');
    }
}
