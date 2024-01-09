<?php

namespace ScottNason\EcoHelpers\Models;

class ehSetting extends ehBaseModel
{
    protected $table = 'eh_settings';

    /**
     * Let's the Controls class know which input data should be treated as date formats.
     *
     * @var string[]
     */
    public $dates = ['date_validation_low', 'created_at', 'updated_at'];

    public $casts = [
        'date_validation_low' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The Controls class will automatically fill in the label names if they are defined in the model.
     * (If not, it will use the database field names.)
     *
     * @var string[]
     */
    public $labels = [


        'id'=>'id',
        'site_lockout'=>'Site Lockout',
        'system_banner'=>'System Banner',
        'system_banner_blink'=>'Banner Blink',

        'message_welcome'=>'Welcome Message',
        'message_jumbotron'=>'Jumbotron Message',
        'message_copyright'=>'Copyright Message',

        'date_validation_low'=>'Low Validation Date',
        'default_time_zone'=>'Default Time Zone',

        'site_contact_email'=>'Site Contact Email',
        'site_contact_name'=>'Site Contact Name',
        'default_from_email'=>'Default From Email',
        'default_from_name'=>'Default From Name',
        'default_subject_line'=>'Default Subject',

        'logout_timer'=>'Logout Time',
        'minimum_password_length'=>'Minimum Password',
        'days_to_lockout'=>'Days to Lockout',
        'failed_attempts'=>'Failed Attempts',
        'failed_attempts_timer'=>'Failed Attempts Timer',

        'created_by'=>'created by',
        'created_at'=>'created date',
        'updated_by'=>'updated by',
        'updated_at'=>'updated date',

        ];

    public $guarded = [
        '_token',
        'new',
        'delete',
        'save'
    ];



}
