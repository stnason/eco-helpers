<?php

namespace App\Models;

use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Models\ehSetting;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// Need this implements on the class to force email validation when a user hits a "protected" route.
// NOTE: You still have to protect the routes with the 'verified' middleware, which checks and adds
//       it automatically by the ehBaseController.
use Illuminate\Contracts\Auth\MustVerifyEmail;

use ScottNason\EcoHelpers\Traits\ehUserFunctions;
use ScottNason\EcoHelpers\Models\ehBaseAuthenticatable;

/**
 * The eco-helpers modified User model.
 * Additional package functions are added through the ehUserFunctions trait.
 *
 */
class User extends ehBaseAuthenticatable implements MustVerifyEmail
{
    //use Notifiable;
    use ehUserFunctions;

    protected $table = 'users';


    // ehControl has been converted to pull in any $casts with date, datetime or timestamp into our $dates array.
    // So, not using it anymore in favor of $casts.
    // public $dates = ['created_at', 'updated_at', 'last_login', 'login_created'];

    /**
     * Controls will use this array to set readonly on these fields.
     *
     * @var string[]
     */
    public $disabled = ['remember_token'];


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

        /* OPTIONAL: Extended business fields
        'title'=>'Title',
        'description'=>'Description',
        'company'=>'Company',
        'reports_to'=>'Reports To',
        'phone_work_desk'=>'Work Phone',
        'phone_work_cell'=>'Work Cell',
        'email_work'=>'Work Email',
        */

        'phone_personal_home'=>'Home Phone',
        'phone_personal_cell'=>'Personal Cell',
        'email_personal'=>'Personal Email',
        'email_alternate'=>'Alternate Email',
        'comments'=>'Comments',

        'timezone'=>'Timezone',
        'login_active'=>'Login Active',
        'default_role'=>'Default Group',
        'acting_role'=>'Acting Role',
        'force_password_reset'=>'Force PW Reset',
        'login_created'=>'Login Created',
        'last_login'=>'Last Login',
        'login_count'=>'Login Count',

        'name'=>'Login User Name',
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

    // Umm... not sure of the exact mechanism yet but using 'timestamp' here does not seem to create a carbon instance.
    // Using datetime looks like it works fine.
    public $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_login' => 'datetime',
        'login_created' => 'datetime',
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
