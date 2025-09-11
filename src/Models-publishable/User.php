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




    ////////////////////////////////////////////////////////////////////////////////////////////
    // SET UP A RE-USABLE USER ENVIRONMENT.
    // This should be updated on initial login and after any role change.

    // Store a key-value pair
    // session(['key' => 'value']);

    // Store multiple key-value pairs
    // session([
    //  'name' => 'John Doe',
    //  'age' => 30,
    // ]);

    // Using the session() helper
    // $value = session('key');

    public static function ehEnvironment($key='ehInit') {


        // Is the user logged in now?
        //if (!Auth::check()) {
        //    return false;
        //}

        if ($key=='ehInit') {

            // 1. Save the combined $settings_config array to the session().
            session(['ehConfig' => self::ehInit()]);

            // 2. Save the user specific dropdown specific menus to the session().
            session(['ehMenus' => self::ehMenus()]);


        } else {

            // Get a copy of eco-helpers config and the settings table
            if ($key=='ehConfig') {
                if (session()->has('ehConfig')) {
                    return session('ehConfig');     // Is the session available here for this call?
                } else {
                    return self::ehInit();               // Otherwise initialize a new one.
                }
            }

            // Set up this user's dropdown menus
            if ($key=='ehMenus') {
                if (session()->has('ehMenus')) {
                    return session('ehMenus');      // Is the session available here for this call?
                } else {
                    return self::ehMenus();              // Otherwise create new ones.
                }

            }
        }


    }

    protected static function ehInit() {
        // 1. Get a copy of the settings table
        $settings_config = ehSetting::find(1)->toArray();

        ////////////////////////////////////////////////////////////////////////////////////////////
        // from ehConfig
        // 2. Add the eco-config file to the self::$settings_config array.
        foreach(config('eco-helpers') as $key => $value) {
            $settings_config[$key] = $value;
        }
        ////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Get the 2 version variables from the eco-helpers package to include in the self::$settings_config array.
        $version_file = include(base_path().'/vendor/scott-nason/eco-helpers/src/version.php');
        $settings_config['eh-app-version'] = $version_file['eh-app-version'];
        $settings_config['eh-last-update'] = $version_file['eh-last-update'];

        return $settings_config;
    }

    protected static function ehMenus() {
        // Create and save this user's dropdown menus
        ///////////////////////////////////////////////////////////////////////////////////////////
        // MAIN DROPDOWN MENUS: Create the navbar user dropdown menus
        // ehMenus() will deliver a complete menu hierarchy based on the logged in user's acting role
        // page security along with any individual page security settings (public, auth, full security check).
        $menus = new ehMenus(0,'user');
        return $menus->getPages();
    }


}
