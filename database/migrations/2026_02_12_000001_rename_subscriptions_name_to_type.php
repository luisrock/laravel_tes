<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cashier v15 renamed the 'name' column to 'type' in the subscriptions table.
     */
    public function up(): void
    {
        if (Schema::hasColumn('subscriptions', 'name') && ! Schema::hasColumn('subscriptions', 'type')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('name', 'type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('subscriptions', 'type') && ! Schema::hasColumn('subscriptions', 'name')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('type', 'name');
            });
        }
    }
};
