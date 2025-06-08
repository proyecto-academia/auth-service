<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('policy_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('model_id'); // Por ejemplo, el ID de una clase concreta
            $table->timestamps();

            $table->unique(['user_id', 'policy_id', 'model_id'], 'user_policy_model_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_policies');
    }
};
