<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use ScottNason\EcoHelpers\Models\ehRole;
use ScottNason\EcoHelpers\Models\ehRoleLookup;
use ScottNason\EcoHelpers\Models\ehPage;



/**
 * Called by the command line utility eco-helpers:sample-data, these are functions to generates the initial,
 * OOTB startup data (users, roles and examples) needed to run and test the ecoHelpers package.
 *
 * These function are called from the Artisan console command, ecoHelpersSampleData.
 *
 */

class ehSampleData {

    /**
     * Batch up and execute all of the individual create sample data tasks.
     *
     * @return void
     */
    public static function createSampleData() {

        // 1. Create users; ehAdmin/ehAdmin and ehUser/ehUser
        foreach(self::userData() as $user) {
            ehSampleData::createUsers($user);
        }

        // 2. Create roles
        foreach(self::roleData() as $role) {
            ehSampleData::createRoles($role);
        }

        // 3. Create the role_lookups
        foreach(self::roleLookupData() as $role_lookup) {
            ehSampleData::createRolesLookup($role_lookup);
        }

        // 5. Load the ehExamples sample data.
        //    Note: currently this is using an Export from a Maria DB table.
        $script = file_get_contents(__DIR__ . '/../database/sql-migrations/eh_examples.sql');
        ehSampleData::createExamples($script);

        // 6. Load the ehPages sample data.
        foreach (self::pagesData() as $page) {
            ehSampleData::createPages($page);
        }


        // xx. Load the ehAccessTokens sample data.
        // TODO: Aren't we going to need this once we start testing roles against specific page access?


    }

    /**
     * Create or update the 2 sample users.
     *
     * @param $user_array
     * @return void
     */
    public static function createUsers($user_array) {

        $message = '';

        // Does this user entry exist?
        if (User::where('name',"=",$user_array['name'])->exists()) {

            // User already exists so just update the record.
            $user = User::where('name',$user_array['name'])->first();
            $message = "Updated user ";

        } else {

            // User does not exist so create the record and return the id
            $user = new User;
            $message = "Created user ";

        }

        // Update the data in this user using the sample data below.
        foreach($user_array as $key=>$value) {
            $user->$key = $value;
        }

        // Save the record.
        $user->save();
        echo $message.$user->name." (id: ".$user->id.")\n";

    }

    public static function createRoles($role_array) {

        // Does this role entry exist?
        if (ehRole::where('name',"=",$role_array['name'])->exists()) {

            // Role already exists so just update the record.
            $role = ehRole::where('name',$role_array['name'])->first();
            $message = "Updated role ";

        } else {

            // Role does not exist so create the record and return the id
            $role = new ehRole;
            $message = "Created role ";

        }

        // Update the data in this role using the sample data below.
        foreach($role_array as $key=>$value) {
            $role->$key = $value;
        }

        // Save the record.
        $role->save();
        echo $message.$role->name." (id: ".$role->id.")\n";
    }

    public static function createRolesLookup($role_lookup_array) {

        // Does this role_lookup entry exist?
        if (ehRoleLookup::where('user_id',$role_lookup_array['user_id'])
            ->where('role_id',$role_lookup_array['role_id'])
            ->exists()) {

            // Role already exists so just update the record.
            $role_lookup = ehRoleLookup::where('user_id',$role_lookup_array['user_id'])
                ->where('role_id',$role_lookup_array['role_id'])
                ->first();
            $message = "Updated role_lookup ";

        } else {

            // Role does not exist so create the record and return the id
            $role_lookup = new ehRoleLookup;
            $message = "Created role_lookup ";

        }

        // Update the data in this role using the sample data below.
        foreach($role_lookup_array as $key=>$value) {
            $role_lookup->$key = $value;
        }

        // Save the record.
        $role_lookup->save();
        $user = User::find($role_lookup->user_id);    // Pull the user's login name
        $role = ehRole::find($role_lookup->role_id);  // Pull the user's role
        echo $message.$role->name.' for user: '.$user->name."\n";
    }

    /**
     * Hold off on this -- this is already being done somewhere else. I think in the initial query of the settings system.
     * @param $settings_array
     * @return void
     */

    /*
    public static function createSettings($settings_array) {


        // Does this settings entry exist?
        if (ehSetting::where('name',"=",$settings_array['name'])->exists()) {

            // User already exists so just update the record.
            $setting = User::where('name',$user_array['name'])->first();
            $message = "Updated user ";

        } else {

            // User does not exist so create the record and return the id
            $setting = new ehSetting;
            $message = "Created user ";

        }

        // Update the data in this user using the sample data below.
        foreach($user_array as $key=>$value) {
            $setting->$key = $value;
        }

        // Save the record.
        $setting->save();
        echo $message.$setting->name." (id: ".$setting->id.")\n";

    }
    */

    public static function createExamples($sql_script) {

        // First just empty out the table.
        DB::table('eh_examples')->truncate();

        // Then insert the sample data.
        DB::unprepared($sql_script);

    }

    public static function createPages($pages_array) {

        // Does this page entry exist?
        if (ehPage::where('id',"=",$pages_array['id'])->exists()) {

            // Page already exists so just update the record.
            $page = ehPage::where('id',$pages_array['id'])->first();
            $message = "Updated page ";

        } else {

            // Role does not exist so create the record and return the id
            $page = new ehPage;
            $message = "Created page ";

        }

        // Update the data in this page using the sample data below.
        foreach($pages_array as $key=>$value) {
            $page->$key = $value;
        }

        // Save the record.
        $page->save();
        echo $message.$page->name." (id: ".$page->id.")\n";
    }

    public static function createAccessTokens() {

    }



    /**
     * Define the 2 startup users: ehAdmin and ehUser.
     * @return array[]
     */
    protected static function userData() {
        return [
            1=>[
                'archived' => 0,
                'first_name' => 'eh',
                'last_name' => 'User',
                'middle_name' => '',
                'nickname' => '',

                /* OPTIONAL: Extended business fields
               'title' => '',
               'description' => 'ecoHelpers startup user account.',
               'company' => 'Eco Helpers',
               'reports_to' => '',
               'phone_work_desk' => '',
               'phone_work_cell' => '',
               'email_work' => '',
               */

                'phone_personal_home' => '',
                'phone_personal_cell' => '',
                'email_alternate' => '',
                'email_personal' => 'ehUser@email.com',
                'comments' => '',
                'login_active' => 1,
                'default_role' => 1,
                'acting_role' => 1,
                'force_password_reset' => 0,
                'login_created' => date(ehConfig::get('date_format_sql_long')),
                'last_login' => null,
                'login_count' => 0,
                'name' => 'ehUser',
                'email' => 'ehUser@email.com',
                'email_verified_at' => date(ehConfig::get('date_format_sql_long')),
                'password' => Hash::make('ehUser'),
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long')),
            ],
            2=>[
                'archived' => 0,
                'first_name' => 'eh',
                'last_name' => 'Admin',
                'middle_name' => '',
                'nickname' => '',

                /* OPTIONAL: Extended business fields
                'title' => '',
                'description' => 'ecoHelpers startup admin account.',
                'company' => 'Eco Helpers',
                'reports_to' => '',
                'phone_work_desk' => '',
                'phone_work_cell' => '',
                'email_work' => '',
                */

                'phone_personal_home' => '',
                'phone_personal_cell' => '',
                'email_alternate' => '',
                'email_personal' => 'ehAdmin@email.com',
                'comments' => '',
                'login_active' => 1,
                'default_role' => 3,
                'acting_role' => 3,
                'force_password_reset' => 0,
                'login_created' => date(ehConfig::get('date_format_sql_long')),
                'last_login' => null,
                'login_count' => 0,
                'name' => 'ehAdmin',
                'email' => 'ehAdmin@email.com',
                'email_verified_at' => date(ehConfig::get('date_format_sql_long')),
                'password' => Hash::make('ehAdmin'),
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long')),
            ],
        ];
    }


    protected static function roleData() {

        return [
            1=>[
                'active'=>1,
                'site_admin'=>0,
                'locked'=>0,
                'name'=>'SIMPLE',
                'description'=>'A highly restricted, read-only role.',
                'restrict_flag'=>0,
                'default_home_page'=>null,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
            2=>[
                'active'=>1,
                'site_admin'=>0,
                'locked'=>0,
                'name'=>'SUPER USER',
                'description'=>'An advanced power user role.',
                'restrict_flag'=>0,
                'default_home_page'=>null,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
            3=>[
                'active'=>1,
                'site_admin'=>1,
                'locked'=>1,
                'name'=>'ADMIN',
                'description'=>'The built in Site Admin role.',
                'restrict_flag'=>0,
                'default_home_page'=>null,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
            4=>[
                'active'=>1,
                'site_admin'=>0,
                'locked'=>1,
                'name'=>'NO ACCESS',
                'description'=>'The built in No Access role.',
                'restrict_flag'=>0,
                'default_home_page'=>null,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
        ];

    }

    protected static function roleLookupData() {
        return [
            1=>[    // ehAdmin is assigned to the Simple role.
                'user_id'=>2,
                'role_id'=>1,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
            2=>[    // ehAdmin is assigned to the Advanced role.
                'user_id'=>2,
                'role_id'=>2,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
            3=>[    // ehAdmin is assigned to the Site Admin role.
                'user_id'=>2,
                'role_id'=>3,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
                
                
            ],
            4=>[    // ehUser is only assigned to the Simple role.
                'user_id'=>1,
                'role_id'=>1,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
        ];
    }


    protected static function settingData() {
        /*
        return [
            1=>[
                'site_lockout'=>0,
                'system_banner'=>'Sample Data Created Banner',
                'system_banner_blink'=>0,
                'message_welcome'=>'Sample Data Created Welcome',
                'message_jumbotron'=>'Sample Data Created Jumbotron',
                'message_copyright'=>'Sample Data Created Copyright',
                'date_validation_low'=>'',
                'default_time_zone'=>'America/New_York',
                'site_contact_email'=>'ehAdmin@contact-me.com',
                'site_contact_name'=>'eh Site Admin',
                'default_from_email'=>'Admin@eco-helpers.com',
                'default_from_name'=>'Eco Helpers Admin',
                'default_subject_line'=>'System Message',
                'logout_timer'=>240,
                'minimum_password_length'=>8,
                'days_to_lockout'=>30,
                'failed_attempts'=>3,
                'failed_attempts_timer'=>120,
                'created_by'=>'system',
                'created_at'=>date(ehConfig::get('date_format_sql_long')),
                'updated_by'=>'system',
                'updated_at'=>date(ehConfig::get('date_format_sql_long'))
            ],
        ];
        */
    }

    /**
     * Note: This uses the spreadsheet "menu array generator" to build out and create this array.
     * @return array
     */
    protected static function pagesData() {
        return [
            ['id'=>'1','name'=>'Admin','alt_text'=>'site adminstrative tasks','description'=>'site adminstrative tasks','type'=>'module','active'=>'1','security'=>'3','icon'=>'','parent_id'=>'0','menu_item'=>'1','order'=>'4','route'=>'module.1','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'2','name'=>'Examples','alt_text'=>'helpful examples','description'=>'helpful examples','type'=>'module','active'=>'1','security'=>'1','icon'=>'','parent_id'=>'0','menu_item'=>'1','order'=>'1','route'=>'module.2','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'3','name'=>'Inventory','alt_text'=>'an inventory module','description'=>'an inventory module','type'=>'module','active'=>'1','security'=>'2','icon'=>'','parent_id'=>'0','menu_item'=>'1','order'=>'2','route'=>'module.3','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'4','name'=>'Utilities','alt_text'=>'system utilities and tasks','description'=>'system utilities and tasks','type'=>'module','active'=>'1','security'=>'3','icon'=>'','parent_id'=>'0','menu_item'=>'1','order'=>'3','route'=>'module.4','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'5','name'=>'Menus/ Pages','alt_text'=>'create and maintain menus and page security','description'=>'create and maintain menus and page security','type'=>'resource','active'=>'1','security'=>'3','icon'=>'fa-regular fa-rectangle-list','parent_id'=>'1','menu_item'=>'1','order'=>'3','route'=>'pages','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'6','name'=>'Roles','alt_text'=>'define user security roles','description'=>'define user security roles','type'=>'resource','active'=>'1','security'=>'3','icon'=>'fa-solid fa-users-between-lines','parent_id'=>'1','menu_item'=>'1','order'=>'2','route'=>'roles','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'7','name'=>'Users','alt_text'=>'maintain individual user information','description'=>'maintain individual user information','type'=>'resource','active'=>'1','security'=>'3','icon'=>'fa-regular fa-user','parent_id'=>'1','menu_item'=>'1','order'=>'1','route'=>'users','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'8','name'=>'System Settings','alt_text'=>'system settings and configuration','description'=>'system settings and configuration','type'=>'resource','active'=>'1','security'=>'3','icon'=>'fa-solid fa-gear','parent_id'=>'1','menu_item'=>'1','order'=>'4','route'=>'config','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'9','name'=>'Util Task 1','alt_text'=>'add utility tasks here','description'=>'add utility tasks here','type'=>'page','active'=>'1','security'=>'3','icon'=>'fa-solid fa-person-digging','parent_id'=>'4','menu_item'=>'1','order'=>'1','route'=>'utility.task1','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'10','name'=>'Util Task 2','alt_text'=>'add utility tasks here','description'=>'add utility tasks here','type'=>'page','active'=>'1','security'=>'3','icon'=>'fa-solid fa-person-digging','parent_id'=>'4','menu_item'=>'1','order'=>'2','route'=>'utility.task2','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'11','name'=>'Util Task 3','alt_text'=>'add utility tasks here','description'=>'add utility tasks here','type'=>'page','active'=>'1','security'=>'3','icon'=>'fa-solid fa-person-digging','parent_id'=>'4','menu_item'=>'1','order'=>'3','route'=>'utility.task3','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'12','name'=>'Examples','alt_text'=>'examples to learn from','description'=>'examples to learn from','type'=>'resource','active'=>'1','security'=>'1','icon'=>'fa-regular fa-id-card','parent_id'=>'2','menu_item'=>'1','order'=>'1','route'=>'examples','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'13','name'=>'Scripts','alt_text'=>'','description'=>'system-based script files that are needed to perform various tasks','type'=>'module','active'=>'1','security'=>'3','icon'=>'fa-regular fa-file-lines','parent_id'=>'0','menu_item'=>'0','order'=>'5','route'=>'module.13','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'14','name'=>'User Role Change','alt_text'=>'','description'=>'change the users role to one of their assigned roles','type'=>'method','active'=>'1','security'=>'2','icon'=>'','parent_id'=>'13','menu_item'=>'0','order'=>'1','route'=>'users.role','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'15','name'=>'Notification (getNext)','alt_text'=>'','description'=>'get the next notification in line for this user.','type'=>'method','active'=>'1','security'=>'1','icon'=>'','parent_id'=>'13','menu_item'=>'0','order'=>'2','route'=>'notifications.get-next','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'16','name'=>'Notification (getTotal)','alt_text'=>'','description'=>'get the total notifications for this user.','type'=>'method','active'=>'1','security'=>'2','icon'=>'','parent_id'=>'13','menu_item'=>'0','order'=>'3','route'=>'notifications.get-total','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'17','name'=>'Notification (deleteNext)','alt_text'=>'','description'=>'delete the current notification for this user.','type'=>'method','active'=>'1','security'=>'2','icon'=>'','parent_id'=>'13','menu_item'=>'0','order'=>'4','route'=>'notifications.delete-next','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'18','name'=>'Example Static','alt_text'=>'examples of a statically called page','description'=>'examples of a statically called page','type'=>'page','active'=>'1','security'=>'2','icon'=>'','parent_id'=>'2','menu_item'=>'1','order'=>'2','route'=>'examples.static-page','feature_1'=>'','feature_2'=>'','feature_3'=>'','feature_4'=>'','comment'=>'','created_by'=>'system','created_at'=>date('Y-m-d'),'updated_by'=>'system','updated_at'=>date('Y-m-d')],
            ['id'=>'19',	'name'=>'Development Log ViewerDevelopment Log Viewer',	'alt_text'=>'',	'description'=>'both application and ecoFramework log histories',	'type'=>'page',	'active'=>'1',	'security'=>'3',	'icon'=>'',	'parent_id'=>'1',	'menu_item'=>'0',	'order'=>'5',	'route'=>'dev-log',	'feature_1'=>'',	'feature_2'=>'',	'feature_3'=>'',	'feature_4'=>'',	'comment'=>'',	'created_by'=>'system',	'created_at'=>date('Y-m-d'),	'updated_by'=>'system',	'updated_at'=>date('Y-m-d')],
            ];
    }

}