<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\MustVerifyEmail;          // Needed (and the implementation on the class) to force email validation for a new user.


use ScottNason\EcoHelpers\Traits\ehUserFunctions;

/**
 * The base model designed to be extended to the User model; this model provides additional package
 * user functions through the ehUserFunctions trait.
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


    /**
     * Create a unique username based on this specific algorithm.
     * Note: passing the entire $request here so this method can be changed to use a different algorithm as needed.
     * This is used by both the RegisteredUserController and the ehUsersController@dataConsistencyCheck().
     *
     * @param Request $request
     * @return string
     */
    public static function uniqueUserName(Request $request) {
        $user_name = '';
        $user_name =    substr(strtolower($request->first_name),0,3) . substr(strtolower($request->last_name),0,3);


        // Determine if this user name is unique. (And create a unique one if needed by adding a number)
        $name_is_unique = false;            // Check to see if this user name is unique among all users.
        $unique_cnt = 1;                    // A number to add after the user name to make it unique.
        $unique_user_name = $user_name;     // The newly created unique user name.
        do {
            $r = DB::select("SELECT * FROM users WHERE name = '".$unique_user_name."';");
            if (count($r) > 0) {          // This name is already in use.
                $unique_user_name = $user_name.$unique_cnt;
                $unique_cnt++;
            } else {
                $name_is_unique = true;     // Drop us out of this unique check and return this version of the user name.
            }
        }  while (!$name_is_unique);

        return $unique_user_name;
    }


    /**
     * Create a unique account number based on this specific algorithm.
     * Note: passing the entire $request here so this method can be changed to use a different algorithm as needed.
     * This is used by both the RegisteredUserController and the ehUsersController@dataConsistencyCheck().
     *
     * @param Request $request
     * @return string
     */
    public static function uniqueAccountNumber(Request $request) {

        $starting_account_number = 100001;
        $user_account_number = '';

        // Get the highest account number in use.
        $highest = DB::select("SELECT account_id FROM users ORDER BY account_id DESC LIMIT 1;");

        if (count($highest) > 0) {
            // If we got a result from the query then add 1 to it to get the next available account id.
           $user_account_number = $highest->account_id + 1;
        } else {
            // Otherwise, it looks like no account ids have been assigned yet so use the starting one.
            $user_account_number = $starting_account_number;
        }

        // But, make sure this user doesn't already have one assigned.
        if (!empty($request->id)) {
            $current_account = DB::select("SSELECT account_id FROM users WHERE id = {$request->id};");
        }

        if (count($current_account) > 0) {
            // Looks like the user already has an account id so just return that one.
            return $current_account->account_id;
        } else {
            // Looks like user did not have an account id assigned so use the one we created above.
            return $user_account_number;
        }

    }

}
