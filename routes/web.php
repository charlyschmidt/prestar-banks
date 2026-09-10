<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FinancialDayController;


/*
|--------------------------------------------------------------------------
| Entrada principal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');

});


/*
|--------------------------------------------------------------------------
| Sistema protegido
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::resource(
        'accounts',
        AccountController::class
    );

    Route::get(
        '/accounts/{account}/movements',
        [AccountController::class, 'movements']
    )->name('accounts.movements');

    Route::resource(
        'transactions',
        TransactionController::class
    );

    Route::get(
        '/jornada/apertura',
        [FinancialDayController::class, 'create']
    )->name('financial-days.create');

    Route::post(
        '/jornada/apertura',
        [FinancialDayController::class, 'store']
    )->name('financial-days.store');

});


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';