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
        Schema::create('eh_roles', function (Blueprint $table) {

            $table->increments('id');
            $table->tinyInteger('active');
            $table->tinyInteger('site_admin');
            $table->string('name');
            $table->string('description')->nullable();
            $table->tinyInteger('restrict_flag');       // Custom Role restriction (and be used bu controllers and views for additional restrictions for this group.
            $table->string('default_home_page')->nullable();;

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
        Schema::dropIfExists('eh_roles');
    }

};