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
        Schema::create('eh_notifications', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('user_id')->nullable();

            $table->tinyinteger('auto_clear')->nullable();
            $table->tinyinteger('auto_popup')->nullable();
            $table->tinyinteger('user_clearable')->nullable();
            $table->tinyinteger('viewed')->nullable();
            $table->tinyinteger('exclusive')->nullable();

            $table->string('route')->nullable();
            $table->string('title')->nullable();

            $table->text('content')->nullable();
            $table->date('expiration')->nullable();

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
        Schema::dropIfExists('eh_notifications');
    }

};


