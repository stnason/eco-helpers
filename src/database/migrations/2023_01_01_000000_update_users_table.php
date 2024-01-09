<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Modify the out of the box user table to include more login/user profile features.
 *
 * Note:
 * The original "name" will be used for the users "login name".
 * The original "email" will be used for the users "registered" email (for password resets).
 * The original "password" stays on the password field.
 *
 * May require:
    https://makitweb.com/how-to-update-table-structure-using-migration-laravel/
    Require doctrine/dbal package to modify existing columns –
    composer require doctrine/dbal
 */


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->tinyInteger('archived')->after('id')->nullable()->default(0);
            $table->string('first_name')->after('archived')->nullable()->default(null);
            $table->string('last_name')->after('first_name')->nullable()->default(null);
            $table->string('middle_name')->after('last_name')->nullable()->default(null);
            $table->string('nickname')->after('middle_name')->nullable()->default(null);

            $table->string('title')->after('middle_name')->nullable()->default(null);
            $table->string('description')->after('title')->nullable()->default(null);
            $table->string('company')->after('description')->nullable()->default(null);
            $table->string('reports_to')->after('company')->nullable()->default(null);

            $table->string('phone_work_desk')->after('reports_to')->nullable()->default(null);
            $table->string('phone_work_cell')->after('phone_work_desk')->nullable()->default(null);
            $table->string('phone_personal_home')->after('phone_work_cell')->nullable()->default(null);
            $table->string('phone_personal_cell')->after('phone_personal_home')->nullable()->default(null);

            $table->string('email_work')->after('phone_personal_cell')->nullable()->default(null);
            $table->string('email_personal')->after('email_work')->nullable()->default(null);

            $table->text('comments')->after('email_personal')->nullable()->default(null);

            $table->tinyInteger('login_active')->after('comments')->nullable()->default(null);
            $table->Integer('default_role')->after('login_active')->nullable()->default(null);
            $table->Integer('acting_role')->after('default_role')->nullable()->default(null);
            $table->tinyInteger('force_password_reset')->after('login_active')->nullable()->default(null);
            $table->timestamp('login_created')->after('force_password_reset')->nullable()->default(null);
            $table->timestamp('last_login')->after('login_created')->nullable()->default(null);
            $table->integer('login_count')->after('last_login')->nullable()->default(null);

            $table->string('timezone')->after('login_count')->nullable()->default(null);

            $table->string('created_by')->after('remember_token')->nullable()->default(null);
            $table->string('updated_by')->after('created_at')->nullable()->default(null);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove all of the created fields;
    }
};
