<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    'is_admin',
    'role'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable

{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin;
    }


    public function isAdministration(): bool
    {
        return !$this->is_admin
            && $this->role === 'administration';
    }


    public function isOperator(): bool
    {
        return !$this->is_admin
            && $this->role === 'operator';
    }
}
