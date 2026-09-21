<?php

namespace App\Services;

use App\Models\AccountDailyBalance;
use Illuminate\Support\Collection;

class LowBalanceAlertService
{
    public function getActiveAlerts(): Collection
    {
        $companyId = app(CompanyContextService::class)->id();

        if (!$companyId) {
            return collect();
        }


        $financialDay = app(FinancialDayService::class)->current();

        if (!$financialDay) {
            return collect();
        }


        return AccountDailyBalance::query()

            ->with([
                'account',
                'accountBalance',
            ])

            ->where(
                'financial_day_id',
                $financialDay->id
            )

            ->whereHas('accountBalance', function ($query) {

                $query->whereNotNull(
                    'low_balance_threshold'
                );
            })

            ->get()

            ->filter(function ($dailyBalance) {

                $accountBalance =
                    $dailyBalance->accountBalance;


                if (!$accountBalance) {
                    return false;
                }


                $threshold =
                    $accountBalance
                    ->low_balance_threshold;


                if ($threshold === null) {
                    return false;
                }


                return
                    (float) $dailyBalance->current_balance
                    <=
                    (float) $threshold;
            })

            ->values();
    }

    public function count(): int
    {
        return $this->getActiveAlerts()->count();
    }
}
