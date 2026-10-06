<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {

        $companyId = (int) $request
            ->attributes
            ->get('company_id');


        /*
        |--------------------------------------------------------------------------
        | Cuentas de la empresa
        |--------------------------------------------------------------------------
        */

        $accounts = Account::withoutGlobalScopes()
            ->with([
                'balances' => function ($query) use ($companyId) {

                    $query
                        ->withoutGlobalScopes()
                        ->where(
                            'company_id',
                            $companyId
                        )
                        ->orderBy('currency');
                }
            ])
            ->where(
                'company_id',
                $companyId
            )
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Respuesta
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => $accounts
                ->map(function ($account) {

                    return [

                        'id' =>
                            $account->id,

                        'name' =>
                            $account->name,

                        'type' =>
                            $account->type,

                        'balances' =>
                            $account->balances
                                ->map(function ($balance) {

                                    return [

                                        'id' =>
                                            $balance->id,

                                        'currency' =>
                                            $balance->currency,

                                    ];

                                })
                                ->values(),

                    ];

                })
                ->values(),
        ]);
    }
}