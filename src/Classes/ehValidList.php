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
     * Sample key-value pair for direct access using ValidList::getList(').
     * Note: see the getList() case statement below for list naming and calling.
     *
     * Kinds of camping site surfaces.
     *
     * @var string[]
     */
    public static $_site_surface = array(
        1=>'Dirt',
        2=>'Grass',
        3=>'Gravel',
        4=>'Asphalt',
        5=>'Concrete',
        6=>'Sand',
        9=>'Other'
    );

    protected static $_month = [
        '01'=>'Jan',
        '02'=>'Feb',
        '03'=>'Mar',
        '04'=>'Apr',
        '05'=>'May',
        '06'=>'Jun',
        '07'=>'Jul',
        '08'=>'Aug',
        '09'=>'Sep',
        '10'=>'Oct',
        '11'=>'Nov',
        '12'=>'Dec'
    ];

    protected static $_day = [
        '01'=>'01',
        '02'=>'02',
        '03'=>'03',
        '04'=>'04',
        '05'=>'05',
        '06'=>'06',
        '07'=>'07',
        '08'=>'08',
        '09'=>'09',
        '10'=>'10',
        '11'=>'11',
        '12'=>'12',
        '13'=>'13',
        '14'=>'14',
        '15'=>'15',
        '16'=>'16',
        '17'=>'17',
        '18'=>'18',
        '19'=>'19',
        '20'=>'20',
        '21'=>'21',
        '22'=>'22',
        '23'=>'23',
        '24'=>'24',
        '25'=>'25',
        '26'=>'26',
        '27'=>'27',
        '28'=>'28',
        '29'=>'29',
        '30'=>'30',
        '31'=>'31',
    ];

    /**
     * Valid time zone entries.
     * @var array
     */
    protected static $_valid_timezones = array(
        "America/New_York"=>"Eastern",
        "America/Chicago"=>"Central",
        "America/Denver"=>"Mountain",
        "America/Phoenix"=>"Mountain no DST",
        "America/Los_Angeles"=>"Pacific",
        "America/Anchorage"=>"Alaska",
        "America/Adak"=>"Hawaii",
        "Pacific/Honolulu"=>"Hawaii no DST"
    );


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

    /* Added these fields to the pages table.
    protected static $_route_type =
        [
            'GET|HEAD'=>'GET|HEAD',
            'PUT|PATCH'=>'PUT|PATCH',
            'POST'=>'POST',
            'DELETE'=>'DELETE',
        ];
    */



    /**
     * Primary way to return the list from ValidList.
     * getList() will return the assigned array (above) -
     * or it will pull the needed data from a table.
     * You can start with an array defined here --- and change to a table later on.
     *
     * @param $list
     * @return array|string[]
     */
    public static function getList($list = '') {


        // Return the key=>value pair from a static list defined above.
        switch ($list) {

            case "month":
                return self::$_month;
                break;

            case "day":
                return self::$_day;
                break;

            case "timezone":
                return self::$_valid_timezones;
                break;

            case "page_type":
                return self::$_page_type;
                break;

            case "page_security":
                return self::$_page_security;
                break;

                /*
            case "route_type":
                return self::$_route_type;
                break;
*/

            case "module_list_all":                 // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'order';                 // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehPage';
                                                    // The Laravel pathname to the Model
                $where_clause = "WHERE type = 'module' ";
                                                    // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;

            case "module_list_active":              // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'order';                 // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehPage';
                                                    // The Laravel pathname to the Model
                $where_clause = "WHERE type = 'module' AND active = 1 ";
                                                    // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;

            // Used by pages-detail for the parent_id dropdown (must be either a module or submenu)
            case "modules_submenus_list":           // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'order';                 // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehPage';       // The Laravel pathname to the Model
                $where_clause = "WHERE type = 'module' OR type = 'submenu' "; // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;

            case "role_list":                      // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'name';                  // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehRole';       // The Laravel pathname to the Model
                $where_clause = '';                 // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;


            // Return the key=>value pair from a database table query.
            /*
            case "master_whole_models":             // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'model_whole';             // The table column name used for the "value"
                $orderBy = 'model_whole';           // ORDER BY clause field(s)
                $model_path = 'App\AssetMaster';    // The Laravel pathname to the Model
                $where_clause = 'WHERE active = 1'; // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;
            */

            case "menus_list":                      // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'id';                    // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehPage';       // The Laravel pathname to the Model
                $where_clause = 'WHERE menu_item = 1'; // Add a complete WHERE clause if needed
                $include_key_in_name = true;        // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;


            // The GroupsController uses this to present a list of pages for the Default Home Page selection.
            case "page_list_active":                // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"
                $value = 'name';                    // The table column name used for the "value"
                $orderBy = 'name';                  // ORDER BY clause field(s)
                $model_path = 'ScottNason\EcoHelpers\Models\ehPage';       // The Laravel pathname to the Model
                $where_clause = "WHERE type = 'page' AND active = 1"; // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = null;              // If we define a column here we'll check to see if it is false
                $active_field = null;               // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;

            // User list for the User Profile Go-To
            case "user_list":                       // Name to use when calling getList()
                $key = 'id';                        // The table column name used for the "key"

                // This can be changed based on whether or not you're using a different username than email
                $value = 'email';                   // The table column name used for the "value"
                $orderBy = 'email';                 // ORDER BY clause field(s)

                $model_path = 'App\Models\User';    // The Laravel pathname to the Model
                $where_clause = ""; // Add a complete WHERE clause if needed
                $include_key_in_name = false;       // 1=>'Value Name' would be -> "01-Value Name"
                $archive_field = 'archived';        // If we define a column here we'll check to see if it is false
                $active_field = 'login_active';     // If we define a column here we'll check to see if it is true
                return self::pullQuery($key, $value, $model_path, $orderBy, $where_clause, $include_key_in_name, $archive_field, $active_field);
                break;

            default:
                return array("No List");
        }
    }



    /**
     * Combine a current getList($list) item with new [$key=>$value] pair items.
     *
     * @param $list
     * @param $new_items
     * @param bool $top
     * @return array
     */
    public static function combineList($list, $new_items=[], $top=false) {

        $new_list = [];
        $current_list = self::getList($list);

        if ($top) {
            // Add both arrays to the new list - starting with the new items:
            foreach ($new_items as $key => $value) {
                $new_list[$key] = $value;
            }
            foreach ($current_list as $key => $value) {
                $new_list[$key] = $value;
            }
        } else {
            // Just add this to the current list
            foreach ($new_items as $key => $value) {
                $current_list[$key] = $value;
            }
            $new_list = $current_list;
        }

        return $new_list;
    }

    /**
     * Pull a Unique list from a table
     *  - returns a $key(id) => $value(name) pair
     *
     * @param $key
     * @param $value
     * @param $model_path
     * @param null $orderBy
     * @param null $where_clause
     * @param bool $include_key_in_name
     * @return array
     */
    protected static function pullUnique($key, $value, $model_path, $orderBy=null, $where_clause=null, $include_key_in_name=false) {

        $thisArray = array();
        $t = New $model_path;
        $t->getTable();

        $query =  "SELECT DISTINCT ".$key.",".$value.",".$orderBy." FROM ". $t->getTable() ." ";

        if ($where_clause) {
            $query .= $where_clause." ";
        }

        if ($orderBy) {
            $query .= "ORDER by ".$orderBy;                              // set the default list order
        }

        $r = DB::select($query);

        //Convert the result set to a key value array
        foreach($r as $row) {
           // $thisArray[$row[$key]] = htmlentities($row[$value]);
            // Trying to clean up some odd characters -- but not really working; just need to fix the underlying data.
            $thisArray[$row->$key] = htmlentities(utf8_decode($row->$value));    // The result set is now an object
        }

        unset($t,$r,$query);

        return $thisArray;
    }

    /**
     * Builds the array from the passed parameters
     *
     * Note: If you include the name of the archive field for this model, then we'll check and identify any entry that is archived.
     *
     * @param $key
     * @param $value
     * @param $model_path
     * @param null $orderBy
     * @param null $where_clause
     * @param bool $include_key_in_name
     * @param string $archive_field
     * @param string $active_field
     * @return array
     */
    protected static function pullQuery($key, $value, $model_path, $orderBy=null, $where_clause=null, $include_key_in_name=false, $archive_field='', $active_field='') {

        $thisArray = array();
        $t = New $model_path;
        $t->getTable();

        $query =  'SELECT '.$key.','.$value.' FROM '.$t->getTable().' ';

        if (!empty($archive_field)) {
            $query =  'SELECT '.$key.','.$value.','.$archive_field.' FROM '.$t->getTable().' ';
        }
        if (!empty($active_field)) {
            $query =  'SELECT '.$key.','.$value.','.$active_field.' FROM '.$t->getTable().' ';
        }
        if (!empty($active_field) && !empty($archive_field)) {
            $query =  'SELECT '.$key.','.$value.','.$active_field.','.$archive_field.' FROM '.$t->getTable().' ';
        }


        if ($where_clause) {
            $query .= $where_clause." ";
        }

        if ($orderBy) {
            $query .= "ORDER BY `" . $orderBy."`;";                              // set the default list order
        }

        // Execute the query
        $r = DB::select($query);


        ///////////////////////////////////////////////////////////////////////////////////////////
        $def_archive_style = '*';   // The default, prepended Archive flag (has to be pretty simply since <select> won't allow any styling inside it.)


        //Convert the result set to a key value array
        foreach($r as $row) {

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Check if this record is archived or not active and prepend the styling.
            $archive_style = '';

            // Archived record?
            if (!empty($archive_field) && $row->$archive_field == 1) {
                $archive_style = $def_archive_style;
            }

            // Active record?
            if (!empty($active_field) && $row->$active_field != 1) {
                $archive_style = $def_archive_style;
            }

            if ($include_key_in_name) {
                $thisArray[$row->$key] = $row->$key.'-'.$archive_style.$row->$value;    // The result set is now an object
            } else {
                $thisArray[$row->$key] = $archive_style.$row->$value;                   // The result set is now an object
            }

        }


        // Clean up.
        unset($t,$r,$query);

        // Return the formatted array list.
        return $thisArray;
    }


}
