<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ej: "showClasses"
            $table->string('request_url');    // Ej: "https://mardev.es/api/core/policies/classes/show/{id}"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('policies');
    }
};
