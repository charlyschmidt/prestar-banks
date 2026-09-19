<?php

namespace App\Http\Controllers;

use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return view(
            'settings.index',
            compact('company')
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
}
