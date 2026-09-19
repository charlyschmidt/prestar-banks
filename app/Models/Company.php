<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tax_id',
        'email',
        'phone',
        'status',
        'background_color',
        'logo',
    ];


    public function users()
    {
        return $this->belongsToMany(
            User::class
        )
            ->withPivot([
                'role',
                'is_admin'
            ])
            ->withTimestamps();
    }


    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}