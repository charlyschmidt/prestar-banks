<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request
    ): RedirectResponse {

        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();


        /*
    |--------------------------------------------------------------------------
    | Platform Admin AERIA
    |--------------------------------------------------------------------------
    */

        if ($user->isPlatformAdmin()) {

            $request->session()->forget(
                'company_id'
            );

            return redirect()
                ->route('aeria-admin.index');
        }


        /*
    |--------------------------------------------------------------------------
    | Empresas activas del usuario
    |--------------------------------------------------------------------------
    */

        $companies = $user
            ->companies()
            ->where(
                'companies.status',
                'active'
            )
            ->get();


        /*
    |--------------------------------------------------------------------------
    | No tiene empresas
    |--------------------------------------------------------------------------
    */

        if ($companies->isEmpty()) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                    'Tu usuario no pertenece a una empresa activa.'
                ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Tiene una sola empresa
    |--------------------------------------------------------------------------
    */

        if ($companies->count() === 1) {

            $request->session()->put(
                'company_id',
                $companies->first()->id
            );

            return redirect()
                ->route('dashboard');
        }


        /*
    |--------------------------------------------------------------------------
    | Tiene varias empresas
    |--------------------------------------------------------------------------
    */

        $request->session()->forget(
            'company_id'
        );

        return redirect()
            ->route('company.select');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(
            'company_id'
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
