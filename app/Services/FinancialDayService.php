<?php

namespace App\Services;

use App\Models\FinancialDay;
use App\Models\AccountDailyBalance;
use Illuminate\Support\Facades\DB;

class FinancialDayService
{


    public function current()
    {
        return FinancialDay::whereDate(
            'date',
            today()
        )
            ->where(
                'status',
                'open'
            )
            ->first();
    }





    public function findByDate($date)
    {
        return FinancialDay::whereDate(
            'date',
            $date
        )
            ->first();
    }





    public function open(array $balances)
    {

        $existing = FinancialDay::whereDate(
            'date',
            today()
        )
            ->where('status', 'open')
            ->first();



        if ($existing) {

            return $existing;
        }



        return DB::transaction(function () use ($balances) {


            $day = FinancialDay::create([

                'date' => today(),

                'status' => 'open',

                'opened_by' => auth()->id(),

                'opened_at' => now()

            ]);



            foreach ($balances as $accountId => $amount) {


                AccountDailyBalance::updateOrCreate(

                    [
                        'financial_day_id' => $day->id,

                        'account_id' => $accountId
                    ],

                    [
                        'initial_balance' => $amount,

                        'current_balance' => $amount
                    ]

                );
            }



            return $day;
        });
    }





    public function close(FinancialDay $day)
    {

        if ($day->status === 'closed') {

            return $day;
        }



        $day->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);



        return $day;
    }
}
