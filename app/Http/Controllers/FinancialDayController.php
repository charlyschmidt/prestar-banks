<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Services\FinancialDayService;
use Illuminate\Http\Request;


class FinancialDayController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Abrir jornada
    |--------------------------------------------------------------------------
    */

    public function create(
        FinancialDayService $financialDayService
    ) {

        $day = $financialDayService->current();


        if ($day) {

            return redirect()
                ->route('dashboard');
        }


        /*
        |--------------------------------------------------------------------------
        | Cuentas + monedas
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | Santander
        |   ARS
        |   USD
        |   EUR
        |
        */

        $accounts = Account::with([
            'balances' => function ($query) {

                $query->orderBy('currency');

            }
        ])
            ->orderBy('name')
            ->get();


        return view(
            'financial-days.create',
            compact('accounts')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar apertura de jornada
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        FinancialDayService $financialDayService
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'balances' =>
                'required|array|min:1',

            'balances.*' =>
                'required|numeric|min:0',

        ], [

            'balances.required' =>
                'Debés ingresar los saldos iniciales.',

            'balances.array' =>
                'Los saldos enviados no son válidos.',

            'balances.min' =>
                'Debés ingresar al menos un saldo.',

            'balances.*.required' =>
                'Todos los saldos son obligatorios.',

            'balances.*.numeric' =>
                'Todos los saldos deben ser numéricos.',

            'balances.*.min' =>
                'Los saldos iniciales no pueden ser negativos.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Validar AccountBalance
        |--------------------------------------------------------------------------
        |
        | No confiamos únicamente en los IDs enviados
        | desde el formulario.
        |
        | AccountBalance utiliza BelongsToCompany,
        | por lo tanto solamente encontrará saldos
        | pertenecientes a la empresa activa.
        |
        */

        foreach (
            $data['balances'] as $accountBalanceId => $amount
        ) {

            AccountBalance::findOrFail(
                $accountBalanceId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Abrir jornada
        |--------------------------------------------------------------------------
        */

        $financialDayService->open(
            $data['balances']
        );


        return redirect()
            ->route('dashboard');
    }
}