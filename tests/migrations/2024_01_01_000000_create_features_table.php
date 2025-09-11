<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->string('name');
            $table->string('scope');
            $table->json('value');
            $table->timestamp('created_at')->nullable();

            $table->unique(['name', 'scope']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('features');
    }
};
