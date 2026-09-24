<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FinancialDayController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\FinancialReminderController;
use App\Http\Controllers\OpeningBalanceImportController;


/*
|--------------------------------------------------------------------------
| Main Entry
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');


Route::get('/register/pending', function () {

    return view('auth.register-pending');
})->name('register.pending');


/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Company Selection
    |--------------------------------------------------------------------------
    |
    | Estas rutas NO llevan middleware company porque justamente
    | se utilizan cuando todavía no existe una empresa activa.
    |
    */

    Route::get(
        '/company/select',
        [CompanySelectionController::class, 'index']
    )->name('company.select');


    Route::post(
        '/company/select',
        [CompanySelectionController::class, 'store']
    )->name('company.select.store');



    /*
|--------------------------------------------------------------------------
| Subscription
|--------------------------------------------------------------------------
|
| Esta pantalla debe quedar fuera de company.access,
| porque justamente se utiliza cuando la empresa ya no tiene acceso.
|
*/

    Route::get(
        '/subscription/expired',
        function () {
            return view('subscription.expired');
        }
    )->name('subscription.expired');


    /*
    |--------------------------------------------------------------------------
    | Active Company
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'company',
        'company.access',
    ])->group(function () {


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        |
        | El dashboard debe poder verse aunque todavía
        | no exista una jornada abierta.
        |
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Financial Day
        |--------------------------------------------------------------------------
        |
        | Deben ser accesibles SIN jornada abierta,
        | porque justamente permiten iniciarla.
        |
        */

        Route::get(
            '/financial-days/open',
            [
                FinancialDayController::class,
                'create'
            ]
        )->name('financial-days.create');


        Route::post(
            '/financial-days/open',
            [
                FinancialDayController::class,
                'store'
            ]
        )->name('financial-days.store');

        Route::post(
            '/financial-days/import-balances/analyze',
            [OpeningBalanceImportController::class, 'analyze']
        )->name('financial-days.import-balances.analyze');

        Route::delete(
            '/financial-days/current/reset',
            [FinancialDayController::class, 'resetCurrent']
        )->name('financial-days.reset-current');

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        |
        | No depende de una jornada financiera.
        |
        */

        Route::get(
            '/settings',
            [SettingsController::class, 'index']
        )->name('settings.index');


        Route::patch(
            '/settings/branding',
            [SettingsController::class, 'updateBranding']
        )->name('settings.branding.update');


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        |
        | La administración de usuarios pertenece a la empresa,
        | pero no depende de una jornada financiera.
        |
        */

        Route::resource(
            'users',
            UserController::class
        )->except([
            'show'
        ]);


        /*
        |--------------------------------------------------------------------------
        | Financial Reminders
        |--------------------------------------------------------------------------
        |
        | Los recordatorios pertenecen al usuario y a la empresa activa.
        | No requieren una jornada financiera abierta.
        |
        */

        Route::get(
            '/reminders',
            [FinancialReminderController::class, 'index']
        )->name('reminders.index');


        Route::post(
            '/reminders',
            [FinancialReminderController::class, 'store']
        )->name('reminders.store');


        Route::patch(
            '/reminders/{reminder}/complete',
            [FinancialReminderController::class, 'complete']
        )->name('reminders.complete');


        Route::delete(
            '/reminders/{reminder}',
            [FinancialReminderController::class, 'destroy']
        )->name('reminders.destroy');


        /*
        |--------------------------------------------------------------------------
        | Requires Open Financial Day
        |--------------------------------------------------------------------------
        |
        | Todo lo que esté dentro de este grupo queda bloqueado
        | cuando la empresa activa no tiene jornada abierta.
        |
        */

        Route::middleware('financial.day')->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Dashboard Sync
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard/sync',
                [DashboardController::class, 'sync']
            )->name('dashboard.sync');


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/history/export',
                [HistoryController::class, 'export']
            )->name('history.export');


            Route::get(
                '/history',
                [HistoryController::class, 'index']
            )->name('history.index');


            /*
            |--------------------------------------------------------------------------
            | Accounts
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | Estas rutas específicas van ANTES del resource.
            |
            */

            Route::get(
                '/accounts/{account}/export',
                [
                    AccountController::class,
                    'export'
                ]
            )->name('accounts.export');


            Route::get(
                '/accounts/{account}/movements',
                [
                    AccountController::class,
                    'movements'
                ]
            )->name('accounts.movements');


            Route::get(
                '/accounts/{account}/movement-control',
                [
                    AccountController::class,
                    'movementControl'
                ]
            )->name('accounts.movement-control');


            Route::post(
                '/accounts/{account}/movement-control',
                [
                    AccountController::class,
                    'processMovementControl'
                ]
            )->name('accounts.movement-control.process');


            Route::post(
                '/accounts/{account}/movement-control/compare',
                [
                    AccountController::class,
                    'compareMovementControl'
                ]
            )->name('accounts.movement-control.compare');


            Route::get(
                '/accounts/{account}/alerts',
                [
                    AccountController::class,
                    'alerts'
                ]
            )->name('accounts.alerts');


            Route::put(
                '/accounts/{account}/alerts',
                [
                    AccountController::class,
                    'updateAlerts'
                ]
            )->name('accounts.alerts.update');


            Route::resource(
                'accounts',
                AccountController::class
            );


            /*
            |--------------------------------------------------------------------------
            | Transactions
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | /transactions/export debe declararse
            | ANTES del resource.
            |
            */

            Route::get(
                '/transactions/export',
                [
                    TransactionController::class,
                    'export'
                ]
            )->name('transactions.export');


            Route::patch(
                '/transactions/{transaction}/execute',
                [
                    TransactionController::class,
                    'execute'
                ]
            )->name('transactions.execute');


            Route::resource(
                'transactions',
                TransactionController::class
            );
        });
    });
});


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Platform Administration
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'platform.admin'
])
    ->prefix('aeria-admin')
    ->name('aeria-admin.')
    ->group(function () {


        Route::get(
            '/',
            [PlatformAdminController::class, 'index']
        )->name('index');


        Route::patch(
            '/companies/{company}/approve',
            [PlatformAdminController::class, 'approve']
        )->name('companies.approve');


        Route::patch(
            '/companies/{company}/suspend',
            [PlatformAdminController::class, 'suspend']
        )->name('companies.suspend');


        Route::get(
            '/companies/{company}',
            [PlatformAdminController::class, 'show']
        )->name('companies.show');
    });
