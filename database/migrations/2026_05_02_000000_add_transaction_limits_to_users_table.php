<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('transaction_limit_enabled')->default(false)->after('new_user_max_amount');
            $table->unsignedInteger('transaction_amount_min')->nullable()->after('transaction_limit_enabled');
            $table->unsignedInteger('transaction_amount_max')->default(50000)->after('transaction_amount_min');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_limit_enabled',
                'transaction_amount_min',
                'transaction_amount_max',
            ]);
        });
    }
};
