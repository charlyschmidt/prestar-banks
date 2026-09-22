<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel(
    'user.{userId}',
    function ($user, $userId) {

        return (int) $user->id
            ===
            (int) $userId;

    }
);

Broadcast::channel(
    'dashboard.{companyId}',
    function ($user, $companyId) {

        return $user
            ->companies()
            ->where(
                'companies.id',
                $companyId
            )
            ->where(
                'companies.status',
                'active'
            )
            ->exists();
    }
);