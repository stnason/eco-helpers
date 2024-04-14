<?php

namespace ScottNason\EcoHelpers\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehNotifier;
use ScottNason\EcoHelpers\Models\ehPage;
use ScottNason\EcoHelpers\Models\ehRole;
use ScottNason\EcoHelpers\Models\ehRoleLookup;

/**
 * ecoHelpers functions for the Users Model.
 *
 * This trait provides all of the functions related to managing and answering granular/ specific questions
 * about a users roles.
 *
 * Because this trait is added to the ehUser model (which User should extend) -- it provide the ability to use
 * the Auth() helper like this: Auth()->user()->isAdmin($user_id)
 *                              Auth()->user()->getUserRoles($user_id)
 */
trait ehUserFunctions
{

    /**
     * Base64 image date for the "no user photo" image.
     * @var string
     */
    public static $na_image_data = '
        /9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAABGAAD/4QNhaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzEyMyA3OS4xNTg5NzgsIDIwMTYvMDIvMTMtMDE6MTE6MTkgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9IjlFODc1NjBGNkQxQjVERTIyRDNCRkY3MkE0MDlBNTE4IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjlBMkE2NENBM0MzQTExRTlCNDY0RDRGMzE1N0IzNzkwIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjlBMkE2NEM5M0MzQTExRTlCNDY0RDRGMzE1N0IzNzkwIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzUuMSBNYWNpbnRvc2giPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMjgwMTE3NDA3MjA2ODExQjMyRUU2QkEzRjBBMTU1RiIgc3RSZWY6ZG9jdW1lbnRJRD0iOUU4NzU2MEY2RDFCNURFMjJEM0JGRjcyQTQwOUE1MTgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBh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
        ';


    /**
     * Simple check to see if this user's current role gives them site admin privileges.
     *
     * @return bool
     */
    public function isAdmin() {

        // Get the current role.
        $role = $this->getActingRole();

        // Is that an Admin group?
        if (!empty($role)) {
            if ($role->site_admin) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

    /**
     * Is this user's login profile active?
     *  - checks both archived and login_active.
     * @param $user_id
     * @return int
     */
    public static function isUserActive($user_id=null) {
        $user = self::normalizeUserID($user_id);

        // Didn't find this user or there is no logged in user.
        if (empty($user)) {
            return false;
        }

        // Check if user is either archived or not-active.
        if ($user->archived || !$user->login_active) {
            // User is not active.
            return false;
        } else {
            // User login is active.
            return true;
        }

    }
    public static function isActingRoleActive($user_id=null) {
        $user = self::normalizeUserID($user_id);

        // Did we have a user here or not?
        // Safety check; return false if for any reason we can't find this user.
        if ($user===null) {
            return false;
        }

        // If anything goes wrong with the getActingRole() call, it will return a 'false'.
        // We need to pass the user id here for certain cases where we're only partially logged in and still checking.
        if (empty($user->getActingRole($user->id))) {
            return false;
        } else {
            return $user->getActingRole($user->id)->active;
        }

    }


    /**
     * Find out how many roles are assigned to this user.
     * @return int
     */
    public function howManyRoles($user_id = null) {

        // If no id passed then use the current logged in user
        if ($user_id === null) {

            if (Auth()->guest()) {
                return 0;
            }
            $user_id = Auth()->user()->id;
        }

        $result = self::getUserRoles($user_id);

        return count($result);
    }


    /**
     * Quick utility for getting all roles associated with a user.
     * If $user_id is null, it will attempt to use the currently logged in user.
     * Note: this is returning a roles_lookup query so it only has $user_id, $role_id to choose from.
     *
     * @param $user_id
     * @return mixed
     */
    /**
     * @param $user_id  // Pass a user id or leave it null to use the currently logged in user.
     * @param $active   // Include only active roles (ture) - or all roles (false).
     * @return []       // No roles found for this user.
     */
    public function getUserRoles($user_id=null, $active=true) {

        // If no id passed then use the current logged in user
        if ($user_id === null) {
            if (Auth()->guest()) {
                return [];
            }
            $user_id = Auth()->user()->id;
        }

        // Include only active roles?
        $additional_where = '';
        if ($active) {
            $having = ' HAVING active = 1 ';
        }

        // Get all the roles this user is assigned to -- filtered by the $active role flag above.
        // Since we're pulling from the eh_roles_lookup,
        //  we're adding the "name" (and "active") from the actual eh_roles table.
        $q = "
        SELECT *, 
        (SELECT name FROM eh_roles WHERE eh_roles.id = eh_roles_lookup.role_id) as name, 
        (SELECT active FROM eh_roles WHERE eh_roles.id = eh_roles_lookup.role_id) as active
        FROM eh_roles_lookup WHERE user_id = {$user_id}".$having."
        ORDER BY name;";

        return DB::select($q);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // The result is converted to an object so properties can be accessed like an Eloquent model using "->"
        // https://stackoverflow.com/questions/1869091/how-to-convert-an-array-to-object-in-php
        // keywords: array to object, collection, convert collection

        //dd($result, json_decode(json_encode($result), FALSE));

        // umm... not so much here.
        //return json_decode(json_encode($result), FALSE);


    }


    /**
     * Get the users associated with the Role.
     *
     * Not really sure this belongs in this trait by definition, but leaving here for convenience (for now).
     *
     * @param $d
     * @return string
     */
    protected static function getUsersInRole($role_id) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create the List of Current Users that are assigned to this Group

        $q = "SELECT * 
        FROM eh_roles_lookup AS l
        JOIN users AS u ON (u.id = l.user_id)
        WHERE l.role_id = {$role_id}
        ORDER by first_name
        ";

        return DB::select($q);

    }


    /**
     * Return the user's acting role's default_home_page (the whole eh_pages record).
     *
     * @param $user_id
     * @return string
     */
    public static function getDefaultHomePage($user_id=null)
    {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $user_id.
        $user = self::normalizeUserID($user_id);

        $acting_role = $user->getActingRole($user->id);

        // Pull the whole eh_tables page record for this id
        if (!empty($acting_role->default_home_page)) {
            return ehPage::find($acting_role->default_home_page);
        } else {
            //return $acting_role->default_home_page;
            return false;
        }

    }


    /**
     * Return the currently set active role [object] (in the users table) for this user.
     * Or if for any reason it's not set, then use the default role assigned.
     *
     * Can pass a specific $user_id, $user object, or if left null -- will use the logged in user.
     *
     * @param $user_id
     * @return mixed
     */
    public static function getActingRole($user_id=null) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $user_id.
        $user = self::normalizeUserID($user_id);

        //
        /* Trying this without for a while but leaving the code here as a reminder.
            If it turns out that we need it, we need to figure out how to call
             ehNotifer reather than hard-coding it here.
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Just a safety net for the notification system (if it got stuck or hung up for any reason).
        // Anytime we request the acting role - go ahead and reset the message_modal notification flag.
        $message_modal = [
            'flag' => false,
            'show' => 'show',
            'content' => '',
            'title' => ''
        ];
        session(['message_modal' => $message_modal]);
        */




        ///////////////////////////////////////////////////////////////////////////////////////////
        // If the acting_role is empty then set it to the default role
        if (empty($user->acting_role)) {
            // But if default_role is empty -- then we can't continue. (Note: the re-login attempt will catch this.)
            if (empty($user->default_role)) {
                //It looks like this actual redirect is just returned to the template and then what? Doesn't do anything but cause problems there.
                //return redirect()->route('logout');     // If the default_role is not set then force a logout.
                Auth::logout();
                return false;
            }
            $user->acting_role = $user->default_role;
            $user->save();                              // If acting role is empty, then set it to default_role
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Is the user's acting role active?
        $acting_role = ehRole::find($user->acting_role);

        if (!$acting_role->active) {
            // I'm not sure we should be doing any redirects from within this class.
            // Whose responsibility is it? Where should redirects reside? The Controller??
            // The ehLoginController controls the error message that are thrown during a login process (so there's that).
            // But (?) doing the redirect here and above since this will be used for BOTH the initial login and any subsequent role change.
            // return redirect()->route('logout');        // If the acting role is not active then force a logout.
            // Especially considering that we're passing a user id that may not be the current.
            // Auth::logout();
            return false;
        };


        ///////////////////////////////////////////////////////////////////////////////////////////
        return $acting_role;        // Return the whole [acting role] role object.

    }


    /**
     * Change the current user's "acting role" to the role id passed.
     *
     * @param $role_id
     * @return void
     */
    public function setActingRole($acting_role_id, $popup=true) {


        // Must pass the below checks otherwise this will not happen and just return.
        // Role must be both assigned to this user and currently active.
        $i_have_this_role_assigned = false;


        //////////////////////////////////////////////////////////////////////////////////
        // 1. If this is the user's default role.
        //    then just go ahead and change to it without further security checks.
        if ($this->default_role == $acting_role_id) {
            goto change_role;
        }

        //////////////////////////////////////////////////////////////////////////////////
        // 2. Was originally the check to see if the "Group" was assignable as a role.
        //    Now we only have roles.

        //////////////////////////////////////////////////////////////////////////////////
        // 3. Make sure this user has this role assigned AND that it's active.
        //      Note: this check is the only way to get $i_have_this_role_assigned set to true.
        $active_roles = $this->getUserRoles();      // With no parameters this will return the currently logged in user's active roles only.

        // Note: Since the getUserRoles() function returns a role_lookup you have to look for $roles_lookup->role_id (not $roles_lookup0>id)
        foreach ($active_roles as $role_lookup) {
            // Is the user assigned to the role that's being requested?
            if ($role_lookup->role_id == $acting_role_id) {
                $i_have_this_role_assigned = true;
            }

        }

        //////////////////////////////////////////////////////////////////////////////////
        // 4. Catch-all -- if we didn't verify positive access to this role above,
        //      then we punch out.
        if (!$i_have_this_role_assigned) {      // Only way to get past here is if we're
            return;                             // assigned to the requested role and it's active.
        }


        //////////////////////////////////////////////////////////////////////////////////
        change_role:
        // Set the requested role as the acting role
        $this->acting_role = $acting_role_id;
        // Then save that in the user's record.
        $this->save();


        //////////////////////////////////////////////////////////////////////////////////
        // Post a notification for this role change.

        // Throw this change into the notification system for the display.
        if ($popup) {
            $notification = [
                'user_id' => Auth()->user()->id,    // The intended user's cUID.
                'auto_clear' => 1,                  // Delete this record after one viewing by the indented user.
                'auto_popup' => 1 ,                 // Force the message_modal display to show notifications (usually used with exclusive).
                'user_clearable' => 0,              // Should the user be presented with an 'X" to delete this record.
                'viewed' => 0,                      // Has this item been viewed once? (but not deleted.)
                'exclusive' => 1,                   // When displaying the modal popup, only show this single notification.
                'route' => '',                      // The name of the route to display this on (commonly used for home - blank for all)
                'title' => 'User Role Changed',     // The notifications title (used in title bar when exclusive).
                'content' => 'User Role has changed to <strong>'.$this->getActingRole()->name.'</strong>.',      // The main body of the notification.
                //'expiration' => \Carbon\Carbon::now(config('eco-helpers.timezone'))->format(config('eco-helpers.date_format_sql_short')), // Delete this item if you try to view it after this date.
                'expiration' => \Carbon\Carbon::now()->format(ehConfig::get('date_format_sql_short')), // Delete this item if you try to view it after this date.
            ];

            ehNotifier::newNotification($notification);
        }
        

    }


    /**
     * Helper to return the user's default role.
     * Has a safety check to force a logout if it is blank. (every user must have one).
     *
     * @param $user_id
     * @return mixed
     */
    public static function getDefaultRole($user_id=null) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $user_id.
        $user = self::normalizeUserID($user_id);


        // If this is not set--which should be enforced in the ehUserController--then we need to force a logout.
        // Any attempt to re-login will throw that error message then.
        if (empty($user->default_role)) {
            return redirect()->route('logout');
        }

        return $user->default_role;

    }








    /**
     * Quick utility for adding an entry to the roles lookup table.
     *
     * @param $user_id
     * @param $role_id
     * @return void
     */
    public function addUserRole($user_id, $role_id) {

        // Don't allow duplicate entries -- so just delete before adding to be sure.
        $q = "DELETE FROM eh_roles_lookup WHERE user_id = {$user_id} AND role_id = {$role_id};";
        DB::delete($q);


        // Note: need to use the model rather than a DB insert so the system dates will auto update.
        $role_lookup = new ehRoleLookup;
        $role_lookup->user_id = $user_id;
        $role_lookup->role_id = $role_id;
        $role_lookup->save();

    }

    /**
     * Remove the specified role by id from the eh_roles_lookup table -- while editing the User Detail.
     * Note: should only be used through ajax calls when updating the user profile.
     * WARNING: This MUST have a roles lookup table id ($role_lookup_id) in order to work properly
     *       -- that refers to a specific entry in the eh_roles lookup table -- by user!
     *       -- When edit the User's dialog box in Roles Detail, use the method below: deleteUsersFromRole()
     *
     * @param Request $request
     * @param ehRoleLookup $role_lookup     // Not sure what's happeing here but had issues trying to auto insert the ehRoleLookup model record
     *                                      // so was forced to just pass an id and then pull our own below. (??)
     * @return mixed
     */
    public function deleteRoleFromUser(Request $request, $role_lookup_id)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Just for Ajax trouble shooting. (so you can see what the heck is happening.)
        //throw new \Exception("stop here: ".$role_lookup->user_id);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Convert the role_lookup_id into a role_lookup instance
        $role_lookup = ehRoleLookup::find($role_lookup_id);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the Role name from the role_lookup role id for the flash message.
        $role_name = ehRole::find($role_lookup->role_id)->name;

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get a user instance for the subsequent checks below.
        $user = User::find($role_lookup->user_id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // RULE: Are we deleting the role you're currently set to - acting_role?
        // If current acting_role is set to this role then change to default
        if ($user->acting_role == $role_lookup->role_id) {
            $user->acting_role = $user->default_role;
            $user->save();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // RULE: Check to see if this is the user's default_role.
        // And if so, then null it and the acting_role out.
        if ($user->default_role == $role_lookup->role_id) {
            $user->default_role = null;
            $user->acting_role = null;
            $user->save();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Delete this role's lookup entry.
        $result = $role_lookup->delete();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message','Role <strong>'.$role_name.'</strong> removed successfully.');
        } else {
            session()->flash('message','Something went wrong.');
        }

        // The ajax call is handling the page refresh on the user-detail template.
        // and role detail template.
        // return redirect()->refresh();
        return;

    }

    /**
     * Used by Roles Detail to remove users from the Users dialog box.
     * Note: this is different than the deleteRolesFromUser() method above. That's for the Users Detail screen.
     *
     * Pass in an array of user_id and role_id
     * [
     * ['user_id'=>$user_id, 'role_id'=>$role_id],
     * ['user_id'=>$user_id, 'role_id'=>$role_id],
     * ]
     * @param $deletion_array   // Expected to be contained in the Ajax call data.
     *
     * @return void
     */
    public function removeUsersFromRole(Request $request) {

        // Make sure the array is not empty.
        if (empty($request->deletion_array)) {
            throw new \Exception('682: Missing deletion_array!');
        }

        // Build out a query from the passed $deletion_array
        $q = "DELETE FROM eh_roles_lookup WHERE ";
        foreach($request->deletion_array as $delete) {
            // Don't build a query with a blank value.
            if (empty($delete['user_id']) or empty($delete['role_id'])) {
                throw new \Exception('683: Missing deletion_array->value!');
            }
            $q .= "(user_id=";
            $q .= $delete['user_id'];
            $q .= " AND role_id=";
            $q .= $delete['role_id'];
            $q .= ") OR ";
        }
        // Remove the trailing or and close the sql statement;
        $q = rtrim($q, ' OR ');
        $q .= ";";

        // TODO: test and make sure the check_permissions is not allowing this if you don't have Delete rights.
        // Delete the roles lookup table entry for the user(s) within this role.
        $result = DB::delete($q);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        //TODO: ummm...flash is not working from here. (??)
        // but session() is available from blade so there may be an opportunity to do this right before the page load.
        /*
        if ($result) {
            session()->flash('message',count($request->deletion_array).' users removed from this role.');
        } else {
            session()->flash('message','Something went wrong.');
        }
        */
        // testing to get the flash to work:
        // "Too many redirect"  ???
        //return redirect('/roles/'.$request->deletion_array[0]['role_id'].'/?module_id='.$request->frm_module_list);

    }

    /**
     * Used by the ehAuthenticatedSessionController to determine this user's proper role right after login.
     * Uses the system configuration to determine if it should be the default_role of the last acting_role.
     *
     * @param $user_id
     * @return mixed
     */
    public function roleAtLogin($user_id=null) {

        $user = self::normalizeUserID($user_id);

        // Check the configuration to see which behavior we want to use:
        // Set the acting role based on the global configuration setting:
        /*
        'role_at_login'=>0,
            0='default' ; the default role set in the user profile.
            1='last'    ; the last role used.
            2='user'    ; NOT IMPLEMENTED. Maybe future expansion.
        */

        // Note: That default_role is a dependent variable and is validated and/or set
        //       in the ehUsersController@dataConsistencyCheck() rules.

        if (ehConfig::get('role_at_login') == 0) {
            // Use the default_role
            return $user->default_role;

        } elseif (ehConfig::get('role_at_login') == 1) {
            // Use the last role (acting_role)
            return $user->acting_role;

        } elseif (ehConfig::get('role_at_login') == 2) {
            // Future expansion -- used for a user specific behavior to override the global default form configuration.
            return $user->default_role;

        } else {
            // If all else fails, then just go back to using the defined default.
            return $user->default_role;
        }

        
    }



    /**
     * All ths methods in this trait can except either a numeric user id, or the whole user object.
     * Additional, if the $user_id is not passed or blank, then it will default to using the logged in user.
     *
     * @param $user_id
     * @return false|mixed
     */
    public static function normalizeUserID($user_id=null) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If the $user_id is blank or null then use the currently logged in user's id.
        if (empty($user_id)) {
            if (Auth()->check()) {              // If this is called on a logged in user.
                return Auth()->user();
            } else {
                return false;                   // If user is not logged in, then just return false.
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $user_id.
        // Are we passing a user id number only or the complete ehUser object?
        if (is_numeric($user_id)) {
            $user = User::find($user_id);       // $user_id is an id number so create the ehUser instance.
        } else {
            $user = $user_id;                   // $user_id is the ehUser instance so use it directly.
        }

        return $user;                           // Return the final user object.
    }


    /**
     * Normalize a $role_id to a $role object -- either from the passed id or from the logged in user
     * @param $role_id
     * @return false|mixed
     */
    public static function normalizeRoleID($role_id=null) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If the $role_id is blank or null then use the currently logged in user's id.
        if (empty($role_id)) {
            if (Auth()->check()) {              // If this is called on a logged in user.
                return ehRole::find(Auth()->user()->getActingRole());
            } else {
                return false;                   // If user is not logged in, then just return false.
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the $role_id.
        // Are we passing a user id number only or the complete ehUser object?
        if (is_numeric($role_id)) {
            $role = ehRole::find($role_id);     // $role_id is an id number so create the ehRole instance.
        } else {
            $role = $role_id;                   // $role_id is the ehRole instance so use it directly.
        }

        return $role;                           // Return the final role object.
    }




    /**
     * Logic for how you want to build-out a full name
     * (with or w/o the middle name or middle initial?)
     *
     * @return string
     */
    public function fullName() {

        if (empty($this->middle_name)) {
            return $this->first_name . ' ' . $this->last_name;
        } else {
            return $this->first_name . ' ' .$this->middle_name. ' ' . $this->last_name;
        }

    }


    /**
     * Check to see if there's a contact photo for this user then return a base_64 encoded version
     *  to include in an <img src=""> tag.
     *
     * @param $user_id
     * @param $add_mime_src     // Include the "data:image/mime/base64" part of the src string.
     * @return string           // The string to be used inside of the <img src=""> tag.
     */
    public static function getUserPhoto($user_id, $add_mime_data = true) {

        $photo_id = $user_id;
        $photo_name_body = 'contactphoto';
        $photo_mime_type = '';
        $photo_extension = '';
        $data_response = 'data:image/jpeg;base64';


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Note: hard coding support for jpg and png for now.
        if (Storage::disk(config('eco-helpers.users_photo_disk'))->exists($photo_id.'-'.$photo_name_body.'.jpg')) {
            $photo_extension = 'jpg';
        } elseif(Storage::disk(config('eco-helpers.users_photo_disk'))->exists($photo_id.'-'.$photo_name_body.'.png')) {
            $photo_extension = 'png';
        } else {
            $photo_extension = 'jpg';
            $photo_id = 'na';
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull the image file from the storage disk.
        $image = Storage::disk(config('eco-helpers.users_photo_disk'))->get($photo_id.'-'.$photo_name_body.'.'.$photo_extension);

        // Encode the image file for handoff to the src tag.
        $imageData = base64_encode($image);

        // Format the image SRC:  data:{mime};base64,{data};
        // Layout::setOptionBlock('<img alt="'.$user->id.'" title="'.$user->fullName().'" src="data:image/jpeg;base64,'.$image.'">');

        if ($photo_id != 'na') {
            // Include the mime data type right in the "src" tag -- if called for in the passed parameter above.
            $src = '';
            if ($add_mime_data) {

                // Build out a full path name to the file for the mime type check to use.
                $photo_full_path = Storage::disk(config('eco-helpers.users_photo_disk'))->path('');
                //$photo_full_path .= '/'.$photo_id.'-'.$photo_name_body.'.'.$photo_extension;
                $photo_full_path .= $photo_id.'-'.$photo_name_body.'.'.$photo_extension;
                $src = 'data:' . mime_content_type($photo_full_path) . ';base64,';
            }
            // Then add the encoded image data to the "src" tag.
            $src .= $imageData;
        } else {
            // When the image file does not exist.
            // Adding the pre-encoded base 64 $na_image_data so we don't have to worry about where that file is or isn't.
            $src = 'data:' . 'jpg' . ';base64,'.self::$na_image_data;
        }

        return $src;

    }


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

        $starting_account_number = 100001;      // TODO: Move this to eco-helper.php
        $user_account_number = '';

        // Get the highest account number in use.
        $highest = DB::select("SELECT account_id FROM users ORDER BY account_id DESC LIMIT 1;");

        if (!empty($highest[0]->account_id)) {
            // If we got a result from the query then add 1 to it to get the next available account id.
            $user_account_number = $highest[0]->account_id + 1;
        } else {
            // Otherwise, it looks like no account ids have been assigned yet so use the starting one.
            $user_account_number = $starting_account_number;
        }

        // But, make sure this user doesn't already have one assigned.
        if (!empty($request->id)) {
            $current_account = DB::select("SELECT account_id FROM users WHERE id = {$request->id};");
        }

        if (!empty($current_account[0]->account_id)) {
            // Looks like the user already has an account id so just return that one.
            return $current_account[0]->account_id;
        } else {
            // Looks like user did not have an account id assigned so use the one we created above.
            return $user_account_number;
        }

    }




    /**
     * Check to see if the user has a time zone assigned and return it.
     * Otherwise check to see if an eco-helpers default is set and return it.
     * Otherwise check the Laravel app timezone and return it.
     *
     * Note: this same logic is hard-coded into ehControl date processing.
     *  (didn't use this function since ehControl is available to all models; didn't want to just make it static.)
     *
     * @return string
     */
    public function getBestTimezone() {

        // Does this user have a time zone set?
        if (!empty($this->time_zone)) {
            return $this->time_zone;
        }

        // Does eco-helpers config have a default_time_zone set?
        if (!empty(ehConfig::get('default_time_zone'))) {
            return ehConfig::get('default_time_zone');
        }

        // Does app config have a system wide default time zone set?
        if (!empty(config('app.timezone'))) {
            return config('app.timezone');
        }

        return '';
    }


    /**
     * Return the account number for this user's parent account. (primary or assigned from)
     * VERY SIMPLE FOR NOW !!
     *
     * @param $user_id
     * @return int
     */
    public static function getUserAccount($user_id = null) {

        // Use the logged in user's id if we don't pass one.
        if (empty($user_id)) {
            $user_id = Auth()->user()->id;
        }

        // 1. Is this user the account owner/admin? (then return my account number)
        // 2. Is this user a user on this account? (then get the primary account number)
        // 3. Is this a Group authorized user? (then get the primary account number)

        // TODO: this will have to incorporate other layers of security when other account users and Groups are introduced.
        $u = User::find($user_id);

        // For now, just return the logged in user's account id.
        if (empty($u->account_id)) {
            $u->account_id = USER_ACCOUNT_BASE + $u->id;
            $u->save();
        }
        return $u->account_id;
    }



}
