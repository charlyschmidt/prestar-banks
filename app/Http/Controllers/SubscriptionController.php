<?php

namespace App\Http\Controllers;

use App\Services\CompanyContextService;
use App\Services\SubscriptionPlanService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use App\Services\MercadoPagoSubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private SubscriptionPlanService $planService,
        private CompanyContextService $companyContextService,
        private MercadoPagoSubscriptionService $mercadoPagoService,
    ) {}

    /*
|--------------------------------------------------------------------------
| Planes de suscripción
|--------------------------------------------------------------------------
*/

    public function index()
    {
        $company = $this->companyContextService->company();

        if (!$company) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | La empresa debe estar aprobada
        |--------------------------------------------------------------------------
        */

        if ($company->status !== 'active') {
            return view('auth.register-pending');
        }


        /*
        |--------------------------------------------------------------------------
        | Última suscripción
        |--------------------------------------------------------------------------
        */

        $subscription = $company
            ->subscriptions()
            ->latest('id')
            ->first();


        $data = [
            'company' => $company,
            'subscription' => $subscription,
            'plans' => $this->planService->all(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Trial vencido y sin suscripción activa
        |--------------------------------------------------------------------------
        |
        | El usuario no entra al dashboard.
        | Mostramos la pantalla externa para contratar AERIA.
        |
        */

        if (
            !$company->isOnTrial()
            && !$company->hasActiveSubscription()
        ) {
            return view(
                'subscription.expired',
                $data
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Trial vigente o suscripción activa
        |--------------------------------------------------------------------------
        |
        | Gestión de la suscripción dentro de AERIA.
        |
        */

        return view(
            'subscription.index',
            $data
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Iniciar suscripción
    |--------------------------------------------------------------------------
    */

    public function subscribe(
        Request $request
    ): RedirectResponse {

        $request->validate([
            'plan' => [
                'required',
                'string',
                'in:monthly,annual',
            ],
        ]);

        $company = $this->companyContextService->company();

        if (!$company) {
            return back()->with(
                'error',
                'No se pudo identificar la empresa activa.'
            );
        }

        /*
|--------------------------------------------------------------------------
| La empresa debe estar aprobada
|--------------------------------------------------------------------------
*/

        if ($company->status !== 'active') {
            return redirect()
                ->route('subscription.index');
        }

        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->planService->exists($request->plan)) {
            return back()->with(
                'error',
                'El plan seleccionado no es válido.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Evitar duplicar una suscripción pendiente/activa
        |--------------------------------------------------------------------------
        */

        $existingSubscription = $company
            ->subscriptions()
            ->whereIn('status', [
                'pending',
                'active',
            ])
            ->latest('id')
            ->first();

        if ($existingSubscription) {

            /*
            |--------------------------------------------------------------------------
            | Si ya existe una pendiente con URL de Mercado Pago
            |--------------------------------------------------------------------------
            */

            if (
                $existingSubscription->status === 'pending'
                && $existingSubscription->init_point
            ) {
                return redirect()->away(
                    $existingSubscription->init_point
                );
            }

            return back()->with(
                'error',
                'La empresa ya posee una suscripción activa o pendiente.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Crear suscripción
        |--------------------------------------------------------------------------
        */

        try {

            $subscription = $this->subscriptionService
                ->create(
                    $company,
                    $request->plan,
                    $user->email
                );
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'No pudimos iniciar la suscripción. Intentá nuevamente.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mercado Pago debe devolver URL de autorización
        |--------------------------------------------------------------------------
        */

        if (!$subscription->init_point) {

            return back()->with(
                'error',
                'Mercado Pago no devolvió la URL para autorizar la suscripción.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Enviar usuario a Mercado Pago
        |--------------------------------------------------------------------------
        */

        return redirect()->away(
            $subscription->init_point
        );
    }

    /*
|--------------------------------------------------------------------------
| Retorno desde Mercado Pago
|--------------------------------------------------------------------------
*/

public function return(Request $request): RedirectResponse
{
    $company = $this->companyContextService->company();

    if (!$company) {
        return redirect()
            ->route('subscription.index')
            ->with(
                'error',
                'No se pudo identificar la empresa.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Buscar la última suscripción pendiente
    |--------------------------------------------------------------------------
    */

    $subscription = $company
        ->subscriptions()
        ->where('status', 'pending')
        ->whereNotNull('provider_subscription_id')
        ->latest('id')
        ->first();


    if (!$subscription) {
        return redirect()
            ->route('subscription.index')
            ->with(
                'error',
                'No encontramos una suscripción pendiente para verificar.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Consultar estado real en Mercado Pago
    |--------------------------------------------------------------------------
    */

    try {

        $providerData = $this->mercadoPagoService
            ->getSubscription(
                $subscription->provider_subscription_id
            );

    } catch (\Throwable $e) {

        report($e);

        return redirect()
            ->route('subscription.index')
            ->with(
                'error',
                'No pudimos verificar la suscripción con Mercado Pago.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Estado informado por Mercado Pago
    |--------------------------------------------------------------------------
    */

    $providerStatus =
        $providerData['status'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Actualizar información local
    |--------------------------------------------------------------------------
    */

    $subscription->update([

        'provider_status' =>
            $providerStatus,

        'next_billing_at' =>
            $providerData['next_payment_date'] ?? null,

    ]);


    /*
    |--------------------------------------------------------------------------
    | Suscripción autorizada
    |--------------------------------------------------------------------------
    */

    if ($providerStatus === 'authorized') {

        $subscription->update([

            'status' => 'active',

            'started_at' =>
                $subscription->started_at ?? now(),

        ]);


        return redirect()
            ->route('subscription.index')
            ->with(
                'success',
                'Tu suscripción a AERIA Finance fue activada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Todavía pendiente
    |--------------------------------------------------------------------------
    */

    if ($providerStatus === 'pending') {

        return redirect()
            ->route('subscription.index')
            ->with(
                'error',
                'La autorización de Mercado Pago todavía está pendiente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Otro estado
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('subscription.index')
        ->with(
            'error',
            'Mercado Pago informó el estado: '
                . ($providerStatus ?? 'desconocido')
                . '.'
        );
}
}
