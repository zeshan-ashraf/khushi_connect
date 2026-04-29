<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table
                ->unsignedInteger('new_user_max_amount')
                ->default(0)
                ->after('new_user_verification')
                ->comment('Maximum allowed amount for unverified new users');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('new_user_max_amount');
        });
    }
};
