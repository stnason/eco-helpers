<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\MustVerifyEmail;          // Needed (and the implementation on the class) to force email validation for a new user.
//use Illuminate\Notifications\Notifiable;

use ScottNason\EcoHelpers\Traits\ehUserFunctions;

/*
 * 5/14/2023; UPDATE: Moved the trait back here and using ehUser for all the internal framework calls to those functions.
 *
 */


class ehUser extends ehBaseAuthenticatable implements MustVerifyEmail
{
    //use Notifiable;
    use ehUserFunctions;

    protected $table = 'users';


    /**
     * Lets the Controls class know which input data should be treated as date formats.
     *
     * @var string[]
     */
    public $dates = ['created_at', 'updated_at'];



    /**
     * Controls will use this array to set readonly on these fields.
     *
     * @var string[]
     */
    public $disabled = ['remember_token', 'email_verified_at'];


    /**
     * The Controls class will automatically fill in the label names if they are defined in the model.
     * (If not, it will use the database field names.)
     *
     * @var string[]
     */
    public $labels = [

        'id'=>'',
        'archived'=>'Archived',
        'first_name'=>'First Name',
        'last_name'=>'Last Name',
        'middle_name'=>'Middle',
        'nickname'=>'Nickname',

        'title'=>'Title',
        'description'=>'Description',
        'company'=>'Company',
        'reports_to'=>'Reports To',

        'default_role'=>'Default Group',
        'acting_role'=>'Acting Role',

        'phone_work_desk'=>'Work Phone',
        'phone_work_cell'=>'Work Cell',
        'phone_personal_home'=>'Home Phone',
        'phone_personal_cell'=>'Personal Cell',

        'email_work'=>'Work Email',
        'email_personal'=>'Personal Email',

        'comments'=>'Comments',

        'login_active'=>'Login Active',
        'force_password_reset'=>'Force PW Reset',
        'login_created'=>'Login Created',
        'last_login'=>'Last Login',
        'login_count'=>'Login Count',

        'timezone'=>'Time Zone',
        'user'=>'Login User Name',
        'email'=>'Registered Email',
        'email_verified_at'=>'Email Verified',
        'password'=>'Password',
        'remember_token'=>'Remember Token',

        'created_by'=>'created by',
        'created_at'=>'created date',
        'updated_by'=>'updated by',
        'updated_at'=>'updated date',

    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   /*
   // Note: This will need to be set when allowing end-users to modify their own profile
    public $fillable = [
        'name',
        'email',
        'password',
    ];
    */

    public $guarded = [
        '_token',
        'new',
        'delete',
        'save'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


}
