<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\FinancialDay;
use App\Models\AccountDailyBalance;
use App\Models\Transaction;
use App\Services\FinancialDayService;
use App\Exports\AccountTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;

class AccountController extends Controller
{


    public function index()
    {

        $accounts = Account::all();


        return view(
            'accounts.index',
            compact('accounts')
        );
    }



    public function create()
    {

        return view('accounts.create');
    }





    public function store(Request $request)
    {

        $data = $request->validate([

            'name' => 'required|string|max:100',

            'type' => 'required|string',

            'logo' => 'nullable|image|max:2048'

        ]);

        if ($request->hasFile('logo')) {

            $data['logo'] =
                $request->file('logo')
                ->store('banks', 'public');
        }

        Account::create($data);



        return redirect()
            ->route('accounts.index')
            ->with(
                'success',
                'Cuenta creada'
            );
    }





    public function edit(Account $account)
    {

        return view(
            'accounts.edit',
            compact('account')
        );
    }





    public function update(Request $request, Account $account)
    {
        $data = $request->validate([

            'name' => 'required|string|max:100',

            'type' => 'required|string',

            'logo' => 'nullable|image|max:2048'

        ]);


        if ($request->hasFile('logo')) {

            $data['logo'] = $request->file('logo')
                ->store('accounts', 'public');
        }


        $account->update($data);


        return redirect()
            ->route('accounts.index')
            ->with('success', 'Cuenta actualizada correctamente');
    }





    public function destroy(Account $account)
    {

        $account->delete();


        return back();
    }

    public function movements(
        Account $account,
        FinancialDayService $financialDayService
    ) {

        $day = $financialDayService->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'No hay jornada abierta'
                );
        }



        $balance = AccountDailyBalance::where(
            'financial_day_id',
            $day->id
        )
            ->where(
                'account_id',
                $account->id
            )
            ->first();



        $movements = Transaction::where(
            'financial_day_id',
            $day->id
        )
            ->where(
                'account_id',
                $account->id
            )
            ->orderBy(
                'date',
                'desc'
            )
            ->get();



        return view(
            'accounts.movements',
            compact(
                'account',
                'balance',
                'movements'
            )
        );
    }

    public function export(
        Account $account,
        FinancialDayService $financialDayService
    ) {

        $day = $financialDayService->current();


        if (!$day) {

            return back()
                ->with(
                    'error',
                    'No hay jornada abierta.'
                );
        }



        return Excel::download(

            new AccountTransactionsExport(
                $account->id,
                $day,
                auth()->user()
            ),

            'Movimientos-' . $account->name . '-' . $day->date->format('Y-m-d') . '.xlsx'

        );
    }
}
