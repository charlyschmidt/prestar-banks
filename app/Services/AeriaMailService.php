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
use App\Mail\ContactMessageMail;

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

    public function sendContactMessage(
        string $name,
        string $email,
        string $message
    ): void {
        Mail::to('ayuda@aeriafinance.com.ar')
            ->send(
                new ContactMessageMail(
                    senderName: $name,
                    senderEmail: $email,
                    contactMessage: $message
                )
            );
    }
}
