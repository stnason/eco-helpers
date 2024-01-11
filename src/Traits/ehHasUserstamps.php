<?php

/**
 * This trait is responsible for maintaining the ecoFramework fields:
 * created_by
 * created_at
 * updated_by
 * updated_at
 *
 * Include "use App/Traits/ehHasUserstamps" in the Model Class declaration
 *
 */

namespace ScottNason\EcoHelpers\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use ScottNason\EcoHelpers\Classes\ehConfig;

trait ehHasUserstamps

{


    /**
     * Mechanism for disabling the auto table timestamp updates.
     *  There may be occasions (system updates and data consistency checks)
     *   when doing internal maintenance updates that we want to leave the stamps alone.
     *
     *   Usage - $modelInstance::$save_with_stamps = false;
     * @var bool
     */
    public static $save_with_stamps = true;


    /**
     * The "Creating" event handler.
     * This should (?) only be invoked when creating a new record.
     *
     * (note: username and date formats are set in the 2 helper methods below)
     *
     * Steps to use:
     * 1. check to see if the field exists on this table
     * 2. is it dirty (does it need to be updated)?
     * 3. set/get the time format we want then
     * 4. set/get the user format we want (name vs id)
     *
     * @param $model
     */
    public static function creatingTimestamps($model)
    {


        if (self::$save_with_stamps) {

            if ($model->created_by == null) {       // Don't overwrite if this already exists
                $model->created_by = self::formatUserStamp();
            }
            if ($model->created_at == null) {       // Don't overwrite if this already exists
                $model->created_at = self::formatDateStamp();
            }

            $model->updated_by = self::formatUserStamp();
            $model->updated_at = self::formatDateStamp();

        }

    }

    /**
     * The "Updating" and "Saving" events handlers.
     * Note: still not completely clear on saving vs updating yet but they do both happen under various circumstances.
     *
     * (note: user name and date formats are set in the 2 helper methods below.
     *
     * @param $model
     */
    public static function updatingTimestamps($model)
    {
        if (self::$save_with_stamps) {
            $model->updated_by = self::formatUserStamp();
            $model->updated_at = self::formatDateStamp();
        }

    }

    /**
     * @param $model
     */
    public static function savingTimestamps($model)
    {

        if (self::$save_with_stamps) {
            $model->updated_by = self::formatUserStamp();
            $model->updated_at = self::formatDateStamp();
        }

    }

    /**
     * User name stamp formatter.
     * Return the user name + (id) of the authenticated user (and/or define the format here)
     * Or, if no authenticated user, then return 'system' as the user name.
     *
     * (if you want it to look different or have different information in it, then change it here)
     *
     * @return string
     */

    protected static function formatUserStamp()
    {

        $user = Auth::user();

        if ($user) {

            // This is the format definition of how we want the user name to appear in the updated_by field
            //TODO: Might need to think about making this system configurable through settings or the config file.

            //$user_value = $user->username;                    // Username only
            //$user_value = $user->email;                       // Registered email only
            $user_value = $user->name .' ('.$user->id.')';      // Username + (id)

        } else {

            // Without a logged in user -- we'll just use 'system' as the username.
            $user_value = "system";

        }
        return $user_value;
    }

    /**
     * User date stamp formatter.
     * This should just be saving a plain UTC format and then the display and timezone get applied in ehControl.
     *
     * (if it should be different, then change it here)
     *
     * @return false|string
     */
    protected static function formatDateStamp()
    {

        //Check to ensure that the datetime format config variables are available before attempting to use them.
        if ( empty(ehConfig::get('date_format_sql_long')) ) {
            throw new \Exception('Missing date time format configuration in eco-helpers.php');
        }

        return Carbon::now()->format(ehConfig::get('date_format_sql_long'));

    }

}
