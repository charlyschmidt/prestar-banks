<?php

namespace App\Http\Controllers;

use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Province;
use App\Rules\ValidCuit;

class SettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Configuración
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(
            CompanyContextService::class
        )->company();

        if (!$company) {
            abort(404);
        }

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $apiKeys = $company->apiKeys()
            ->latest()
            ->get();

        return view(
            'settings.index',
            compact(
                'company',
                'provinces',
                'apiKeys'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar identidad visual
    |--------------------------------------------------------------------------
    */

    public function updateBranding(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(
            \App\Services\CompanyContextService::class
        )->company();

        if (!$company) {
            abort(404);
        }

        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'background_color' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'logo' => [
                    'nullable',
                    'image',
                    'mimes:png,jpg,jpeg,webp',
                    'max:2048',
                ],
            ],
            [
                'name.required' => 'Ingresá el nombre de la empresa.',
                'name.max' => 'El nombre no puede superar los 120 caracteres.',

                'background_color.required' => 'Seleccioná un color.',
                'background_color.regex' => 'El color seleccionado no es válido.',

                'logo.image' => 'El archivo seleccionado debe ser una imagen.',
                'logo.mimes' => 'El logo debe ser PNG, JPG, JPEG o WEBP.',
                'logo.max' => 'El logo no puede superar los 2 MB.',
            ]
        );


        /*
    |--------------------------------------------------------------------------
    | Datos de empresa
    |--------------------------------------------------------------------------
    */

        $company->name = trim($data['name']);

        $company->background_color =
            strtoupper($data['background_color']);


        /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('logo')) {

            if (
                $company->logo
                &&
                \Illuminate\Support\Facades\Storage::disk('public')
                ->exists($company->logo)
            ) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete($company->logo);
            }

            $company->logo =
                $request->file('logo')->store(
                    'companies/logos',
                    'public'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

        $company->save();


        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Configuración actualizada correctamente.'
            );
    }

    /*
|--------------------------------------------------------------------------
| Actualizar datos de la empresa
|--------------------------------------------------------------------------
*/

    public function updateCompany(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(
            CompanyContextService::class
        )->company();

        if (!$company) {
            abort(404);
        }


        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $data = $request->validate(
            [
                'billing_name' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'tax_id' => [
                    'nullable',
                    new ValidCuit(),
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:150',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'billing_address' => [
                    'nullable',
                    'string',
                    'max:180',
                ],

                'billing_city' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'billing_province' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'billing_postal_code' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'billing_tax_status' => [
                    'nullable',
                    'in:responsable_inscripto,monotributista,exento,consumidor_final',
                ],

                'requires_invoice' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'billing_name.max' =>
                'La razón social no puede superar los 150 caracteres.',

                'tax_id.max' =>
                'El CUIT no puede superar los 20 caracteres.',

                'email.email' =>
                'Ingresá un email válido.',

                'email.max' =>
                'El email no puede superar los 150 caracteres.',

                'phone.max' =>
                'El teléfono no puede superar los 50 caracteres.',

                'billing_address.max' =>
                'La dirección no puede superar los 180 caracteres.',

                'billing_city.max' =>
                'La localidad no puede superar los 100 caracteres.',

                'billing_province.max' =>
                'La provincia no puede superar los 100 caracteres.',

                'billing_postal_code.max' =>
                'El código postal no puede superar los 20 caracteres.',

                'billing_tax_status.in' =>
                'La condición fiscal seleccionada no es válida.',
            ]
        );


        /*
    |--------------------------------------------------------------------------
    | Actualizar empresa
    |--------------------------------------------------------------------------
    */

        $company->billing_name =
            !empty($data['billing_name'])
            ? trim($data['billing_name'])
            : null;

        $company->tax_id =
            !empty($data['tax_id'])
            ? trim($data['tax_id'])
            : null;

        $company->email =
            !empty($data['email'])
            ? trim($data['email'])
            : null;

        $company->phone =
            !empty($data['phone'])
            ? trim($data['phone'])
            : null;

        $company->billing_address =
            !empty($data['billing_address'])
            ? trim($data['billing_address'])
            : null;

        $company->billing_city =
            !empty($data['billing_city'])
            ? trim($data['billing_city'])
            : null;

        $company->billing_province =
            !empty($data['billing_province'])
            ? trim($data['billing_province'])
            : null;

        $company->billing_postal_code =
            !empty($data['billing_postal_code'])
            ? trim($data['billing_postal_code'])
            : null;

        $company->billing_tax_status =
            $data['billing_tax_status'] ?? null;

        $company->requires_invoice =
            $request->boolean('requires_invoice');
        /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

        $company->save();


        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Datos de la empresa actualizados correctamente.'
            );
    }

    public function api()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(CompanyContextService::class)->company();

        if (!$company) {
            abort(404);
        }

        $apiKeys = $company->apiKeys()
            ->latest()
            ->get();

        return view(
            'settings.api',
            compact(
                'company',
                'apiKeys'
            )
        );
    }

    public function apiDocumentation()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $company = app(CompanyContextService::class)->company();

        if (!$company) {
            abort(404);
        }

        return view('settings.api-documentation', compact('company'));
    }
}
