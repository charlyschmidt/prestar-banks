<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'username' => env('ADMIN_USERNAME', 'ivantorio'),
            ],
            [
                'name' => env('ADMIN_NAME', 'Ivantorio'),
                'email' => env(
                    'ADMIN_USERNAME',
                    'ivantorio'
                ) . '@local',
                'password' => env(
                    'ADMIN_PASSWORD',
                    'cambiar-password'
                ),
                'is_admin' => true,
            ]
        );
    }
}
