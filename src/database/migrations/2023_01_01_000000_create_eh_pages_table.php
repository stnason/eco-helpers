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
        Schema::create('eh_pages', function (Blueprint $table) {

            $table->increments('id');

            $table->string('name');
            $table->string('alt_text');
            $table->string('description');
            $table->string('type');
            $table->tinyInteger('active');
            $table->tinyInteger('security');
            $table->string('icon');
            $table->integer('parent_id');
            $table->tinyInteger('menu_item');
            $table->integer('order');
            $table->string('route');

            /*
            $table->tinyInteger('http_get_head');
            $table->tinyInteger('http_put_patch');
            $table->tinyInteger('http_post');
            $table->tinyInteger('http_delete');
            */

            $table->text('feature_1');
            $table->text('feature_2');
            $table->text('feature_3');
            $table->text('feature_4');
            $table->text('comment');

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
        Schema::dropIfExists('eh_pages');
    }

};