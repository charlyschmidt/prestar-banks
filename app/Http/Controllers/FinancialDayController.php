<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FinancialDay;
use App\Services\FinancialDayService;
use Illuminate\Http\Request;


class FinancialDayController extends Controller
{


    public function create()
    {

        $day = FinancialDay::whereDate(
            'date',
            today()
        )
        ->where(
            'status',
            'open'
        )
        ->first();



        if($day){

            return redirect()
                ->route('dashboard');

        }



        $accounts = Account::all();



        return view(
            'financial-days.create',
            compact('accounts')
        );

    }





    public function store(
        Request $request,
        FinancialDayService $financialDayService
    )
    {


        $data = $request->validate([

            'balances' => 'required|array'

        ]);



        foreach($data['balances'] as $balance){

            if($balance === null || $balance === ''){

                return back()
                    ->withErrors([
                        'balances'=>'Todos los saldos son obligatorios'
                    ]);

            }

        }




        $financialDayService->open(
            $data['balances']
        );



        return redirect()
            ->route('dashboard');

    }

}