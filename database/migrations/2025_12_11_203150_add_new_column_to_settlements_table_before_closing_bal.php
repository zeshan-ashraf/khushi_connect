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
        // Using raw SQL to add column after 'settled' (which is before 'closing_bal')
        // MySQL doesn't support BEFORE keyword, so we use AFTER with the preceding column
        DB::statement("ALTER TABLE settlements ADD COLUMN transfer_wallet DOUBLE DEFAULT 0 AFTER settled");
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

