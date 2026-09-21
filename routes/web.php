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

/*
|--------------------------------------------------------------------------
| Entrada principal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/registro/pendiente', function () {

    return view('auth.register-pending');
})->name('register.pending');

/*
|--------------------------------------------------------------------------
| Usuario autenticado
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Selección de empresa
    |--------------------------------------------------------------------------
    |
    | Estas rutas NO llevan middleware company porque justamente
    | se utilizan cuando todavía no existe una empresa activa.
    |
    */

    Route::get(
        '/seleccionar-empresa',
        [CompanySelectionController::class, 'index']
    )->name('company.select');


    Route::post(
        '/seleccionar-empresa',
        [CompanySelectionController::class, 'store']
    )->name('company.select.store');


    /*
    |--------------------------------------------------------------------------
    | Sistema con empresa activa
    |--------------------------------------------------------------------------
    */

    Route::middleware('company')->group(function () {


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
        | Jornada financiera
        |--------------------------------------------------------------------------
        |
        | Deben ser accesibles SIN jornada abierta,
        | porque justamente permiten iniciarla.
        |
        */

        Route::get(
            '/jornada/apertura',
            [
                FinancialDayController::class,
                'create'
            ]
        )->name('financial-days.create');


        Route::post(
            '/jornada/apertura',
            [
                FinancialDayController::class,
                'store'
            ]
        )->name('financial-days.store');


        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        |
        | No depende de una jornada financiera.
        |
        */

        Route::get(
            '/configuracion',
            [SettingsController::class, 'index']
        )->name('settings.index');


        Route::patch(
            '/configuracion/apariencia',
            [SettingsController::class, 'updateBranding']
        )->name('settings.branding.update');


        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        |
        | La administración de usuarios pertenece a la empresa,
        | pero no depende de una jornada financiera.
        |
        */

        Route::resource(
            'usuarios',
            UserController::class
        )->except([
            'show'
        ]);


        /*
        |--------------------------------------------------------------------------
        | Requieren jornada abierta
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
            | Historial
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/historial/exportar',
                [HistoryController::class, 'export']
            )->name('history.export');


            Route::get(
                '/historial',
                [HistoryController::class, 'index']
            )->name('history.index');


            /*
            |--------------------------------------------------------------------------
            | Cuentas
            |--------------------------------------------------------------------------
            */

            /*
            * IMPORTANTE:
            * estas rutas específicas van ANTES del resource.
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
                [AccountController::class, 'alerts']
            )->name('accounts.alerts');

            Route::put(
                '/accounts/{account}/alerts',
                [AccountController::class, 'updateAlerts']
            )->name('accounts.alerts.update');

            Route::resource(
                'accounts',
                AccountController::class
            );


            /*
            |--------------------------------------------------------------------------
            | Movimientos
            |--------------------------------------------------------------------------
            */

            /*
             * IMPORTANTE:
             * /transactions/export debe declararse
             * ANTES del resource.
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
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

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
