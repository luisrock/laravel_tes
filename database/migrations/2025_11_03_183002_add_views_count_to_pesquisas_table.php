<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewsCountToPesquisasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisas', function (Blueprint $table) {
            $table->integer('views_count')->default(0)->after('concept_validated_at');
            $table->index(['views_count']);
            $table->timestamp('last_synced_at')->nullable()->after('views_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisas', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropColumn(['views_count', 'last_synced_at']);
        });
    }
}
