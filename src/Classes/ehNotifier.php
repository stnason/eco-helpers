<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ScottNason\EcoHelpers\Models\ehUser;
use ScottNason\EcoHelpers\Models\ehNotification;

/**
 *
 */
class ehNotifier
{

    /**
     * Return the total number of notifications I have pending.
     */
    public static function getTotal($user=null) {

        // Minimum security check Make sure the person calling this is at least logged in.
        if (!self::authorized()) {return json_encode(['responseText'=>'ehNotifier: Not authorized']);}

        // Note: will use Auth()->user() if null.
        $user = ehUser::normalizeUserID($user);

        // First remove any expired notifications.
        self::removeExpired($user);

        // Build and execute the get total query.
        $q = "SELECT count(*) AS total FROM eh_notifications WHERE user_id=".$user->id;
        $result = DB::select($q);
        if (count($result) > 0) {
            return json_encode($result[0]->total);
        } else {
            return json_encode(0);
        }
    }

    /**
     * Get all the current notification for this user.
     *
     * @param $user         // Either a user_id or the whole user object. (will default to Auth()->user() without it.)
     * @return false|mixed
     */
    public static function getAll($user=null) {

        // Minimum security check Make sure the person calling this is at least logged in.
        if (!self::authorized()) {return json_encode(['responseText'=>'ehNotifier: Not authorized']);}

        // Note: will use Auth()->user() if null.
        $user = ehUser::normalizeUserID($user);

        // First remove any expired notifications.
        self::removeExpired($user);

        // Build and execute the get all notifications for this user query.
        $q = "SELECT * FROM eh_notifications WHERE user_id=".$user->id." ORDER BY created_by ASC";
        $result = DB::select($q);
        if (count($result) > 0) {
            return json_encode($result[0]);
        } else {
            return json_encode(['responseText'=>'No notification data.']);
        }

    }

    /**
     * Return the next notification (in order oldest to newest) for this user.
     *
     * @return mixed
     */
    public static function getNext($user=null) {

        // Minimum security check Make sure the person calling this is at least logged in.
        if (!self::authorized()) {return json_encode(['responseText'=>'ehNotifier: Not authorized']);}

        // Note: will use Auth()->user() if null.
        $user = ehUser::normalizeUserID($user);


        // First remove any expired notifications.
        self::removeExpired($user);

        // Build and execute the get next notification query.
        $q = "SELECT * FROM eh_notifications WHERE user_id=".$user->id." ORDER BY created_by ASC LIMIT 1";
        $result = DB::select($q);

        if (count($result) > 0) {
            return json_encode($result[0]);
        } else {
            return json_encode(['responseText'=>'ehNotifier: No notification data.']);
        }

    }

    /**
     * Delete the next notification (in order oldest to newest) for this user.
     *
     */
    public static function deleteNext($user=null) {

        // Minimum security check Make sure the person calling this is at least logged in.
        if (!self::authorized()) {return json_encode(['responseText'=>'ehNotifier: Not authorized']);}

        // Note: will use Auth()->user() if null.
        $user = ehUser::normalizeUserID($user);

        // First remove any expired notifications.
        self::removeExpired($user);

        // Build and execute the delete notification query.
        $q = "DELETE FROM eh_notifications WHERE user_id = ".$user->id." ORDER BY created_at ASC LIMIT 1";

        DB::delete($q);
        return json_encode(['responseText'=>'ehNotifier: Notification deleted.']);

    }

    public static function setViewed() {
        //TODO: set the status of this notification that have been seen once already
        // $notification->viewed = 1
        // I think there will need to be a corresponding js function built too.
    }

    /**
     * Pass an array to create a new ticket.
     *
     * @param array $notification
     */
    public static function newNotification($notification = []) {

        // Minimum security check Make sure the person calling this is at least logged in.
        if (!self::authorized()) {return json_encode(['responseText'=>'ehNotifier: Not authorized']);}

        /* USE THIS TEMPLATE FOR THE $notification ARRAY TO PASS:
        ////////////////////////////////////////////////////////////////////////////////////////////

        $notification = [
            'user_id' => 0,                 // The intended user's id.
            'auto_clear' => 0,              // Delete this record after one viewing by the indented user.
            'auto_popup' => 0 ,             // Force the message_modal display to show notifications (usually used with exclusive).
            'user_clearable' => 0,          // Should the user be presented with an 'X" to delete this record.
            'viewed' => 0,                  // Has this item been viewed once? (but not deleted.)
            'exclusive' => 0,               // When displaying the modal popup, only show this single notification.
            'route' => '',                  // The name of the route to display this on (commonly used for home - blank for all)
            'title' => '',                  // The notifications title (used in title bar when exclusive).
            'content' => '',                // The main body of the notification.
            'expiration' => null,           // Go ahead and delete this item if you try to view it after this date.
        ];

        ////////////////////////////////////////////////////////////////////////////////////////////
         */

        // Spot check that we've received a valid array
        if (
            isset($notification['user_id']) &&
            isset($notification['viewed']) &&
            isset($notification['route']) &&
            isset($notification['title']) &&
            isset($notification['content'])
        ) {

            $n = new ehNotification();
            $n->fill($notification);
            $n->save();

        } else {
            return json_encode(['responseText'=>'ehNotifier: Did not receive valid notification data.']);
        }

    }



    /**
     * Just delete any notification with an expired date older than today.
     *
     */
    protected static function removeExpired($user) {

        // Note: $user is required here, but the calling function will have already checked it and used Auth()->user() if null.

        // Minimum security check.
        // This is only being used by other functions here that have already checked this.
        // if (!self::authorized()) {return false;}

        // Build and execute the delete expired notifications query.
        $q = "DELETE FROM eh_notifications WHERE user_id=".$user->id." AND expiration < CURDATE()";
        $result = DB::delete($q);

    }


    /**
     * Minimum security check - must be at least logged in.
     *
     * @return bool
     */
    protected static function authorized() {

        // Minimum security check.
        // Note: Since, get-next is called from the navbar so it will return an error if you rely on the middleware to block it.
        //        For that reason, its page security level is set to 1-Public. (all the other method routes here are set to 2-Authenticated.)

        // Since non-admin users can only interact with their own notifications this simple check should be fine.
        //return !Auth::guest();
        return Auth()->check();

    }



}