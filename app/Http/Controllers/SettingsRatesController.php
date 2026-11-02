<?php

namespace App\Http\Controllers;

use App\Services\CompanyContextService;
use App\Services\FinanzasArgyRatesService;
use App\Support\FxMarketVisibility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsRatesController extends Controller
{
    public function index(CompanyContextService $context, FinanzasArgyRatesService $rates): View
    {
        abort_unless(Auth::user()?->isSuperAdmin(), 403);

        $company = $context->company();
        abort_unless($company, 404);

        return view('settings.cotizaciones', [
            'company' => $company,
            'groups' => FxMarketVisibility::GROUPS,
            'selectedMarkets' => FxMarketVisibility::selected($company->visible_fx_markets),
            'currentRates' => $rates->current(), // solo cache; no llamadas HTTP
        ]);
    }

    public function update(Request $request, CompanyContextService $context): RedirectResponse
    {
        abort_unless(Auth::user()?->isSuperAdmin(), 403);

        $company = $context->company();
        abort_unless($company, 404);

        $validated = $request->validate([
            'markets' => ['sometimes', 'array'],
            'markets.*' => ['required', 'string', Rule::in(array_keys(FxMarketVisibility::labels()))],
        ]);

        // Si se apagan todos los toggles, el navegador no envía `markets`.
        // Guardamos [] explícito; NO usar null, que significa "mostrar todos".
        $company->visible_fx_markets = FxMarketVisibility::selected($validated['markets'] ?? []);
        $company->save();

        return redirect()
            ->route('settings.cotizaciones')
            ->with('success', 'Cotizaciones visibles actualizadas correctamente.');
    }
}
