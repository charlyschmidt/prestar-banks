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

        'trial_started_at',
        'trial_ends_at',
        'subscription_started_at',
        'subscription_ends_at',
        'subscription_lifetime',
        'trial_expired_email_sent_at',
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

        if (!$this->subscription_started_at) {
            return false;
        }

        if (!$this->subscription_ends_at) {
            return false;
        }

        return now()->lt($this->subscription_ends_at);
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
}
