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
        Schema::create('extension_usage_dailies', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('extension_version', 16)->default('unknown');
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();

            $table->unique(['date', 'extension_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_usage_dailies');
    }
};
