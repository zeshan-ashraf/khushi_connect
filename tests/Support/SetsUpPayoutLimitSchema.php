<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal schema for payout-limit tests (avoids incomplete production migrations).
 */
trait SetsUpPayoutLimitSchema
{
    protected function setUpPayoutLimitSchema(): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('app.timezone', 'Asia/Karachi');
        Config::set('payout.limits', [
            'amount_min' => 1,
            'amount_max' => 50000,
            'daily_default' => 100000,
        ]);

        $this->app['db']->purge();
        $this->app['db']->reconnect();

        Schema::dropIfExists('payouts');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('user_role')->nullable();
            $table->unsignedBigInteger('payout_daily_limit')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 16, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('phone')->nullable();
            $table->string('transaction_type')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at'], 'payouts_user_id_status_created_at_index');
        });
    }
}
