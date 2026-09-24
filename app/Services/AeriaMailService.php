<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Mail\AccountApprovedMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\AccountCreatedMail;
use App\Mail\TrialExpiredMail;

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

        Mail::to($user->email)
            ->send(
                new PasswordResetMail(
                    recipientName: $user->name,
                    resetUrl: $resetUrl
                )
            );
    }


    public function sendAccountApproved(
        User $user,
        Company $company
    ): void {

        $loginUrl = route('login');

        $trialEndsAt =
            $company->trial_ends_at;

        Mail::to($user->email)
            ->send(
                new AccountApprovedMail(
                    recipientName: $user->name,
                    loginUrl: $loginUrl,
                    trialEndsAt: $trialEndsAt
                )
            );
    }

    public function sendAccountCreated(
        User $user
    ): void {

        Mail::to($user->email)
            ->send(
                new AccountCreatedMail(
                    recipientName: $user->name
                )
            );
    }

    public function sendTrialExpired(
       User $user
    ): void {
        Mail::to($user->email)
            ->send(
                new TrialExpiredMail(
                    recipientName: $user->name,
                    subscriptionUrl: route(
                        'subscription.expired'
                    )
                )
            );
    }
}
