<?php

namespace ScottNason\EcoHelpers\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use ScottNason\EcoHelpers\Classes\ehConfig;

/**
 * This trait is responsible for maintaining the ecoFramework fields:
 * created_by
 * created_at
 * updated_by
 * updated_at
 *
 * This is included in the ehBaseModel.
 *
 */
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
     * Return the format as configured in the eco-helpers config file
     * under 'user_update_stamp'
     *
     * If there is no user logged in, then return 'system' as the username.
     *
     * @return string       // Returns the contents of $user_value which is the formatted user stamp
     *                      // for created_by and updated_by.
     */

    protected static function formatUserStamp()
    {

        $user = Auth::user();

        if ($user) {

            // This is the format definition of how what we want the user name to look like in the updated_by field.
            // Note: this is set in the eco-helpers config file under 'user_update_stamp'

            // Default, out of the box user name stamp.
            $user_value = $user->name .' ('.$user->id.')';      // Username + (id)

            // Separate out the $field names from the text
            // and then just concat them all together in the $user_value.
            $uc = ehConfig::get('user_update_stamp');
            $user_value = '';
            foreach($uc as $attr) {
                if (substr($attr,0, 1) == '$') {
                    // This is a field name
                    $attr = ltrim($attr, '$');    // Remove the prepended $ before using this.
                    $user_value .= $user->$attr;
                } else {
                    // This is just text to include as is.
                    $user_value .= $attr;
                }
            }

        } else {

            // But if there is no logged in user -- we'll just use 'system' as the username.
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
        if ( empty(ehConfig::get('date_sql_long')) ) {
            throw new \Exception('Missing date time format configuration in eco-helpers.php');
        }

        return Carbon::now()->format(ehConfig::get('date_sql_long'));

    }

}
