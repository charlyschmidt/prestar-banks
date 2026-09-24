<?php

namespace App\Http\Controllers;

use App\Models\AccountDailyBalance;
use App\Models\Company;
use App\Models\FinancialDay;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use App\Services\AeriaMailService;

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

            'trial_started_at' => now(),

            'trial_ends_at' => now()->addDays(7),
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
}
