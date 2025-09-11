<?php

namespace ScottNason\EcoHelpers\Classes;

use ScottNason\EcoHelpers\Models\ehSetting;
use Illuminate\Support\Facades\Cache;

/**
 * ehConfig is a helper function to provide access to the values contained in both
 * the system eh_settings table or the eco-helpers.php configuration file.
 *
 */

class ehConfig
{

    public static $combined_config = [];       // The combined settings table and eco-helpers file.


    /**
     * Pull the single settings record from the settings table and then add the eco-config.php file to it.
     * Without a $parameter it returns the combined table + eco-helpers config file.
     * With a $parameter it will return just that key value.
     * @param $parameters
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function get($parameters = null) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Create the internal $combined_config array from the combined settings table and eco-helpers config file.
        self::initializeSettingsArray();


        ////////////////////////////////////////////////////////////////////////////////////////////
        // If a specific parameter is not called for then return the whole combined collection.
        if (empty($parameters)) {
            return self::$combined_config;
        } else {

            // Convert the simple months integer into the appropriate php date format ready to use in Laravel validation rules
            if ($parameters == 'date_validation_postdate' || $parameters == 'date_validation_backdate') {
                return self::formatValidationDates($parameters);
            }


            // How we make this multi-level aware? param_top.next_level.deeper_level.etc
            return self::recurseParameters($parameters, self::$combined_config);


            // If we're calling for a specific parameter -- check if it's real then return it (or an error message).
            /*
            if (isset($combined_config->$parameter)) {
                return $combined_config->$parameter;
            } else {
                return 'Error (ehConfig): parameter '.$parameter.' not found.';
            }
            */


        }

    }


    /**
     * Recursive check to drill-down into the dot parameters to pull out just that data.
     *
     * @param $parameters           // The "key" (or multi-level keys) we're asking for.
     * @param $data                 // The $combined_config array; systematically broken down into smaller and smaller pieces.
     * @return mixed|void           // The resultant key value.
     */
    protected static function recurseParameters($parameters, $data = null) {

        // Note: $data starts out as the whole $combined_config array and then at each pass just sends along the called for "leg"
        // or key to the next recursive check.

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Break apart the passed input $parameters into pieces (on the dot) and recursively check the leftover part.
        // Limit to breaking into to pieces - the 0 part to check and the 1 part to pass on.
        $param_array = explode('.',$parameters,2);

        // If we have more than one part, then run this recursive check again.
        if (count($param_array)>1) {

            // Pull out this "leg" of the settings and pass it onto the next recursive check.
            $new_data = $data[$param_array[0]];
            // Pass the second part of the input parameter for another check.
            return self::recurseParameters($param_array[1], $new_data);

        } else {

            // If we only have one part of the input $parameters, then just return that part of the $data.
            //if (isset($data[$param_array[0]])) {          // This errors our on an empty of null entry
            if (key_exists($param_array[0], $data)) {       // So just check to see if the actual key exists.
                return $data[$param_array[0]];              // Just return this key from settings.
            } else {

                // For now throwing a message -- not sure if we should just return false or null ??
                throw new \Exception('Error (eco-helpers): parameter "'.$param_array[0].'" not found.');
                //return 'Error (ehConfig): parameter "'.$param_array[0].'" not found.';
            }

        }

    }

    /**
     * Build the $combined_config array by combining the setup information in the settings table
     * with the information in the eco-helpers.php config file.
     *
     * @return void
     */
    protected static function initializeSettingsArray() {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Add caching to keep this from doing a query every single time.
        //$users = Cache::remember('all_users', 60, function () {
        //    return User::all();
        //});
        // NOT HELPING??
        $config_set = Cache::remember('config_set', 60, function () {
            return ehSetting::find(1);
        });



        ////////////////////////////////////////////////////////////////////////////////////////////
        // 0. Do we have a settings table entry?
        // If not, create the first time setup defaults.
        // THIS IS HAMMERING QUERIES TO THE DB.

        //if (!ehSetting::find(1)) {
        if (!$config_set) {

            // throw new \Exception('Error: missing settings table entries. Please run /settings to initialize.');
            // Since the ehBaseController depends on this -- we need to ensure that it's instantiated here.
            // Note: the /settings route redirects to ehSettingsController@create() which depends on the settings being there, so we get stuck.

            // Moved the code from the ehSettingsController@create() method to here:

            $setting = new ehSetting();

            // Set the OOTB package defaults for the settings table.
            $setting->site_lockout = 0;
            $setting->system_banner ='<strong>Eco Helpers</strong> Banner';
            $setting->system_banner_blink = 0;
            $setting->message_welcome ='Welcome to Eco Helpers!';
            $setting->message_jumbotron ='This is our Home page.';
            $setting->message_copyright ='Copyright (C)2023, NasonProductions';
            $setting->date_validation_low ='2010-01-01';

            $setting->default_time_zone ='America/New_York';
            $setting->site_contact_email ='Admin@mysite.com';
            $setting->site_contact_name ='Admin';
            $setting->default_from_email ='Admin@mysite.com';
            $setting->default_from_name ='Admin';
            $setting->default_subject_line ='WEBSITE INQUIRY';
            $setting->logout_timer =(120*60);

            $setting->minimum_password_length =8;
            $setting->days_to_lockout = 180;
            $setting->failed_attempts = 5;
            $setting->failed_attempts_timer = 10;

            // !! This causes an infinite loop !!
            //$setting->save();                 // This is using the trait ehHasUserstamps -- which checks ehConfig!!

            $setting->created_by = 'system';    // This has to be defined here since not using the ehHasUserstamps trait.
            $setting->updated_by = 'system';    // This has to be defined here since not using the ehHasUserstamps trait.
            $setting->saveQuietly();            // Does not use any Model events.

        } /*else {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // 1. Get the single record from the settings table
            self::$combined_config = ehSetting::find(1)->toArray();
        }*/


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 1. Get the single record from the settings table
        //self::$combined_config = ehSetting::find(1)->toArray();
        self::$combined_config = $config_set->toArray();


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 2. Get the 2 version file variables to include in the master $setting array.
        $version_file = include(__DIR__.'/../version.php');
        self::$combined_config['eh-app-version'] = $version_file['eh-app-version'];
        self::$combined_config['eh-last-update'] = $version_file['eh-last-update'];


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Add the eco-config file to the settings record.
        foreach(config('eco-helpers') as $key => $value) {
            self::$combined_config[$key] = $value;
        }
    }


    protected static function formatValidationDates($parameters)
    {
        ////////////////////////////////////////////////////////////////////////////////////////////
        // Create the validation variables from the system settings for backdate and postdate (remember that a value of "0" is today).
        // Note: we're having to build the whole date variable here so we can include the format W/O h:m:i !!

        if ($parameters == 'date_validation_postdate' ) {
            if (empty(self::$combined_config['date_validation_postdate'])) {
                return date('m/d/Y',strtotime('now'));
            } else {
                return date('m/d/Y',strtotime('+'.self::$combined_config['date_validation_postdate'].' month'));
            }
        }

        if ($parameters == 'date_validation_backdate' ) {
            if (empty(self::$combined_config['date_validation_backdate'])) {
                return date('m/d/Y',strtotime('now'));
            } else {
                return date('m/d/Y',strtotime('-'.self::$combined_config['date_validation_backdate'].' month'));
            }
        }

        return null;
    }


}










