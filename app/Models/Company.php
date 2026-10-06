<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Company extends Model
{
    protected $fillable = [
        'name',
        'billing_name',
        'slug',
        'tax_id',
        'email',
        'phone',

        'billing_address',
        'billing_city',
        'billing_province',
        'billing_postal_code',
        'billing_tax_status',

        'status',
        'background_color',
        'logo',

        'trial_started_at',
        'trial_ends_at',
        'subscription_started_at',
        'subscription_ends_at',
        'subscription_lifetime',

        'trial_expired_email_sent_at',
        'onboarding_completed_at',
        'requires_invoice',
    ];

    protected function casts(): array
    {
        return [
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',

            'subscription_started_at' => 'datetime',
            'subscription_ends_at' => 'datetime',

            'subscription_lifetime' => 'boolean',

            'trial_expired_email_sent_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'requires_invoice' => 'boolean',
        ];
    }

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

    public function isOnTrial(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if (!$this->trial_started_at || !$this->trial_ends_at) {
            return false;
        }

        return now()->lt($this->trial_ends_at);
    }


    public function hasActiveSubscription(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->subscription_lifetime) {
            return true;
        }

        return $this->activeSubscription() !== null;
    }


    public function hasAccess(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return
            $this->isOnTrial()
            || $this->hasActiveSubscription();
    }

    /*
    |--------------------------------------------------------------------------
    | Suscripciones
    |--------------------------------------------------------------------------
    */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pagos de suscripciones
    |--------------------------------------------------------------------------
    */

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(
            SubscriptionPayment::class
        );
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(CompanyApiKey::class);
    }
}
