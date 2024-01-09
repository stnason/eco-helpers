<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


class ehPage extends ehBaseModel
{

    protected $table = 'eh_pages';

    /**
     * Let's the Controls class know which input data should be treated as date formats.
     *
     * @var string[]
     */
    public $dates = ['created_at', 'updated_at'];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    /**
     * The Controls class will automatically fill in the label names if they are defined in the model.
     * (If not, it will use the database field names.)
     *
     * @var string[]
     */
    public $labels = [

        'id'=>'Page ID',
        'name'=>'Name',
        'alt_text'=>'Alt Text',
        'description'=>'Description',
        'type'=>'Type',
        'active'=>'Active',
        'security'=>'Security',
        'icon'=>'Icon',
        'parent_id'=>'Parent ID',
        'menu_item'=>'Menu Item',
        'order'=>'Order',
        'route'=>'Route',

        'http_get_head'=>'GET|HEAD',
        'http_put_patch'=>'PUT|PATCH',
        'http_post'=>'POST',
        'http_delete'=>'DELETE',

        'feature_1'=>'Feature 1',
        'feature_2'=>'Feature 2',
        'feature_3'=>'Feature 3',
        'feature_4'=>'Feature 4',
        'comment'=>'Comment',
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





    ////////////////////////////////////////////////////////////////////////////////////////////
    // Helper functions to interact with routes
    ////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Return the page information from the pages table for this route name or page id.
     *
     * The sole purpose of this method is to "find" the page entry in the pages table.
     * No decisions are made here regarding access.
     *
     *
     * @param $route_name       // The route name as it would appear in the pages table.
     *                          // If no $route_name passed then uses the current route; Route::currentRouteName().
     *
     * @return array|bool
     */
    // shouldn't need "byRouteName" since this accepts either name or page id.
    // public static function getPageInfoByRouteName($route_name) {
    public static function getPageInfo($route_name = null) {


        ////////////////////////////////////////////////////////////////////////////////////////////
        // if $route_name is blank then use current.
        if ($route_name === null) {
            $route_name = Route::currentRouteName();
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // If $route_name is numeric then query by page number rather than 'route'
        $find_by_page_number = false;
        if (is_numeric($route_name)) {
            $find_by_page_number = true;
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Is there an entry in the pages table for the name or id "as is"
        if ($find_by_page_number) {

            // So, $route_name is numeric.
            //$result = self::find($route_name);  ???? This returns all records in the table. ???
            $result = self::where('id',$route_name)->get();

            // If we search by page_id and not find anything then we have a big problem
            if ($result->count() == 0) {
                return false;
            }

        } else {

            // So, $route_name is a string.
            // WHAT THE HELL -- when the $route_name = 'home' this returns route = 'module.10' ??????
            $result = self::where('route', $route_name)->get();

            // BUT this gives the SAME RESULT !!!
            //$q = "SELECT * FROM eh_pages WHERE route = '".$route_name."';";
            ///$result = DB::select($q);



////////////////////////////////////////////////////////////////////////////////////////////
/// TESTING
//if($route_name == 'home'){dd($route_name, $result);}




        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // If we didn't find an entry in pages above,
        //  then we need to build a query to check the base route name + resourceful flag.
        if ($result->count() > 0) {
            return $result[0];
        } else {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // So now we have to deal (maybe) with the resourceful route checks.

            // Break the route into parts -- if it has one of the pre-defined resourceful routes then strip that off
            $base_route_name = self::getResourcefulBaseRoute($route_name);


            // If this is not a resourceful route, $base_route_name will be false.
            if (!$base_route_name) {
                return false;
            }

            // Do we have a result for just the base route as a "resourceful" entry in the pages table?
            $result = ehPage::where(['route'=>$base_route_name, 'type'=>'resource'])->get();


            if ($result->count() > 0 ) {
                // Looks like this is a resourceful route so return that result.



/*
                ////////////////////////////////////////////////////////////////////////////////////////////
                dd( 'in ehPage::getPageInfo():',
                    Route::current(),                       // Complete route object with everything in it!
                    Route::current()->uri,                  // As passed in the browser address bar "pages/{page}"
                    Route::current()->methods,              // List of the html "states" called; i.e. GET, HEAD, PATCH, POST, etc.
                    Route::current()->action,               // 'as' - Discrete route name (w/show, index, destroy, update, etc.)
                    // 'uses' - Fully qualified method name.
                    Route::current()->action['as'],         // (see 'as' above)
                    $base_route_name,
                    $result[0]
                );
                ////////////////////////////////////////////////////////////////////////////////////////////
*/


                return $result[0];
            } else {
                // If no resourceful result, then this is not a resourceful route
                //  and we've been unable to find a page entry.
                return false;
            }


        }




    }





    /**
     * Determine if this is a resourceful route and if so, then return just the base portion of that route in front of that resource call.
     * This is used by getPageInfo()
     * @return mixed
     */

    protected static function getResourcefulBaseRoute($route_name) {

        // Check to see if a resourceful name is contained in this route:
        $resourceful_routes = ['index', 'show', 'create', 'edit', 'destroy', 'store', 'update' ];
        foreach ($resourceful_routes as $resource) {

            if ( strpos($route_name, '.'.$resource ) > 1 ) {
                return substr($route_name, 0, strpos($route_name, '.'.$resource ));
            }
        }

        // If the route does not contain any of the resourceful routes.
        return false;

    }


    /**
     * Pull a route name for this page entry using its page id.
     * Pull a route name for this page entry using its page id.
     *
     * @param int $page_id
     * @return string
     */
    public static function getRouteFromPageNumber($page_id) {

        $route_name = '';

        $m = self::findOrFail($page_id);

        if (empty($m->route)) {
            $route_name = null;
        } else {
            $route_name = $m->route;
        }

        return $route_name;
    }





    /**
     * Checks to see of the route name is a string or a number.
     * If it's a number -- IT IS ASSUMED TO BE A PAGE ID (id) -- and converted to the corresponding route name.
     * If it's a string -- then we check for forward slashes and convert to dots as needed.
     *  NOTE/REMEMBER: you may have to cast the input to force it to an integer if you know that's what it should be. -- (int)$page_id
     *
     * @param $route_name
     * @return mixed
     */
    public static function cleanRouteName($route_name = null) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // If no $route_name is passed then use the current route name.
        if (empty($route_name)) {
            $route_name = Route::currentRouteName();
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // In case we're passing in a page object
        if (is_object($route_name)) {
            $route_name = $route_name->route;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // If the route name is an actual--as expected--string name for a route.
        if (is_string($route_name)) {

            // if this is a "/contacts/find" style then convert to dots
            $route_name = ltrim($route_name,'/');                   // Removing leading forward slash.
            $route_name = rtrim($route_name,'/');                   // Removing trailing forward slash.
            $route_name = str_replace('/','.',$route_name);    // Replace forward slash with period.
            return $route_name;

        // If the passed $route_name is a number then we'll assume that it's a $page_id.
        } elseif (is_numeric($route_name)) {

            // Get the full route name from page id number.
            return self::getRouteFromPageNumber($route_name);

        } else {

            // Not sure what we ended up with so leave it alone for now and just return it.
            // If this is really a problem, we can throw and Exception and see why/how we got here.
            // For now leaving dd() as a "catch-all".
            dd('ehPage@cleanRouteName() dropped through to the bottom with $route_name: ', $route_name);
            return $route_name;
        }

    }

    /**
     * Accept either an ehPage object or a numeric page id and return a complete page object.
     *
     * @param $page_id
     * @return void
     */
    public static function normalizePageID($page_id) {

        if (is_numeric($page_id)) {
            return self::find($page_id);    // Returning an ehPage object from the passed number id.
        } else {
            return $page_id;                // Assuming this is an ehPage object already.
        }

    }






}
