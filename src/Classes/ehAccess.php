<?php

namespace ScottNason\EcoHelpers\Classes;

//use App\Models\User;      // Note; using ehUser here instead of User -
                            // since we're just accessing the core methods of that class.
use http\Exception;
use ScottNason\EcoHelpers\Models\ehUser;
use ScottNason\EcoHelpers\Models\ehPage;
use ScottNason\EcoHelpers\Models\ehAccessToken;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;



/**
 * eco-helpers class to handle resource and feature permission checks.
 * Note: The Menu/Page System is NOT a security system. Making an item inactive there ONLY removes it from the Menu
 * tree. Note: The security ACCESS permission levels are defined as constants in the non-published eco-constants.php
 * file.
 *
 * Remember each page has these access rights now:<br/>
 *  0- No access<br/>
 *  1- Public<br/>
 *  2- Authenticated<br/>
 *  3- Permissions Check<br/>
 *
 * Resource routes map to these permissions (or rights):<br/>
 *
 *  index   -   GET/HEAD    ACCESS_VIEW<br/>
 *  store   -   POST        ACCESS_ADD<br/>
 *  create  -   GET/HEAD    ACCESS_ADD<br/>
 *  show    -   GET/HEAD    ACCESS_VIEW<br/>
 *  update  -   PUT/PATCH   ACCESS_EDIT<br/>
 *  destroy -   DELETE      ACCESS_DELETE<br/>
 *  edit    -   GET/HEAD    ACCESS_EDIT<br/>
 *
 */
class ehAccess
{


    // In some cases NULLs or blanks are returning values so just force something that will never be found..
    // We're using a standard role_id OR user_id so this keeps it from finding
    // anything (rather than erroneously matching a null).
    //  (Note: MySQL largest int = 2147483647)
    protected static $force_no_find = '9999999';

    // Looks like we're doing less and less with needing to know the resource route parts.
    // I think switching token over to page id has helped a lot.
    // protected static $route_parts;                  // Legacy; Used to split a route name on a period - then how many parts?
    // protected static $number_of_parts;              // Legacy; Number of parts of the $route_name after splitting.
    // protected static $is_resource_request;          // Legacy; If the 2nd part is one of the "resource" names then we'll have to search on ".resource"


    /**
     * This needs to line up with the constants defined in eco-constants.php (loaded during the register() process)
     * Used for any loop where we need to rifle through all of the permissions levels and check for something.
     */
     protected static $access_token_array = ([
        'view' => false,
        'export_restricted' => false,
        'export_displayed' => false,
        'edit' => false,
        'add' => false,
        'delete' => false,
        'export_table' => false,
        'feature_1' => false,
        'feature_2' => false,
        'feature_3' => false,
        'feature_4' => false,
        'admin' => false
    ]);



    /**
     * Check user resource access. (view, export_restricted, export_displayed, edit, add, delete, export_table,
     * feature_1-4).
     * (see config/eco-constants.php)
     *      This is used by the check_permissions middleware to determine access by resourceful level.
     *      and
     *      This is used for checking per page from within the controller or template.
     *      Check to see if this user has this appropriate level of access to show or use this resource.
     *      Only returns true or false -- WILL NOT DO ANY REDIRECTS; calling routine is responsible for handling that.
     *
     *      Note: When building a form, this is a convenience, "front-side check" only and not considered "secure"
     *            (that is handled by the Middleware check).
     *
     *      Note: If $user_id is blank or null, ehUser::normalizeUserID() will attempt to use the currently logged in user.
     *            If $route_name is null, the current route name will be used.
     *
     * @param $user_id      // The person to check the permissions for (default to currently logged in user).
     * @param $route_name   // The route name or page id to check against (defaults to current route).
     * @param $access_level // The access constant to check against (see config/eco-helpers.php)
     * @return bool
     */
    public static function chkUserResourceAccess($user_id = null, $route_name = null, $access_level = 0)
    {


        // The intent here is to COMPARE a specific ACCESS right with a particular resource to see
        // if this user has permissions to access at that level.


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $user_id and $route_name.
        $user = ehUser::normalizeUserID($user_id);
        $route_name = ehPage::normalizeRouteName($route_name);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // !! IMPORTANT !! -- Site Admin bypasses all other checks - first thing we look for.
        // So before anything else - Is this user is a Site Admin (they have no checks on anything)
        // This needs to check for your role too.
        if ($user && $user->isAdmin()) {        // Is the user both logged in AND an admin?
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Is either the user or acting role not active.
        ////////////////////////////////////////////////////////////////////////////////////////////

        // 1. Is the user's login active?
        if (!ehUser::isUserActive()) {
            return false;
        }

        // 2. Is the user's acting role active?
        if (!ehUser::isActingRoleActive()) {
            return false;
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Get the page information for this route.
        $page = ehPage::getPageInfo($route_name);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 3. No page information returned.
        //    Will have to consider this as no access since there's nothing to check against.
        if (!isset($page->id)) {
            return false;
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Get this user's acting role token for this page.
        $token = self::getAccessToken($page, $user->getActingRole());


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Then check it to see if they have exactly that right.
        foreach (self::$access_token_array as $right_name=>$right_value) {

            // Get the system/ constant defined value for this $right_name
            $system_value = constant('ACCESS_'.strtoupper($right_name));

            if (($token & $access_level) == $system_value) {return true;}

        }

        return false;




/*
   DOES THIS EVEN NEED TO BE CHECKED BASED ON THE CHECK ABOVE.
   Or should this consider the allow unauthenticated children??
   I kind of don't think so. We're just checking to see if you have this right on this resource (?)
    ////////////////////////////////////////////////////////////////////////////////////////////
    // 5. Does the user have access to this module?
    //    Note: that since this function's purpose is to only check role assigned permissions.
    //          we're not concerned with the allow_unauthenticated_children flag.
    //          We're proceeding to check only the parent module access first -- regardless of that flag.
    //    If user's role has no access to this module then,
    //    no sense checking any further -- that WILL kill access to any children.
    //    Note: The special case where a child page is set to public and and module may be set to auth.
    //          If allow_unauthenticated_children is set to false then the parent module permissions apply
    //          but this should all be handled in the check_permissions middleware prior to these checks.
    $module_id = ehMenus::getMyModule($page->id)->id;
    $token = self::getAccessToken($module_id, $user->getActingRole());
    if (!$token >= $access_level) {
        return false;
    }
*/



    }

    /**
     * Mainly for checking user feature access in a template (xd, xr, f1-f4), but can be used for any permissions check.
     * Used for controlling access to per page features (as defined in the Menu/ Page system).
     * Check to see that this user has this $security_right assigned (individually or through the current role).
     *
     * @param $user_id
     * @param $route
     * @param $access_level
     * @return bool
     */
    public static function chkUserFeatureAccess($user_id = null, $route = null, $access_level = 0)
    {

        // Design decision:
        // Deprecating this feature and just using the function above here until there
        // seems to be a need for this by itself.

        // Remember that the template has access to all the user rights through ehAccess::getUserRights()

        return true;

    }



    ////////////////////////////////////////////////////////////////////////////////////////////
    // Maintenance functions
    ////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Pull an access token based on a $route_name and $role_id.
     *  Note: Access Tokens belong to page ids -- they DO NOT belong to routes. The page record will save the route.
     *  Note: This defaults to returning a combined (summed) access number - you can specify an array if desired.
     *  Note: role_id should always be passing in the acting_role!
     *
     *
     * @param $page_id // The page id associated with the permissions token.
     * @param $role_id // Required. Should always be the acting_role.
     * @param $as_array
     * @return float|mixed
     */
    public static function getAccessToken($page_id, $role_id, $as_array = false)
    {

        // Safety net; Clean up the input parameters to actively force a no-find if blank:
        // The query matches an empty string or null to a zero or null value in the table.
        // So put something in there that we know can't be found.
        if (empty($role_id)) {
            $role_id = self::$force_no_find;
        }
        if (empty($page_id)) {
            $page_id = self::$force_no_find;
        }



        /* NO MORE ROUTES -- passing (expecting) only page id's
        ////////////////////////////////////////////////////////////////////////////////////////////
        // Replace forward slashes with dots.
        $route_name = ehPage::normalizeRouteName($route_name);
        */

        ////////////////////////////////////////////////////////////////////////////////////////////
        // We have to have a role_id so if it's missing or blank for any reason then grab the user's
        $role = ehUser::normalizeRoleID($role_id);

        // Normalize the page id to a page object.
        $page = ehPage::normalizePageID($page_id);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Does this role have site_admin privileges?
        if ($role->site_admin) {
            // Force all right bits to turn on (ACCESS_ADMIN is the highest bit).
            return (ACCESS_ADMIN * 2) - 1;
        }


        /* I think this has all been solved for and implemented in ehPage::getPageInfo()
                // Find out something about the route name passed first.
                // Does it have 2 parts? (or more)
                // Is the second part one of the resourceful stack?
                ////////////////////////////////////////////////////////////////////////////////////////////
                self::$route_parts = explode('.',$route_name);                 // Break apart the route name
                self::$number_of_parts = count(array_filter(self::$route_parts));       // Determine how many parts it has
                if (self::$number_of_parts == 0) { return 0; }                          // Can't search on an empty string

                // Figure out is this is a standard resourceful route stack
                // Is the 2nd name one of the standard resource calls?
                self::$is_resource_request = false;
                if (self::$number_of_parts > 1) {
                    switch (self::$route_parts[1]) {                // Zero based array so get the second element.
                        case 'index':
                        case 'show':
                        case 'create':
                        case 'edit':
                        case 'destroy':
                        case 'store':
                        case 'update':
                            self::$is_resource_request = true;       // Yes, the incoming request looks like a resource request.
                            break;
                        default:
                            self::$is_resource_request = false;      // No, the incoming request does not match a resource request.
                    }
                }

        */
        /*  THIS SHOULD ALL me irrelevant now. We're only searching by page id.
        // NOT RESOURCE ROUTE
        // If the route name only has one part then that's all we can check
        // Which should yield the same results as checking any non-resourceful whole name - regardless of how many parts (kill 2 birds with 1 stone)
        if (!self::$is_resource_request) {

            $q = "
                SELECT * FROM eh_access_tokens WHERE
                ".$search_field." = '{$role->id}'                                    
                AND route = '{$route_name}';
            ";

        } else {

            // IS A RESOURCE ROUTE
            // -- then look for a route_name first part only (strip off the resourceful part).
            $q = "
                SELECT * FROM eh_access_tokens WHERE
                ".$search_field." = '{$role->id}'                                    
                AND route = '".self::$route_parts[0]."';
            ";

        }
*/


        // Build an execute the get access token query.
        $q = "SELECT * FROM eh_access_tokens WHERE page_id = " . $page->id . " AND role_id = " . $role->id . ";";
        $result_token = DB::select($q);


        // If there's a result -- then use that and we're done.
        if ($result_token) {
            if ($as_array) {
                return $result_token[0];
            } else {
                return self::combineTokens($result_token);
            }
        }


        // SAFETY: If we got here then we missed all of the access queries.
        return 0;

    }




    /**
     * Saves the access token for this group and this route
     * Note: ONLY PASS one; $role_id or $user_id (if there are multiples passed in, then it will check in that order
     * and use the first one it finds)
     *
     *
     * @param     $route_name       - Any route; currently requested $_SESSION['route'] or entered as a string
     * @param     $new_access_token - Can pass either a fully summed integer or an array with individual values
     * @param int $role_id          - The Group's ID number
     * @param int $user_id          - For individual override (cUID)
     * @return bool                 - Completion/ error status
     * @throws \Exception
     */
    //public static function saveAccessToken($route_name, $new_access_token, $role_d = 0, $user_id = 0) {
    public static function saveAccessToken($page_id, $new_access_token, $role_id = 0, $user_id = 0)
    {

        // We have to have one or the other to continue;
        if ($role_id == 0 && $user_id == 0) {
            return false;
        }

        if (!is_numeric($page_id)) {
            throw new \Exception('saveAccessToken: requires a numeric page id to continue.');
        }

        /*  Rewriting to require a page_id to save the token.
                // $route_name can accept a $page_id, a string $route_name or a complete Route object
                // So determine which and then convert to a $page_id for processing.

                // If we passed in a complete Page model then just grab the 'route' field.
                if (is_object($route_name)) {
                    $route_name = $route_name->route;
                }
        */


        ////////////////////////////////////////////////////////////////////////////////////////////
        // We need to know a couple of things
        // 1. No check is done on the route_name -- just assumed to be a real route.
        // 2. access_tokens don't know anything about page, or module or methods; just whole route names.
        // 3. We do need to know if the $access_token passed is an array of individual security bits or already pre summed.


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 1a Check to see if the token is an array or not
        if (is_array($new_access_token)) {
            // And if it is an array, is it the right kind (just check one key to make sure
            if (key_exists('VIEW', $new_access_token)) {
                // Then turn this back into a fully formed integer token
                $new_access_token = array_sum($new_access_token);
            } else {
                // Looks like it is an array, but is either empty or doesn't have the right keys needed
                return false;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Delete the current access token before saving it. (effectively clearing it out)
        //self::deleteAccessToken($route_name, $role_id, $user_id);
        self::deleteAccessToken($page_id, $role_id, $user_id);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Determine which one to do the save on:
        $this_id = 'no_id';
        if ($user_id > 0) {
            $this_id = 'user_id';
        }

        if ($role_id > 0) {
            $this_id = 'role_id';
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Something went wrong -- so don't bother attempting the store.
        if ($this_id == 'no_id') {
            throw new \Exception('Group or User id required in self::saveAccessToken($route_name, $new_access_token, $role_id = 0, $user_id = 0).');
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Go ahead and add the token now by executing the insert
        if ($new_access_token > 0) {        // But don't save zero values
            $token = new ehAccessToken;
            $token->page_id = $page_id;
            $token->token = $new_access_token;
            $token->$this_id = $$this_id;
            //$token->route = ehPage::getRouteFromPageNumber($page_id)->route;
            //$token->route = ehPage::getPageInfo($page_id)->route; DEPRECATED

            return $token->save();
        }

        return false;
    }

    /**
     * Delete the access token for this $page_id, $user_id or $role_id.
     *
     * @param int $page_id      // The numeric page id used to store this token.
     * @param int $role_id
     * @param int $user_id
     * @return bool
     */

    public static function deleteAccessToken($page_id, $role_id = 0, $user_id = 0)
    {

        // We have to have one of these to continue;
        if ($user_id == 0 && $role_id == 0) {
            throw new \Exception ('deleteAccessToken: missing role id or a user id.');
        }

        // This function expects a numeric page id.
        // Note: this was a design change from initially using a route name so leaving this here to catch any legacy calls.
        if (!is_numeric($page_id)) {
            $page_id_type = gettype($page_id);
            throw new \Exception ('deleteAccessToken: expecting a numeric page id; '.$page_id_type.' given.');
        }


        // Note: $access_token is only used for the saveAccessToken above (we don't need that when deleting it).

        // We need to know a couple of things
        // 1. No check is done on the route_name -- just assumed to be a real route.
        // 2. access_tokens don't know anything about page or module or methods; just whole route names.


        // We don't want to store a 0 token value - that acts as an express lockout overriding any other right
        /* WHAT?? this keeps you from turning off a menu item for a group --- ???
        // YES - passing a zero for a route does turn it off - that's the expected behavior when you uncheck all rights in Group profile.
        if ($access_token==0) {
            return false;
        }
        */

        // Clean out (delete) any current tokens for this page_id.
        $query = 'DELETE FROM eh_access_tokens ';

        // WHERE clause safety net (use if both the checks below fail).
        $where = "WHERE user_id = '" . self::$force_no_find . "';";

        /*
        $this_id = 'no_id';
        if ($user_id > 0) {
            $this_id = 'user_id';
            $where = "WHERE user_id = '{$user_id}' AND route = '{$route_name}'; ";
        } elseif ($role_id > 0) {
            $this_id = 'd';
            $where = "WHERE d = '{$role_id}' AND route = '{$route_name}'; ";
        }
        */

        $this_id = 'no_id';
        if ($user_id > 0) {
            $this_id = 'user_id';
            $where = "WHERE user_id = '{$user_id}' AND page_id = '{$page_id}'; ";
        } elseif ($role_id > 0) {
            $this_id = 'role_id';
            $where = "WHERE role_id = '{$role_id}' AND page_id = '{$page_id}'; ";
        }

        return DB::delete($query . $where);

    }




    /**
     * Turn a combined access token into a rights array
     *  with either true/false for each ($return_values = false)
     *  or ($return_values = true) with the actual numeric value of the constants.
     *
     *
     * @param $combined_token
     * @param $return_values // This is used when building out the permission checkboxes so the checkbox value = the
     *                       actual security value.
     * @return array
     */
    //public static function decodeToken($combined_token, $site_admin = false, $return_values = false) {
    public static function decodeToken($combined_token, bool $return_values = false)
    {


        /*
         * This is used for things other than the currently logged in user so this can't be used here.
        ////////////////////////////////////////////////////////////////////////////////////////////
        // !! IMPORTANT !! -- Site Admin can access anything.
        $site_admin = false;
        if (ehRole::findOrFail(Auth()->user()->ugsID)->site_admin) {
            $site_admin = true;     // override any false that occurs below;
        }
        */

        self::$access_token_array = [];

        // Uses simple names for keys here to avoid confusion with either numbered keys or the actual security constant names (preceded with 'ACCESS_').
        // Note these names are used elsewhere to build out security checks so they must remain the same as the last part of the constant name.
        // Start out (initialize) the access array for this page with no access for anything.

        // Note: constants are setup in the packages config/eco-constants.php file.
        self::$access_token_array = ([
            'view' => false,
            'export_restricted' => false,
            'export_displayed' => false,
            'edit' => false,
            'add' => false,
            'delete' => false,
            'export_table' => false,
            'feature_1' => false,
            'feature_2' => false,
            'feature_3' => false,
            'feature_4' => false,
            'admin' => false
        ]);

        // Loop the array and check the permissions (using the system defined 'ACCESS_' constants defined in eco-constants.php  -- then set either true/false or the actual value
        foreach (self::$access_token_array as $key => $value) {

            $access_constant = constant('ACCESS_' . strtoupper($key));

            if (((int)$access_constant & (int)$combined_token) == (int)$access_constant) {
                if ($return_values) {
                    self::$access_token_array[$key] = $access_constant;
                } else {
                    self::$access_token_array[$key] = true;
                }
            }

        }

        return self::$access_token_array;    // Return decoded access for this group or all false if nothing found

    }


    /**
     * Loop any result set and combine all the access tokens into one (highest) number.
     * Note: Using the bitwise OR function to combine binary digits.
     *
     * @param $result_tokens
     * @return int
     */
    protected static function combineTokens($result_tokens)
    {

        // Ensure we are returning 0 if there was no result passed;
        $combined_token = 0;

        // Using bitwise operator OR to only add the bits together that are different.
        // Basically this should just give the highest possible permissions of all (without duplication)
        foreach ($result_tokens as $item) {
            $combined_token = ($combined_token | $item->token);
        }

        return $combined_token;
    }


    /**
     * Updates any matching entry for old_route_name with new_route_name
     *  - Across all users and groups.
     *  - e're going to expect a fully qualified (as stored) route_name.
     *
     * @param $old_route_name
     * @param $new_route_name
     * @return bool
     */
    public static function updateAccessToken($old_route_name, $new_route_name)
    {

        $query = "UPDATE access_tokens SET route = '{$new_route_name}' WHERE route = '{$old_route_name}';";
        return DB::update($query);

    }


    /**
     * Check if a $role_id has a certain access $security_level permission to a certain numeric $page_id.
     *
     * @param int $role_id
     * @param int $page_id
     * @param int $security_level
     * @return bool
     */
    public static function chkRoleSecurityAccess($role_id, $page_id, $security_level)
    {

        // This function expects a numeric page id.
        // Note: this was a design change from initially using a route name so leaving this here to catch any legacy calls.
        if (!is_numeric($page_id)) {
            $page_id_type = gettype($page_id);
            throw new \Exception ('chkRoleSecurityAccess: expecting a numeric page id; '.$page_id_type.' given.');
        }



        ////////////////////////////////////////////////////////////////////////////////////////////
        // We can accept a $role object instance or a $role_id number
        $role = ehUser::normalizeRoleID($role_id);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Check the route name and normalize slashes or convert to a name if it's a number.
        //$route_name = ehPage::normalizeRouteName($route_name);


        /*
         * NOTE: I took this out of decodeToken because it didn't seem to be the right place to make that decision.
         *       So commenting this out of here for now.
         *
        // Since this is at the Role level -- we will check to see if that Group is a Site Admin
        ////////////////////////////////////////////////////////////////////////////////////////////
        // !! IMPORTANT !! -- Site Admin bypasses all other checks - first thing we look for.
        // So before anything else - Is this user a Site Admin (they have no checks on anything)
        //if (ehRole::findOrFail($group->id)->site_admin) { return true; }
        if ($group->site_admin) { return true; }
        */


        ////////////////////////////////////////////////////////////////////////////////////////////
        $role_can_view = false;        // Make sure and default to no access.
        $combined_token = 0;            // This is (should be) overwritten right away; just a 'no access' safety net.



        ////////////////////////////////////////////////////////////////////////////////////////////
        // Get the combined tokens for this Role only.
        $combined_token = self::getAccessToken($page_id, $role->id);


        ////////////////////////////////////////////////////////////////////////////////////////////
        //  Does the group have the $security_level needed for this right?
        if (((int)$combined_token & (int)$security_level) == (int)$security_level) {
            $role_can_view = true;
        };
        return $role_can_view;

    }


    /**
     * Pull a list of access right for this user for this route name.
     * (replaces the old Layout function to initUserRights -- that was the wrong place for that.)
     * Note: If no $route_name is passed, the current route name will be used.
     *
     * USAGE (in Blade template):
     * @inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')
     * $access->getUserRights()->feature_1
     *
     * "view": true/false
     * "export_restricted": true/false
     * "export_displayed": true/false
     * "edit": true/false
     * "add": true/false
     * "delete": true/false
     * "export_table": true/false
     * "feature_1": true/false
     * "feature_2": true/false
     * "feature_3": true/false
     * "feature_4": true/false
     * "admin": true/false
     *
     * @param $user_id
     * @param $route_name   // route_name or page-id;
     *                      // Note since getAccessToken() is already route_name/ page->id aware
     *                         -- we don't have to check that here.
     * @return array|false[]
     */
    public static function getUserRights($user_id = null, $route_name = null)
    {

        $rights = [];

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Note: if $route_name is a $page_id then getAccessToken() will deal with that
        //  (so that's perfectly fine and no check needed here).


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Clean up the format of the route name
        $route_name = ehPage::normalizeRouteName($route_name);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the user id
        $user = ehUser::normalizeUserID($user_id);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Get the page information for this route.
        $page = ehPage::getPageInfo($route_name);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Pull the decoded token for this $page.
        if (!$user) {
            // If this user is not logged in the use "0" as the token.
            $rights = self::decodeToken(0);
        } else {
            // If the user is logged in:
            ////////////////////////////////////////////////////////////////////////////////////////////
            // Get the access token for this user on the current route -- then decode it into a usable array.
            if (!empty($user->getActingRole())) {    // Error check for no role assigned.
                $combined_token = self::getAccessToken($page, $user->getActingRole());
            } else {
                // If for any reason you do not have a role assigned then you have no access.
                $combined_token = 0;
            }
            $rights = self::decodeToken($combined_token);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // The result is converted to an object so properties can be accessed like an Eloquent model using "->"
        // https://stackoverflow.com/questions/1869091/how-to-convert-an-array-to-object-in-php
        // keywords: array to object, collection, convert collection
        return json_decode(json_encode($rights), FALSE);

    }


}
