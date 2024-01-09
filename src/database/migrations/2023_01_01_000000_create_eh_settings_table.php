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
        Schema::create('eh_settings', function (Blueprint $table) {

            $table->increments('id');

            // System Information
            $table->tinyinteger('site_lockout')->nullable();
            $table->string('system_banner')->nullable();
            $table->tinyinteger('system_banner_blink')->nullable();
            $table->string('message_welcome')->nullable();
            $table->string('message_jumbotron')->nullable();
            $table->string('message_copyright')->nullable();

            // Data Validation & Defaults
            $table->string('date_validation_low')->nullable();
            $table->string('default_time_zone')->nullable();

            // Site Contacts & Emails
            $table->string('site_contact_email')->nullable();
            $table->string('site_contact_name')->nullable();
            $table->string('default_from_email')->nullable();
            $table->string('default_from_name')->nullable();
            $table->string('default_subject_line')->nullable();

            // Security & Authentication
            // Note: eco-helpers does not provide an authentication system
            // but these variables are provided to incorporate into your own controller modifications if desired.
            $table->integer('logout_timer')->nullable();                    // Seconds before session cookie expires.
            $table->integer('minimum_password_length')->nullable();         // Shortest password allowed.
            $table->integer('days_to_lockout')->nullable();                 // Number of days with no login before user is deactivated.
            $table->integer('failed_attempts')->nullable();                 // Number of sequential failed attempts before wait timer kicks in.
            $table->integer('failed_attempts_timer')->nullable();           // Seconds before you can try the login again after failed_attempts is reached.

            // System maintained
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
        Schema::dropIfExists('eh_settings');
    }

};