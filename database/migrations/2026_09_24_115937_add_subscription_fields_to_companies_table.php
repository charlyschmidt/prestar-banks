<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('companies', function (Blueprint $table) {

        $table->timestamp('trial_started_at')
            ->nullable();

        $table->timestamp('trial_ends_at')
            ->nullable()
            ->after('trial_started_at');

        $table->timestamp('subscription_started_at')
            ->nullable()
            ->after('trial_ends_at');

        $table->timestamp('subscription_ends_at')
            ->nullable()
            ->after('subscription_started_at');

        $table->boolean('subscription_lifetime')
            ->default(false)
            ->after('subscription_ends_at');

        $table->timestamp('trial_expired_email_sent_at')
            ->nullable()
            ->after('subscription_lifetime');
    });
}


    public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {

        $table->dropColumn([
            'trial_started_at',
            'trial_ends_at',
            'subscription_started_at',
            'subscription_ends_at',
            'subscription_lifetime',
            'trial_expired_email_sent_at',
        ]);
    });
}
};