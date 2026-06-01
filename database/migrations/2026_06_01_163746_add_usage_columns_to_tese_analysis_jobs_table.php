<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tese_analysis_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('tese_analysis_jobs', 'input_tokens')) {
                $table->unsignedInteger('input_tokens')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('tese_analysis_jobs', 'output_tokens')) {
                $table->unsignedInteger('output_tokens')->nullable()->after('input_tokens');
            }

            if (! Schema::hasColumn('tese_analysis_jobs', 'cost_usd')) {
                $table->decimal('cost_usd', 10, 6)->nullable()->after('output_tokens');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tese_analysis_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('tese_analysis_jobs', 'cost_usd')) {
                $table->dropColumn('cost_usd');
            }

            if (Schema::hasColumn('tese_analysis_jobs', 'output_tokens')) {
                $table->dropColumn('output_tokens');
            }

            if (Schema::hasColumn('tese_analysis_jobs', 'input_tokens')) {
                $table->dropColumn('input_tokens');
            }
        });
    }
};
