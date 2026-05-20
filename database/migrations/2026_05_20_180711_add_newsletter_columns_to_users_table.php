<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('newsletter_subscribed_at')->nullable()->after('email_verified_at');
            $table->timestamp('newsletter_synced_at')->nullable()->after('newsletter_subscribed_at');
            $table->index('newsletter_subscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['newsletter_subscribed_at']);
            $table->dropColumn(['newsletter_subscribed_at', 'newsletter_synced_at']);
        });
    }
};
