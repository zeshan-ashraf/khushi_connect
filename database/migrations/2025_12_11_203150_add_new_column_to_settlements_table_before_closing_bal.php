<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * NOTE: Please change 'transfer_wallet' to your desired column name before running the migration.
     */
    public function up(): void
    {
        // First add the column using Schema builder (will be added at the end)
        Schema::table('settlements', function (Blueprint $table) {
            $table->double('transfer_wallet', 15, 2)->default(0)->after('settled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropColumn('transfer_wallet');
        });
    }
};

