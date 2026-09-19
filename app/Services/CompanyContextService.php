<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyContextService
{
    public function id(): ?int
    {
        if (!Auth::check()) {
            return null;
        }

        $companyId = session('company_id');

        if (!$companyId) {
            return null;
        }

        $belongsToCompany = Auth::user()
            ->companies()
            ->where(
                'companies.id',
                $companyId
            )
            ->exists();

        if (!$belongsToCompany) {
            session()->forget('company_id');

            return null;
        }

        return (int) $companyId;
    }


    public function company(): ?Company
    {
        $companyId = $this->id();

        if (!$companyId) {
            return null;
        }

        return Company::find(
            $companyId
        );
    }


    public function set(Company $company): void
    {
        if (!Auth::check()) {
            abort(403);
        }

        $belongsToCompany = Auth::user()
            ->companies()
            ->where(
                'companies.id',
                $company->id
            )
            ->exists();

        if (!$belongsToCompany) {
            abort(404);
        }

        session([
            'company_id' => $company->id
        ]);
    }


    public function clear(): void
    {
        session()->forget(
            'company_id'
        );
    }
}