<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'username',
    'email',
    'password',

    /*
    | Temporales mientras terminamos
    | la migración SaaS.
    */
    'is_admin',
    'role',
    'company_id',
    'is_platform_admin'
])]
#[Hidden([
    'password',
    'remember_token'
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Empresas
    |--------------------------------------------------------------------------
    */

    public function companies()
    {
        return $this->belongsToMany(
            Company::class
        )
            ->withPivot([
                'role',
                'is_admin'
            ])
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Empresa antigua
    |--------------------------------------------------------------------------
    |
    | TEMPORAL.
    | Se eliminará cuando quitemos users.company_id.
    |
    */

    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Membresía en empresa activa
    |--------------------------------------------------------------------------
    */

    public function currentCompanyMembership()
    {
        $companyId = session(
            'company_id'
        );

        if (!$companyId) {
            return null;
        }


        return $this
            ->companies()
            ->where(
                'companies.id',
                $companyId
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Rol actual
    |--------------------------------------------------------------------------
    */

    public function currentCompanyRole(): ?string
    {
        $membership =
            $this->currentCompanyMembership();

        if (!$membership) {
            return null;
        }

        return $membership
            ->pivot
            ->role;
    }


    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        $membership =
            $this->currentCompanyMembership();

        if (!$membership) {
            return false;
        }

        return (bool)
            $membership
                ->pivot
                ->is_admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Administración
    |--------------------------------------------------------------------------
    */

    public function isAdministration(): bool
    {
        $membership =
            $this->currentCompanyMembership();

        if (!$membership) {
            return false;
        }


        return
            !(bool) $membership
                ->pivot
                ->is_admin
            &&
            $membership
                ->pivot
                ->role
                === 'administration';
    }


    /*
    |--------------------------------------------------------------------------
    | Operador
    |--------------------------------------------------------------------------
    */

    public function isOperator(): bool
    {
        $membership =
            $this->currentCompanyMembership();

        if (!$membership) {
            return false;
        }


        return
            !(bool) $membership
                ->pivot
                ->is_admin
            &&
            $membership
                ->pivot
                ->role
                === 'operator';
    }


    /*
    |--------------------------------------------------------------------------
    | Puede administrar usuarios
    |--------------------------------------------------------------------------
    */

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }


    /*
    |--------------------------------------------------------------------------
    | Puede ejecutar movimientos
    |--------------------------------------------------------------------------
    */

    public function canExecuteTransactions(): bool
    {
        return
            $this->isSuperAdmin()
            ||
            $this->isAdministration();
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }
}