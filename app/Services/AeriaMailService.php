<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Mail\AccountApprovedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AeriaMailService
{

    public function sendPasswordReset(
        User $user,
        string $token
    ): void {

        $resetUrl = URL::route(
            'password.reset',
            [
                'token' => $token,
                'email' => $user->email,
            ]
        );


        $userName =
            $user->name
            ?? $user->username
            ?? 'Usuario';


        Mail::to($user->email)
            ->send(
                new PasswordResetMail(
                    userName: $userName,
                    resetUrl: $resetUrl
                )
            );
    }

    public function sendAccountApproved(
        User $user
    ): void {
        $userName =
            $user->name
            ?? $user->username
            ?? 'Usuario';

        $loginUrl = route('login');

        Mail::to($user->email)
            ->send(
                new AccountApprovedMail(
                    userName: $userName,
                    loginUrl: $loginUrl
                )
            );
    }
}
