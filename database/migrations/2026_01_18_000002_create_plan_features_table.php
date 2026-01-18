<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_product_id');
            $table->string('feature_key');
            $table->text('feature_value')->nullable();
            $table->timestamps();

            // Constraint para evitar duplicações
            $table->unique(['stripe_product_id', 'feature_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_features');
    }
}
