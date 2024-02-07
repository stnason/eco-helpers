<?php


namespace ScottNason\EcoHelpers\Controllers;

use ScottNason\EcoHelpers\Models\ehNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ScottNason\EcoHelpers\Models\ehUser;

/**
 * Note: this was setup as a controller so we could extend the base controller which used the web middleware.
 *      (which is needed to get Auth() status when calling ajax functions).
 *
 */
class ehNotificationsController extends ehBaseController
{

    /**
     * Return the total number of notifications I have pending.
     */
    public static function getTotal() {

        // Minimum security check.
        if (!self::authorized()) {return false;}

        // First remove any expired notifications.
        self::removeExpired();

        // Build and execute the get total query.
        $q = "SELECT count(*) AS total FROM eh_notifications WHERE user_id=".Auth()->user()->id;
        $result = DB::select($q);
        if (count($result) > 0) {
            return $result[0]->total;
        } else {
            return 0;
        }
    }

    /**
     * Get all the current notification for this user.
     *
     * @param $user         // Either a user_id or the whole user object. (will default to Auth()->user() without it.)
     * @return false|mixed
     */
    public static function getAll($user=null) {

        // Minimum security check.
        if (!self::authorized()) {return false;}

        // if we pass a user id (or object then use that for the delete)
        // Note: Adding this functionality so others (Admin -- or maybe a Dashboard) can check as needed.
        if (!empty($user)) {
            // If we passed either a $user id or object then normalize it to the whole $user object.
            $user = ehUser::normalizeUserID($user);
        } else {
            // If we didn't pass a $user, then just use the currently logged in one.
            $user = Auth()->user();
        }

        // First remove any expired notifications.
        self::removeExpired();

        // Build and execute the get all notifications for this user query.
        $q = "SELECT * FROM eh_notifications WHERE user_id=".$user->id." ORDER BY created_by ASC";
        $result = DB::select($q);
        if (count($result) > 0) {
            return $result[0];
        } else {
            return false;
        }

    }

    /**
     * Return the next notification (in order oldest to newest) for this user.
     *
     * @return mixed
     */
    public static function getNext() {

        // Minimum security check.
        if (!self::authorized()) {return false;}
        //if (!self::authorized()) {return 'ding-dong';}    // for testing -- return a weird, easy to find in code, message.

        // First remove any expired notifications.
        self::removeExpired();

        // Build and execute the get next notification query.
        $q = "SELECT * FROM eh_notifications WHERE user_id=".Auth()->user()->id." ORDER BY created_by ASC LIMIT 1";
        $result = DB::select($q);

        if (count($result) > 0) {
            return json_encode($result[0]);
        } else {
            return false;
        }

    }

    /**
     * Delete the next notification (in order oldest to newest) for this user.
     *
     */
    public static function deleteNext() {

        // Minimum security check.
        if (!self::authorized()) {return false;}

        // First remove any expired notifications.
        self::removeExpired();

        // Build and execute the delete notification query.
        $q = "DELETE FROM eh_notifications WHERE user_id=".Auth()->user()->id." ORDER BY created_by ASC LIMIT 1";
        return DB::delete($q);

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

        // Minimum security check.
        if (!self::authorized()) {return false;}

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
            return false;
        }

    }



    /**
     * Just delete any notification with an expired date older than today.
     *
     */
    protected static function removeExpired() {

        // Minimum security check.
        if (!self::authorized()) {return false;}

        // Build and execute the delete expired notifications query.
        $q = "DELETE FROM eh_notifications WHERE user_id=".Auth()->user()->id." AND expiration < CURDATE()";
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

        // Since users can only interact with their own notifications this simple check should be fine.
        if (Auth()->guest()) {
            return false;
        } else {
            return true;
        }

    }

}
