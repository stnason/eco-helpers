<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eh_access_tokens', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('page_id')->unsigned()->nullable();
            $table->integer('role_id')->unsigned()->nullable();
            $table->integer('token')->unsigned()->nullable();

            $table->string('created_by');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by');
            $table->timestamp('updated_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eh_access_tokens');
    }

};





