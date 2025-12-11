<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            // Fix timestamps so MySQL stops throwing errors
            if (Schema::hasColumn('settlements', 'created_at')) {
                $table->timestamp('created_at')->nullable()->change();
            }

            if (Schema::hasColumn('settlements', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->change();
            }

            // Add your new column
            $table->double('transfer_wallet', 15, 2)->default(0)->after('settled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'transfer_wallet')) {
                $table->dropColumn('transfer_wallet');
            }
        });
    }
};
