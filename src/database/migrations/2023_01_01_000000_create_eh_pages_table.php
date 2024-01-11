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
            $table->string('alt_text')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
            $table->string('type')->nullable()->default(null);
            $table->tinyInteger('active')->nullable()->default(null);
            $table->tinyInteger('security')->nullable()->default(null);
            $table->string('icon')->nullable()->default(null);
            $table->integer('parent_id')->nullable()->default(null);
            $table->tinyInteger('menu_item')->nullable()->default(null);
            $table->integer('order')->nullable()->default(null);
            $table->string('route')->nullable()->default(null);

            $table->text('feature_1')->nullable()->default(null);
            $table->text('feature_2')->nullable()->default(null);
            $table->text('feature_3')->nullable()->default(null);
            $table->text('feature_4')->nullable()->default(null);
            $table->text('comment')->nullable()->default(null);

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