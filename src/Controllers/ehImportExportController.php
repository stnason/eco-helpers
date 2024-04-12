<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehCSV;


/**
 * Import Export functions
 *
 * Note: Export is still a "route" so it will need to have an associated permissions by role just to get here.
 *       After that, it will check the "calling" route to see if the user actually
 *        has Export Table permissions on that route.
 *
 */
class ehImportExportController extends ehBaseController
{

    /*
     * A list of resource route names along with the table they are responsible for maintaining.
     */
    public $route_to_tables = [
        'pages'=>'eh_pages'
    ];


    /*
     *TODO: Need to add a good explanation of what's going on with the security here in the manual.
     * First the user needs access to the /export route (just to get in the front door)
     * Then they need ACCESS_EXPORT_TABLE rights on the "calling" route to pass through.
     * We'll check that calling route by itself and with ".resource" appended to it.
     */

    public function export($table_name_parameter = null) {

        // NOTE:
        // It appears that previous() works as intended when we "link" here from a page.
        // But it DOES NOT work when we simply type /export/name into the browser!
        //  (in that case, previous() = /export!)

        ///////////////////////////////////////////////////////////////////////////////////////////
        // We need to know where we just came from so, get the route before this export function:
        // Note: previous() returns the whole uri so we'll need to break it up from here.
        $calling_url = explode('/',url()->previous());
        $calling_route = $calling_url['3'];

        // Direct entered on the browser address line. There is no "calling" route.
        if ($calling_route == 'export') {
            return null;
        }


        // SECURITY (part 1): The user role must have access to the route "export"
        //                    That basically opens up this route to anyone with that permission.
        //                    Below we will check to see if they have Export Table permissions
        //                    on the calling route.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Initialize the internal table name variable to a random name.
        // Dummy table (not real) to force the sql query to return nothing
        //  (if for any reason this doesn't pick up another value along the way. Just to be safe.)
        $this->table_name = 'xyzz123zzb';

        ///////////////////////////////////////////////////////////////////////////////////////////
        // $table_name is an optional parameter.
        if (!empty($table_name_parameter)) {
            $this->table_name = $table_name_parameter;
        } else {
            $this->table_name = $calling_route;
        }

        //dd($table_name_parameter, $this->table_name, $calling_url);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Replace any dashed (-) with underscores (_).
        // Some of the resource routes may use dashes -- ALL of the SQL tables should use the underscore.
        // But keep the original table name for the SECURITY checks below!
        $original_table_name = $this->table_name;
        $this->table_name = str_replace('-','_',$this->table_name);



        ///////////////////////////////////////////////////////////////////////////////////////////
        // SECURITY (part 2): What method called this one and does the user's role have
        //                    Export Table permissions on that route?




        // 1. Check the custom case for any table that are NOT named the same as their resource controllers.
        //TODO: I think the best thing to do here is provide an array of resource names to table names that the
        // can then be overridden to provide this information.
        /*
        if ($this->table_name == 'users') {
            // Check for Table Export rights for any table that has a different name than its resource route.
            if (ehAccess::chkUserResourceAccess(Auth()->user(),'contacts.resource',ACCESS_EXPORT_TABLE)) {
                goto done_checking;
            }
        }
        */


        // 2. Check the table_name.resource route for permissions.
        // Check for Table Export rights for the calling method's permission on the current user's role.
        // NOTE: This is done on the resource route only!
        if (
            ehAccess::chkUserResourceAccess(Auth()->user(),$this->table_name.'.resource',ACCESS_EXPORT_TABLE) ||
            ehAccess::chkUserResourceAccess(Auth()->user(),$original_table_name.'.resource',ACCESS_EXPORT_TABLE)
        ) {
            goto done_checking;
        }


        // 3. Check the calling route only for permissions.
        // Check for Table Export rights on the calling route.
        if (ehAccess::chkUserResourceAccess(Auth()->user(),$calling_route,ACCESS_EXPORT_TABLE)) {
            goto done_checking;
        } else {
            return redirect(route()->previous);      // If none of the other checks pass then return from here and do nothing.
        }

        // If you got here; you've passed the previous security gauntlet.
        // Time to attempt to export the data.
        done_checking:


        //TODO: I think there should be another variable (that could be overridden as needed)
        // that sets the maximum number of records you'll allow to be exported.
        // Case in point is the Activities or Meters table in the original eesfm
        // -- Neither is practical for export since they have nearly half a million records.


        // Unless individual processing is needed, this is the generic -- export all -- for any table.
        ///////////////////////////////////////////////////////////////////////////////////////////
        ini_set('max_execution_time', 300);

        // Let's start out by making sure the table exists:
        if (!Schema::hasTable($this->table_name)) {

            //dd($this->table_name, $table_name_parameter, $calling_url, $calling_url['3'], Schema::hasTable($this->table_name));
            return redirect()->back();              // If this is not a valid table name then go back.
        }


        $resultset = DB::select("SELECT * FROM ".$this->table_name);        // These returns the dates just like they are in sql

        // TODO: We need to add the ability to pass a WHERE criteria (do we want to go as far as WHERE, ORDER, LIMIT?)
        //$resultset = DB::select("SELECT * FROM ".$this->table_name." WHERE serial_number LIKE 'E%'; ");

        // For some reason this (my) export is way faster than the Laravel Excel (maatweb) -- and slightly faster than Fast-Excel
        // Note: if you don't return in front, the bottom of the generated file will have part of the next page on it.
        //return Utility::outputCSV(Schema::getColumnListing($this->table_name),$resultset,Utility::getFilename($this->table_name));
        $name_p1 = $this->table_name;      // The name of the requested table
        $name_p2 = config('app.env');      // The current App Environment
        return ehCSV::outputCSV(Schema::getColumnListing($this->table_name),$resultset,$name_p1, $name_p2);


    }
}