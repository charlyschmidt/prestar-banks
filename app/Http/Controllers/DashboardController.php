<?php

namespace App\Http\Controllers;

use App\Services\BalanceDayService;


class DashboardController extends Controller
{

    public function index(
        BalanceDayService $balanceDayService
    )
    {

        $summary = $balanceDayService->getDashboardSummary();



        if (!$summary['has_day']) {


            return view(
                'dashboard.no-day',
                [
                    'summary' => $summary
                ]
            );

        }




        return view(
            'dashboard.index',
            [
                'summary' => $summary
            ]
        );

    }

}