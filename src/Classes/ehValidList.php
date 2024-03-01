<?php
/**
 * Pull or create a $key=>$value pair for a dropdown <select>
 * ValidList class to either hard code a list for a drop-down or pull from a database query
 * abstracted here so you can quickly hard code an array; and as needed, and then build a real query later
 *
 * created: 12/24/2014
 *
 * Provides methods:
 * getList()
 * pullQuery()
 * pullUnique()
 * combineLists()
 * makeListWith()
 *
 */
namespace ScottNason\EcoHelpers\Classes;


use Illuminate\Support\Facades\DB;


class ehValidList {
    
    /**
     * The default, appended Archive flag.
     * (has to be pretty simply since <select> won't allow any styling inside it.)
     * This will appear behind the selection name if the passed "criteria" is met.
     *
     * @var string
     */
    protected static $def_inactive_style = '*';

    protected static $lists = [];


    protected static $_month =
        [
            1=>'Jan',
            2=>'Feb',
            3=>'Mar',
            4=>'Apr',
            5=>'May',
            6=>'Jun',
            7=>'Jul',
            8=>'Aug',
            9=>'Sep',
            10=>'Oct',
            11=>'Nov',
            12=>'Dec'
        ];
    protected static $_day =
        [
            1=>'01',
            2=>'02',
            3=>'03',
            4=>'04',
            5=>'05',
            6=>'06',
            7=>'07',
            8=>'08',
            9=>'09',
            10=>'10',
            11=>'11',
            12=>'12',
            13=>'13',
            14=>'14',
            15=>'15',
            16=>'16',
            17=>'17',
            18=>'18',
            19=>'19',
            20=>'20',
            21=>'21',
            22=>'22',
            23=>'23',
            24=>'24',
            25=>'25',
            26=>'26',
            27=>'27',
            28=>'28',
            29=>'29',
            30=>'30',
            31=>'31',
        ];
    protected static $_timezone =
        [
            "America/New_York"=>"Eastern",
            "America/Chicago"=>"Central",
            "America/Denver"=>"Mountain",
            "America/Phoenix"=>"Mountain no DST",
            "America/Los_Angeles"=>"Pacific",
            "America/Anchorage"=>"Alaska",
            "America/Adak"=>"Hawaii",
            "Pacific/Honolulu"=>"Hawaii no DST"
        ];
    protected static $_page_security =
        [
            0=>'0-No Access',
            3=>'3-Full Permissions Check',
            2=>'2-Authenticated Only',
            1=>'1-Public Access',
        ];
    protected static $_page_type =
        [
            'page'=>'Page',
            'method'=>'Method',
            'resource'=>'Resource',
            'module'=>'Module',
            'submenu'=>'Submenu',
        ];
    protected static $_menus_list =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehPage',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'id',                      // ORDER BY clause field(s)
            'criteria' => 'WHERE menu_item = 1',    // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => null,                // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => null,               // When this field is false, we'll add the inactive symbol (like 'active')
        ];

    protected static $_module_list_all =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehPage',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'order',                   // ORDER BY clause field(s)
            'criteria' => "WHERE type = 'module'",  // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => null,                // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => null,               // When this field is false, we'll add the inactive symbol (like 'active')
        ];

    protected static $_role_list =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehRole',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'name',                    // ORDER BY clause field(s)
            'criteria' => "",                       // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => null,                // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => null,               // When this field is false, we'll add the inactive symbol (like 'active')
        ];

    // Used by pages-detail for the parent_id dropdown (must be either a module or submenu)
    protected static $_modules_submenus_list =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehPage',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'order',                   // ORDER BY clause field(s)
            'criteria' => "WHERE type = 'module' OR type = 'submenu'",
                                                    // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => null,                // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => null,               // When this field is false, we'll add the inactive symbol (like 'active')
        ];

    // User list for the User Profile Go-To
    protected static $_user_list =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'App\Models\User',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'name',                    // ORDER BY clause field(s)
            'criteria' => "",                       // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => 'archived',          // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => 'login_active',     // When this field is false, we'll add the inactive symbol (like 'active')
        ];
    // Examples list for the Example Detail Go-To
    protected static $_example_list =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehExample',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'name',                    // ORDER BY clause field(s)
            'criteria' => "",                       // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => 'archived',          // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => 'active',           // When this field is false, we'll add the inactive symbol (like 'active')
        ];
    /*



     */

    /**
     * A list of "active" page items (not necessarily just "menu" items - could be a static page link)
     * @var array
     */
    protected static $_page_list_active =
        [
            'method'=>'pullQuery',                  // The ValidList method used for this query.
            'key' => 'id',                          // The table column name used for the "key"
            'value' => 'name',                      // The table column name used for the "value"
            'model_path' => 'ScottNason\EcoHelpers\Models\ehPage',
                                                    // The Laravel pathname to the Model
            'orderBy' => 'name',                    // ORDER BY clause field(s)
            'criteria' => "WHERE active = 1 AND type = 'page'",       // Add a complete WHERE or LIKE clause if needed
            'include_key_in_name' => false,         // 1=>'Value Name' would be -> "01-Value Name"
            'inactive_true' => '',                  // When this field is true, we'll add the inactive symbol (like 'archived')
            'inactive_false' => '',                 // When this field is false, we'll add the inactive symbol (like 'active')
        ];





    /**
     * Return the list (either static or dynamically pulled) from the internal $lists array.
     *
     * @param $list_name
     * @return mixed|string
     */
    public static function getList($list_name)
    {
        // If the $list_name does not exist then return some kind of 'no list' message.
        if (!empty(self::$lists[$list_name])) {

            // Check to see if this is a dynamic query request.
            // Note: Just checking for 2 of the fields in the required $arg parameters for a pullQuery().
            //       That should be enough to establish this is a query request rather than a request
            //       for a statically defined list.
            if (key_exists('key',self::$lists[$list_name]) && key_exists('orderBy',self::$lists[$list_name])) {

                // Return the query called for in the 'method' parameter passing it the $arg contained in the $list_name.
                $func = self::$lists[$list_name]['method'];
                return self::$func(self::$lists[$list_name]);

            }

            // If not a dynamic query, then we'll assume this is a static list.
            return self::$lists[$list_name];

        } else {

            // This list key does not seem to exist.
            return 'no list';
        }

        // Check to see if the list is a query request rather than a static list.
        // Does the 'model_path' AND 'orderBy' entry exist?
        //return self::pullQuery($key, $value, $model_path, $orderBy, $criteria, $include_key_in_name, $inactive_true, $inactive_false);
        // return pullQuery($parameters);


    }


    /**
     * Add all the static and dynamic lists for this class.
     *
     * @return void
     */
    protected static function initLists() {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Initialize the base (included) lists for the package ehValidList class.
        // To add additional lists, define them as protected static variables above and then add them here.
        self::addList('month', self::$_month);
        self::addList('day', self::$_day);
        self::addList('timezone', self::$_timezone);
        self::addList('page_security', self::$_page_security);
        self::addList('page_type', self::$_page_type);
        self::addList('menus_list', self::$_menus_list);
        self::addList('module_list_all', self::$_module_list_all);
        self::addList('role_list', self::$_role_list);
        self::addList('modules_submenus_list', self::$_modules_submenus_list);
        self::addList('user_list', self::$_user_list);
        self::addList('example_list', self::$_example_list);
        self::addList('page_list_active', self::$_page_list_active);

    }


    /**
     * A function to add another $list "key" to the internal $lists array.
     *
     * @param $list_name
     * @param $list_array
     * @return void
     */
    protected static function addList($list_name, $list_array) {
        self::$lists = array_merge(self::$lists, [$list_name=>$list_array]);
    }





    /**
     * Builds the array from the passed parameters
     * Note: Include the name of the archive and/or active field for this model,
     *       then we'll check and identify any entry that is archived and add the
     *       self::$def_inactive_style symbol to the individual <select> entries.
     *
     * @param $key
     * @param $value
     * @param $model_path
     * @param null $orderBy
     * @param null $criteria
     * @param bool $include_key_in_name
     * @param string $inactive_true
     * @param string $inactive_false
     * @return array
     */
    //protected static function pullQuery($key, $value, $model_path, $orderBy=null, $criteria=null, $include_key_in_name=false, $inactive_true='', $inactive_false='') {
    protected static function pullQuery($args) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        $thisArray = array();           // Container for the <select> list data.
        $t = New $args['model_path'];   // Model instance from the passed model name.
        $table_name = $t->getTable();   // Raw table name.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build to SELECT portion of the query.
        //  (Note: None of these fields are from "user" input. They are supplied by the class code above.)
        $query =  'SELECT '.$args['key'].','.$args['value'].' FROM '.$table_name.' ';
        if (!empty($args['inactive_true'])) {
            $query =  'SELECT '.$args['key'].','.$args['value'].','.$args['inactive_true'].' FROM '.$table_name.' ';
        }
        if (!empty($args['inactive_false'])) {
            $query =  'SELECT '.$args['key'].','.$args['value'].','.$args['inactive_false'].' FROM '.$table_name.' ';
        }
        if (!empty($args['inactive_false']) && !empty($args['inactive_true'])) {
            $query =  'SELECT '.$args['key'].','.$args['value'].','.$args['inactive_false'].','.$args['inactive_true'].' FROM '.$table_name.' ';
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build the additional WHERE/LIKE clause and ORDER portion of the query.
        if (!empty($args['criteria'])) {
            $query .= $args['criteria']." ";                        // Note: Add a blank space to the end of the passed WHERE to be safe.
        }
        if (!empty($args['orderBy'])) {
            $query .= "ORDER BY `" . $args['orderBy']."`;";         // Set the default list order and terminate the query.
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Execute the query
        $r = DB::select($query);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // This is defined as a class property at the top.
        // $this->def_inactive_style = '*';


        // Convert the result set to a key value pair array
        // And add the "inactive" symbol.
        foreach($r as $row) {

            ///////////////////////////////////////////////////////////////////////////////////////////
            // The styling character if this record is archived or not active (appended to the end).
            $inactive_style = '';

            // Create local function variables so they will work in the "$row->$var" syntax below.
            $inactive_false = $args['inactive_false'];
            $inactive_true = $args['inactive_true'];
            $key = $args['key'];
            $value = $args['value'];

            // Example of an Archived (archived=1) record. Add style when this field is "true".
            if (!empty($args['inactive_true']) && $row->$inactive_true == 1) {
                $inactive_style = self::$def_inactive_style;
            }

            // Example of an Active (active=1) record. Add style when this field is "false".
            if (!empty($args['inactive_false']) && $row->$inactive_false != 1) {
                $inactive_style = self::$def_inactive_style;
            }

            // Add the key name to the value (if calling for it).
            if ($args['include_key_in_name']) {
                $thisArray[$row->$key] = $row->$key.'-'.$row->$value.$inactive_style;
            } else {
                $thisArray[$row->$key] = $row->$value.$inactive_style;
            }


        }

        // Clean up.
        unset($t, $table_name, $r, $key_tmp, $value_tmp, $query);

        // Return the properly formatted list array.
        return $thisArray;
    }



}
