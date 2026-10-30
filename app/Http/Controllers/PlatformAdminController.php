<?php

namespace App\Http\Controllers;

use App\Models\AccountDailyBalance;
use App\Models\Company;
use App\Models\FinancialDay;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use App\Services\AeriaMailService;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\FinancialReminder;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;

class PlatformAdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado general de empresas
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
    |--------------------------------------------------------------------------
    | Empresas
    |--------------------------------------------------------------------------
    */

        $companies = Company::query()
            ->withCount('users')
            ->with([
                'subscriptions' => function ($query) {
                    $query
                        ->whereIn('status', ['pending', 'active'])
                        ->latest('id');
                }
            ])
            ->latest()
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Estadísticas de empresas
    |--------------------------------------------------------------------------
    */

        $companiesTotal = $companies->count();

        $companiesPending = $companies
            ->where('status', 'pending')
            ->count();

        $companiesActive = $companies
            ->where('status', 'active')
            ->count();

        $companiesSuspended = $companies
            ->where('status', 'suspended')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        |
        | Contamos membresías de empresa.
        |
        | Esto representa la cantidad total de usuarios registrados
        | dentro de empresas de AERIA.
        |
        */

        $usersTotal = $companies
            ->sum('users_count');


        /*
    |--------------------------------------------------------------------------
    | Movimientos globales
    |--------------------------------------------------------------------------
    */

        $transactions = Transaction::withoutGlobalScopes();


        $movementsTotal = (clone $transactions)
            ->count();


        /*
    |--------------------------------------------------------------------------
    | Ingresos globales
    |--------------------------------------------------------------------------
    */

        $incomeTotal = (clone $transactions)
            ->where('type', 'income')
            ->sum('amount');


        /*
    |--------------------------------------------------------------------------
    | Egresos globales
    |--------------------------------------------------------------------------
    */

        $expenseTotal = (clone $transactions)
            ->where('type', 'expense')
            ->sum('amount');


        /*
    |--------------------------------------------------------------------------
    | Volumen total movido
    |--------------------------------------------------------------------------
    |
    | Volumen = ingresos + egresos.
    |
    | No incluimos saldos iniciales porque no representan
    | movimientos realizados dentro de AERIA.
    |
    */

        $volumeTotal =
            $incomeTotal +
            $expenseTotal;


        /*
    |--------------------------------------------------------------------------
    | Saldo actual global
    |--------------------------------------------------------------------------
    |
    | No podemos sumar todos los account_daily_balances porque
    | incluiríamos jornadas históricas.
    |
    | Tomamos la última jornada registrada de cada empresa
    | y sumamos los current_balance de sus cuentas.
    |
    */

        $currentBalanceTotal = 0;


        foreach ($companies as $company) {

            $latestDay = FinancialDay::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->orderByDesc('date')
                ->first();


            if (!$latestDay) {
                continue;
            }


            $companyBalance = AccountDailyBalance::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where(
                    'financial_day_id',
                    $latestDay->id
                )
                ->sum('current_balance');


            $currentBalanceTotal += $companyBalance;
        }


        /*
    |--------------------------------------------------------------------------
    | Stats para la vista
    |--------------------------------------------------------------------------
    */

        $stats = [

            'companies' => $companiesTotal,

            'pending' => $companiesPending,

            'active' => $companiesActive,

            'suspended' => $companiesSuspended,

            'users' => $usersTotal,

            'movements' => $movementsTotal,

            'income' => $incomeTotal,

            'expense' => $expenseTotal,

            'volume' => $volumeTotal,

            'current_balance' => $currentBalanceTotal,

        ];


        return view(
            'aeria-admin.index',
            compact(
                'companies',
                'stats'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detalle de empresa
    |--------------------------------------------------------------------------
    */

    public function show(Company $company)
    {
        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        $usersCount = $company
            ->users()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Jornadas
        |--------------------------------------------------------------------------
        */

        $financialDays = FinancialDay::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->orderByDesc('date')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Movimientos generales
        |--------------------------------------------------------------------------
        */

        $transactionsQuery = Transaction::withoutGlobalScopes()
            ->where('company_id', $company->id);


        $movementsCount = (clone $transactionsQuery)
            ->count();


        $totalIncome = (clone $transactionsQuery)
            ->where('type', 'income')
            ->sum('amount');


        $totalExpense = (clone $transactionsQuery)
            ->where('type', 'expense')
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | Saldo actual
        |--------------------------------------------------------------------------
        |
        | Tomamos los saldos actuales de la jornada más reciente.
        |
        */

        $latestDay = $financialDays->first();

        $currentBalance = 0;

        if ($latestDay) {

            $currentBalance = AccountDailyBalance::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('financial_day_id', $latestDay->id)
                ->sum('current_balance');
        }


        /*
        |--------------------------------------------------------------------------
        | Resumen por jornada
        |--------------------------------------------------------------------------
        */

        $days = $financialDays->map(function ($day) use ($company) {

            $balances = AccountDailyBalance::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('financial_day_id', $day->id)
                ->get();


            $initialBalance = $balances
                ->sum('initial_balance');


            $finalBalance = $balances
                ->sum('current_balance');


            $dayTransactions = Transaction::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('financial_day_id', $day->id);


            $income = (clone $dayTransactions)
                ->where('type', 'income')
                ->sum('amount');


            $expense = (clone $dayTransactions)
                ->where('type', 'expense')
                ->sum('amount');


            $movements = (clone $dayTransactions)
                ->count();


            return [
                'id' => $day->id,

                'date' => $day->date,

                'status' => $day->status,

                'initial_balance' => $initialBalance,

                'income' => $income,

                'expense' => $expense,

                'final_balance' => $finalBalance,

                'movements' => $movements,
            ];
        });


        /*
        |--------------------------------------------------------------------------
        | Datos generales
        |--------------------------------------------------------------------------
        */

        $summary = [
            'users' => $usersCount,

            'movements' => $movementsCount,

            'income' => $totalIncome,

            'expense' => $totalExpense,

            'current_balance' => $currentBalance,

            'financial_days' => $financialDays->count(),
        ];


        return view(
            'aeria-admin.companies.show',
            compact(
                'company',
                'summary',
                'days'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Aprobar / Reactivar
    |--------------------------------------------------------------------------
    */

    public function approve(
        Company $company,
        AeriaMailService $mailService
    ): RedirectResponse {

        if ($company->status === 'active') {

            return back()->with(
                'success',
                'La empresa ya se encuentra activa.'
            );
        }


        $company->update([
            'status' => 'active',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Notificar administradores de la empresa
    |--------------------------------------------------------------------------
    */

        $admins = $company
            ->users()
            ->wherePivot('is_admin', true)
            ->get();


        foreach ($admins as $admin) {

            $mailService->sendAccountApproved(
                $admin,
                $company
            );
        }


        return back()->with(
            'success',
            'La empresa ' .
                $company->name .
                ' fue aprobada correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Suspender
    |--------------------------------------------------------------------------
    */

    public function suspend(Company $company): RedirectResponse
    {
        if ($company->status === 'suspended') {

            return back()->with(
                'success',
                'La empresa ya se encuentra suspendida.'
            );
        }

        $company->update([
            'status' => 'suspended',
        ]);

        return back()->with(
            'success',
            'La empresa ' .
                $company->name .
                ' fue suspendida.'
        );
    }

    public function destroy(Company $company): RedirectResponse
    {
        DB::transaction(function () use ($company) {

            /*
        |--------------------------------------------------------------------------
        | Movimientos
        |--------------------------------------------------------------------------
        */

            Transaction::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Saldos diarios
        |--------------------------------------------------------------------------
        */

            AccountDailyBalance::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Jornadas financieras
        |--------------------------------------------------------------------------
        */

            FinancialDay::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Recordatorios
        |--------------------------------------------------------------------------
        */

            FinancialReminder::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Saldos / monedas de cuentas
        |--------------------------------------------------------------------------
        */

            AccountBalance::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Cuentas
        |--------------------------------------------------------------------------
        */

            Account::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Pagos de suscripción
        |--------------------------------------------------------------------------
        */

            SubscriptionPayment::query()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Suscripciones
        |--------------------------------------------------------------------------
        */

            Subscription::query()
                ->where('company_id', $company->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        |
        | NO eliminamos users.
        | Eliminamos solamente su pertenencia a esta empresa.
        |
        */


            /*
            |--------------------------------------------------------------------------
            | Usuarios
            |--------------------------------------------------------------------------
            |
            | Eliminar usuarios exclusivos de esta empresa.
            | Conservar los que pertenezcan a otras empresas.
            |
            */

            $users = $company->users()->get();

            foreach ($users as $user) {

                $otherCompanies = DB::table('company_user')
                    ->where('user_id', $user->id)
                    ->where('company_id', '!=', $company->id)
                    ->exists();

                if (!$otherCompanies) {

                    // El usuario pertenece únicamente a esta empresa.
                    $company->users()->detach($user->id);

                    // Eliminar el usuario.
                    $user->delete();
                } else {

                    // El usuario también pertenece a otra empresa.
                    // Solo eliminar la relación con esta empresa.
                    $company->users()->detach($user->id);
                }
            }



            /*
        |--------------------------------------------------------------------------
        | Empresa
        |--------------------------------------------------------------------------
        */

            $company->delete();
        });


        return redirect()
            ->route('aeria-admin.index')
            ->with(
                'success',
                'La empresa y todos sus datos fueron eliminados correctamente.'
            );
    }


    /*
|--------------------------------------------------------------------------
| ACTIVAR / QUITAR LICENCIA LIFETIME
|--------------------------------------------------------------------------
*/


    public function setLifetime(
        \Illuminate\Http\Request $request,
        \App\Models\Company $company,
        \App\Services\MercadoPagoSubscriptionService $mercadoPagoService
    ): \Illuminate\Http\RedirectResponse {

        $request->validate([
            'lifetime' => ['required', 'boolean'],
        ]);

        $activate = $request->boolean('lifetime');

        /*
    |--------------------------------------------------------------------------
    | ACTIVAR LIFETIME
    |--------------------------------------------------------------------------
    */

        if ($activate) {

            // Obtener las suscripciones que todavía podrían estar vigentes.
            $subscriptions = $company
                ->subscriptions()
                ->whereIn('status', ['active', 'pending'])
                ->orderBy('id')
                ->get();

            foreach ($subscriptions as $subscription) {

                /*
            |--------------------------------------------------------------------------
            | SUSCRIPCIÓN ACTIVA
            |--------------------------------------------------------------------------
            */

                if ($subscription->status === 'active') {

                    return back()->with(
                        'error',
                        'La empresa tiene una suscripción activa. '
                            . 'Debés revisar su cancelación antes de otorgar Lifetime.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | SUSCRIPCIÓN PENDIENTE SIN ID EXTERNO
            |--------------------------------------------------------------------------
            */

                if (!$subscription->provider_subscription_id) {

                    // Si existe un checkout pero no tenemos el ID externo,
                    // no podemos verificar con seguridad su situación.
                    if ($subscription->init_point) {

                        return back()->with(
                            'error',
                            'Hay una suscripción pendiente con un checkout '
                                . 'de Mercado Pago, pero sin identificador externo. '
                                . 'Revisala antes de activar Lifetime.'
                        );
                    }

                    // Registro local incompleto, sin checkout ni referencia externa.
                    $subscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'next_billing_at' => null,
                        'renewal_prepared_at' => null,
                    ]);

                    continue;
                }

                /*
            |--------------------------------------------------------------------------
            | CONSULTAR ESTADO REAL EN MERCADO PAGO
            |--------------------------------------------------------------------------
            */

                try {

                    $providerData = $mercadoPagoService
                        ->getSubscription(
                            $subscription->provider_subscription_id
                        );

                    $providerStatus = $providerData['status'] ?? null;

                    /*
                | Si fue autorizada, no otorgar Lifetime.
                */

                    if ($providerStatus === 'authorized') {

                        return back()->with(
                            'error',
                            'Mercado Pago informa que existe una suscripción '
                                . 'autorizada. Cancelala antes de activar Lifetime.'
                        );
                    }

                    /*
                | Si continúa pendiente, cancelarla en Mercado Pago.
                */

                    if ($providerStatus === 'pending') {

                        $cancelData = $mercadoPagoService
                            ->cancelSubscription(
                                $subscription->provider_subscription_id
                            );

                        $providerStatus = $cancelData['status'] ?? null;

                        // Verificación adicional cuando la respuesta
                        // de cancelación no informa un estado definitivo.
                        if ($providerStatus !== 'canceled') {

                            $verifiedData = $mercadoPagoService
                                ->getSubscription(
                                    $subscription->provider_subscription_id
                                );

                            $providerStatus = $verifiedData['status'] ?? null;
                        }
                    }

                    /*
                | Solamente continuar si Mercado Pago
                | confirma que está cancelada.
                */

                    if ($providerStatus !== 'canceled') {

                        return back()->with(
                            'error',
                            'No se pudo confirmar la cancelación de una '
                                . 'suscripción en Mercado Pago. Estado informado: '
                                . ($providerStatus ?? 'desconocido')
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | SINCRONIZAR ESTADO LOCAL
                |--------------------------------------------------------------------------
                */

                    $subscription->update([
                        'status' => 'cancelled',
                        'provider_status' => 'canceled',
                        'cancelled_at' => now(),
                        'next_billing_at' => null,
                        'renewal_prepared_at' => null,
                    ]);
                } catch (\Throwable $e) {

                    report($e);

                    return back()->with(
                        'error',
                        'No pudimos verificar o cancelar la suscripción '
                            . 'pendiente en Mercado Pago. No se modificó '
                            . 'la licencia Lifetime.'
                    );
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR LIFETIME
    |--------------------------------------------------------------------------
    */

        $company->subscription_lifetime = $activate;
        $company->save();

        return back()->with(
            'success',
            $activate
                ? 'Licencia Lifetime activada para ' . $company->name . '.'
                : 'Licencia Lifetime desactivada para ' . $company->name . '.'
        );
    }


    public function cancelSubscription(
        \Illuminate\Http\Request $request,
        \App\Models\Company $company,
        \App\Models\Subscription $subscription,
        \App\Services\SubscriptionCancellationService $cancellationService
    ): \Illuminate\Http\RedirectResponse {

        // Evitar cancelar una suscripción de otra empresa.
        abort_unless(
            (int) $subscription->company_id === (int) $company->id,
            404
        );

        if (!in_array(
            $subscription->status,
            ['pending', 'active'],
            true
        )) {
            return back()->with(
                'error',
                'Esta suscripción ya no está pendiente ni activa.'
            );
        }

        $wasPending = $subscription->status === 'pending';

        try {

            $cancellationService->cancel($subscription);
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'No se pudo confirmar la cancelación '
                    . 'con Mercado Pago. Revisá el log de Laravel.'
            );
        }

        return back()->with(
            'success',
            $wasPending
                ? 'Solicitud pendiente cancelada para '
                . $company->name . '.'
                : 'Suscripción activa cancelada para '
                . $company->name . '.'
        );
    }
}
