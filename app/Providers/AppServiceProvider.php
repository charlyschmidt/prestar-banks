<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\HeaderService;
use App\Services\ExchangeRateService;
use App\Services\FinanzasArgyRatesService;
use Illuminate\Support\Facades\Cache;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {

        View::composer(
            '*',
            function ($view) {

                $headerService = app(HeaderService::class);


                $view->with(
                    'header',
                    $headerService->getData()
                );
            }
        );


        View::composer('subscription.expired', function ($view) {

            $exchangeRate = null;

            try {

                $exchangeRate = Cache::remember(
                    'aeria_usd_oficial_venta',
                    now()->addMinutes(10),
                    fn() => app(ExchangeRateService::class)
                        ->getUsdSellRate()
                );
            } catch (\Throwable $e) {

                report($e);
            }

            $view->with(
                'exchangeRate',
                $exchangeRate
            );
        });

        // Header: solo leer cache (nunca hacer HTTP mientras se renderiza).
        View::composer('layouts.header', function ($view) {
            $view->with(
                'officialRates',
                app(FinanzasArgyRatesService::class)->current()
            );
        });
    }
}
