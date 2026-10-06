<?php

use App\Http\Controllers\Api\V1\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TransactionController;


Route::prefix('v1')
    ->middleware(\App\Http\Middleware\AuthenticateApiKey::class)
    ->group(function () {

        Route::get(
            '/accounts',
            [AccountController::class, 'index']
        );

        Route::post(
            '/transactions',
            [TransactionController::class, 'store']
        );

        Route::post(
            '/transactions/bulk',
            [TransactionController::class, 'bulk']
        );
    });
